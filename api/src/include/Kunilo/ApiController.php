<?php
declare(strict_types=1);

namespace Kunilo;

use Boronczyk\Alistair\DbAccess;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Swift_Mailer;

class ApiController extends DbAccess
{
    protected $jwtEncoder;
    protected $jwtDecoder;
    protected $mailer;
    protected $twig;

    public function __construct(\PDO $db)
    {
        parent::__construct($db);
    }

    public function setJwtEncoder(callable $encoder)
    {
        $this->jwtEncoder = $encoder;
    }

    public function setJwtDecoder(callable $decoder)
    {
        $this->jwtDecoder = $decoder;
    }

    public function setMailer(Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function setTwig(Twig $twig)
    {
        $this->twig = $twig;
    }

    protected function encodeJwt(array $fields): string
    {
        return call_user_func($this->jwtEncoder, $fields);
    }

    protected function decodeJwt(string $token): array
    {
        try {
            return (array)call_user_func($this->jwtDecoder, $token);
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function generateCode(int $length): string
    {
        $code = bin2hex(openssl_random_pseudo_bytes($length * 2));
        $code = preg_replace('/[^\d]/', '', $code);
        $code = substr($code, 0, $length);
        return $code;
    }

    public function getUsername(Request $req, Response $resp, array $args):
        Response
    {
        $username = $args['username'];

        $count = (int)$this->queryValue(
            'SELECT COUNT(`id`) FROM `accounts` WHERE `username` = ?'
            [$username]
        );

        if ($count == 0) {
            return $resp->withStatus(404);
        }

        return $resp->withJson([
            'username' => $username
        ]);
    }

    public function getEmail(Request $req, Response $resp, array $args):
        Response
    {
        $email = rawurldecode($args['email']);

        $count = (int)$this->queryValue(
            'SELECT COUNT(`id`) FROM `accounts` WHERE `email` = ?',
            [$email]
        );

        if ($count == 0) {
            return $resp->withStatus(404);
        }

        return $resp->withJson([
            'email' => $email
        ]);
    }

    public function authenticate(Request $req, Response $resp, array $args):
        Response
    {
        $data = $req->getParsedBody();

        // authentication can be performed with either username:password
        // (standard sign in) or email:code (sign in for password reset)
        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';
        $email = trim($data['email'] ?? '');
        $code = trim($data['code'] ?? '');

        if (!empty($username) && !empty($password)) {
            return $this->authenticateUsername($resp, $username, $password);
        }
        if (!empty($email) && !empty($code)) {
            return $this->authenticateEmail($resp, $email, $code);
        }

        return $resp->withJson([
            'error' => 'Missing required fields'
        ], 400);
    }

    protected function authenticateUsername(Response $resp, string $username,
        string $password): Response
    {
        $row = $this->queryRow(
            'SELECT `id`, `password` FROM `accounts` WHERE `username` = ?',
            [$username]
        );

        if (!$row || !password_verify($password, $row['password'])) {
            return $resp->withJson([
                'error' => 'Authentication failed'
            ], 401);
        }

        return $resp->withJson([
            'token' => $this->encodeJwt([
                'userid' => $row['id'],
                'username' => $username
            ])
        ]);
    }

    protected function authenticateEmail(Response $resp, string $email,
        string $code): Response
    {
        $row = $this->queryRow(
            'SELECT `id`, `username`, `code` FROM `accounts` WHERE
             `email` = ? AND `code_time` > DATE_SUB(NOW(), INTERVAL 2 HOUR)',
            [$email]
        );

        if (!$row && !password_verify($code, $row['code'])) {
            return $resp->withJson([
                'error' => 'Authentication failed'
            ], 401);
        }

        $this->query(
            'UPDATE `accounts` SET `code` = NULL, `code_time` = NULL
             WHERE `id` = ?',
             [$row['id']]
        );

        return $resp->withJson([
            'token' => $this->encodeJwt([
                'userid' => $row['id'],
                'username' => $row['username']
            ])
        ]);

    }

    public function getResetCode(Request $req, Response $resp, array $args):
        Response
    {
        $data = $req->getParsedBody();

        $email = trim($data['email'] ?? '');

        if (empty($email)) {
            return $resp->withJson([
                'error' => 'Missing required fields'
            ], 400);
        }

        $user = $this->queryRow(
            'SELECT `id`, `username`, `email` FROM `accounts`
             WHERE `email` = ?',
            [$email]
        );

        if (!$user) {
            // return 202 to prevent fishing
            return $resp->withStatus(202);
        }

        $code = $this->generateCode(6);
        $user['code'] = $code;

        $code = password_hash($code, PASSWORD_BCRYPT);
        $this->query(
            'UPDATE `accounts` SET `code` = ?, `code_time` = NOW()
             WHERE `id` = ?',
            [$code, $user['id']]
        );

        $msg = $this->mailer->createMessage()
            ->setTo([$user['email']])
            ->setFrom($_ENV['EMAIL_SYSTEM_ADDRESS'])
            ->setSubject('Kunilo Password Reset')
            ->setBody($this->twig->fetch('password-reset.html', $user), 'text/html')
            ->addPart($this->twig->fetch('password-reset.txt', $user), 'text/plain');

        $this->mailer->send($msg);

        return $resp->withStatus(202);
    }

    public function registerUser(Request $req, Response $resp, array $args):
       Response
    {
        $data = $req->getParsedBody();

        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';
        $email = trim($data['email'] ?? '');

        if (empty($username) || empty($password) || empty($email)) {
            return $resp->withJson([
                'error' => 'Missing required fields'
            ], 400);
        }

        $password = password_hash($password, PASSWORD_BCRYPT);

        try {
            $this->query(
                'INSERT INTO `accounts` (`username`, `password`, `email`)
                 VALUES (?, ?, ?)',
                [$username, $password, $email]
            );
            $id = (int)$this->db->lastInsertId();
        } catch (\PDOException $e) {
            return $resp->withJson([
                'error' => 'Failed to register account'
            ], 403);
        }

        return $resp->withJson([
            'token' => $this->encodeJwt([
                'userid' => $id,
                'username' => $username
            ])
        ], 201);
    }

    public function resetPassword(Request $req, Response $resp, array $args):
        Response
    {
        $data = $req->getParsedBody();

        $token = $data['token'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($password)) {
            return $resp->withJson([
                'error' => 'Missing required fields'
            ], 400);
        }

        $token = $this->decodeJwt($token);
        if (!$token) {
            return $resp->withJson([
                'error' => 'Unauthorized'
            ], 401);
        }

        $id = $token['userid'];
        $password = password_hash($password, PASSWORD_BCRYPT);
        $this->query(
            'UPDATE `accounts` SET `password` = ? WHERE `id` = ?',
            [$password, $id]
        );

        return $resp;
    }
}

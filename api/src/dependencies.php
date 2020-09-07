<?php
declare(strict_types=1);

use Psr\Container\ContainerInterface as Container;
use Firebase\JWT\JWT;
use Kunilo\ApiController;
use Slim\Views\Twig;

return [
    'db' => function (Container $c) {
        $pdo = new PDO($_ENV['DB_DSN'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    },

    'JwtEncoder' => function (Container $c): callable {
        return function (array $token): string {
            return JWT::encode($token, $_ENV['JWT_SECRET']);
        };
    },

    'JwtDecoder' => function (Container $c): callable {
        return function (string $token): array {
            return (array)JWT::decode($token, $_ENV['JWT_SECRET'], ['HS256']);
        };
    },

    Swift_Mailer::class => DI\create()
        ->constructor(new Swift_SmtpTransport(
            $_ENV['SMTP_HOST'],
            $_ENV['SMTP_PORT']
        )),

    Twig::class => function (Container $c): Twig {
        $twig = Twig::create('templates', [
            'debug' => $_ENV['DEBUG'] == 'true'
        ]);
        $twig['env'] = $_ENV;

        return $twig;
    },

    ApiController::class => DI\create()
        ->constructor(DI\get('db'))
        ->method('setJwtEncoder', DI\get('JwtEncoder'))
        ->method('setJwtDecoder', DI\get('JwtDecoder'))
        ->method('setMailer', DI\get(Swift_Mailer::class))
        ->method('setTwig', DI\get(Twig::class))
];

<?php
declare(strict_types=1);

use Slim\Routing\RouteCollectorProxy;
use Kunilo\ApiController;

/** @var $app Slim\App */
$app->group('/api/v1', function (RouteCollectorProxy $group) {
    $group->group('/account', function (RouteCollectorProxy $group) {
        $group->post('/auth', ApiController::class . ':authenticate');
        $group->post('/password', ApiController::class . ':getResetCode');
        $group->get('/email/{email:.+}', ApiController::class . ':getEmail');
        $group->get('/user/{username:\w+}', ApiController::class . ':getUsername');
    });

    $group->group('/user', function (RouteCollectorProxy $group) {
        $group->post('', ApiController::class . ':registerUser');
        $group->post('/password', ApiController::class . ':resetPassword');
    });
});

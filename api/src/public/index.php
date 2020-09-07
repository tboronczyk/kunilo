<?php
declare(strict_types=1);

use Dotenv\Dotenv;
use DI\ContainerBuilder;
use Slim\Factory\AppFactory;

chdir(dirname(__DIR__, 1));
require_once 'vendor/autoload.php';

Dotenv::createImmutable('.')->load();

$builder = new ContainerBuilder();
$builder->addDefinitions('dependencies.php');
$container = $builder->build();

AppFactory::setContainer($container);
$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware($_ENV['DEBUG'] == 'true', true, true);

require_once 'routes.php';

$app->run();

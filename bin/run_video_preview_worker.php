<?php

use Pimple\Container;
use App\Queue\Consumers\VideoPreviewConsumer;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteParser;

require_once __DIR__ . '/../vendor/autoload.php';

try {
    $container = new Container();

    $settings = require __DIR__ . '/../app/settings.php';
    $settings($container);

    $dependencies = require __DIR__ . '/../app/dependencies.php';
    $dependencies($container);

    $app = AppFactory::create();
    $routes = require __DIR__ . '/../app/routes.php';
    $routes($app);
    $routerParser = $app->getRouteCollector()->getRouteParser();
    $container[RouteParser::class] = $routerParser;

    /** @var VideoPreviewConsumer $videoPreviewConsumer */
    $videoPreviewConsumer = $container[VideoPreviewConsumer::class];
    $videoPreviewConsumer->process();
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
    exit(1);
}
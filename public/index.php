<?php

declare(strict_types=1);

use Slim\Factory\AppFactory;
use Pimple\Container;
use Pimple\Psr11\Container as Psr11Container;
use Slim\Views\TwigMiddleware;
use Slim\Views\Twig;
use App\Handlers\HttpErrorHandler;
use App\Views\HttpErrorHandlerView;
use Slim\Middleware\ContentLengthMiddleware;
use App\Middleware\SessionStartMiddleware;
use Slim\Routing\RouteParser;

require_once __DIR__ . '/../vendor/autoload.php';

$container = new Container();

$settings = require __DIR__ . '/../app/settings.php';
$settings($container);

$dependencies = require __DIR__ . '/../app/dependencies.php';
$dependencies($container);

AppFactory::setContainer(new Psr11Container($container));
$app = AppFactory::create();

$routerParser = $app->getRouteCollector()->getRouteParser();
$container[RouteParser::class] = $routerParser;

$callableResolver = $app->getCallableResolver();

$app->addRoutingMiddleware();
$app->add(TwigMiddleware::create($app, $container[Twig::class]));
$app->addBodyParsingMiddleware();
$app->add($container[ContentLengthMiddleware::class]);
$app->add($container[SessionStartMiddleware::class]);

$responseFactory = $app->getResponseFactory();
$errorHandler = new HttpErrorHandler(
    $callableResolver,
    $responseFactory,
    $container[HttpErrorHandlerView::class]
);
$dispalyErrorDetails = $container['settings']['displayErrorDetails'] ??
    throw new Exception('Отсутствует ключ settings или displayErrorDetails в массиве');    
$errorMiddleware = $app->addErrorMiddleware($dispalyErrorDetails, true, true);
$errorMiddleware->setDefaultErrorHandler($errorHandler);

$routes = require __DIR__ . '/../app/routes.php';
$routes($app);

$app->run();
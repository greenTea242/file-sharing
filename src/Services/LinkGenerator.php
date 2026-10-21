<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Interfaces\RouteParserInterface as RouteParser;
use Slim\Routing\RouteContext;
use Slim\Interfaces\RouteInterface as Route;

/**
 * Генератор ссылок
 */
class LinkGenerator
{
    private RouteParser $routeParser;
    private Route $route;

    /**
     * Конструктор
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $routeContext = RouteContext::fromRequest($request);
        $this->routeParser = $routeContext->getRouteParser();
        $route = $routeContext->getRoute();
        if (is_null($route)) {
            throw new Exception('Маршрут должен быть заполнен');
        }
        $this->route = $route;
    }

    /**
     * Сгенерировать сссылку
     *
     * @param string $routeName
     * @param array<string, string> $queryParams
     * @param array<string, string> $data
     * @return string
     */
    public function generate(string $routeName, array $queryParams = [], array $data = []): string
    {
        return $this->routeParser->urlFor($routeName, $data, $queryParams);
    }

    /**
     * Получить ссылку для страницы
     *
     * @param integer $page
     * @return string
     */
    public function getLinkForPage(int $page): string
    {
        $routeName = $this->route->getName();
        $routeArgs = $this->route->getArguments();
        $queryParams = ['page' => (string)$page];
        return $this->routeParser->urlFor($routeName, $routeArgs, $queryParams);
    }
}

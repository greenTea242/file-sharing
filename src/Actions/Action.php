<?php

declare(strict_types=1);

namespace App\Actions;

use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;

/**
 * Действие пользователя
 */
abstract class Action
{
    /**
     * Метод __invoke
     *
     * @param Request $request
     * @param Response $response
     * @param array<string> $args
     * @return Response
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        return $this->action($request, $response, $args);
    }

    /**
     * Запустить действие
     *
     * @param Request $request
     * @param Response $response
     * @param array<string> $args
     * @return Response
     */
    abstract protected function action(Request $request, Response $response, array $args): Response;

    /**
     * Переадресовать пользователя на URL
     *
     * @param string $url
     * @param integer $code
     * @param Response $response
     * @return Response
     */
    protected function redirect(
        string $url,
        int $code,
        Response $response
    ): Response {
        return $response->withHeader('Location', $url)->withStatus($code);
    }

    /**
     * Получить ip запроса
     *
     * @param Request $request
     * @return string
     */
    protected function getRequestIp(Request $request): string
    {
        return $request->getServerParams()['REMOTE_ADDR'] ?? 
            throw new Exception('Отсутствует ключ в в REMOTE_ADDR в массиве');
    }

    /**
     * Получить аргумент
     *
     * @param Request $request
     * @param array<string> $args
     * @param string $name
     * @return string
     */
    protected function resolveArg(Request $request, array $args, string $name): string
    {
        if (!isset($args[$name])) {
            throw new HttpBadRequestException($request, "Не удалось получить аргумент `{$name}`.");
        }

        return $args[$name];
    }

    /**
     * Получить страницу
     *
     * @param Request $request
     * @return integer
     */
    protected function getPage(Request $request): int
    {
        $queryParams = $request->getQueryParams();
        $pageRaw = $queryParams['page'] ?? null;
        return is_null($pageRaw) ? 1 : (int)($pageRaw);
    }    
}

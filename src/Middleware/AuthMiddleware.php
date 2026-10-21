<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Services\UserAuthService;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpUnauthorizedException;

/**
 * Middleware для аутентификации
 */
class AuthMiddleware implements Middleware
{
    private UserAuthService $userAuthService;

    /**
     * Конструктор
     *
     * @param UserAuthService $userAuthService
     */
    public function __construct(
        UserAuthService $userAuthService
    ) {
        $this->userAuthService = $userAuthService;    
    }

    /**
     * Запустить middleware
     *
     * @param Request $request
     * @param RequestHandler $handler
     * @return Response
     */
    public function process(Request $request, RequestHandler $handler): Response
    {        
        if (!$this->userAuthService->canAccessUser($request)) {
            throw new HttpUnauthorizedException($request, 'Доступ к запрошенному ресурсу запрещён');
        }
        $response = $handler->handle($request);
        return $response;
    }
}

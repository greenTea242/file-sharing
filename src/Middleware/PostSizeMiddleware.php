<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use App\Exceptions\HttpPayloadTooLargeException;
use App\Utilities\FileUtils;

/**
 * Middleware для размера POST запроса
 */
class PostSizeMiddleware implements Middleware
{
    /**
     * Запустить middleware
     *
     * @param Request $request
     * @param RequestHandler $handler
     * @return Response
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        $maxPostSize = $this->getPostMaxSize();
        $contentLength = (int)$request->getHeaderLine('Content-Length');

        if ($contentLength > $maxPostSize) {
            throw new HttpPayloadTooLargeException($request, 'Превышен размер запроса');        
        }
        
        $response = $handler->handle($request);
        return $response;
    }

    /**
     * Получить максимальный размер POST запроса
     *
     * @return integer
     */
    private function getPostMaxSize(): int
    {
        $maxPostSizeRaw = ini_get('post_max_size');
        $maxPostSize = FileUtils::getBytes($maxPostSizeRaw);
        return $maxPostSize;
    }
}

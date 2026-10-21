<?php

declare(strict_types=1);

namespace App\Handlers;

use Psr\Http\Message\ResponseFactoryInterface as ResponseFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface as Logger;
use Slim\Handlers\ErrorHandler;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpForbiddenException;
use App\Exceptions\HttpPayloadTooLargeException;
use Slim\Interfaces\CallableResolverInterface as CallableResolver;
use App\Views\HttpErrorHandlerView;
use Slim\Exception\HttpUnauthorizedException;

/**
 * Обработчик HTTP ошибок
 */
class HttpErrorHandler extends ErrorHandler
{
    private HttpErrorHandlerView $view;

    /**
     * Конструктор
     *
     * @param CallableResolver $callableResolver
     * @param ResponseFactory $responseFactory
     * @param HttpErrorHandlerView $view
     * @param Logger|null $logger
     */
    public function __construct(
        CallableResolver $callableResolver,
        ResponseFactory $responseFactory,
        HttpErrorHandlerView $view,
        ?Logger $logger = null
    ) {
        parent::__construct($callableResolver, $responseFactory, $logger);
        $this->view = $view;
    }

    /**
     * Вернуть ответ
     *
     * @return Response
     */
    protected function respond(): Response
    {
        $exception = $this->exception;
        $message = '';
        if ($exception instanceof HttpNotFoundException) {
            $statusCode = 404;
            $message = 'Страница не найдена';
        } elseif ($exception instanceof HttpForbiddenException) {
            $statusCode = 403;
            $message = 'Доступ к запрошенному ресурсу запрещён';
        } elseif ($exception instanceof HttpPayloadTooLargeException) {
            $statusCode = 413;
            $message = $exception->getMessage();
        } elseif ($exception instanceof HttpUnauthorizedException) {
            $statusCode = 401;
            $message = $exception->getMessage();
        } else {
            $statusCode = 500;
            $message = 'Внутренняя ошибка';

        }
        $response = $this->responseFactory->createResponse($statusCode);
        return $this->view->render($response, [
            'statusCode' => $statusCode,
            'message' => $message,
            'errorDetails' => $this->displayErrorDetails ? $exception : null,
            'activeTab' => null
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use App\Exceptions\HttpPayloadTooLargeException;
use App\Factories\FormFactories\FileUploadFormFactory;
use App\Validators\FileUploadFormValidator;
use App\Validators\FileUploadFormFileValidator;

/**
 * Middleware для размера файла
 */
class FileSizeMiddleware implements Middleware
{
    private FileUploadFormFactory $fileUploadFormFactory;
    private FileUploadFormValidator $fileUploadFormValidator;

    /**
     * Конструктор
     *
     * @param FileUploadFormFactory $fileUploadFormFactory
     * @param FileUploadFormValidator $fileUploadFormValidator
     */
    public function __construct(
        FileUploadFormFactory $fileUploadFormFactory,
        FileUploadFormValidator $fileUploadFormValidator
    ) {
        $this->fileUploadFormFactory = $fileUploadFormFactory;
        $this->fileUploadFormValidator = $fileUploadFormValidator;
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
        $formData = $request->getParsedBody();
        $formFile = $request->getUploadedFiles()['file'] ?? null;
        $form = $this->fileUploadFormFactory->create(formData: $formData, file: $formFile);
        $errors = $this->fileUploadFormValidator->validate($form);
        $errorTooBigFile = $errors['file'][FileUploadFormFileValidator::ERROR_TOO_BIG] ?? null;

        if ($errorTooBigFile) {
            throw new HttpPayloadTooLargeException($request, $errorTooBigFile);
        }
        
        $response = $handler->handle($request);
        return $response;
    }
}

<?php

declare(strict_types=1);

namespace App\Actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Factories\FormFactories\FileUploadFormFactory;
use Slim\Flash\Messages;
use App\Views\FileUploadFormView;
use App\Validators\FileUploadFormValidator;
use App\Actions\Action;
use App\Factories\UserFactory;
use App\Services\UserAuthService;

/**
 * Действие для показа формы для загрузки файла
 */
class ShowFileUploadFormAction extends Action
{
    private FileUploadFormFactory $fileUploadFormFactory;
    private Messages $messages;
    private FileUploadFormView $view;
    private FileUploadFormValidator $fileUploadFormValidator;
    private UserAuthService $userAuthService;

    /**
     * Конструктор
     *
     * @param FileUploadFormFactory $fileUploadFormFactory
     * @param Messages $messages
     * @param FileUploadFormView $view
     * @param FileUploadFormValidator $fileUploadFormValidator
     * @param UserAuthService $userAuthService
     */
    public function __construct(
        FileUploadFormFactory $fileUploadFormFactory,
        Messages $messages,
        FileUploadFormView $view,
        FileUploadFormValidator $fileUploadFormValidator,
        UserAuthService $userAuthService
    ) {
        $this->fileUploadFormFactory = $fileUploadFormFactory;
        $this->messages = $messages;
        $this->view = $view;
        $this->fileUploadFormValidator = $fileUploadFormValidator;
        $this->userAuthService = $userAuthService;
    }

    /**
     * {@inheritdoc}
     */
    protected function action(
        Request $request,
        Response $response,
        array $args
    ): Response {
        $user = $this->userAuthService->getUser($request);
        $errors = $this->messages->getFirstMessage('fileUploadFormErrors');
        $fileUploadFormData = $this->messages->getFirstMessage('fileUploadFormData');

        if (empty($fileUploadFormData)) {
            $fileUploadForm = $this->fileUploadFormFactory->createEmpty();
        } else {
            $fileUploadForm = $this->fileUploadFormFactory->create($fileUploadFormData);
        }

        $context = [
            'activeTab' => 'fileUpload',
            'fileUploadForm' => $fileUploadForm,
            'errors' => $errors,
            'fileUploadFormValidator' => $this->fileUploadFormValidator,
            'user' => $user
        ];
        return $this->view->render($response, $context);
    }
}

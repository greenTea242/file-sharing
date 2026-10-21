<?php

declare(strict_types=1);

namespace App\Actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Views\FileView;
use App\Factories\FileFactories\UploadedFileFactory;
use App\Actions\Action;
use App\Database\CommentGateway;
use App\Database\TableDataGateway;
use App\Entities\Comments\CommentTree;
use App\Entities\Files\UploadedFiles\UploadedFile;
use App\Entities\User;
use App\Factories\FormFactories\CommentFormFactory;
use App\Factories\UserFactory;
use App\Forms\CommentForm;
use App\Services\CommentServices\CommentTreeFactory;
use App\Services\UserAuthService;
use App\Validators\CommentFormValidator;
use Slim\Flash\Messages;

/**
 * Действие для показа файла
 */
class ShowFileAction extends Action
{
    private UploadedFileFactory $uploadedFileFactory;
    private FileView $view;
    private CommentFormFactory $commentFormFactory;
    private Messages $messages;
    private CommentFormValidator $commentFormValidator;
    private UserAuthService $userAuthService;

    /**
     * Конструктор
     *
     * @param FileView $view
     * @param UploadedFileFactory $uploadedFileFactory
     * @param CommentFormFactory $commentFormFactory
     * @param Messages $messages
     * @param CommentFormValidator $commentFormValidator
     * @param UserAuthService $userAuthService
     */
    public function __construct(
        FileView $view,
        UploadedFileFactory $uploadedFileFactory,
        CommentFormFactory $commentFormFactory,
        Messages $messages,
        CommentFormValidator $commentFormValidator,
        UserAuthService $userAuthService
    ) {
        $this->view = $view;
        $this->uploadedFileFactory = $uploadedFileFactory;
        $this->commentFormFactory = $commentFormFactory;
        $this->messages = $messages;
        $this->commentFormValidator = $commentFormValidator;
        $this->userAuthService = $userAuthService;
    }

    /**
     * {@inheritdoc}
     */
    protected function action(Request $request, Response $response, array $args): Response
    {
        $user = $this->userAuthService->getUser($request);
        $file = $fileSpecificData = $commentForm = $errors = null;
        $context = $this->getContext(user: $user);    
        $id = $this->resolveArg($request, $args, 'id');

        if (!TableDataGateway::checkIdFormat($id)) {
            return $this->view->render($response, $context);
        }

        $id = (int)$id;
        $file = $this->uploadedFileFactory->createById($id);

        if (is_null($file)) {
            return $this->view->render($response, $context);
        }

        $fileSpecificData = $file->getSpecificData();
        $errors = $this->messages->getFirstMessage('commentFormErrors');        
        $commentFormData = $this->messages->getFirstMessage('commentFormData');

        if (empty($commentFormData)) {
            $commentForm = $this->commentFormFactory->createEmpty($file);
        } else {
            $commentForm = $this->commentFormFactory->create($commentFormData);
        }

        $context = $this->getContext(
            user: $user,
            file: $file,
            fileSpecificData: $fileSpecificData,
            commentForm: $commentForm,
            errors: $errors
        );
        return $this->view->render($response, $context);
    }

    /**
     * Получить контекст
     *
     * @param User $user
     * @param UploadedFile|null $file
     * @param array<string, mixed>|null $fileSpecificData
     * @param CommentForm|null $commentForm
     * @param array<string, mixed>|null $errors
     * @return array<string, mixed>
     */
    private function getContext(
        User $user,
        ?UploadedFile $file = null, 
        ?array $fileSpecificData = null, 
        ?CommentForm $commentForm = null,
        ?array $errors = null): array {
        return [
            'file' => $file,            
            'fileSpecificData' => $fileSpecificData,
            'commentForm' => $commentForm,
            'errors' => $errors,
            'activeTab' => null,
            'commentFormValidator' => $this->commentFormValidator,
            'user' => $user
        ]; 
    }
}

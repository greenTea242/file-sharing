<?php

declare(strict_types=1);

namespace App\Actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Actions\Action;
use App\Database\CommentGateway;
use App\Entities\User;
use App\Factories\CommentFactories\CommentFactory;
use App\Factories\FormFactories\CommentFormFactory;
use App\Factories\UserFactory;
use App\Forms\CommentForm;
use App\Services\CommentService;
use App\Services\LinkGenerator;
use App\Services\UserAuthService;
use App\Validators\CommentFormValidator;
use Slim\Flash\Messages;

/**
 * Действие для добавления комментария
 */
class AddCommentAction extends Action
{
    private CommentGateway $commentGateway;
    private CommentFormFactory $commentFormFactory;
    private CommentFormValidator $commentFormValidator;
    private Messages $messages;
    private CommentFactory $commentFactory;
    private UserAuthService $userAuthService;

    /**
     * Конструктор
     *
     * @param CommentGateway $commentGateway
     * @param CommentFormFactory $commentFormFactory
     * @param CommentFormValidator $commentFormValidator
     * @param Messages $messages
     * @param CommentFactory $commentFactory
     * @param UserAuthService $userAuthService
     */
    public function __construct(
        CommentGateway $commentGateway,
        CommentFormFactory $commentFormFactory,
        CommentFormValidator $commentFormValidator,
        Messages $messages,
        CommentFactory $commentFactory,
        UserAuthService $userAuthService
    ) {
        $this->commentGateway = $commentGateway;
        $this->commentFormFactory = $commentFormFactory;
        $this->commentFormValidator = $commentFormValidator;
        $this->messages = $messages;
        $this->commentFactory = $commentFactory;
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
        $formData = $request->getParsedBody();
        $form = $this->commentFormFactory->create($formData, false);
        $commentsCount = 0;

        if (!is_null($form->parentId)) {
            $commentsCount = $this->commentGateway->countComments($form->parentId);
        }

        $redirectUrl = $this->getRedirectUrl($request, $form);
        $errors = $this->commentFormValidator->validate($form, $commentsCount);

        if ($errors) {
            $this->messages->addMessage('commentFormErrors', $errors);
            $this->messages->addMessage('commentFormData', $formData);
            return $this->redirect(
                url: $redirectUrl,
                code: 303,
                response: $response
            );
        }

        $this->commentGateway->beginTransaction();
        $user = $this->createUser($request);
        $response = $this->userAuthService->authorizeUser($user, $response);
        $comment = $this->commentFactory->createByCommentForm(
            form: $form,
            commentsCount: $commentsCount,
            user: $user
        );        
        $id = $this->commentGateway->addComment($comment);
        $comment->setId($id);
        $this->commentGateway->commit();
        return $this->redirect(
            url: $redirectUrl,
            code: 302,
            response: $response
        );
    }

    private function getRedirectUrl(Request $request, CommentForm $form): string
    {
        $linkGenerator = new LinkGenerator($request);

        if (is_null($form->uploadedFileId)) {
            return $linkGenerator->generate('files.upload.form');
        } else {
            return $linkGenerator->generate(
                routeName: 'files.show',
                data: ['id' => (string)$form->uploadedFileId]
            );
        }
    }

    /**
     * Создать пользователя
     *
     * @param Request $request
     * @return User
     */
    private function createUser(Request $request): User
    {
        $user = $this->userAuthService->getUser($request);

        if (!$user->isRegistered()) {
            $this->userAuthService->registerUser($user);
        }
        
        return $user;
    }    
}

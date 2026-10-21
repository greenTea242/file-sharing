<?php

declare(strict_types=1);

namespace App\Actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Actions\Action;
use App\Database\UploadedFileGateway;
use App\Database\UserGateway;
use App\Services\LinkGenerator;
use App\Services\Pagination;
use App\Services\UserAuthService;
use App\Views\UserFilesView;

/**
 * Действие для показа файлов пользователя
 */
class ShowUserFilesAction extends Action
{
    private UserAuthService $userAuthService;
    private UserFilesView $view;
    private UploadedFileGateway $uploadedFileGateway;

    /**
     * Конструктор
     *
     * @param UserAuthService $userAuthService
     * @param UserFilesView $view
     * @param UploadedFileGateway $uploadedFileGateway
     */
    public function __construct(
        UserAuthService $userAuthService,
        UserFilesView $view,
        UploadedFileGateway $uploadedFileGateway
    ) {
        $this->userAuthService = $userAuthService;
        $this->view = $view;
        $this->uploadedFileGateway = $uploadedFileGateway;
    }

    /**
     * {@inheritdoc}
     */
    protected function action(
        Request $request,
        Response $response,
        array $args
    ): Response {
        $userId = $this->resolveArg($request, $args, 'id');
        $userId = (int)$userId;
        $filesCount = $this->uploadedFileGateway->countFilesByUserId($userId);
        $page = $this->getPage($request);
        $pagination = new Pagination($filesCount, $page);        
        $user = $this->userAuthService->getUser(
            request: $request,
            filesOffset: $pagination->getOffset(),
            filesLimit: $pagination->recordsPerPage
        );
        $linkGenerator = new LinkGenerator($request);
        $context = [
            'activeTab' => 'userFiles',
            'files' => $user->files,
            'pagination' => $pagination,
            'linkGenerator' => $linkGenerator,
            'user' => $user
        ];
        return $this->view->render($response, $context);
    }
}

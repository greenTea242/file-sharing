<?php

declare(strict_types=1);

namespace App\Actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Views\RecentFilesView;
use App\Database\UploadedFileGateway;
use App\Services\LinkGenerator;
use App\Services\Pagination;
use App\Actions\Action;
use App\Factories\FileFactories\UploadedFileFactory;
use App\Factories\UserFactory;
use App\Services\UserAuthService;

/**
 * Действие для показа последних файлов
 */
class ShowRecentFilesAction extends Action
{
    private UploadedFileGateway $uploadedFileGateway;
    private RecentFilesView $view;
    private UploadedFileFactory $uploadedFileFactory;
    private UserAuthService $userAuthService;

    /**
     * Конструктор
     *
     * @param RecentFilesView $view
     * @param UploadedFileGateway $uploadedFileGateway
     * @param UploadedFileFactory $uploadedFileFactory
     * @param UserAuthService $userAuthService
     */
    public function __construct(
        RecentFilesView $view,
        UploadedFileGateway $uploadedFileGateway,
        UploadedFileFactory $uploadedFileFactory,
        UserAuthService $userAuthService
    ) {
        $this->view = $view;
        $this->uploadedFileGateway = $uploadedFileGateway;
        $this->uploadedFileFactory = $uploadedFileFactory;
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
        $page = $this->getPage($request);
        $filesCount = $this->uploadedFileGateway->countFiles();
        $pagination = new Pagination($filesCount, $page);

        $filesOffset = $pagination->getOffset();
        $files = $this->uploadedFileFactory->createMany(
            $filesOffset,
            $pagination->recordsPerPage
        );

        $linkGenerator = new LinkGenerator($request);
        $context = [
            'activeTab' => 'recentFiles',
            'files' => $files,
            'pagination' => $pagination,
            'linkGenerator' => $linkGenerator,
            'user' => $user
        ];
        return $this->view->render($response, $context);
    }
}

<?php

declare(strict_types=1);

namespace App\Actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Actions\Action;
use App\Factories\FileDownloaderFactory;
use App\Factories\FileFactories\VideoPreviewFactory;
use App\Factories\UserFactory;
use App\Services\UserAuthService;

/**
 * Действие для загрузки видео-превью
 */
class DownloadVideoPreviewAction extends Action
{
    private FileDownloaderFactory $fileDownloaderFactory;
    private string $fileDownloaderType;
    private VideoPreviewFactory $videoPreviewFactory;

    /**
     * Конструктор
     *
     * @param FileDownloaderFactory $fileDownloaderFactory
     * @param string $fileDownloaderType
     * @param VideoPreviewFactory $videoPreviewFactory
     */
    public function __construct(
        FileDownloaderFactory $fileDownloaderFactory,
        string $fileDownloaderType,
        VideoPreviewFactory $videoPreviewFactory
    ) {
        $this->fileDownloaderFactory = $fileDownloaderFactory;
        $this->fileDownloaderType = $fileDownloaderType;
        $this->videoPreviewFactory = $videoPreviewFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function action(
        Request $request,
        Response $response,
        array $args
    ): Response {
        $id = $this->resolveArg($request, $args, 'id');
        $id = (int)$id;
        $preview = $this->videoPreviewFactory->createByIdOrFail($id);
        $fileDownloader = $this->fileDownloaderFactory->create($this->fileDownloaderType);
        return $fileDownloader->download($preview, $response);
    }
}

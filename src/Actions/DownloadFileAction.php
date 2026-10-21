<?php

declare(strict_types=1);

namespace App\Actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Actions\Action;
use App\Factories\FileDownloaderFactory;
use App\Factories\FileFactories\UploadedFileFactory;
use App\Factories\UserFactory;
use App\Services\UserAuthService;

/**
 * Действие для скачивания файла
 */
class DownloadFileAction extends Action
{
    private FileDownloaderFactory $fileDownloaderFactory;
    private string $fileDownloaderType;
    private UploadedFileFactory $uploadedFileFactory;

    /**
     * Конструктор
     *
     * @param FileDownloaderFactory $fileDownloaderFactory
     * @param string $fileDownloaderType
     * @param UploadedFileFactory $uploadedFileFactory
     */
    public function __construct(
        FileDownloaderFactory $fileDownloaderFactory,
        string $fileDownloaderType,
        UploadedFileFactory $uploadedFileFactory
    ) {
        $this->fileDownloaderFactory = $fileDownloaderFactory;
        $this->fileDownloaderType = $fileDownloaderType;
        $this->uploadedFileFactory = $uploadedFileFactory;
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
        $file = $this->uploadedFileFactory->createByIdOrFail($id);
        $fileDownloader = $this->fileDownloaderFactory->create($this->fileDownloaderType);
        return $fileDownloader->download(
            file: $file,
            response: $response,
            saveWithFilename: $file->userFilename
        );
    }
}

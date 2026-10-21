<?php

declare(strict_types=1);

namespace App\Services\FileDownloaders;

use App\Entities\Files\File;
use Psr\Http\Message\ResponseInterface as Response;
use App\Entities\Files\UploadedFiles\UploadedFile;

/**
 * Загрузчик файлов через Apache модуль XSendFile
 */
class XSendFileDownloader extends FileDownloader {
    /**
     * {@inheritdoc}
     */
    public function download(
        File $file, 
        Response $response, 
        ?string $saveWithFilename = null): Response
    {      
        if (is_null($saveWithFilename)) {
            $saveWithFilename = $file->filename;
        }
        $contentDisposition = $this->getContentDisposition($saveWithFilename);
        return $response->withHeader('Content-Type', $file->mediaInfo->getMimeType())
            ->withHeader('Content-Length', (string)$file->mediaInfo->getSize())
            ->withHeader('Content-Disposition', $contentDisposition)
            ->withHeader('X-SendFile', $file->getPath());
    }
}
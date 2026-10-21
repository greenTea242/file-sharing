<?php

declare(strict_types=1);

namespace App\Services\FileDownloaders;

use App\Entities\Files\File;
use Psr\Http\Message\ResponseInterface as Response;
use App\Entities\Files\UploadedFiles\UploadedFile;

/**
 * Загрузчик файлов через PHP
 */
class PhpNativeDownloader extends FileDownloader
{
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
        $stream = $this->getStream($file);
        $contentDisposition = $this->getContentDisposition($saveWithFilename);
        return $response->withHeader('Content-Description', 'UploadedFile Transfer')
            ->withHeader('Content-Length', (string)$file->mediaInfo->getSize())
            ->withHeader('Content-Type', $file->mediaInfo->getMimeType())
            ->withHeader('Content-Disposition', $contentDisposition)
            ->withHeader('Content-Transfer-Encoding', 'binary')
            ->withHeader('Expires', '0')
            ->withHeader('Cache-Control', 'must-revalidate')
            ->withHeader('Pragma', 'public')
            ->withBody($stream);
    }
}

<?php

declare(strict_types=1);

namespace App\Services\FileDownloaders;

use App\Entities\Files\File;
use Psr\Http\Message\ResponseInterface as Response;
use App\Entities\Files\UploadedFiles\UploadedFile;
use Slim\Psr7\Stream;
use Exception;

/**
 * Загрузчик файлов
 */
abstract class FileDownloader
{
    /**
     * Скачать файл
     *
     * @param File $file
     * @param Response $response
     * @param string|null $saveWithFilename
     * @return Response
     */
    abstract public function download(
        File $file, 
        Response $response, 
        ?string $saveWithFilename = null): Response;

    /**
     * Получить HTTP заголовок Content-Disposition
     *
     * @param string $filename
     * @return string
     */
    protected function getContentDisposition(string $filename): string {
        return 'attachment; filename="' . $filename . '"';
    }

    /**
     * Получить поток
     *
     * @param File $file
     * @return Stream
     */
    protected function getStream(File $file): Stream {
        $fh = fopen($file->getPath(), 'rb');
        if (!$fh) {
            throw new Exception('Не удалось открыть файл');
        }
        return new Stream($fh);
    }
}

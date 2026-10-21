<?php

namespace App\Services\FileServices;

use App\Database\ThumbnailGateway;
use App\DTO\FileInfoDTO;
use App\Entities\Files\File;
use App\Entities\Files\UploadedFiles\UploadedFile;
use App\Entities\Files\UploadedFiles\UploadedImage;
use App\Entities\Files\UploadedFiles\UploadedVideo;
use App\Services\FileServices\FileService;
use App\Services\GifService;
use App\Storages\ThumbnailStorage;
use App\Utilities\FileUtils;
use Imagick;
use InvalidArgumentException;
use Exception;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Сервис для миниатюр
 */
class ThumbnailService extends FileService
{
    /**
     * Конструктор
     *
     * @param ThumbnailGateway $thumbnailGateway
     */
    public function __construct(
        ThumbnailGateway $thumbnailGateway
    ) {
        parent::__construct($thumbnailGateway);
    }

    /**
     * @inheritDoc          
     */
    public function getScope(): string
    {
        return File::SCOPE_PUBLIC;
    }

    /**
     * Получить URL
     *
     * @param string $filename
     * @param string $dir     
     * @return string
     */
    public function getUrl(string $filename, string $dir): string
    {
        return $this->getPublicUrl($filename, $dir);
    }

    /**
     * Получить имя файла
     *
     * @param UploadedFile $file
     * @return string
     */
    public function getFilename(UploadedFile $file): string
    {
        $filenameWithoutExt = $this->getFilenameWithoutExt($file);        
        $ext = $this->getExt($file);
        return FileUtils::getFilenameByParts($filenameWithoutExt, $ext);
    }

    /**
     * Создать превью
     *
     * @param integer $maxWidth
     * @param integer $maxHeight
     * @param string $imagePath
     * @param string $thumbnailPath
     * @return void
     */
    public function createThumbnail(
        string $imagePath,
        string $thumbnailPath,
        int $maxWidth = 300,
        int $maxHeight = 300,
    ): void {
        $thumbnail = new Imagick($imagePath);
        $thumbnail->thumbnailImage(
            columns: $maxWidth,
            rows: $maxHeight,
            bestfit: true
        );
        $thumbnail->writeImage($thumbnailPath);
        $thumbnail->clear();
    }

    /**     
     * @inheritDoc
     */
    protected function getParentDir(): string
    {
        $parentDir = 'images/thumbnails';
        $date = gmdate('y/m/d');
        $parentDir .= "/{$date}";
        return $parentDir;
    }

    /**
     * Получить расширение
     *
     * @param UploadedFile $file
     * @return string
     */
    private function getExt(UploadedFile $file): string
    {
        if ($file instanceof UploadedImage) {
            return FileUtils::EXTENSION_JPG;
        } elseif ($file instanceof UploadedVideo) {
            return FileUtils::EXTENSION_GIF;
        } else {
            throw new InvalidArgumentException('Неизвестный тип файла');
        }
    }

    /**
     * Получить имя файла без расширения
     *
     * @param UploadedFile $file
     * @return string
     */
    private function getFilenameWithoutExt(UploadedFile $file): string
    {
        $fileFilenameWithoutExt = $file->getFilenameWithoutExt();
        return "{$fileFilenameWithoutExt}_thumbnail";
    }
}

<?php

declare(strict_types=1);

namespace App\Storages;

use App\DTO\FileInfoDTO;
use App\Entities\Files\File;
use App\Entities\Files\UploadedFiles\UploadedImage;
use App\Entities\Files\UploadedFiles\UploadedVideo;
use App\Services\FileServices\ThumbnailService;
use App\Services\GifService;

/**
 * Хранилище миниатюр
 */
class ThumbnailStorage extends FileStorage
{
    private GifService $gifService;
    private ThumbnailService $thumbnailService;

    /**
     * Конструктор
     *
     * @param GifService $gifService
     * @param ThumbnailService $thumbnailService
     */
    public function __construct(GifService $gifService, ThumbnailService $thumbnailService)
    {
        $this->gifService = $gifService;
        $this->thumbnailService = $thumbnailService;
    }

    /**
     * Сохранить миниатюру через видео
     *
     * @param UploadedVideo $video
     * @return FileInfoDTO
     */
    public function saveByVideo(UploadedVideo $video): FileInfoDTO
    {
        $dir = $this->thumbnailService->getDir();
        $scope = $this->thumbnailService->getScope();

        $pathToDir = $this->thumbnailService->getPathToDir($dir, $scope);
        if (!file_exists($pathToDir)) {
            $this->createDir($pathToDir);
        }

        $filename = $this->thumbnailService->getFilename($video);
        $path = $this->thumbnailService->getPath(
            filename: $filename,
            dir: $dir,
            scope: $scope
        );
        $this->gifService->generateFromVideo(
            videoPath: $video->getPath(),
            gifPath: $path,
            width: 640,
            height: 480
        );

        $createdAt = $this->thumbnailService->getCreatedAtByPath($path);
        return new FileInfoDTO(
            filename: $filename,
            dir: $dir,
            scope: $scope,
            createdAt: $createdAt
        );
    }

    /**
     * Сохранить миниатюру через изображение
     *
     * @param UploadedImage $image
     * @return FileInfoDTO
     */
    public function saveByImage(UploadedImage $image): FileInfoDTO
    {
        $dir = $this->thumbnailService->getDir();
        $scope = $this->thumbnailService->getScope();
        $pathToDir = $this->thumbnailService->getPathToDir($dir, $scope);
        if (!file_exists($pathToDir)) {
            $this->createDir($pathToDir);
        }

        $filename = $this->thumbnailService->getFilename($image);
        $path = $this->thumbnailService->getPath(
            filename: $filename,
            dir: $dir,
            scope: $scope
        );
        $this->thumbnailService->createThumbnail($image->getPath(), $path);

        $createdAt = $this->thumbnailService->getCreatedAtByPath($path);
        return new FileInfoDTO(
            filename: $filename,
            dir: $dir,
            scope: $scope,
            createdAt: $createdAt
        );
    }
}

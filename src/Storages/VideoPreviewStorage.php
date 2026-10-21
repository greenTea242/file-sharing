<?php

declare(strict_types=1);

namespace App\Storages;

use App\DTO\FileInfoDTO;
use App\Entities\Files\UploadedFiles\UploadedVideo;
use App\Services\FileServices\VideoPreviewService;
use App\Services\VideoConverter;

/**
 * Хранилище видео-превью
 */
class VideoPreviewStorage extends FileStorage
{
    private VideoConverter $videoConverter;
    private VideoPreviewService $videoPreviewService;

    /**
     * Конструктор
     *
     * @param VideoConverter $videoConverter
     * @param VideoPreviewService $videoPreviewService
     */
    public function __construct(
        VideoConverter $videoConverter, 
        VideoPreviewService $videoPreviewService)
    {        
        $this->videoConverter = $videoConverter;
        $this->videoPreviewService = $videoPreviewService;
    }
    
    /**
     * Создать превью
     *
     * @param UploadedVideo $video
     * @return FileInfoDTO
     */
    public function save(UploadedVideo $video): FileInfoDTO
    {
        if (!$this->videoConverter->canConvertToMp4($video->mediaInfo->getMimeType())) {
            return new FileInfoDTO(
                filename: $video->filename,
                dir: $video->dir,
                scope: $video->scope,
                createdAt: $video->createdAt
            );
        }
    
        $dir = $this->videoPreviewService->getDir();
        $scope = $this->videoPreviewService->getScope();
        $pathToDir = $this->videoPreviewService->getPathToDir($dir, $scope);
        if (!file_exists($pathToDir)) {
            $this->createDir($pathToDir);
        }


        $filename = $this->videoPreviewService->getFilename($video);
        $path = $this->videoPreviewService->getPath(
            filename: $filename, 
            dir: $dir, 
            scope: $scope
        );
        $this->videoConverter->convertToMp4($video->getPath(), $path);

        $createdAt = $this->videoPreviewService->getCreatedAtByPath($path);
        return new FileInfoDTO(
            filename: $filename,
            dir: $dir,
            scope: $scope,
            createdAt: $createdAt
        );
    }
}

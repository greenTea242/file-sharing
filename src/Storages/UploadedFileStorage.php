<?php

declare(strict_types=1);

namespace App\Storages;

use App\DTO\FileInfoDTO;
use App\Services\FileServices\UploadedFileService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface as Source;

/**
 * Хранилище загруженных файлов
 */
class UploadedFileStorage extends FileStorage
{    
    private UploadedFileService $uploadedFileService;

    public function __construct(UploadedFileService $uploadedFileService)
    {        
        $this->uploadedFileService=  $uploadedFileService;
    }

    /**
     * Сохранить файл
     *
     * @param Source $source
     * @return FileInfoDTO
     */
    public function save(Source $source): FileInfoDTO {        
        $dir = $this->uploadedFileService->getDir();
        $scope = $this->uploadedFileService->getScope();
        $pathToDir = $this->uploadedFileService->getPathToDir($dir, $scope);
        
        if (!file_exists($pathToDir)) {
            $this->createDir($pathToDir);
        }            

        $filename = $this->uploadedFileService->getFilename($source->getClientFilename());
        $path = $this->uploadedFileService->getPath(
            filename: $filename, 
            dir: $dir, 
            scope: $scope
        );
        $source->moveTo($path);
        
        $createdAt = $this->uploadedFileService->getCreatedAtByPath($path);
        return new FileInfoDTO(
            filename: $filename, 
            dir: $dir, 
            scope: $scope,
            createdAt: $createdAt
        );
    }
}

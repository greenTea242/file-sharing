<?php

declare(strict_types=1);

namespace App\Entities\Files;

use App\Entities\MediaInfo\ImageInfo;
use Exception;

/**
 * Миниатюра
 */
class Thumbnail extends File
{     
    public readonly ImageInfo $imageInfo;
    public readonly int $uploadedFileId;

    /**
     * Конструктор
     *
     * @param string $filename
     * @param integer $createdAt
     * @param string $dir
     * @param ImageInfo $imageInfo
     * @param integer $uploadedFileId
     * @param string $scope
     * @param string $url
     * @param integer|null $id
     */
    public function __construct(        
        string $filename,
        int $createdAt,
        string $dir,
        ImageInfo $imageInfo,        
        int $uploadedFileId,  
        string $scope,
        string $url,
        ?int $id = null
    ) {
        parent::__construct(
            mediaInfo: $imageInfo,
            filename: $filename,
            createdAt: $createdAt,
            dir: $dir,            
            id: $id,
            scope: $scope,
            url: $url
        );     
        $this->imageInfo = $imageInfo;                   
        $this->uploadedFileId = $uploadedFileId;
    }
}

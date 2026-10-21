<?php

declare(strict_types=1);

namespace App\Entities\Files;

use App\Entities\MediaInfo\VideoInfo;

/**
 * Видео-превью
 */
class VideoPreview extends File {
    public readonly VideoInfo $videoInfo;
    public readonly int $uploadedFileId;
    public readonly bool $isOriginal;

    /**
     * Конструктор
     *
     * @param string $filename
     * @param integer $createdAt
     * @param string $dir
     * @param VideoInfo $videoInfo
     * @param integer $uploadedFileId
     * @param string $scope
     * @param boolean $isOriginal
     * @param string|null $url
     * @param integer|null $id
     */
    public function __construct(        
        string $filename,
        int $createdAt,
        string $dir,
        VideoInfo $videoInfo,        
        int $uploadedFileId, 
        string $scope,
        bool $isOriginal,
        ?string $url = null,
        ?int $id = null
    ) {
        parent::__construct(
            mediaInfo: $videoInfo,
            filename: $filename,
            createdAt: $createdAt,
            dir: $dir,            
            id: $id,
            scope: $scope,
            url: $url
        );     
        $this->videoInfo = $videoInfo;                   
        $this->uploadedFileId = $uploadedFileId;
        $this->isOriginal = $isOriginal;
    }
}
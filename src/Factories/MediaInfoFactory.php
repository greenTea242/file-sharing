<?php

declare(strict_types=1);

namespace App\Factories;

use App\Services\MetadataServices\MetadataService;
use App\Entities\MediaInfo\MediaInfo;
use App\Entities\MediaInfo\AudioInfo;
use App\Entities\MediaInfo\VideoInfo;
use App\Entities\MediaInfo\ImageInfo;

/**
 * Фабрика медиа информации
 */
class MediaInfoFactory
{
    private MetadataService $metadataService;

    /**
     * Конструктор
     *
     * @param MetadataService $metadataService
     */
    public function __construct(MetadataService $metadataService)
    {
        $this->metadataService = $metadataService;
    }

    /**
     * Создать информацию о медиа через метаданные
     *
     * @param string $metadata
     * @return MediaInfo
     */
    public function createByMetadata(string $metadata): MediaInfo
    {
        $deserializedMetadata = $this->metadataService->deserializeMetadata($metadata);
        return $this->create($deserializedMetadata);
    }

    /**
     * Создать медиа информацию через путь к файлу
     *
     * @param string $path
     * @return MediaInfo
     */
    public function createByPath(string $path): MediaInfo {
        $metadata = $this->metadataService->getMetadata($path);
        return $this->create($metadata);
    }

    /**
     * Создать медиа информацию
     *
     * @param array<string, mixed> $metadata     
     * @return MediaInfo
     */
    private function create(array $metadata): MediaInfo
    {
        if ($this->metadataService->isAudio($metadata)) {
            return new AudioInfo($metadata);
        } elseif ($this->metadataService->isImage($metadata)) {
            return new ImageInfo($metadata);
        } elseif ($this->metadataService->isVideo($metadata)) {
            return new VideoInfo($metadata);
        } else {
            return new MediaInfo($metadata);
        }
    }    
}

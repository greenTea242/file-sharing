<?php

declare(strict_types=1);

namespace App\Services\MetadataServices;

use App\Entities\MediaInfo\MediaInfo;
use App\Services\MimeTypeService;
use App\Traits\MimeTypeAwareTrait;
use JamesHeinrich\GetID3\GetID3;

/**
 * Сервис для метаданных
 */
class MetadataService
{    
    private GetID3 $getID3;
    private MimeTypeService $mimeTypeService;
    private GifMetadataService $gifMetadataService;

    /**
     * Конструктор
     *
     * @param GetID3 $getID3
     * @param MimeTypeService $mimeTypeService
     * @param GifMetadataService $gifMetadataService
     */
    public function __construct(
        GetID3 $getID3, 
        MimeTypeService $mimeTypeService,
        GifMetadataService $gifMetadataService)
    {
        $this->getID3 = $getID3;
        $this->mimeTypeService = $mimeTypeService;
        $this->gifMetadataService = $gifMetadataService;
    }

    /**
     * Получить метаданные
     *
     * @param string $path     
     * @return array<mixed>
     */
    public function getMetadata(string $path): array
    {
        $isGif = $this->gifMetadataService->isGif($path);
        /**
         * На GIF изображениях зависает getID3 библиотека
         */
        if ($isGif) {
            $metadata = $this->gifMetadataService->getMetadata($path);
        } else {
            $metadata = $this->getID3->analyze($path);
            if (!isset($metadata['mime_type'])) {
                $metadata['mime_type'] = $this->mimeTypeService->getDefaultMimeType();
            }
        }
        return $metadata;
    }

    /**
     * Десериализовать метаданные
     *
     * @param string $metadata
     * @return array<string, mixed>
     */
    public function deserializeMetadata(string $metadata): array
    {
        return json_decode($metadata, true);
    }

    /**
     * Метаданные изображения?
     *
     * @param array<string, mixed> $metadata
     * @return boolean
     */
    public function isImage(array $metadata): bool
    {
        $mimeType = $this->mimeTypeService->getMimeTypeByMetadata($metadata);
        return $this->mimeTypeService->isImage($mimeType);
    }

    /**
     * Метаданные видео?
     *
     * @param array<string, mixed> $metadata
     * @return boolean
     */
    public function isVideo(array $metadata): bool
    {
        $mimeType = $this->mimeTypeService->getMimeTypeByMetadata($metadata);
        return $this->mimeTypeService->isVideo($mimeType);
    }

    /**
     * Метаданные аудио?
     *
     * @param array<string, mixed> $metadata
     * @return boolean
     */
    public function isAudio(array $metadata): bool
    {
        $mimeType = $this->mimeTypeService->getMimeTypeByMetadata($metadata);
        return $this->mimeTypeService->isAudio($mimeType);
    }
}

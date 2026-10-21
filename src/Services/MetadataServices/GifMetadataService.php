<?php

declare(strict_types=1);

namespace App\Services\MetadataServices;

use App\Services\MimeTypeService;

/**
 * Сервис для метаданных GIF изображений
 */
class GifMetadataService {
    private MimeTypeService $mimeTypeService;

    /**
     * Конструктор
     *
     * @param MimeTypeService $mimeTypeService
     */
    public function __construct(MimeTypeService $mimeTypeService)
    {
        $this->mimeTypeService = $mimeTypeService;
    }

    /**
     * Файл является GIF изображением?
     *
     * @param string $path
     * @return boolean
     */
    public function isGif(string $path): bool
    {
        $mimeType = $this->mimeTypeService->getMimeTypeByPath($path);
        return $this->mimeTypeService->isGif($mimeType);
    }

    /**
     * Получить метаданные
     *
     * @param string $path
     * @return array<string, mixed>
     */
    public function getMetadata(string $path): array {
        $metadata = [];
        $metadata['mime_type'] = $this->mimeTypeService->getMimeTypeByPath($path);        

        $metadata['video'] = [];
        [$width, $height] = $this->getWidthAndHeight($path);
        $metadata['video']['resolution_x'] = $width;
        $metadata['video']['resolution_y'] = $height;

        $metadata['filesize'] = $this->getSize($path);
        return $metadata;
    }

    /**
     * Получить длину и высоту
     *
     * @param string $path
     * @return array<mixed>
     */
    private function getWidthAndHeight(string $path): array
    {
        return getimagesize($path);
    }

    /**
     * Получить размер
     *
     * @param string $path
     * @return integer
     */
    private function getSize(string $path): int {
        return filesize($path);
    }
}
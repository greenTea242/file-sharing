<?php

declare(strict_types=1);

namespace App\Services;

use App\Traits\MimeTypeAwareTrait;

/**
 * Сервис для MIME-типов
 */
class MimeTypeService {
    public const string MIME_TYPE_GIF = 'image/gif';
    public const string MIME_TYPE_MP4 = 'video/mp4';

    use MimeTypeAwareTrait;

    /**
     * Получить MIME-тип через путь
     *
     * @param string $path
     * @return string
     */
    public function getMimeTypeByPath(string $path): string {
       $finfo = finfo_open(FILEINFO_MIME_TYPE);
       return finfo_file($finfo, $path);  
    }

    /**
     * Получить MIME-тип через метаданные
     *
     * @param array<string, mixed> $metadata
     * @return string
     */
    public function getMimeTypeByMetadata(array $metadata): string
    {
        return $this->getMimeTypeInternal($metadata);
    }

    /**
     * Получить MIME-тип по-умолчанию
     *
     * @return string
     */
    public function getDefaultMimeType(): string {
        return 'application/octet-stream';
    }

    /**
     * MIME-тип изображения?
     *
     * @param string $mimeType
     * @return boolean
     */
    public function isImage(string $mimeType): bool
    {        
        return str_starts_with($mimeType, 'image');
    }

    /**
     * MIME-тип видео?
     *
     * @param string $mimeType
     * @return boolean
     */
    public function isVideo(string $mimeType): bool
    {        
        return str_starts_with($mimeType, 'video');
    }

    /**
     * MIME-тип аудио?
     *
     * @param string $mimeType
     * @return boolean
     */
    public function isAudio(string $mimeType): bool
    {        
        return str_starts_with($mimeType, 'audio');
    }

    /**
     * MIME-тип GIF изображения?
     *
     * @param string $mimeType
     * @return boolean
     */
    public function isGif(string $mimeType): bool {        
        return ($mimeType == MimeTypeService::MIME_TYPE_GIF);
    }    
}
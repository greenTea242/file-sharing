<?php

declare(strict_types=1);

namespace App\Entities\MediaInfo;

use App\Traits\MimeTypeAwareTrait;
use Exception;

/**
 * Информация о медиа
 */
readonly class MediaInfo
{    
    use MimeTypeAwareTrait;
    
    /**
     * Метаданные
     *
     * @var array<string, mixed>
     */
    public array $metadata;    

    /**
     * Конструктор
     *
     * @param array<string, mixed> $metadata     
     */
    public function __construct(array $metadata)
    {
        $this->metadata = $metadata;
    }

    /**
     * Получить размер
     *
     * @return integer
     */
    public function getSize(): int
    {
        return $this->metadata['filesize'] ?? 
            throw new Exception('Отсутствует ключ filesize в массиве');
    }

    /**
     * Получить MIME-тип
     *
     * @return string
     */
    public function getMimeType(): string {
        return $this->getMimeTypeInternal($this->metadata);
    }
}

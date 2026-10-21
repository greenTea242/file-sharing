<?php

declare(strict_types=1);

namespace App\Entities\MediaInfo;

use Exception;

/**
 * Информация о изображении
 */
readonly class ImageInfo extends MediaInfo {
    /**
     * Получить ширину
     *
     * @return integer
     */
    public function getWidth(): int {
        $width = $this->metadata['video']['resolution_x'] ?? 
            throw new Exception('Ширина отсутствует в метаданных');
        return $width;
    }

    /**
     * Получить высоту
     *
     * @return integer
     */
    public function getHeight(): int {
        $height = $this->metadata['video']['resolution_y'] ??
            throw new Exception('Высота отсутствует в метаданных');
        return $height;
    }
}
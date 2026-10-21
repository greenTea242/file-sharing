<?php

declare(strict_types=1);

namespace App\Entities\MediaInfo;

use Exception;

/**
 * Информация о видео
 */
readonly class VideoInfo extends MediaInfo {
    /**
     * Получить продолжительность видео
     *
     * @return string
     */
    public function getPlaytime(): string {
        return $this->metadata['playtime_string'] ?? 
            throw new Exception('Отсутствует ключ playtime_string в массиве');
    }
}
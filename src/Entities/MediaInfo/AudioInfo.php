<?php

declare(strict_types=1);

namespace App\Entities\MediaInfo;

use Exception;

/**
 * Информация о аудио
 */
readonly class AudioInfo extends MediaInfo {
    /**
     * Получить продолжительность аудио
     *
     * @return string
     */
    public function getPlaytime(): string {
        return $this->metadata['playtime_string'] ?? 
            throw new Exception('Отсутствует ключ playtime_string в массиве');
    }
}
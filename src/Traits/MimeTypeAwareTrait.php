<?php

declare(strict_types=1);

namespace App\Traits;

use Exception;

/**
 * Трейт для работы с MIME-типом
 */
trait MimeTypeAwareTrait
{
    /**
     * Получить MIME-тип (внутренняя реализация)
     *
     * @param array<string, mixed> $metadata
     * @return string
     */
    private function getMimeTypeInternal(
        array $metadata
    ): string {
         return $metadata['mime_type'] ??
            throw new Exception('Отсутствует ключ mime_type в массиве');
    }
}

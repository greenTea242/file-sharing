<?php

declare(strict_types=1);

namespace App\Utilities;

/**
 * Построитель DSN 
 * 
 * DSN - имя источника данных
 */
class DsnBuilder
{
    /**
     * Получить DSN
     *
     * @param string $scheme
     * @param string $host
     * @param string $port
     * @return string
     */
    public static function getDsn(
        string $scheme,
        string $host,
        string $port
    ): string {
        return sprintf(
            '%s://%s:%s',
            $scheme,
            $host,
            $port
        );
    }
}

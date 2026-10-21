<?php

declare(strict_types=1);

namespace App\Utilities;

use Exception;

/**
 * Утилиты для путей
 */
class PathUtils {
    /**
     * Получить корневой путь
     *
     * @return string
     */
    public static function getRootPath(bool $isAbsolute = true): string
    {
        $path = dirname(__DIR__, 2);
         if ($isAbsolute) {
            $path = realpath($path);
            if ($path === false) {
                throw new Exception('Нет корневого пути');
            }
        }
        return $path;
    }
    
    /**
     * Получить публичный путь
     *
     * @return string
     */
    public static function getPublicPath(bool $isAbsolute = true): string
    {
        $path = self::getRootPath($isAbsolute) . '/public'; 
        if ($isAbsolute) {
            $path = realpath($path);
            if ($path === false) {
                throw new Exception('Нет публичного пути');
            }
        }
        return $path;
    }

    /**
     * Получить путь
     *
     * @param string $filename
     * @param string $dir
     * @return string
     */
    public static function getPath(string $filename, string $dir): string
    {
        return "{$dir}/{$filename}";
    }
}
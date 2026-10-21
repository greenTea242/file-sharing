<?php

declare(strict_types=1);

namespace App\Utilities;

use Exception;

/**
 * Утилиты для файлов
 */
class FileUtils
{
    public const string EXTENSION_TXT = 'txt';
    public const string EXTENSION_JPG = 'jpg';
    public const string EXTENSION_GIF = 'gif';
    public const string EXTENSION_MP4 = 'mp4';
    private const int BYTES_IN_GB = 1024 * 1024 * 1024;
    private const int BYTES_IN_MB = 1024 * 1024;
    private const int BYTES_IN_KB = 1024;

    /**
     * Получить имя файла через путь
     *
     * @param string $path
     * @return string
     */
    public static function getFilenameByPath(string $path): string
    {
        return pathinfo($path, PATHINFO_BASENAME);
    }

    /**
     * Получить папку для файла
     *
     * @param string $path
     * @return string
     */
    public static function getDir(string $path): string
    {
        return pathinfo($path, PATHINFO_DIRNAME);
    }

    /**
     * Получить имя файла без расширения
     *
     * @param string $filename
     * @return string
     */
    public static function getFilenameWithoutExt(string $filename): string
    {
        return pathinfo($filename, PATHINFO_FILENAME);
    }

    /**
     * Получить байты
     *
     * @param string $size
     * @return integer
     */
    public static function getBytes(string $size): int
    {
        $size = trim($size);
        $unit = strtolower($size[strlen($size) - 1]);
        $bytes = (int)$size;
        $bytes = match ($unit) {
            'g' => $bytes *= self::BYTES_IN_GB,
            'm' => $bytes *= self::BYTES_IN_MB,
            'k' => $bytes *= self::BYTES_IN_KB,
            default => throw new Exception("Неизвестная единица измерения $unit")
        };
        return $bytes;
    }

    /**
     * Форматировать байты
     *
     * @param integer $bytes
     * @return string
     */
    public static function formatSize(int $bytes): string
    {
        if ($bytes >= self::BYTES_IN_GB) {
            $size = $bytes / self::BYTES_IN_GB;
            $unit = 'ГБ';
        } elseif ($bytes >= self::BYTES_IN_MB) {
            $size = $bytes / self::BYTES_IN_MB;
            $unit = 'МБ';
        } elseif ($bytes >= self::BYTES_IN_KB) {
            $size = $bytes / self::BYTES_IN_KB;
            $unit = 'КБ';
        } else {
            $size = $bytes;
            $unit = 'байт';
            return "{$size} {$unit}";
        }
        // Приводит число к целому виду, если дробная часть отсутствует
        $size = (float)number_format($size);
        return "{$size} {$unit}";
    }

    /**
     * Заменить расширение файла
     *
     * @param string $filename
     * @param string  $newExt
     * @return string
     */
    public static function changeExt(string $filename, string $newExt): string
    {
        $filenameWithoutExt = static::getFilenameWithoutExt($filename);
        return static::getFilenameByParts(
            $filenameWithoutExt,
            $newExt
        );
    }

    /**
     * Получить имя файла через части
     *
     * @param string $filenameWithoutExt
     * @param string $ext
     * @return string
     */
    public static function getFilenameByParts(
        string $filenameWithoutExt,
        string $ext
    ): string {
        return "{$filenameWithoutExt}.{$ext}";
    }

    /**
     * Получить расширение
     *
     * @param string $filename
     * @return string
     */
    public static function getExt(string $filename): string {
        return pathinfo($filename, PATHINFO_EXTENSION);
    }
}

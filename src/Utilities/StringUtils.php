<?php

declare(strict_types=1);

namespace App\Utilities;

/**
 * Утилиты для строк
 */
class StringUtils
{
    /**
     * Транслитерировать строку на латиницу
     *
     * @param string $string
     * @return string
     */
    public static function transliterateToLatin(string $string): string
    {
        $transliterator = \Transliterator::create('Any-Latin; Latin-ASCII');
        return $transliterator->transliterate($string);
    }
}

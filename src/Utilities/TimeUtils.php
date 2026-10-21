<?php

declare(strict_types=1);

namespace App\Utilities;

/**
 * Утилиты для времени
 */
class TimeUtils
{
    /**
     * Форматировать разницу во времени
     *
     * @param integer $timestamp
     * @return string
     */
    public static function formatTimeDifference(int $timestamp): string
    {
        $currentTime = time();
        $difference = $currentTime - $timestamp;

        // Меньше минуты
        if ($difference < 60) {
            $seconds = $difference;
            $word = self::getWordForm($seconds, ['секунду', 'секунды', 'секунд']);
            return "{$seconds} {$word} назад";
        }

        // Меньше часа
        if ($difference < 3600) {
            $minutes = (int)floor($difference / 60);
            $word = self::getWordForm($minutes, ['минуту', 'минуты', 'минут']);
            return "{$minutes} {$word} назад";
        }

        // Меньше 24 часов
        if ($difference < 86400) {
            $hours = (int)floor($difference / 3600);
            $word = self::getWordForm($hours, ['час', 'часа', 'часов']);
            return "{$hours} {$word} назад";
        }

        // Больше или равно 24 часам
        return "более дня назад";
    }

    /**
     * Вспомогательная функция для склонения слов в зависимости от числа
     * 
     * @param int $number
     * @param array<string> $forms
     * @return string
     */
    private static function getWordForm(int $number, array $forms): string
    {
        $mod100 = $number % 100;
        if ($mod100 >= 11 && $mod100 <= 19) {
            return $forms[2]; 
        }

        $mod10 = $number % 10;
        
        if ($mod10 == 1) {
            return $forms[0]; 
        }

        if ($mod10 >= 2 && $mod10 <= 4) {
            return $forms[1];
        }

        return $forms[2];
    }
}

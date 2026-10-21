<?php

declare(strict_types=1);

namespace App\Interfaces;

interface DirectoryFinderInterface
{
    /**
     * Находит максимальное значение директории по маске
     * 
     * @param string $mask 
     * @return string|null
     */
    public function findMaxDir(string $mask): ?string;
}
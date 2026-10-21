<?php

declare(strict_types=1);

namespace App\Traits;

use App\Entities\Files\File;
use App\Utilities\FileUtils;
use App\Utilities\PathUtils;
use InvalidArgumentException;

/**
 * Трейт для работы с путями к файлам
 */
trait FilePathAwareTrait
{
    /**
     * Построить путь
     *
     * @param string $filename
     * @param string $dir
     * @param string $scope
     * @return string
     */
    private function buildPath(
        string $dir,
        string $scope,
        ?string $filename = null,
        bool $isAbsolute = true
    ): string {
        if ($scope == File::SCOPE_PUBLIC) {
            $dir = PathUtils::getPublicPath($isAbsolute) . "/{$dir}";
        } elseif ($scope == FILE::SCOPE_PRIVATE) {
            $dir = PathUtils::getRootPath($isAbsolute) . "/{$dir}";
        } else {
            throw new InvalidArgumentException("Неизвестная область видимости {$scope}");
        }
        if (is_null($filename)) {
            return $dir;
        } else {
            return PathUtils::getPath($filename, $dir);
        }
    }
}

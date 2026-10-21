<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entities\Files\File;
use App\Traits\FilePathAwareTrait;

/**
 * DTO для информации о файле
 */
readonly class FileInfoDTO {
    use FilePathAwareTrait;
    /**
     * Конструктор
     *
     * @param string $filename
     * @param string $dir
     * @param string $scope
     * @param integer $createdAt
     */
    public function __construct(
        public string $filename,
        public string $dir,
        public string $scope,
        public int $createdAt
    ) {}

    /**
     * Получить путь
     *
     * @return string
     */
    public function getPath(): string {
        return $this->buildPath(
            dir: $this->dir, 
            scope: $this->scope, 
            filename: $this->filename
        );
    }
}
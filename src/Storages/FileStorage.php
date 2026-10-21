<?php

declare(strict_types=1);

namespace App\Storages;

use App\DTO\FileInfoDTO;
use App\Entities\Files\File;
use App\Services\FileServices\FileService;
use Exception;
use App\Utilities\FileUtils;

/**
 * Хранилище файлов
 */
class FileStorage
{    
    /**
     * Создать папку
     *
     * @param string $pathToDir
     * @return void
     */
    public function createDir(string $pathToDir): void {
        $isDirMade = mkdir($pathToDir, 0777, true);
        if (!$isDirMade) {
            throw new Exception('Не удалось создать папку');
        }
    }

    /**
     * Удалить файл
     *
     * @param string $path
     * @return void
     */
    public function delete(string $path): void
    {        
        $isDeleted = unlink($path);
        if (!$isDeleted) {
            throw new Exception('Не удалось удалить файл');
        }
    }    
}

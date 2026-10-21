<?php

declare(strict_types=1);

namespace App\Entities;

/**
 * Список URL для загруженного файла
 */
readonly class UploadedFileUrlList {
    public string $downloadFile;
    public string $deleteFile;  
    
    /**
     * Конструктор
     *
     * @param string $downloadFile
     * @param string $deleteFile
     */
    public function __construct(string $downloadFile, string $deleteFile)
    {
        $this->downloadFile = $downloadFile;
        $this->deleteFile = $deleteFile;
    }
}
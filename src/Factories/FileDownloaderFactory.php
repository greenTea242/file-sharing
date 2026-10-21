<?php

declare(strict_types=1);

namespace App\Factories;
use App\Services\FileDownloaders\PhpNativeDownloader;
use App\Services\FileDownloaders\XSendFileDownloader;
use App\Services\FileDownloaders\FileDownloader;
use Exception;

class FileDownloaderFactory {
    public function create(string $type): FileDownloader {
        return match($type) {
            'phpNativeDownloader' => new PhpNativeDownloader(),
            'xSendFileDownloader' => new XSendFileDownloader(),
            default => throw new Exception("Неизвестный тип загрузчика $type")
        };
    }
}
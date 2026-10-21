<?php

declare(strict_types=1);

namespace App\Storages;

use App\Entities\Files\File;
use App\Entities\Files\Thumbnail;
use App\Entities\Files\UploadedFiles\UploadedAudio;
use App\Entities\Files\UploadedFiles\UploadedFile;
use App\Entities\Files\UploadedFiles\UploadedImage;
use App\Entities\Files\UploadedFiles\UploadedVideo;
use App\Entities\Files\VideoPreview;
use App\Storages\FileStorage;
use RuntimeException;

/**
 * Сервис для паттерна Unit of Work
 * 
 * Регистрирует файловые хранилища данных и файлы чтобы потом одновременно их удалить
 */
class FileUnitOfWork
{
    /**
     * Файлы
     *
     * @var array<File> 
     */
    private array $files = [];
    /**
     * Map хранилищ
     *
     * @var array<FileStorage> 
     */
    private array $storageMap = [];


    /**
     * Зарегистрировать хранилища 
     *     
     * @param UploadedFileStorage|null $uploadedFileStorage
     * @param ThumbnailStorage|null $thumbnailStorage
     * @param VideoPreviewStorage|null $videoPreviewStorage
     * @return void
     */
    public function registerStorages(
        ?UploadedFileStorage $uploadedFileStorage = null,
        ?ThumbnailStorage $thumbnailStorage = null,
        ?VideoPreviewStorage $videoPreviewStorage = null
    ) {
        $this->registerStorage(UploadedFile::class, $uploadedFileStorage);
        $this->registerStorage(UploadedImage::class, $uploadedFileStorage);
        $this->registerStorage(UploadedVideo::class, $uploadedFileStorage);
        $this->registerStorage(UploadedAudio::class, $uploadedFileStorage);
        $this->registerStorage(Thumbnail::class, $thumbnailStorage);
        $this->registerStorage(VideoPreview::class, $videoPreviewStorage);
    }

    /**
     * Зарегистрировать файл
     *
     * @param File $file
     * @return void
     */
    public function registerFile(File $file): void
    {
        $this->files[] = $file;
    }

    /**
     * Очистить файлы
     *
     * @return void
     */
    public function refreshFiles(): void
    {
        $this->files = [];
    }

    /**
     * Сделать rollback
     *
     * @return void
     */
    public function rollback(): void
    {
        foreach ($this->files as $file) {
            $storage = $this->getStorage($file);
            $storage->delete($file->getPath());
        }
    }

    /**
     * Получить хранилище
     *
     * @param File $file
     * @return FileStorage
     */
    private function getStorage(File $file): FileStorage
    {
        $className = get_class($file);
        if (!isset($this->storageMap[$className])) {
            throw new RuntimeException("Нет хранилища для {$className}");
        }
        return $this->storageMap[$className];
    }

    /**
     * Зарегистрировать хранилище
     *
     * @param string $fileClass
     * @param FileStorage $storage
     * @return void
     */
    private function registerStorage(string $fileClass, FileStorage $storage): void
    {
        $this->storageMap[$fileClass] = $storage;
    }
}

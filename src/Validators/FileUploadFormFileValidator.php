<?php

declare(strict_types=1);

namespace App\Validators;

use Psr\Http\Message\UploadedFileInterface as UploadedFile;
use App\Services\FileServices\UploadedFileService;

class FileUploadFormFileValidator extends FormValidator
{
    private UploadedFileService $uploadedFileService;

    public const int ERROR_NO_FILE = 1;
    public const int ERROR_TOO_BIG = 2;
    public const int ERROR_OTHER = 3;

    /**
     * Конструктор
     *
     * @param UploadedFileService $uploadedFileService
     */
    public function __construct(UploadedFileService $uploadedFileService)
    {
        $this->uploadedFileService = $uploadedFileService;
    }

    /**
     * Валидировать форму
     *
     * @param UploadedFile|null $file
     * @param boolean $checkSize
     * @return array<string>
     */
    public function validate(?UploadedFile $file, bool $checkSize = true): array
    {
        $errors = [];
        if (!$this->hasFile($file)) {
            $errors[static::ERROR_NO_FILE] = 'Нет файла. Выберите файл';
            return $errors;
        }
        if (
            $checkSize &&
            !$this->checkSize($file)
        ) {
            $maxFileSize = $this->uploadedFileService->getFormattedMaxSize();
            $errors[static::ERROR_TOO_BIG] = sprintf(
                'Размер файла не должен превышать %s. Выберите файл меньшего размера',
                $maxFileSize
            );
            return $errors;
        };
        if (!$this->checkForOtherErrors($file)) {
            $errors[static::ERROR_OTHER] = 'Не удалось загрузить файл. Попробуйте еще раз';
            return $errors;
        };

        return $errors;
    }


    /**
     * Получить максимальный размер файла
     *
     * @return integer
     */
    public function getMaxSize(): int
    {
        return $this->uploadedFileService->getMaxSize();
    }

    /**
     * Есть файл?
     *
     * @param UploadedFile|null $file
     * @return boolean
     */
    private function hasFile(?UploadedFile $file): bool
    {
        if (
            !is_null($file) &&
            $file->getError() !== UPLOAD_ERR_NO_FILE
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Проверить размер файла
     *
     * @param UploadedFile $file
     * @return boolean
     */
    private function checkSize(UploadedFile $file): bool
    {
        if ($file->getError() !== UPLOAD_ERR_INI_SIZE) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Проверить файл на остальные ошибки
     *
     * @param UploadedFile $file
     * @return boolean
     */
    private function checkForOtherErrors(UploadedFile $file): bool
    {
        if ($file->getError() === UPLOAD_ERR_OK) {
            return true;
        } else {
            return false;
        }
    }
}

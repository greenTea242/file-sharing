<?php

declare(strict_types=1);

namespace App\Validators;

use App\Forms\FileUploadForm;
use App\Validators\FileUploadFormFileValidator;

/**
 * Валидатор формы для загрузки файла
 */
class FileUploadFormValidator extends FormValidator
{
    public const int ERROR_TOO_LONG = 1;

    public const int USER_COMMENT_MAX_LEN = 250;

    public readonly FileUploadFormFileValidator $fileValidator;

    /**
     * Конструктор
     *
     * @param FileUploadFormFileValidator $fileValidator
     */
    public function __construct(FileUploadFormFileValidator $fileValidator)
    {
        $this->fileValidator = $fileValidator;
    }

    /**
     * Валидировать форму
     *
     * @param FileUploadForm $form
     * @param boolean $checkFileSize
     * @return array<mixed>
     */
    public function validate(FileUploadForm $form, bool $checkFileSize = true): array
    {
        $errors = [];
        $fileErrors = $this->fileValidator->validate($form->file, $checkFileSize);
        if ($fileErrors) {
            $errors['file'] = $fileErrors;
        }
        if (!$this->checkUserCommentLen($form)) {
            $errors['userComment'][static::ERROR_TOO_LONG] = sprintf(
                'Длина комментария не должна превышать %d символов',
                static::USER_COMMENT_MAX_LEN
            );
        };
        return $errors;
    }

    /**
     * Проверить длину комментария пользователя
     *
     * @param FileUploadForm $form
     * @return boolean
     */
    private function checkUserCommentLen(FileUploadForm $form): bool
    {        
        if (
            is_null($form->userComment) ||
            mb_strlen($form->userComment) <= static::USER_COMMENT_MAX_LEN
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Получить максимальный размер файла
     *
     * @return integer
     */
    public function getFileMaxSize(): int
    {
        return $this->fileValidator->getMaxSize();
    }
}

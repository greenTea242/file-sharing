<?php

declare(strict_types=1);

namespace App\Validators;

use App\Database\CommentGateway;
use App\Database\UploadedFileGateway;
use App\Forms\CommentForm;

/**
 * Валидатор формы для комментария
 */
class CommentFormValidator extends FormValidator
{
    public const int MAX_TEXT_LEN = 130;
    public const int MAX_AUTHOR_NAME_LEN = 20;
    public const int ERROR_AUTHOR_NAME_TOO_LONG = 1;
    public const int ERROR_TEXT_TOO_LONG = 2;
    public const int ERROR_NO_TEXT = 3;
    public const int ERROR_NO_UPLOADED_FILE_ID = 4;
    public const int ERROR_UPLOADED_FILE_NOT_EXIST = 5;
    public const int ERROR_PARENT_COMMENT_NOT_EXIST = 6;
    public const int ERROR_TOO_MANY_COMMENTS = 7;
    private const int MAX_COMMENT_NUMBER = 999;

    private UploadedFileGateway $uploadedFileGateway;
    private CommentGateway $commentGateway;

    /**
     * Конструктор
     *
     * @param UploadedFileGateway $uploadedFileGateway
     * @param CommentGateway $commentGateway
     */
    public function __construct(UploadedFileGateway $uploadedFileGateway, CommentGateway $commentGateway)
    {
        $this->uploadedFileGateway = $uploadedFileGateway;
        $this->commentGateway = $commentGateway;
    }

    /**
     * Валидировать форму
     *
     * @param CommentForm $form
     * @param integer $commentsCount
     * @return array<string, mixed>
     */
    public function validate(CommentForm $form, int $commentsCount): array
    {
        $errors = [];

        if (!$this->hasUploadedFileId($form)) {
            $errors['uploadedFileId'][self::ERROR_NO_UPLOADED_FILE_ID] = $this->getDefaultError();
            return $errors;
        } elseif (!$this->uploadedFileExists($form)) {
            $errors['uploadedFileId'][self::ERROR_UPLOADED_FILE_NOT_EXIST] = $this->getDefaultError();
            return $errors;
        }

        if (!is_null($form->parentId) &&
            !$this->parentCommentExists($form)) {
            $errors['parentComment'][self::ERROR_UPLOADED_FILE_NOT_EXIST] = $this->getDefaultError();
            return $errors;
        }

        if (!$this->checkCommentsCount($commentsCount)) {{
            $errors['commentsCount'][self::ERROR_TOO_MANY_COMMENTS] = sprintf(
                'Разрешено не больше %d комментариев',
                self::MAX_COMMENT_NUMBER
            );
        }}

        if (!$this->checkUserNameLen($form)) {
            $errors['userName'][self::ERROR_AUTHOR_NAME_TOO_LONG] = sprintf(
                'Длина вашего имени не должна превышать %d символов',
                self::MAX_AUTHOR_NAME_LEN
            );
        }

        if (!$this->hasText($form)) {
            $errors['text'][self::MAX_TEXT_LEN] = 'Введите комментарий';
        } elseif (!$this->checkTextLen($form)) {
            $errors['text'][self::ERROR_TEXT_TOO_LONG] = sprintf(
                'Длина вашего комментария не должна превышать %d символов',
                self::MAX_TEXT_LEN
            );
        }

        return $errors;
    }

    /**
     * Есть текст?
     *
     * @param CommentForm $form
     * @return boolean
     */
    private function hasText(CommentForm $form): bool
    {
        if (
            !is_null($form->text) &&
            $form->text !== ''
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Проверить длину текста
     *
     * @param CommentForm $form
     * @return boolean
     */
    private function checkTextLen(CommentForm $form): bool
    {
        if (
            is_null($form->text) ||
            mb_strlen($form->text) <= self::MAX_TEXT_LEN
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Проверить имя автора
     *
     * @param CommentForm $form
     * @return boolean
     */
    private function checkUserNameLen(CommentForm $form): bool
    {
        if (
            is_null($form->userName) ||
            mb_strlen($form->userName) <= self::MAX_AUTHOR_NAME_LEN
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Есть идентификатор загруженного файла?
     *
     * @param CommentForm $form
     * @return boolean
     */
    private function hasUploadedFileId(CommentForm $form): bool
    {
        return (!is_null($form->uploadedFileId));
    }

    /**
     * Загруженный файл существует?
     *
     * @param CommentForm $form
     * @return boolean
     */
    private function uploadedFileExists(CommentForm $form): bool {
        return $this->uploadedFileGateway->fileExists($form->uploadedFileId);
    }

    /**
     * Комментарий-родитель существует?
     *
     * @param CommentForm $form
     * @return boolean
     */
    private function parentCommentExists(CommentForm $form): bool {
        return $this->commentGateway->commentExists($form->parentId);
    }
    
    /**
     * Проверить число комментариев
     *
     * @param integer $commentsCount
     * @return boolean
     */
    private function checkCommentsCount(int $commentsCount): bool {        
        return ($commentsCount < self::MAX_COMMENT_NUMBER);                    
    }
}

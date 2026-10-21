<?php

namespace App\Services\FormServices;

/**
 * Сервис для формы для комментария
 */
class CommentFormService extends FormService {    
    /**
     * Получить имя пользователя
     *
     * @param array<string, mixed> $formData
     * @return string|null
     */
    public function getUserName(array $formData): ?string {
        $userName = $formData['userName'] ?? null;
        if ($userName === '') {
            $userName = null;
        }
        return $userName;
    }

    /**
     * Получить текст
     *
     * @param array<string, mixed> $formData
     * @return string|null
     */
    public function getText(array $formData): ?string {
        $text = $formData['text'] ?? null;

        if ($text === '') {
            $text = null;
        }

        return $text;
    }

    /**
     * Получить идентификатор загруженного файла
     *
     * @param array<string, mixed> $formData
     * @return integer|null
     */
    public function getUploadedFileId(array $formData): ?int {
        $uploadedFileId = $formData['uploadedFileId'] ?? null;

        if ($uploadedFileId === '') {
            $uploadedFileId = null;
        }

        return $uploadedFileId;
    }

    /**
     * Получить идентификатор родителя
     *
     * @param array<string, mixed> $formData
     * @return integer|null
     */
    public function getParentId(array $formData): ?int {
        $parentId = $formData['parentId'] ?? null;

        if ($parentId === '') {
            $parentId = null;
        }

        return $parentId;
    }
}
<?php

namespace App\Services\FormServices;

/**
 * Сервис для формы для скачивания файла
 */
class FileUploadFormService extends FormService {    
    /**
     * Получить комментарий пользователя
     *
     * @param array<string, mixed> $formData
     * @return string|null
     */
    public function getUserComment(array $formData): ?string {
        $userComment = $formData['userComment'] ?? null;
        if ($userComment === '') {
            $userComment = null;
        }
        return $userComment;
    }
}
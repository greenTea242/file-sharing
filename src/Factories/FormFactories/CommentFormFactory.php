<?php

declare(strict_types=1);

namespace App\Factories\FormFactories;

use App\Entities\Comments\Comment;
use App\Entities\Files\UploadedFiles\UploadedFile;
use App\Forms\CommentForm;
use App\Services\FormServices\CommentFormService;

/**
 * Фабрика форм для загрузки файла
 */
class CommentFormFactory
{
    private CommentFormService $commentFormService;

    public function __construct(CommentFormService $commentFormService)
    {
        $this->commentFormService = $commentFormService;
    }

    /**
     * Создать форму
     *
     * @param array<string, mixed> $formData     
     * @param boolean $createCsrfToken
     * @return CommentForm
     */
    public function create(
        array $formData,
        bool $createCsrfToken = true
    ): CommentForm {
        $userName = $this->commentFormService->getUserName($formData);
        $text = $this->commentFormService->getText($formData);
        $uploadedFileId = $this->commentFormService->getUploadedFileId($formData);
        $parentId = $this->commentFormService->getParentId($formData);

        if ($createCsrfToken) {
            $csrfToken = $this->commentFormService->createCsrfToken();
        } else {
            $csrfToken = $this->commentFormService->getCsrfToken($formData);
        }


        return new CommentForm(
            csrfToken: $csrfToken,
            text: $text,
            userName: $userName,
            uploadedFileId: $uploadedFileId,
            parentId: $parentId
        );
    }

    public function createEmpty(UploadedFile $file, ?Comment $parentComment = null): CommentForm
    {
        $csrfToken = $this->commentFormService->createCsrfToken();
        return new CommentForm(
            csrfToken: $csrfToken,
            uploadedFileId: $file->getId(),
            parentId: $parentComment?->getId()
        );
    }
}

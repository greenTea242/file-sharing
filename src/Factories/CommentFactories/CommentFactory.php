<?php

declare(strict_types=1);

namespace App\Factories\CommentFactories;

use App\Database\CommentGateway;
use App\Entities\Comments\Comment;
use App\Entities\User;
use App\Exceptions\CommentNotFoundException;
use App\Forms\CommentForm;
use App\Services\CommentService;
use Exception;

/**
 * Фабрика комментариев
 */
class CommentFactory
{
    private CommentService $commentService;
    private CommentGateway $commentGateway;

    public function __construct(
        CommentService $commentService,
        CommentGateway $commentGateway
    ) {
        $this->commentService = $commentService;
        $this->commentGateway = $commentGateway;
    }

    /**
     * Создать комментарий через форму для комментария
     *
     * @param CommentForm $form
     * @param integer $commentsCount
     * @param User $user
     * @return Comment
     */
    public function createByCommentForm(
        CommentForm $form,        
        int $commentsCount,
        User $user
    ): Comment {
        $createdAt = $this->commentService->getCreatedAtByNow();
        $number = $this->commentService->getNumber($commentsCount);
        $parentComment = null;

        if (!is_null($form->parentId)) {
            $parentComment = $this->createByIdOrFail($form->parentId);
        }

        $path = $this->commentService->getPath($number, $parentComment);
        $text = $this->commentService->getText($form->text);
        return new Comment(
            number: $number,            
            path: $path,
            text: $text,
            uploadedFileId: $form->uploadedFileId,
            createdAt: $createdAt,
            userId: $user->getId(),
            parentId: $form->parentId,
            userName: $form->userName
        );
    }

    /**
     * Создать комментарий через идентификатор, или выбросить исключение
     *
     * @param integer $id
     * @return Comment
     */
    public function createByIdOrFail(int $id): Comment
    {
        $commentData = $this->commentGateway->findComment($id);

        if (!$commentData) {
            throw new Exception('Комментарий не найден');
        }

        return $this->createByCommentData($commentData);
    }

    /**
     * Создать файлы
     *
     * @param integer $uploadedFileId
     * @return array<Comment>
     */
    public function createMany(int $uploadedFileId): array
    {
        $comments = [];
        $commentsData = $this->commentGateway->findComments($uploadedFileId);

        foreach ($commentsData as $commentData) {
            $comments[] = $this->createByCommentData($commentData);
        }

        return $comments;
    }

    /**
     * Получить комментарий через данные комментария
     *
     * @param array<string, mixed> $commentData
     * @return Comment
     */
    private function createByCommentData(array $commentData): Comment
    {
        $id = $commentData['id'] ?? throw new Exception('Отсутствует ключ id в массиве');
        $number = $commentData['number'] ?? throw new Exception('Отсутствует ключ number в массиве');        
        $text = $commentData['text'] ?? throw new Exception('Отсутствует ключ text в массиве');
        $uploadedFileId = $commentData['uploaded_file_id'] ??
            throw new Exception('Отсутствует ключ uploaded_file_id в массиве');

        if (array_key_exists('user_name', $commentData)) {
            $userName = $commentData['user_name'];
        } else {
            throw new Exception('Отсутствует ключ user_name в массиве');
        }

        $createdAtRaw = $commentData['created_at'] ?? throw new Exception('Отсутствует ключ created_at в массиве');
        $createdAt = $this->commentService->getCreatedAtByRaw($createdAtRaw);

        if (array_key_exists('updated_at', $commentData)) {
            $updatedAtRaw = $commentData['updated_at'];
            
            if (is_null($updatedAtRaw)) {            
                $updatedAt = $updatedAtRaw;
            } else {
                $updatedAt = $this->commentService->getUpdatedAt($updatedAtRaw);
            }
        } else {
            throw new Exception('Отсутствует ключ updated_at в массиве');
        }

        if (array_key_exists('parent_id', $commentData)) {
            $parentId = $commentData['parent_id'];
        } else {
            throw new Exception('Отсутствует ключ parent_id в массиве');
        }

        $path = $commentData['path'] ?? throw new Exception('Отсутствует ключ path в массиве');
        $userId = $commentData['user_id'] ?? throw new Exception('Отсутствует ключ user_id в массиве');
        return new Comment(
            id: $id,
            number: $number,            
            path: $path,
            text: $text,
            uploadedFileId: $uploadedFileId,
            createdAt: $createdAt,
            userId: $userId,
            parentId: $parentId,
            userName: $userName,
            updatedAt: $updatedAt
        );
    }
}

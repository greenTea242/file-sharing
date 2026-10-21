<?php

declare(strict_types=1);

namespace App\Services;

use App\Database\CommentGateway;
use App\Entities\Comments\Comment;
use App\Entities\Comments\CommentTree;
use App\Entities\Files\UploadedFiles\UploadedFile;
use App\Forms\CommentForm;

/**
 * Сервис для комментариев
 */
class CommentService {   
    private CommentGateway $commentGateway;

    /**
     * Конструктор
     *
     * @param CommentGateway $commentGateway
     */
    public function __construct(CommentGateway $commentGateway)
    {
        $this->commentGateway = $commentGateway;
    }
    
    /**
     * Получить число комментариев
     *
     * @param integer|null $parentId
     * @return integer
     */
    public function countComments(?int $parentId = null): int {
        if (is_null($parentId)) {
            return 1;
        }
        $siblingsCount = $this->commentGateway->countComments($parentId);
        return ($siblingsCount + 1);
    }

    /**
     * Получить путь
     *
     * @param integer $number
     * @param Comment|null $parentComment
     * @return string
     */
    public function getPath(int $number, ?Comment $parentComment): string {        
        $path = str_pad((string)$number, 3, '0', STR_PAD_LEFT);
        if (!is_null($parentComment)) {
           $path = $parentComment->path .
             Comment::PATH_DIVIDER .
             $path;
        }
        return $path;
    }

    public function getNumber(int $commentsCount): int {
        return ($commentsCount + 1);
    }

    /**
     * Получить дату создания через текущее время
     *
     * @return integer
     */
    public function getCreatedAtByNow(): int {
        return time();
    }

    /**
     * Получить дату создания через необработанные данные
     *
     * @param string $createdAtRaw
     * @return integer
     */
    public function getCreatedAtByRaw(string $createdAtRaw): int {
        return strtotime($createdAtRaw);
    }

    /**
     * Получить дату обновления
     *
     * @param string $updatedAtRaw
     * @return integer
     */
    public function getUpdatedAt(string $updatedAtRaw): int {
        return $this->getCreatedAtByRaw($updatedAtRaw);
    }

    /**
     * Получить текст
     *
     * @param string $textRaw
     * @return string
     */
    public function getText(string $textRaw): string {
        return htmlspecialchars($textRaw, ENT_QUOTES, 'UTF-8');
    }     
}
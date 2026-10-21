<?php

declare(strict_types=1);

namespace App\Database;

use App\Entities\Comments\Comment;
use Pdo;

/**
 * Шлюз для таблицы comments (комментарии)
 */
class CommentGateway extends TableDataGateway
{
    /**
     * Добавить комментарий
     *
     * @param Comment $comment
     * @return integer
     */
    public function addComment(Comment $comment): int
    {
        $query = <<<SQL
            INSERT INTO comments (                        
                number,             
                path, 
                text,
                uploaded_file_id,
                user_name,
                created_at,
                updated_at,
                parent_id,
                user_id
            )
            VALUES (                        
                :number_bind,             
                :path_bind, 
                :text_bind,
                :uploaded_file_id_bind,
                :user_name_bind,
                to_timestamp(:created_at_bind),
                to_timestamp(:updated_at_bind),
                :parent_id_bind,
                :user_id_bind
            );
        SQL;
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':number_bind', $comment->number, PDO::PARAM_INT);        
        $stmt->bindValue(':path_bind', $comment->path, PDO::PARAM_STR);
        $stmt->bindValue(':text_bind', $comment->text, PDO::PARAM_STR);
        $stmt->bindValue(':uploaded_file_id_bind', $comment->uploadedFileId, PDO::PARAM_INT);
        $stmt->bindValue(':user_name_bind', $comment->userName, PDO::PARAM_STR);
        $stmt->bindValue(':created_at_bind', $comment->createdAt, PDO::PARAM_INT);
        $stmt->bindValue(':updated_at_bind', $comment->updatedAt, PDO::PARAM_INT);
        $stmt->bindValue(':parent_id_bind', $comment->parentId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id_bind', $comment->userId, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Найти комментарий
     *
     * @param integer $id
     * @return array<string, mixed>|null
     */
    public function findComment(int $id): ?array
    {
        $query = <<<SQL
            SELECT id, 
                number,                 
                path, 
                text,
                uploaded_file_id,
                user_name,
                created_at,
                updated_at,
                parent_id,
                user_id
            FROM comments
            WHERE id = :id_bind;
        SQL;
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_bind', $id, PDO::PARAM_INT);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $comment = $stmt->fetch();
        if ($comment === false) {
            return null;
        } else {
            return $comment;
        }
    }

    /**
     * Найти комментарии
     *
     * @param integer $uploadedFileId
     * @return array<string, mixed>
     */
    public function findComments(int $uploadedFileId): array
    {
        $query = <<<SQL
            SELECT id, 
                number,                 
                path, 
                text,
                uploaded_file_id,
                user_name,
                created_at,
                updated_at,
                parent_id,
                user_id
            FROM comments
            WHERE uploaded_file_id = :uploaded_file_id_bind;
        SQL;
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':uploaded_file_id_bind', $uploadedFileId, PDO::PARAM_INT);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        return $stmt->fetchAll();
    }

    /**
     * Комментарий существует?
     *
     * @param integer $id
     * @return boolean
     */
    public function commentExists(int $id): bool {
        return (!is_null($this->findComment($id)));
    }

    /**
     * Посчитать комментарии
     *
     * @param integer $parentId
     * @return integer
     */
    public function countComments(int $parentId): int {
        $query = <<<SQL
            SELECT COUNT(*)
              FROM comments
             WHERE parent_id = :parent_id_bind;
        SQL;
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':parent_id_bind', $parentId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn();
    }
}

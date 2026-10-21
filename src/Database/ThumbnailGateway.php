<?php

declare(strict_types=1);

namespace App\Database;

use App\Entities\Files\Thumbnail;
use App\Interfaces\DirectoryFinderInterface as DirectoryFinder;
use Pdo;

/**
 * Шлюз для таблицы thumbnails (миниатюры)
 */
class ThumbnailGateway extends TableDataGateway implements DirectoryFinder
{
    /**
     * Добавить миниатюру
     *
     * @param Thumbnail $thumbnail
     * @return integer
     */
    public function addThumbnail(Thumbnail $thumbnail): int
    {
        $query = <<<SQL
        INSERT INTO thumbnails (            
            uploaded_file_id, 
            filename, 
            created_at, 
            dir, 
            metadata,
            scope
        )
        VALUES (            
            :uploaded_file_id_bind, 
            :filename_bind, 
            to_timestamp(:created_at_bind), 
            :dir_bind, 
            :metadata_bind,
            :scope_bind
        );
        SQL;        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':uploaded_file_id_bind', $thumbnail->uploadedFileId, PDO::PARAM_STR);
        $stmt->bindValue(':filename_bind', $thumbnail->filename, PDO::PARAM_STR);
        $stmt->bindValue(':created_at_bind', $thumbnail->createdAt, PDO::PARAM_INT);
        $stmt->bindValue(':dir_bind', $thumbnail->dir, PDO::PARAM_STR);

        $metadata = $this->serializeJson($thumbnail->mediaInfo->metadata);
        $stmt->bindValue(':metadata_bind', $metadata, PDO::PARAM_STR);        
        
        $stmt->bindValue(':scope_bind', $thumbnail->scope, PDO::PARAM_STR);
        $stmt->execute();
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Найти максимальное значение папки
     *
     * @param string $dirMask 
     * @return string|null
     */
    public function findMaxDir(string $dirMask): ?string
    {
        $query = <<<SQL
            SELECT MAX(dir) 
              FROM thumbnails
             WHERE dir LIKE :dir_bind;
        SQL;
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':dir_bind', $dirMask, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * Найти миниатюру
     *
     * @param integer $uploadedFileId
     * @return array<string, mixed>|null
     */
    public function findThumbnail(int $uploadedFileId): ?array
    {
        $query = <<<SQL
            SELECT filename, 
                   created_at, 
                   dir, 
                   metadata,                                       
                   id,
                   uploaded_file_id,
                   scope         
            FROM thumbnails
            WHERE uploaded_file_id = :uploaded_file_id_bind;
        SQL;
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':uploaded_file_id_bind', $uploadedFileId, PDO::PARAM_INT);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $file = $stmt->fetch();
        if ($file === false) {
            return null;
        } else {
            return $file;
        }
    }
}

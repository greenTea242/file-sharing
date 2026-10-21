<?php

declare(strict_types=1);

namespace App\Database;

use App\Entities\Files\UploadedFiles\UploadedFile;
use App\Interfaces\DirectoryFinderInterface as DirectoryFinder;
use InvalidArgumentException;
use Pdo;

/**
 * Шлюз для таблицы uploaded_files (загруженные файлы)
 */
class UploadedFileGateway extends TableDataGateway implements DirectoryFinder
{
    /**
     * Добавить файл
     *
     * @param UploadedFile $file
     * @return int
     */
    public function addFile(UploadedFile $file): int
    {
        $query = <<<SQL
        INSERT INTO uploaded_files (            
            filename,             
            dir, 
            metadata,                 
            user_filename,             
            user_comment,
            created_at,
            scope,
            user_id
        )
        VALUES (            
            :filename_bind,             
            :dir_bind, 
            :metadata_bind,             
            :user_filename_bind,             
            :user_comment_bind,
            to_timestamp(:created_at_bind),
            :scope_bind,
            :user_id_bind
        );
        SQL;
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':filename_bind', $file->filename, PDO::PARAM_STR);
        $stmt->bindValue(':dir_bind', $file->dir, PDO::PARAM_STR);

        $metadata = $this->serializeJson($file->mediaInfo->metadata);
        $stmt->bindValue(':metadata_bind', $metadata, PDO::PARAM_STR);

        $stmt->bindValue(':user_filename_bind', $file->userFilename, PDO::PARAM_STR);
        $stmt->bindValue(':user_comment_bind', $file->userComment, PDO::PARAM_STR);
        $stmt->bindValue(':created_at_bind', $file->createdAt, PDO::PARAM_INT);
        $stmt->bindValue(':scope_bind', $file->scope, PDO::PARAM_STR);
        $stmt->bindValue(':user_id_bind', $file->userId, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Найти файл
     *
     * @param integer $id
     * @return array<string, mixed>|null
     */
    public function findFile(int $id): ?array
    {
        $query = <<<SQL
            SELECT id, 
                   filename,                    
                   dir,                    
                   user_filename,                    
                   metadata,
                   user_comment,
                   created_at,
                   scope,
                   user_id
              FROM uploaded_files
             WHERE id = :id_bind;
        SQL;
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_bind', $id, PDO::PARAM_INT);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $file = $stmt->fetch();
        if ($file === false) {
            return null;
        } else {
            return $file;
        }
    }

    /**
     * Найти максимальную папку
     *
     * @param string $dirMask
     * @return string|null
     */
    public function findMaxDir(string $dirMask): ?string
    {
        $query = <<<SQL
            SELECT MAX(dir) 
              FROM uploaded_files
             WHERE dir LIKE :dir_bind;
        SQL;
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':dir_bind', $dirMask, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * Посчитать файлы
     *
     * @return integer
     */
    public function countFiles(): int
    {
        $query = <<<SQL
            SELECT COUNT(*)
              FROM uploaded_files;
        SQL;
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * Найти файлы
     *
     * @param integer $offset
     * @param integer|null $limit
     * @return array<string, mixed>
     */
    public function findFiles(int $offset = 0, ?int $limit = null): array
    {
        $query = <<<SQL
            SELECT id, 
                   filename,                    
                   dir,                                               
                   user_filename,                     
                   metadata,
                   user_comment,
                   created_at,
                   scope,
                   user_id
            FROM uploaded_files
           ORDER BY created_at desc
          OFFSET :offset_bind
        SQL;

        if (!is_null($limit)) {
            $query .= ' LIMIT :limit_bind';
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':offset_bind', $offset, PDO::PARAM_INT);

        if (!is_null($limit)) {
            $stmt->bindValue(':limit_bind', $limit, PDO::PARAM_INT);
        }

        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        return $stmt->fetchAll();
    }

    /**
     * Файл существует?
     *
     * @param integer $id
     * @return boolean
     */
    public function fileExists(int $id): bool
    {
        $file = $this->findFile($id);
        return (!is_null($file));
    }

    /**
     * Найти файлы через идентификатор пользователя
     *
     * @param integer $userId
     * @param integer $offset
     * @param integer|null $limit
     * @return array<string, mixed>
     */
    public function findFilesByUserId(int $userId, int $offset = 0, ?int $limit = null): array
    {
        return $this->findFilesByColumn(
            column: 'user_id',
            columnValue: $userId,
            offset: $offset,
            limit: $limit
        );
    }    

    /**
     * Посчитать файлы через идентификатор пользователя
     *
     * @param integer $userId
     * @return integer
     */
    public function countFilesByUserId(int $userId): int
    {
        return $this->countFilesByColumn('user_id', $userId);
    }

    /**
     * Найти файлы через столбец
     *
     * @param string $column
     * @param mixed $columnValue
     * @param integer $offset
     * @param integer|null $limit
     * @return array<string, mixed>
     */
    private function findFilesByColumn(
        string $column,
        mixed $columnValue,
        int $offset = 0,
        ?int $limit = null
    ): array {        
        if (!$this->checkColumn($column)) {
            throw new InvalidArgumentException("Неизвестный столбец {$column}");
        }

        $query = <<<SQL
            SELECT id, 
                   filename,                    
                   dir,                                               
                   user_filename,                     
                   metadata,
                   user_comment,
                   created_at,
                   scope,
                   user_id
            FROM uploaded_files
           WHERE {$column} = :column_value_bind
           ORDER BY created_at DESC
          OFFSET :offset_bind
        SQL;

        if (!is_null($limit)) {
            $query .= ' LIMIT :limit_bind';
        }

        $stmt = $this->pdo->prepare($query);        
        $stmt->bindValue(':column_value_bind', $columnValue);
        $stmt->bindValue(':offset_bind', $offset, PDO::PARAM_INT);

        if (!is_null($limit)) {
            $stmt->bindValue(':limit_bind', $limit, PDO::PARAM_INT);
        }

        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        return $stmt->fetchAll();
    }

    /**
     * Проверить столбец
     *
     * @param string $column
     * @return boolean
     */
    private function checkColumn(string $column): bool {
        $allowedColumns = [
            'id',
            'filename',
            'dir',
            'user_filename',
            'metadata',
            'user_comment',
            'created_at',
            'scope',
            'user_id'
        ];
        return in_array($column, $allowedColumns);
    }

    /**
     * Посчитать файлы через столбец
     *
     * @param string $column
     * @param mixed $columnValue
     * @return integer
     */
    private function countFilesByColumn(string $column, mixed $columnValue): int {
        if (!$this->checkColumn($column)) {
            throw new InvalidArgumentException("Неизвестный столбец {$column}");
        }

        $query = <<<SQL
            SELECT COUNT(*)
              FROM uploaded_files
            WHERE {$column} = :column_value_bind;
        SQL;
        $stmt = $this->pdo->prepare($query);        
        $stmt->bindValue(':column_value_bind', $columnValue);
        $stmt->execute();
        return $stmt->fetchColumn();
    }
}

<?php

declare(strict_types=1);

namespace App\Database;

use App\Entities\Files\VideoPreview;
use App\Interfaces\DirectoryFinderInterface as DirectoryFinder;
use InvalidArgumentException;

;

use Pdo;

/**
 * Шлюз для таблицы video_previews (видео-превью)
 */
class VideoPreviewGateway extends TableDataGateway implements DirectoryFinder
{
    /**
     * Добавить видео-превью
     *
     * @param VideoPreview $preview
     * @return integer
     */
    public function addPreview(VideoPreview $preview): int
    {
        $query = <<<SQL
        INSERT INTO video_previews (            
            uploaded_file_id, 
            filename, 
            created_at, 
            dir, 
            metadata,
            scope,
            is_original           
        )
        VALUES (            
            :uploaded_file_id_bind, 
            :filename_bind, 
            to_timestamp(:created_at_bind), 
            :dir_bind, 
            :metadata_bind,
            :scope_bind,
            :is_original_bind
        );
        SQL;
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':uploaded_file_id_bind', $preview->uploadedFileId, PDO::PARAM_STR);
        $stmt->bindValue(':filename_bind', $preview->filename, PDO::PARAM_STR);
        $stmt->bindValue(':created_at_bind', $preview->createdAt, PDO::PARAM_INT);
        $stmt->bindValue(':dir_bind', $preview->dir, PDO::PARAM_STR);

        $metadata = $this->serializeJson($preview->mediaInfo->metadata);
        $stmt->bindValue(':metadata_bind', $metadata, PDO::PARAM_STR);

        $stmt->bindValue(':scope_bind', $preview->scope, PDO::PARAM_STR);
        $stmt->bindValue(':is_original_bind', $preview->isOriginal, PDO::PARAM_BOOL);
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
              FROM video_previews
             WHERE dir LIKE :dir_bind;
        SQL;
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':dir_bind', $dirMask, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * Найти видео-превью через идентификатор загруженного файла
     *
     * @param integer $uploadedFileId
     * @return array<string, mixed>|null
     */
    public function findPreviewByUploadedFileId(int $uploadedFileId): ?array
    {
        return $this->findPreviewByColumn('uploaded_file_id', $uploadedFileId);
    }

    /**
     * Найти видео-превью через идентфиикатор
     *
     * @param integer $id
     * @return array<string, mixed>|null
     */
    public function findPreviewById(int $id): ?array {
        return $this->findPreviewByColumn('id', $id);
    }

    /**
     * Найти видео-превью через столбцец
     *
     * @param string $column
     * @param mixed $columnValue
     * @return array<string, mixed>|null
     */
    private function findPreviewByColumn(string $column, mixed $columnValue): ?array
    {
        if (!$this->checkColumn($column)) {
            throw new InvalidArgumentException("Неизвестный столбец {$column}");
        }

        $query = <<<SQL
            SELECT filename, 
                   created_at, 
                   dir, 
                   metadata,                                       
                   id,
                   uploaded_file_id,
                   scope,
                   is_original
            FROM video_previews
            WHERE {$column} = :column_value_bind;
        SQL;
        $stmt = $this->pdo->prepare($query);
        $stmt->bindColumn(':column_bind', $column);
        $stmt->bindValue(':column_value_bind', $columnValue);        
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
     * Проверить столбец
     *
     * @param string $column
     * @return boolean
     */
    private function checkColumn(string $column): bool {
        $allowedColumns = [
            'created_at',
            'dir',
            'metadata',
            'id',
            'uploaded_file_id',
            'scope',
            'is_original'
        ];
        return in_array($column, $allowedColumns);
    }
}

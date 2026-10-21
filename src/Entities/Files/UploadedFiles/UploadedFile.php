<?php

declare(strict_types=1);

namespace App\Entities\Files\UploadedFiles;

use App\Entities\MediaInfo\MediaInfo;
use App\Entities\Files\File;
use App\Entities\Comments\CommentTree;

/**
 * Загруженный файл
 */
class UploadedFile extends File
{    
    public readonly string $userFilename;
    public readonly CommentTree $commentTree;
    public readonly int $userId;    
    public readonly ?string $userComment;

    /**
     * Конструктор
     *
     * @param string $dir
     * @param MediaInfo $mediaInfo
     * @param string $filename
     * @param string $userFilename
     * @param integer $createdAt
     * @param string $scope
     * @param CommentTree $commentTree
     * @param integer $userId
     * @param string|null $url
     * @param integer|null $id
     * @param string|null $userComment
     */
    public function __construct(
        string $dir,
        MediaInfo $mediaInfo,                        
        string $filename,
        string $userFilename,
        int $createdAt,
        string $scope,
        CommentTree $commentTree,
        int $userId,
        ?string $url = null,
        ?int $id = null,
        ?string $userComment = null
    ) {
        parent::__construct(
            dir: $dir,  
            mediaInfo: $mediaInfo,
            filename: $filename,
            createdAt: $createdAt,                      
            scope: $scope,
            url: $url,
            id: $id                   
        );
        $this->userId = $userId;
        $this->userFilename = $userFilename;
        $this->userComment = $userComment;
        $this->commentTree = $commentTree;
    }

    /**
     * Получить специфичные данные
     *
     * @return array<string, mixed>
     */
    public function getSpecificData(): array
    {
        return [];
    }
}

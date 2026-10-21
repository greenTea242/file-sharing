<?php

declare(strict_types=1);

namespace App\Entities\Files\UploadedFiles;

use App\Entities\Comments\CommentTree;
use App\Entities\MediaInfo\AudioInfo;

/**
 * Загруженное видео
 */
class UploadedAudio extends UploadedFile
{
    public readonly AudioInfo $audioInfo;
    
    /**
     * Конструктор
     *
     * @param string $dir
     * @param AudioInfo $audioInfo
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
        AudioInfo $audioInfo,
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
            mediaInfo: $audioInfo,
            filename: $filename,
            userFilename: $userFilename,            
            createdAt: $createdAt,
            scope: $scope,
            commentTree: $commentTree,
            userId: $userId,           
            url: $url,
            id: $id,
            userComment: $userComment,                        
        );
        $this->audioInfo = $audioInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function getSpecificData(): array
    {
        $specificData = [];
            
        $specificData['playtime'] = $this->audioInfo->getPlaytime();
        return $specificData;
    }    
}

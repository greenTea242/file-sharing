<?php

declare(strict_types=1);

namespace App\Entities\Files\UploadedFiles;

use App\Entities\Comments\CommentTree;
use App\Entities\MediaInfo\ImageInfo;
use App\Entities\Files\Thumbnail;
use App\Entities\Files\UploadedFilePreviews\UploadedImagePreview;
use Exception;
use LogicException;

/**
 * Загруженное Изображение
 */
class UploadedImage extends UploadedFile
{
    private ?Thumbnail $thumbnail = null;
    public readonly ImageInfo $imageInfo;

    /**
     * Конструктор
     *
     * @param string $dir
     * @param ImageInfo $imageInfo
     * @param string $filename
     * @param string $userFilename
     * @param integer $createdAt
     * @param string $scope
     * @param CommentTree $commentTree
     * @param integer $userId
     * @param string|null $url
     * @param integer|null $id
     * @param string|null $userComment
     * @param Thumbnail|null $thumbnail
     */
    public function __construct(
        string $dir,
        ImageInfo $imageInfo,        
        string $filename,
        string $userFilename,
        int $createdAt,
        string $scope,
        CommentTree $commentTree,
        int $userId,
        ?string $url = null,
        ?int $id = null,
        ?string $userComment = null,
        ?Thumbnail $thumbnail = null
    ) {
         parent::__construct(            
            dir: $dir,
            mediaInfo: $imageInfo,
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
        $this->thumbnail = $thumbnail;
        $this->imageInfo = $imageInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function getSpecificData(): array
    {
        $specificData = [];

        if ($this->hasThumbnail()) {
            $specificData['thumbnail'] = $this->getThumbnail();
        }
        
        return $specificData;
    }

    /**
     * Установить миниатюру
     *
     * @param Thumbnail $thumbnail
     * @return void
     */
    public function setThumbnail(Thumbnail $thumbnail): void
    {
        $this->thumbnail = $thumbnail;
    }

    /**
     * Получить миниатюру
     *
     * @return Thumbnail
     */
    public function getThumbnail(): Thumbnail
    {
        if (is_null($this->thumbnail)) {
            $error = 'Поле $this->thumbnail должно быть установлено ' .
                'перед вызовом метода getThumbnail()';
            throw new LogicException($error);
        }
        return $this->thumbnail;
    }
    
    /**
     * Есть миниатюра?
     *
     * @return boolean
     */
    private function hasThumbnail(): bool {
        return (!is_null($this->thumbnail));
    }
}

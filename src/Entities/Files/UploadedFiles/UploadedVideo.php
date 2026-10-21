<?php

declare(strict_types=1);

namespace App\Entities\Files\UploadedFiles;

use App\Entities\Comments\CommentTree;
use App\Entities\Files\Thumbnail;
use App\Entities\MediaInfo\VideoInfo;
use App\Entities\Files\UploadedFilePreviews\UploadedImagePreview;
use App\Entities\Files\VideoPreview;
use Exception;
use LogicException;

/**
 * Загруженное видео
 */
class UploadedVideo extends UploadedFile
{
    public readonly VideoInfo $videoInfo;
    private ?Thumbnail $thumbnail = null;
    private ?VideoPreview $videoPreview = null;

    /**
     * Конструктор
     *
     * @param string $dir
     * @param VideoInfo $videoInfo
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
     * @param VideoPreview|null $videoPreview
     */
    public function __construct(
        string $dir,
        VideoInfo $videoInfo,
        string $filename,
        string $userFilename,
        int $createdAt,
        string $scope,
        CommentTree $commentTree,
        int $userId,
        ?string $url = null,
        ?int $id = null,
        ?string $userComment = null,
        ?Thumbnail $thumbnail = null,
        ?VideoPreview $videoPreview = null
    ) {
         parent::__construct(            
            dir: $dir,
            mediaInfo: $videoInfo,
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
        $this->videoPreview = $videoPreview;
        $this->videoInfo = $videoInfo;
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
     * Установить видео-превью
     *
     * @param VideoPreview $videoPreview
     * @return void
     */
    public function setVideoPreview(VideoPreview $videoPreview): void
    {
        $this->videoPreview = $videoPreview;
    }

    /**
     * Получить видео-превью
     *
     * @return VideoPreview
     */
    public function getVideoPreview(): VideoPreview
    {
        if (is_null($this->videoPreview)) {
            $error = 'Поле $this->thumbnail должно быть установлено ' .
                'перед вызовом метода getVideoPreview()';
            throw new LogicException($error);
        }
        return $this->videoPreview;
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
        
        if ($this->hasVideoPreview()) {
            $specificData['videoPreview'] = $this->getVideoPreview();        
        }

        return $specificData;
    }

    /**
     * Есть миниатюра?
     *
     * @return boolean
     */
    private function hasThumbnail(): bool {
        return (!is_null($this->thumbnail));
    }

    /**
     * Есть видео-превью?
     *
     * @return boolean
     */
    private function hasVideoPreview(): bool {
        return (!is_null($this->videoPreview));
    } 
}

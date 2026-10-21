<?php

declare(strict_types=1);

namespace App\Services\FileServices;

use App\Database\VideoPreviewGateway;
use App\DTO\FileInfoDTO;
use App\Entities\Files\File;
use App\Entities\Files\UploadedFiles\UploadedFile;
use App\Entities\Files\UploadedFiles\UploadedVideo;
use App\Entities\MediaInfo\VideoInfo;
use App\Factories\MediaInfoFactory;
use App\Utilities\FileUtils;
use Exception;
use Slim\Routing\RouteParser;

/**
 * Сервия для видео-превью
 */
class VideoPreviewService extends FileService
{
    private MediaInfoFactory $mediaInfoFactory;
    private RouteParser $routeParser;

    /**
     * Конструктор
     *
     * @param VideoPreviewGateway $videoPreviewGateway
     * @param MediaInfoFactory $mediaInfoFactory
     * @param RouteParser $routeParser
     */
    public function __construct(
        VideoPreviewGateway $videoPreviewGateway,
        MediaInfoFactory $mediaInfoFactory,
        RouteParser $routeParser        
    ) {
        parent::__construct($videoPreviewGateway);
        $this->mediaInfoFactory = $mediaInfoFactory;
        $this->routeParser = $routeParser;
    }

    /**
     * @inheritDoc          
     */
    public function getScope(): string
    {
        return File::SCOPE_PRIVATE;
    }

    /**
     * Получить URL
     *
     * @param integer $id     
     * @return string
     */
    public function getUrl(int $id): string
    {
        $data = [
            'id' => (string)$id
        ];
        return $this->routeParser->urlFor('video-previews.download', $data);
    }

    /**
     * Получить имя видео-превью
     *
     * @param UploadedVideo $video
     * @return string
     */
    public function getFilename(UploadedVideo $video): string
    {
        $ext = $this->getExt();
        $filenameWithoutExt = $this->getFilenameWithoutExt($video);
        return FileUtils::getFilenameByParts($filenameWithoutExt, $ext);
    }

    /**
     * Превью оригинал?
     *
     * @param UploadedVideo $video
     * @param FileInfoDTO $previewInfoDTO
     * @return boolean
     */
    public function isOriginal(UploadedVideo $video, FileInfoDTO $previewInfoDTO): bool
    {
        $path = $this->getPath(
            filename: $previewInfoDTO->filename,
            dir: $previewInfoDTO->dir,
            scope: $previewInfoDTO->scope
        );
        if ($path == $video->getPath()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Получить информацию о видео через видео
     *
     * @param UploadedVideo $video
     * @param FileInfoDTO $previewInfoDTO
     * @param boolean $isOriginal
     * @return VideoInfo
     */
    public function getVideoInfoByVideo(
        UploadedVideo $video,
        FileInfoDTO $previewInfoDTO,
        bool $isOriginal
    ): VideoInfo {
        if ($isOriginal) {
            return clone $video->videoInfo;
        }
        $path = $this->getPath(
            filename: $previewInfoDTO->filename,
            dir: $previewInfoDTO->dir,
            scope: $previewInfoDTO->scope
        );
        $videoInfo = $this->mediaInfoFactory->createByPath($path);
        if (!($videoInfo instanceof VideoInfo)) {
            throw new Exception('Информация должна быть о видео');
        }                
        return $videoInfo;
    }

    /**
     * Получить информацию о видео через метаданные
     *
     * @param string $metadata
     * @return VideoInfo
     */
    public function getVideoInfoByMetadata(string $metadata): VideoInfo {
        $videoInfo = $this->mediaInfoFactory->createByMetadata($metadata);
        if (!($videoInfo instanceof VideoInfo)) {
            throw new Exception('Информация должна быть о видео');
        }
        return $videoInfo;
    }

    /**
     * @inheritDoc
     */
    protected function getParentDir(): string
    {
        $parentDir = 'uploads/video_previews';
        $date = gmdate('y/m/d');
        $parentDir .= "/{$date}";
        return $parentDir;
    }

    /**
     * Получить расширение
     *
     * @return string
     */
    private function getExt(): string
    {
        return FileUtils::EXTENSION_MP4;
    }

    /**
     * Получить имя файла без расширения
     *
     * @param UploadedVideo $video
     * @return string
     */
    private function getFilenameWithoutExt(UploadedVideo $video): string
    {
        $videoFilenameWithoutExt = $video->getFilenameWithoutExt();
        return "{$videoFilenameWithoutExt}_preview";
    }
}

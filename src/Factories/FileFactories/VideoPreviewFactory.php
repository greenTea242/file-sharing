<?php

declare(strict_types=1);

namespace App\Factories\FileFactories;

use App\Database\VideoPreviewGateway;
use App\DTO\FileInfoDTO;
use App\Entities\Files\VideoPreview;
use App\Entities\Files\UploadedFiles\UploadedVideo;
use App\Exceptions\VideoPreviewNotFoundException;
use App\Services\FileServices\VideoPreviewService;
use Exception;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Фабрика превью загруженных файлов
 */
class VideoPreviewFactory
{
    private VideoPreviewService $videoPreviewService;
    private VideoPreviewGateway $videoPreviewGateway;

    /**
     * Конструктор
     *
     * @param VideoPreviewService $videoPreviewService     
     * @param VideoPreviewGateway $videoPreviewGateway
     */
    public function __construct(
        VideoPreviewService $videoPreviewService,
        VideoPreviewGateway $videoPreviewGateway
    ) {
        $this->videoPreviewService = $videoPreviewService;
        $this->videoPreviewGateway = $videoPreviewGateway;
    }

    /**
     * Создать видео-превью через загруженное видео
     *
     * @param UploadedVideo $video
     * @param FileInfoDTO $previewInfoDTO
     * @return VideoPreview
     */
    public function createByUploadedFile(
        UploadedVideo $video,
        FileInfoDTO $previewInfoDTO
    ): VideoPreview {
        $isOriginal = $this->videoPreviewService->isOriginal($video, $previewInfoDTO);
        $videoInfo = $this->videoPreviewService->getVideoInfoByVideo(
            video: $video,
            previewInfoDTO: $previewInfoDTO,
            isOriginal: $isOriginal
        );
        return new VideoPreview(
            dir: $previewInfoDTO->dir,
            videoInfo: $videoInfo,
            filename: $previewInfoDTO->filename,
            createdAt: $previewInfoDTO->createdAt,
            uploadedFileId: $video->getId(),
            scope: $previewInfoDTO->scope,
            isOriginal: $isOriginal
        );
    }

    /**
     * Создать видео-превью через идентификатор загруженного файла
     *
     * @param integer $uploadedFileId
     * @return VideoPreview|null
     */
    public function createByUploadedFileId(int $uploadedFileId): ?VideoPreview
    {
        $previewData = $this->videoPreviewGateway->findPreviewByUploadedFileId($uploadedFileId);

        if (is_null($previewData)) {
            return null;
        }
      
        return $this->createByPreviewData($previewData);
    }

    /**
     * Создать видео-превью через идентификатор,
     * или выбросить исключение
     *
     * @param integer $id
     * @return VideoPreview
     */
    public function createByIdOrFail(int $id): VideoPreview
    {
        $previewData = $this->videoPreviewGateway->findPreviewById($id);

        if (is_null($previewData)) {
            throw new Exception('Видео-превью не найдено');
        }
        
        return $this->createByPreviewData($previewData);
    }

    /**
     * Создать видео-превью через данные видео-превью
     *
     * @param array<string, mixed> $previewData
     * @return VideoPreview
     */
    private function createByPreviewData(array $previewData): VideoPreview
    {
        $filename = $previewData['filename'] ??
            throw new Exception('Отсутствует ключ filename в массиве');

        $createdAtRaw = $previewData['created_at'] ??
            throw new Exception('Отсутствует ключ created_at в массиве');
        $createdAt = $this->videoPreviewService->getCreatedAtByRaw($createdAtRaw);

        $dir = $previewData['dir'] ??
            throw new Exception('Отсутствует ключ dir в массиве');
        $id = $previewData['id'] ?? throw new Exception('Отсутствует ключ id в массиве');

        $uploadedFileId = $previewData['uploaded_file_id'] ??
            throw new Exception('Отсутствует ключ uploaded_file_id в массиве');

        $metadata = $previewData['metadata'] ??
            throw new Exception('Отсутствует ключ metadata в массиве');
        $videoInfo = $this->videoPreviewService->getVideoInfoByMetadata($metadata);

        $scope = $previewData['scope'] ??
            throw new Exception('Отсутствует ключ scope в массиве');
        $url = $this->videoPreviewService->getUrl($id);
        $isOriginal = $previewData['is_original'] ??
            throw new Exception('Отсутствует ключ is_original в массиве');            
        return new VideoPreview(
            filename: $filename,
            createdAt: $createdAt,
            dir: $dir,
            videoInfo: $videoInfo,
            uploadedFileId: $uploadedFileId,
            id: $id,
            url: $url,
            scope: $scope,
            isOriginal: $isOriginal
        );            
    }
}

<?php

declare(strict_types=1);

namespace App\Factories\FileFactories;

use App\Database\ThumbnailGateway;
use App\DTO\FileInfoDTO;
use App\Entities\Files\Thumbnail;
use App\Entities\Files\UploadedFilePreviews\UploadedFilePreview;
use App\Entities\Files\UploadedFiles\UploadedFile;
use App\Entities\MediaInfo\ImageInfo;
use App\Exceptions\ThumbnailNotFoundException;
use App\Factories\MediaInfoFactory;
use App\Services\FileServices\ThumbnailService;
use App\Utilities\FileUtils;
use Exception;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Фабрика миниатюр
 */
class ThumbnailFactory
{
    private ThumbnailService $thumbnailService;
    private MediaInfoFactory $mediaInfoFactory;
    private ThumbnailGateway $thumbnailGateway;

    /**
     * Конструктор
     *
     * @param ThumbnailService $thumbnailService
     * @param MediaInfoFactory $mediaInfoFactory
     * @param ThumbnailGateway $thumbnailGateway
     */
    public function __construct(
        ThumbnailService $thumbnailService,
        MediaInfoFactory $mediaInfoFactory,
        ThumbnailGateway $thumbnailGateway   
    ) {
        $this->thumbnailService = $thumbnailService;
        $this->mediaInfoFactory = $mediaInfoFactory;
        $this->thumbnailGateway = $thumbnailGateway;
    }

    /**
     * Создать миниатюру через загруженный файл
     *
     * @param UploadedFile $file
     * @param FileInfoDTO $thumbnailInfoDTO
     * @return Thumbnail
     */
    public function createByUploadedFile(
        UploadedFile $file,
        FileInfoDTO $thumbnailInfoDTO
    ): Thumbnail {
        $path = $this->thumbnailService->getPath(
            filename: $thumbnailInfoDTO->filename,
            dir: $thumbnailInfoDTO->dir,
            scope: $thumbnailInfoDTO->scope
        );
        $mediaInfo = $this->mediaInfoFactory->createByPath($path);
        if (!($mediaInfo instanceof ImageInfo)) {
            throw new Exception('Некорректный тип информации');
        }
        /**
         * @var ImageInfo $imageInfo
         */
        $imageInfo = $mediaInfo;

        $uploadedFileId = $file->getId();
        $url = $this->thumbnailService->getUrl(
            $thumbnailInfoDTO->filename, 
            $thumbnailInfoDTO->dir
        );
        return new Thumbnail(
            uploadedFileId: $uploadedFileId,
            filename: $thumbnailInfoDTO->filename,
            createdAt: $thumbnailInfoDTO->createdAt,
            dir: $thumbnailInfoDTO->dir,
            imageInfo: $imageInfo,
            scope: $thumbnailInfoDTO->scope,
            url: $url
        );
    }

    /**
     * Создать миниатюру через идентификатор загруженного файла
     *
     * @param integer $uploadedFileId
     * @return Thumbnail|null
     */
    public function createByUploadedFileId(int $uploadedFileId): ?Thumbnail {
        $thumbnailData = $this->thumbnailGateway->findThumbnail($uploadedFileId);

        if (is_null($thumbnailData)) {
            return null;
        }

        $imageInfo = $this->mediaInfoFactory->createByMetadata($thumbnailData['metadata']);
        
        if (!($imageInfo instanceof ImageInfo)) {
            throw new Exception('Информация должна быть о изображении');
        }

        $filename = $thumbnailData['filename'] ?? 
            throw new Exception('Отсутствует ключ filename в массиве');

        $createdAtRaw = $thumbnailData['created_at'] ?? 
            throw new Exception('Отсутствует ключ created_at в массиве');
        $createdAt = $this->thumbnailService->getCreatedAtByRaw($createdAtRaw);
        
        $dir = $thumbnailData['dir'] ?? throw new Exception('Отсутствует ключ dir в массиве');        
        $id = $thumbnailData['id'] ?? throw new Exception('Отсутствует ключ id в массиве');
        $uploadedFileId = $thumbnailData['uploaded_file_id'] ?? 
            throw new Exception('Отсутствует ключ uploaded_file_id в массиве');
        $scope = $thumbnailData['scope'] ??
            throw new Exception('Отсутсвует ключ scope в массиве');
        $url = $this->thumbnailService->getUrl($filename, $dir);
        return new Thumbnail(
            filename: $filename,
            createdAt: $createdAt,
            dir: $dir,
            imageInfo: $imageInfo,
            uploadedFileId: $uploadedFileId,
            id: $id,
            scope: $scope,
            url: $url
        );
    }
}

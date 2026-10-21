<?php

declare(strict_types=1);

namespace App\Factories\FileFactories;

use App\Database\CommentGateway;
use App\Forms\FileUploadForm;
use App\Services\FileServices\UploadedFileService;
use App\Database\UploadedFileGateway;
use App\DTO\FileInfoDTO;
use App\Factories\MediaInfoFactory;
use App\Exceptions\FileNotFoundException;
use App\Entities\MediaInfo\AudioInfo;
use App\Entities\MediaInfo\ImageInfo;
use App\Entities\MediaInfo\VideoInfo;
use App\Entities\MediaInfo\MediaInfo;
use App\Entities\Files\UploadedFiles\UploadedAudio;
use App\Entities\Files\UploadedFiles\UploadedImage;
use App\Entities\Files\UploadedFiles\UploadedVideo;
use App\Entities\Files\UploadedFiles\UploadedFile;
use App\Entities\User;
use App\Factories\CommentFactories\CommentFactory;
use App\Factories\CommentFactories\CommentTreeFactory;
use Exception;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Фабрика загруженных файлов
 */
class UploadedFileFactory
{
    private UploadedFileGateway $uploadedFileGateway;
    private MediaInfoFactory $mediaInfoFactory;
    private UploadedFileService $uploadedFileService;
    private VideoPreviewFactory $videoPreviewFactory;
    private ThumbnailFactory $thumbnailFactory;
    private CommentTreeFactory $commentTreeFactory;
    private CommentFactory $commentFactory;

    /**
     * Конструктор
     *
     * @param UploadedFileGateway $uploadedFileGateway
     * @param MediaInfoFactory $mediaInfoFactory
     * @param UploadedFileService $uploadedFileService
     * @param VideoPreviewFactory $videoPreviewFactory
     * @param ThumbnailFactory $thumbnailFactory
     * @param CommentTreeFactory $commentTreeFactory
     * @param CommentFactory $commentFactory
     */
    public function __construct(
        UploadedFileGateway $uploadedFileGateway,
        MediaInfoFactory $mediaInfoFactory,
        UploadedFileService $uploadedFileService,
        VideoPreviewFactory $videoPreviewFactory,
        ThumbnailFactory $thumbnailFactory,
        CommentTreeFactory $commentTreeFactory,
        CommentFactory $commentFactory
    ) {
        $this->uploadedFileGateway = $uploadedFileGateway;
        $this->mediaInfoFactory = $mediaInfoFactory;
        $this->uploadedFileService = $uploadedFileService;
        $this->videoPreviewFactory = $videoPreviewFactory;
        $this->thumbnailFactory = $thumbnailFactory;
        $this->commentTreeFactory = $commentTreeFactory;
        $this->commentFactory = $commentFactory;
    }

    /**
     * Создать файл через форму для загрузки файла
     *
     * @param FileUploadForm $form     
     * @return UploadedFile
     */
    public function createByFileUploadForm(
        FileUploadForm $form,        
        FileInfoDTO $fileInfoDTO,
        User $user
    ): UploadedFile {
        $path = $this->uploadedFileService->getPath(
            filename: $fileInfoDTO->filename,
            dir: $fileInfoDTO->dir,
            scope: $fileInfoDTO->scope
        );
        $mediaInfo = $this->mediaInfoFactory->createByPath($path);
        $userFilename = $this->uploadedFileService->getUserFilename($form->file);        
        $userComment = $this->uploadedFileService->getUserComment($form->userComment);
        return $this->create(
            filename: $fileInfoDTO->filename,            
            dir: $fileInfoDTO->dir,
            mediaInfo: $mediaInfo,            
            userFilename: $userFilename,
            createdAt: $fileInfoDTO->createdAt,
            userComment: $userComment,
            scope: $fileInfoDTO->scope,
            userId: $user->getId()            
        );
    }

    /**
     * Создать файл через идентификатор, или выбросить исключение
     *
     * @param integer $id     
     * @return UploadedFile
     */
    public function createByIdOrFail(int $id): UploadedFile
    {
        $fileData = $this->uploadedFileGateway->findFile($id);

        if (is_null($fileData)) {
            throw new Exception('Файл не найден');
        }    

        return $this->createByFileData($fileData);
    }

    /**
     * Создать файл через идентификатор
     *
     * @param integer $id     
     * @return UploadedFile
     */
    public function createById(int $id): ?UploadedFile
    {
        $fileData = $this->uploadedFileGateway->findFile($id);

        if (is_null($fileData)) {
            return null;
        }    

        return $this->createByFileData($fileData);
    }    

    /**
     * Создать файлы
     *
     * @param integer $offset
     * @param integer|null $limit
     * @return array<UploadedFile>
     */
    public function createMany(int $offset = 0, ?int $limit = null): array
    {
        $filesData = $this->uploadedFileGateway->findFiles($offset, $limit);

        if (empty($filesData)) {
            return [];
        }

        $files = [];

        foreach ($filesData as $fileData) {
            $file = $this->createByFileData($fileData);
            $files[] = $file;
        }

        return $files;
    }

    /**
     * Создать файлы через идентификатор пользователя
     *
     * @param integer $userId
     * @return array<UploadedFile>
     */
    public function createManyByUserId(int $userId, int $offset = 0, ?int $limit = null): array
    {
        $filesData = $this->uploadedFileGateway->findFilesByUserId($userId, $offset, $limit);

        if (empty($filesData)) {
            return [];
        }

        $files = [];

        foreach ($filesData as $fileData) {
            $file = $this->createByFileData($fileData);
            $files[] = $file;
        }

        return $files;
    }  

    /**
     * Создать файл через данные файла
     *
     * @param array<string, mixed> $fileData     
     * @return UploadedFile
     */
    private function createByFileData(array $fileData): UploadedFile
    {
        $metadata = $fileData['metadata'] ?? throw new Exception('Отсутсвует ключ metadata в массиве');
        $mediaInfo = $this->mediaInfoFactory->createByMetadata($metadata);

        $id = $fileData['id'] ?? throw new Exception('Отсутствует ключ id в массиве');
        $filename = $fileData['filename'] ??
            throw new Exception('Отсутствует ключ filename в массиве');
        $dir = $fileData['dir'] ?? throw new Exception('Отсутствует ключ dir в массиве');
        $userFilename = $fileData['user_filename'] ??
            throw new Exception('Отсутствует ключ user_filename в массиве');

        if (array_key_exists('user_comment', $fileData)) {
            $userComment = $fileData['user_comment'];            
        } else {
            throw new Exception('Отсутствует ключ user_comment в массиве');
        }

        $createdAtRaw = $fileData['created_at'] ??
            throw new Exception('Отсутствует ключ created_at в массиве');
        $createdAt = $this->uploadedFileService->getCreatedAtByRaw($createdAtRaw);
        
        $scope = $fileData['scope'] ?? throw new Exception('Отсутствует ключ scope в массиве');
        $userId =  $fileData['user_id'] ?? throw new Exception('Отсутствует ключ user_id в массиве');
        $url = $this->uploadedFileService->getUrl($id);
        return $this->create(
            id: $id,
            filename: $filename,            
            dir: $dir,
            mediaInfo: $mediaInfo,            
            userFilename: $userFilename,
            userComment: $userComment,
            createdAt: $createdAt,
            url: $url,
            scope: $scope,
            userId: $userId
        );
    }

    /**
     * Создать файл
     *
     * @param string $filename
     * @param string $dir
     * @param MediaInfo $mediaInfo
     * @param string $userFilename
     * @param integer $createdAt
     * @param string $scope
     * @param integer $userId
     * @param string|null $userComment
     * @param integer|null $id
     * @param string|null $url
     * @return UploadedFile
     */
    private function create(
        string $filename,        
        string $dir,
        MediaInfo $mediaInfo,        
        string $userFilename,
        int $createdAt,
        string $scope,        
        int $userId,
        ?string $userComment = null,
        ?int $id = null,
        ?string $url = null
    ): UploadedFile {
        $videoPreview = null;
        $thumbnail = null;
        $comments = [];
        if (!is_null($id)) {
            if (
                $mediaInfo instanceof VideoInfo ||
                $mediaInfo instanceof ImageInfo
            ) {
                $thumbnail = $this->thumbnailFactory->createByUploadedFileId($id); 
            }

            if ($mediaInfo instanceof VideoInfo) {                
                $videoPreview = $this->videoPreviewFactory->createByUploadedFileId($id);
            }

            $comments = $this->commentFactory->createMany($id);
        }

        $commentTree = $this->commentTreeFactory->create($comments);

        if ($mediaInfo instanceof AudioInfo) {
            $file = new UploadedAudio(
                id: $id,
                filename: $filename,                
                audioInfo: $mediaInfo,
                dir: $dir,                
                userFilename: $userFilename,
                userComment: $userComment,
                createdAt: $createdAt,
                scope: $scope,
                url: $url,
                commentTree: $commentTree,
                userId: $userId
            );
        } elseif ($mediaInfo instanceof VideoInfo) {
            $file = new UploadedVideo(
                id: $id,
                filename: $filename,                
                videoInfo: $mediaInfo,
                dir: $dir,                
                userFilename: $userFilename,
                userComment: $userComment,
                createdAt: $createdAt,
                thumbnail: $thumbnail,
                videoPreview: $videoPreview,
                scope: $scope,
                url: $url,
                commentTree: $commentTree,
                userId: $userId
            );
        } elseif ($mediaInfo instanceof ImageInfo) {
            $file = new UploadedImage(
                id: $id,
                filename: $filename,                
                imageInfo: $mediaInfo,
                dir: $dir,                
                userFilename: $userFilename,
                userComment: $userComment,
                createdAt: $createdAt,
                thumbnail: $thumbnail,
                scope: $scope,
                url: $url,
                commentTree: $commentTree,
                userId: $userId
            );
        } elseif ($mediaInfo instanceof MediaInfo) {
            $file = new UploadedFile(
                id: $id,
                filename: $filename,                
                mediaInfo: $mediaInfo,
                dir: $dir,                
                userFilename: $userFilename,
                userComment: $userComment,
                createdAt: $createdAt,
                scope: $scope,                
                url: $url,
                commentTree: $commentTree,
                userId: $userId
            );
        } else {
            throw new Exception('Неизвестная информация о медиа');
        }

        return $file;
    }  
}

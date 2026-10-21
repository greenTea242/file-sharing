<?php

declare(strict_types=1);

namespace App\Queue\Consumers;

use App\Database\ThumbnailGateway;
use App\Entities\Files\Thumbnail;
use App\Entities\Files\UploadedFiles\UploadedVideo;
use App\Factories\FileFactories\ThumbnailFactory;
use App\Factories\RedisQueueClientFactory;
use App\Loggers\QueueLoggers\ConsumerLoggers\ThumbnailConsumerLogger;
use Throwable;
use InvalidArgumentException;
use App\Services\FileServices\ThumbnailService;
use App\Storages\ThumbnailStorage;
use Exception;

/**
 * Потребитель миниатюр
 */
class ThumbnailConsumer extends Consumer
{
    private ThumbnailService $thumbnailService;
    private ThumbnailStorage $thumbnailStorage;
    private ThumbnailFactory $thumbnailFactory;
    private ThumbnailGateway $thumbnailGateway;

    /**
     * Конструктор
     *
     * @param ThumbnailService $thumbnailService
     * @param ThumbnailStorage $thumbnailStorage
     * @param ThumbnailFactory $thumbnailFactory
     * @param ThumbnailGateway $thumbnailGateway
     * @param RedisQueueClientFactory $redisQueueClientFactory
     * @param boolean $isEnabled
     * @param ThumbnailConsumerLogger $logger
     */
    public function __construct(
        ThumbnailService $thumbnailService,
        ThumbnailStorage $thumbnailStorage,
        ThumbnailFactory $thumbnailFactory,
        ThumbnailGateway $thumbnailGateway,
        RedisQueueClientFactory $redisQueueClientFactory,
        bool $isEnabled,
        ThumbnailConsumerLogger $logger
    ) {
        parent::__construct(
            isEnabled: $isEnabled, 
            redisQueueClientFactory: $redisQueueClientFactory,
            logger: $logger
        );
        $this->thumbnailService = $thumbnailService;
        $this->thumbnailStorage = $thumbnailStorage;
        $this->thumbnailFactory = $thumbnailFactory;
        $this->thumbnailGateway = $thumbnailGateway;
    }

    /**
     * @inheritDoc          
     */
    public function getQueue(): string
    {
        return 'thumbnail consumer queue';
    }

    /**
     * @inheritDoc       
     */
    protected function processInternal(mixed $data): void
    {   
        $object = unserialize($data)     ;
        if (!($object instanceof UploadedVideo)) {
            throw new InvalidArgumentException('Пришло не загруженное видео');
        }
        /** @var UploadedVideo $video */
        $video = $object;                

        $message = 'Начинаем делать миниатюру для видео';            
        $this->logger->info($message, ['id' => $video->getId()]);

        $thumbnail = $this->createThumbnail($video);        
        $message = 'Сделали';
        $this->logger->info($message, [
            'id' => $thumbnail->getId(),
            'uploadedFileId' => $thumbnail->uploadedFileId
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function getWorkerLogPath(): string
    {                
        return __DIR__ . '/../../../var/log/thumbnail_worker/worker.log';
    }

    /**
     * Создать превью
     *
     * @param UploadedVideo $video
     * @return Thumbnail
     */
    private function createThumbnail(UploadedVideo $video): Thumbnail
    {
        $thumbnailInfoDTO = $this->thumbnailStorage->saveByVideo($video);
        try {
            $thumbnail = $this->thumbnailFactory->createByUploadedFile(
                $video, 
                $thumbnailInfoDTO
            );
            $this->thumbnailGateway->beginTransaction();
            
            $id = $this->thumbnailGateway->addThumbnail($thumbnail);
            $thumbnail->setId($id);

            $url = $this->thumbnailService->getUrl(
                $thumbnail->filename, 
                $thumbnail->dir
            );
            $thumbnail->setUrl($url);

            $this->thumbnailGateway->commit();
            return $thumbnail;
        } catch (Exception $e) {
            $this->thumbnailStorage->delete($thumbnailInfoDTO->getPath());
            throw $e;
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Queue\Consumers;

use App\Database\VideoPreviewGateway;
use App\Entities\Files\VideoPreview;
use App\Entities\Files\UploadedFiles\UploadedVideo;
use App\Factories\FileFactories\VideoPreviewFactory;
use App\Factories\RedisQueueClientFactory;
use App\Loggers\QueueLoggers\ConsumerLoggers\VideoPreviewConsumerLogger;
use InvalidArgumentException;
use App\Services\FileServices\VideoPreviewService;
use App\Storages\VideoPreviewStorage;
use Exception;

/**
 * Потребитель видео-превью
 */
class VideoPreviewConsumer extends Consumer
{
    private VideoPreviewFactory $videoPreviewFactory;
    private VideoPreviewGateway $videoPreviewGateway;
    private VideoPreviewService $videoPreviewService;
    private VideoPreviewStorage $videoPreviewStorage;

    /**
     * Конструктор
     *
     * @param VideoPreviewFactory $videoPreviewFactory
     * @param VideoPreviewGateway $videoPreviewGateway
     * @param VideoPreviewService $videoPreviewService
     * @param VideoPreviewStorage $videoPreviewStorage
     * @param boolean $isEnabled
     * @param RedisQueueClientFactory $redisQueueClientFactory
     */
    public function __construct(
        VideoPreviewFactory $videoPreviewFactory,
        VideoPreviewGateway $videoPreviewGateway,
        VideoPreviewService $videoPreviewService,
        VideoPreviewStorage $videoPreviewStorage,
        bool $isEnabled,
        RedisQueueClientFactory $redisQueueClientFactory,
        VideoPreviewConsumerLogger $logger
    ) {
        parent::__construct(
            isEnabled: $isEnabled, 
            redisQueueClientFactory: $redisQueueClientFactory,
            logger: $logger
        );
        $this->videoPreviewFactory = $videoPreviewFactory;
        $this->videoPreviewGateway = $videoPreviewGateway;
        $this->videoPreviewService = $videoPreviewService;
        $this->videoPreviewStorage = $videoPreviewStorage;
    }

    /**
     * @inheritDoc          
     */
    public function getQueue(): string
    {
        return 'video preview consumer queue';
    }

    /**
     * @inheritDoc     
     */
    protected function processInternal(mixed $data): void
    {    
        $object = unserialize($data);    
        if (!($object instanceof UploadedVideo)) {            
            throw new InvalidArgumentException('Пришло не загруженное видео');
        }        
        /** @var UploadedVideo $video */
        $video = $object;
        
        $message = 'Начинаем делать видео-превью для видео';
        $this->logger->info($message, ['id' => $video->getId()]);

        $videoPreview = $this->createVideoPreview($video);
        $message = 'Сделали';
        $this->logger->info($message, [
            'id' => $videoPreview->getId(), 
            'uploadedFileId' => $videoPreview->uploadedFileId
        ]);

    }

    /**
     * Создать видео-превью
     *
     * @param UploadedVideo $video
     * @return VideoPreview
     */
    private function createVideoPreview(UploadedVideo $video): VideoPreview
    {
        $previewInfoDTO = $this->videoPreviewStorage->save($video);
        try {
            $preview = $this->videoPreviewFactory->createByUploadedFile(
                $video, 
                $previewInfoDTO
            );
            $this->videoPreviewGateway->beginTransaction();
            
            $id = $this->videoPreviewGateway->addPreview($preview);
            $preview->setId($id);

            $url = $this->videoPreviewService->getUrl($id);
            $preview->setUrl($url);

            $video->setVideoPreview($preview);

            $this->videoPreviewGateway->commit();
            return $preview;
        } catch (Exception $e) {
            $this->videoPreviewStorage->delete($previewInfoDTO->getPath());
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    protected function getWorkerLogPath(): string
    {
        return __DIR__ . '/../../../var/log/video_preview_worker/worker.log';
    }
}

<?php

declare(strict_types=1);

namespace App\Loggers\QueueLoggers\ConsumerLoggers;

/**
 * Логгер для потребителя видео-превью
 */
class VideoPreviewConsumerLogger extends ConsumerLogger {
    /**
     * @inheritDoc          
     */
    protected function getPath(): string
    {
        return __DIR__ . '/../../../var/log/video_preview_worker/app.log';
    }

    /**
     * @inheritDoc          
     */    
    protected function getChannel(): string {
        return 'video_preview_worker';
    }
}
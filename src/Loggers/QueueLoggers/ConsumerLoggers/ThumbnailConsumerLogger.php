<?php

declare(strict_types=1);

namespace App\Loggers\QueueLoggers\ConsumerLoggers;

/**
 * Логгер для потребителя миниатюр
 */
class ThumbnailConsumerLogger extends ConsumerLogger {
    /**
     * @inheritDoc          
     */
    protected function getPath(): string
    {
        return __DIR__ . '/../../../var/log/thumbnail_worker/app.log';
    }

    /**
     * @inheritDoc          
     */    
    protected function getChannel(): string {
        return 'thumbnail_worker';
    }
}
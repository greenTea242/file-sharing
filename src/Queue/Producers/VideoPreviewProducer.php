<?php

declare(strict_types=1);

namespace App\Queue\Producers;

use App\Entities\Files\UploadedFiles\UploadedVideo;

/**
 * Продюсер видео-превью
 */
class VideoPreviewProducer extends Producer {
    /**
     * Отправить задачу в воркер
     *
     * @param UploadedVideo $video
     * @return void
     */    
    public function send(UploadedVideo $video): void {
        $serializedVideo = serialize($video);
        $this->sendInternal($serializedVideo);
    }
}
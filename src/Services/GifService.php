<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Media\Video;
use InvalidArgumentException;

/**
 * Сервис для GIF файлов
 */
class GifService
{
    private FFMpeg $ffmpeg;

    /**
     * Конструктор
     *
     * @param FFMpeg $ffmpeg
     */
    public function __construct(FFMpeg $ffmpeg)
    {
        $this->ffmpeg = $ffmpeg;
    }

    /**
     * Сгенерировать GIF файл из видео
     *
     * @param string $videoPath
     * @param string $gifPath
     * @return void
     */
    public function generateFromVideo(
        string $videoPath,
        string $gifPath,
        int $width,
        int $height
    ): void {
        $video = $this->ffmpeg->open($videoPath);
        $dimension = new Dimension($width, $height);
        if (!($video instanceof Video)) {
            throw new InvalidArgumentException('Файл не видео');
        }
        $gif = $video->gif(TimeCode::fromSeconds(2), $dimension, 3);
        $gif->save($gifPath);
    }
}

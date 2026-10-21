<?php

declare(strict_types=1);

namespace App\Services;

use App\Entities\Files\UploadedFiles\UploadedVideo;
use App\Entities\MediaInfo\VideoInfo;
use Exception;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;

/**
 * Видео конвертер
 */
class VideoConverter
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
     * Можно конвертировать видео в MP4?
     *
     * @param string $mimeType
     * @return boolean
     */
    public function canConvertToMp4(string $mimeType): bool
    {        
        return ($mimeType != MimeTypeService::MIME_TYPE_MP4);
    }

    /**
     * Конвертировать видео в MP4
     *
     * @param string $videoPath
     * @param string $mp4VideoPath
     * @return void
     */
    public function convertToMp4(string $videoPath, string $mp4VideoPath): void
    {                          
        $ffmpegVideo = $this->ffmpeg->open($videoPath);
        $format = new X264('libmp3lame', 'libx264');
        $format->setKiloBitrate(1000)->setAudioKiloBitrate(128);    
        $ffmpegVideo->save($format, $mp4VideoPath);
    }
}

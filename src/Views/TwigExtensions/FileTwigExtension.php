<?php

declare(strict_types=1);

namespace App\Views\TwigExtensions;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use App\Utilities\FileUtils;
use Twig\TwigFunction;
use App\Services\FileServices\UploadedFileService;

/**
 * Расширение Twig для файлов
 */
class FileTwigExtension extends AbstractExtension
{
    private UploadedFileService $uploadedFileService;

    public function __construct(UploadedFileService $uploadedFileService)
    {
        $this->uploadedFileService = $uploadedFileService;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('format_file_size', [FileUtils::class, 'formatSize'])
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_image', [$this->uploadedFileService, 'isUploadedImage']),
            new TwigFunction('is_audio', [$this->uploadedFileService, 'isUploadedAudio']),
            new TwigFunction('is_video', [$this->uploadedFileService, 'isUploadedVideo'])
        ];
    }
}

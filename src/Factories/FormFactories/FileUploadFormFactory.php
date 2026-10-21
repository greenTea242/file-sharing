<?php

declare(strict_types=1);

namespace App\Factories\FormFactories;

use App\Forms\FileUploadForm;
use App\Services\FormServices\FileUploadFormService;
use Psr\Http\Message\UploadedFileInterface as UploadedFile;

/**
 * Фабрика форм для загрузки файла
 */
class FileUploadFormFactory
{
    private FileUploadFormService $fileUploadFormService;

    /**
     * Консутруктор
     *
     * @param FileUploadFormService $fileUploadFormService
     */
    public function __construct(FileUploadFormService $fileUploadFormService)
    {
        $this->fileUploadFormService = $fileUploadFormService;
    }

    /**
     * Создать форму
     *
     * @param array<string, mixed> $formData
     * @param boolean $createCsrfToken
     * @param UploadedFile|null $file
     * @return FileUploadForm
     */
    public function create(
        array $formData,                
        bool $createCsrfToken = true,
        ?UploadedFile $file = null
    ): FileUploadForm {        
        $userComment = $this->fileUploadFormService->getUserComment($formData);

        if ($createCsrfToken) {
            $csrfToken = $this->fileUploadFormService->createCsrfToken();
        } else {
            $csrfToken = $this->fileUploadFormService->getCsrfToken($formData);
        }

        return new FileUploadForm(
            csrfToken: $csrfToken, 
            userComment: $userComment, 
            file: $file
        );
    }

    /**
     * Создать пустую форму
     *
     * @return FileUploadForm
     */
    public function createEmpty(): FileUploadForm {
        $csrfToken = $this->fileUploadFormService->createCsrfToken();        
        return new FileUploadForm(
            csrfToken: $csrfToken
        );
    }
}

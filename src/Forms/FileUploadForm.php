<?php

declare(strict_types=1);

namespace App\Forms;

use Psr\Http\Message\UploadedFileInterface as UploadedFile;
use App\Entities\CsrfToken;

/**
 * Форма для загрузки файла
 */
readonly class FileUploadForm extends Form
{    
    public readonly ?UploadedFile $file;    
    public readonly ?string $userComment;    

    /**
     * Конструктор
     *
     * @param CsrfToken $csrfToken
     * @param string|null $userComment
     * @param UploadedFile|null $file
     */
    public function __construct(
        CsrfToken $csrfToken,             
        ?string $userComment = null,
        ?UploadedFile $file = null
    ) {        
        parent::__construct($csrfToken);
        $this->file = $file;
        $this->userComment = $userComment;        
    }
}

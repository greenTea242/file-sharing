<?php

declare(strict_types=1);

namespace App\Forms;

use Psr\Http\Message\UploadedFileInterface as UploadedFile;
use App\Entities\CsrfToken;

/**
 * Форма для комментария
 */
readonly class CommentForm extends Form
{        
    public readonly ?string $userName;
    public readonly ?string $text;
    public readonly ?int $uploadedFileId;
    public readonly ?int $parentId;    

    /**
     * Конструктор
     *
     * @param CsrfToken $csrfToken
     * @param string|null $text
     * @param string|null $userName
     * @param integer|null $uploadedFileId
     * @param integer|null $parentId     
     */
    public function __construct(
        CsrfToken $csrfToken,             
        ?string $text = null,
        ?string $userName = null,
        ?int $uploadedFileId = null,
        ?int $parentId = null,        
    ) {        
        parent::__construct($csrfToken);
        $this->text = $text;
        $this->userName = $userName; 
        $this->uploadedFileId = $uploadedFileId;
        $this->parentId = $parentId;        
    }
}

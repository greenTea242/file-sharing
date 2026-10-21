<?php

declare(strict_types=1);

namespace App\Forms;

use App\Entities\CsrfToken;

/**
 * Форма
 */
readonly class Form
{
    public readonly CsrfToken $csrfToken; 

    /**
     * Конструктор
     *
     * @param CsrfToken $csrfToken
     */
    public function __construct(
        CsrfToken $csrfToken
    ) {        
        $this->csrfToken = $csrfToken;      
    }
}

<?php

namespace App\Services\FormServices;

use App\Services\CsrfProtection;
use App\Entities\CsrfToken;

/**
 * Сервис для форм
 */
class FormService {    
    private CsrfProtection $csrfProtection;

    /**
     * Конструктор
     *
     * @param CsrfProtection $csrfProtection
     */
    public function __construct(CsrfProtection $csrfProtection)
    {
        $this->csrfProtection = $csrfProtection;
    }

    /**
     * Создать токен защиты от CSRF
     *
     * @return CsrfToken
     */
    public function createCsrfToken(): CsrfToken {
        return $this->csrfProtection->createToken();
    }

    /**
     * Получить токен защиты от CSRF
     *
     * @param array<string, mixed> $formData
     * @return CsrfToken
     */
    public function getCsrfToken(array $formData): CsrfToken {
        return $this->csrfProtection->getToken($formData);
    }
}
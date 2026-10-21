<?php

declare(strict_types=1);

namespace App\Services;

use Slim\Csrf\Guard;
use App\Entities\CsrfToken;

/**
 * Класс для защиты от csrf
 */
class CsrfProtection
{
    private Guard $slimCsrfProtection;

    /**
     * Конструктор
     *
     * @param Guard $slimCsrfProtection
     */
    public function __construct(Guard $slimCsrfProtection)
    {
        $this->slimCsrfProtection = $slimCsrfProtection;
    }

    /**
     * Создать токен
     *
     * @return CsrfToken
     */
    public function createToken(): CsrfToken
    {
        $csrfToken = new CsrfToken(
            $this->slimCsrfProtection->getTokenNameKey(),
            $this->slimCsrfProtection->getTokenValueKey(),
            $this->slimCsrfProtection->getTokenName(),
            $this->slimCsrfProtection->getTokenValue()
        );
        return $csrfToken;
    }

    /**
     * Получить токен
     *
     * @param array<string, mixed> $formData
     * @return CsrfToken
     */
    public function getToken(array $formData): CsrfToken
    {
        return new CsrfToken(
            $this->slimCsrfProtection->getTokenNameKey(),
            $this->slimCsrfProtection->getTokenValueKey(),
            $formData[$this->slimCsrfProtection->getTokenNameKey()] ?? null,
            $formData[$this->slimCsrfProtection->getTokenValueKey()] ?? null
        );
    }    
}

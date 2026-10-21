<?php

declare(strict_types=1);

namespace App\Entities;

/**
 * Токен для защиты от CSRF
 */
readonly class CsrfToken
{
    public string $nameKey;
    public string $valueKey;
    public ?string $name;
    public ?string $value;

    /**
     * Конструктор
     *
     * @param string $nameKey
     * @param string $valueKey
     * @param string|null $name
     * @param string|null $value
     */
    public function __construct(
        string $nameKey,
        string $valueKey,
        ?string $name = null,
        ?string $value = null
    ) {
        $this->nameKey = $nameKey;
        $this->valueKey = $valueKey;
        $this->name = $name;
        $this->value = $value;
    }
}

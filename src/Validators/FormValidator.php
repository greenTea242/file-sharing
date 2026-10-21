<?php

declare(strict_types=1);

namespace App\Validators;

/**
 * Валидатор формы
 */
class FormValidator
{
    /**
     * Получить ошибку по-умолчанию
     *
     * @return string
     */
    public function getDefaultError(): string {
        return 'Ошибка отправка формы. Попробуйте еще раз';
    }
}

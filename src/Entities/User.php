<?php

declare(strict_types=1);

namespace App\Entities;

use App\Entities\Files\UploadedFiles\UploadedFile;
use LogicException;

/**
 * Пользователь
 */
class User {
    public readonly string $authToken;
    private ?int $id;
    /**
     * Файлы
     *
     * @var array<UploadedFile>
     */
    public readonly array $files;

    /**
     * Конструктор
     *
     * @param string $authToken
     * @param array<UploadedFile> $files
     * @param integer|null $id
     */
    public function __construct(string $authToken, array $files = [], ?int $id = null)
    {        
        $this->authToken = $authToken;
        $this->files = $files;
        $this->id = $id;
    }

    /**
     * Получить идентификатор
     *
     * @return integer
     */
    public function getId(): int
    {
        if (is_null($this->id)) {
            $error = 'Поле $this->id должно быть установлено перед вызовом метода getId()';
            throw new LogicException($error);
        }
        return $this->id;
    }

    
    /**
     * Установить идентфикатор
     *
     * @param integer $id
     * @return void
     */
    public function setId(int $id): void
    {
        $this->id = $id;         
    }    

    /**
     * Есть файлы?
     *
     * @return boolean
     */
    public function hasFiles(): bool {
        return !empty($this->files);
    }

    /**
     * Пользователь зарегистрирован?
     *
     * @return boolean
     */
    public function isRegistered(): bool {
        return !is_null($this->id);
    }
}
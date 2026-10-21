<?php

declare(strict_types=1);

namespace App\Entities\Files;

use App\Entities\MediaInfo\MediaInfo;
use App\Traits\FilePathAwareTrait;
use App\Utilities\FileUtils;
use InvalidArgumentException;
use LogicException;

/**
 * Файл
 */
class File
{
    use FilePathAwareTrait;

    public readonly string $dir;
    public readonly MediaInfo $mediaInfo;
    public readonly int $createdAt;
    public readonly string $filename;
    public readonly string $scope;
    private ?string $url;
    private ?int $id = null;

    public const SCOPE_PUBLIC = 'public';
    public const SCOPE_PRIVATE = 'private';

    /**
     * Конструктор
     *
     * @param string $dir
     * @param MediaInfo $mediaInfo
     * @param string $filename
     * @param integer $createdAt
     * @param string $scope
     * @param string|null $url
     * @param integer|null $id
     */
    public function __construct(
        string $dir,
        MediaInfo $mediaInfo,
        string $filename,
        int $createdAt,
        string $scope,
        ?string $url = null,
        ?int $id = null
    ) {
        $this->dir = $dir;
        $this->mediaInfo = $mediaInfo;
        $this->filename = $filename;
        $this->createdAt = $createdAt;
        if (
            $scope != static::SCOPE_PUBLIC &&
            $scope != static::SCOPE_PRIVATE
        ) {
            throw new InvalidArgumentException("Неизвестная область видимости {$scope}");
        }
        $this->scope = $scope;
        $this->setId($id);
        $this->setUrl($url);        
    }

    /**
     * Установить идентификатор
     *
     * @param integer|null $id
     * @return void
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * Установить URL
     *
     * @param string|null $url
     * @return void
     */
    public function setUrl(?string $url): void
    {
        $this->url = $url;
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
     * Получить URL
     *
     * @return string
     */
    public function getUrl(): string
    {
        if (is_null($this->url)) {
            $error = 'Поле $this->url должно быть установлено перед вызовом метода getUrl()';
            throw new LogicException($error);
        }
        return $this->url;
    }

    /**
     * Получить имя файла без расширения
     *
     * @return string
     */
    public function getFilenameWithoutExt(): string
    {
        return FileUtils::getFilenameWithoutExt($this->filename);
    }

    /**
     * Получить путь
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->buildPath(
            filename: $this->filename,
            dir: $this->dir,
            scope: $this->scope
        );
    }
}

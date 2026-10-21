<?php

declare(strict_types=1);

namespace App\Entities\Comments;

use App\Utilities\TimeUtils;
use Exception;
use LogicException;

class Comment
{
    public const string PATH_DIVIDER = '/';
    private const string ANONYMOUS_USER_INITIALS = 'АН';
    
    public readonly int $number;    
    public readonly string $path;
    public readonly string $text;
    public readonly int $uploadedFileId;    
    public readonly int $createdAt;
    public readonly int $userId;
    public readonly ?string $userName;
    public readonly ?int $updatedAt;
    public readonly ?int $parentId;
    private ?int $id;

    /**
     * Конструктор
     *
     * @param integer $number
     * @param string $path
     * @param string $text
     * @param integer $uploadedFileId
     * @param integer $createdAt
     * @param integer $userId
     * @param integer|null $parentId
     * @param string|null $userName
     * @param integer|null $updatedAt
     * @param integer|null $id
     */
    public function __construct(
        int $number,        
        string $path,
        string $text,
        int $uploadedFileId,
        int $createdAt,
        int $userId,
        ?int $parentId = null,
        ?string $userName = null,
        ?int $updatedAt = null,
        ?int $id = null
    ) {
        $this->number = $number;        
        $this->path = $path;
        $this->text = $text;
        $this->uploadedFileId = $uploadedFileId;
        $this->createdAt = $createdAt;
        $this->userId = $userId;
        $this->parentId = $parentId;
        $this->userName = $userName;
        $this->updatedAt = $updatedAt;
        $this->id = $id;
    }

    /**
     * Установить идентификатор
     *
     * @param integer $id
     * @return void
     */
    public function setId(int $id)
    {
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
     * Получить инициалы пользователя
     *
     * @return string
     */
    public function getUserInitials(): string {
        if (is_null($this->userName)) {
            return self::ANONYMOUS_USER_INITIALS;
        }

        $userInitials = '';
        $words = explode(' ', $this->userName); 

        $wordsCount = count($words);

        for ($i = 0; $i < $wordsCount && $i < 2; $i++) {
            $word = $words[$i];

            if (mb_strlen($word) == 0) {
                throw new Exception('Строка не может быть пустой');
            }

            $firstChar = mb_substr($word, 0, 1);
            $capitilizedFirstChar = mb_strtoupper($firstChar);
            $userInitials .= $capitilizedFirstChar;
        }

        return $userInitials;
    }

    /**
     * Получить вымышленное пользовательское имя
     *
     * @return string
     */
    public function getFakeUserName(): string {
        return "@user_{$this->userId}";
    }

    /**
     * Форматировать дату создания
     *
     * @return string
     */
    public function formatCreatedAt(): string {
        return TimeUtils::formatTimeDifference($this->createdAt);
    }
    
    /**
     * Получить глубину
     *
     * @return integer
     */
    public function getDepth(): int {
        $pathDividers = substr_count($this->path, self::PATH_DIVIDER);
        return $pathDividers + 1;
    }
}

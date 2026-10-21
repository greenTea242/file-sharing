<?php

declare(strict_types=1);

namespace App\Database;

use App\Entities\Files\File;
use Pdo;

/**
 * Шлюз табличных данных
 */
class TableDataGateway
{
    protected Pdo $pdo;

    /**
     * Конструктор
     *
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Сериализовать JSON
     *
     * @param array<mixed, mixed> $json
     * @return string
     */
    protected function serializeJson(array $json): string
    {
        $serializedJson = json_encode($json);
        return str_replace('\u0000', '', $serializedJson);
    }

    /**
     * Отменить работу
     *
     * @return void
     */
    public function rollback(): void
    {
        $this->pdo->rollBack();
    }

    /**
     * Сделать коммит
     *
     * @return void
     */
    public function commit(): void
    {
        $this->pdo->commit();
    }


    /**
     * Инициализировать транзакцию
     *
     * @return void
     */
    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    /**
     * Проверить формат идентификатора
     *
     * @param string $column
     * @return boolean
     */
    public static function checkIdFormat(string $column): bool {
        return is_numeric($column);
    }
}

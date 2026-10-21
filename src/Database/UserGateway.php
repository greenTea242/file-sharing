<?php

declare(strict_types=1);

namespace App\Database;

use App\Entities\User;
use Pdo;

/**
 * Шлюз для таблицы users (пользователи)
 */
class UserGateway extends TableDataGateway
{
    /**
     * Добавить пользователя
     *
     * @param User $user
     * @return integer
     */
    public function addUser(User $user): int
    {
        $query = <<<SQL
            INSERT INTO users (                        
                auth_token
            )
            VALUES (                        
                :auth_token_bind
            );
        SQL;
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':auth_token_bind', $user->authToken, PDO::PARAM_STR);
        $stmt->execute();
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Найти пользователя по идентификатору
     *
     * @param integer $id
     * @return array<string, mixed>|null
     */
    public function findUserById(int $id): ?array
    {
        $query = <<<SQL
            SELECT id, 
                auth_token
            FROM users
            WHERE id = :id_bind;
        SQL;
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_bind', $id, PDO::PARAM_INT);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $comment = $stmt->fetch();
        if ($comment === false) {
            return null;
        } else {
            return $comment;
        }
    }

    /**
     * Найти пользователя по токену авторизации
     *
     * @param string $authToken
     * @return array<string, mixed>|null
     */
    public function findUserByAuthToken(string $authToken): ?array
    {
        $query = <<<SQL
            SELECT id, 
                auth_token
            FROM users
            WHERE auth_token = :auth_token_bind;
        SQL;
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':auth_token_bind', $authToken, PDO::PARAM_STR);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $comment = $stmt->fetch();
        if ($comment === false) {
            return null;
        } else {
            return $comment;
        }
    }
}
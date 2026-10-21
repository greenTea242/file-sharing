<?php

declare(strict_types=1);

namespace App\Factories;

use App\Database\UploadedFileGateway;
use App\Database\UserGateway;
use App\Entities\User;
use App\Factories\FileFactories\UploadedFileFactory;
use App\Services\UserAuthService;
use Exception;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Фабрика пользователей
 */
class UserFactory
{
    private UserGateway $userGateway;
    private UploadedFileFactory $uploadedFileFactory;    

    /**
     * Конструктор
     *
     * @param UserGateway $userGateway
     * @param UploadedFileFactory $uploadedFileFactory     
     */
    public function __construct(
        UserGateway $userGateway,
        UploadedFileFactory $uploadedFileFactory
    ) {
        $this->userGateway = $userGateway;
        $this->uploadedFileFactory = $uploadedFileFactory;        
    }

    /**
     * Создать пользователя через токен аутентификации
     *
     * @param string $authToken
     * @param boolean $isUserRegistered     
     * @param integer $filesOffset
     * @param integer|null $filesLimit
     * @return User
     */
    public function createByAuthToken(
        string $authToken,
        bool $isUserRegistered = false,
        int $filesOffset = 0,
        ?int $filesLimit = null
    ): User {
        if (!$isUserRegistered) {
            return new User($authToken);
        }

        $userData = $this->userGateway->findUserByAuthToken($authToken);

        if (is_null($userData)) {
            return new User($authToken);
        }

        return $this->createByUserData(
            userData: $userData,
            filesOffset: $filesOffset,
            filesLimit: $filesLimit
        );
    }

    /**
     * Создать пользователя через идентификатор, или выбросить исключение
     *
     * @param integer $id
     * @return User
     */
    public function createByIdOrFail(int $id): User
    {
        $userData = $this->userGateway->findUserById($id);

        if (is_null($userData)) {
            throw new Exception('Пользователь не найден');
        }

        return $this->createByUserData($userData);
    }

    /**
     * Создать пользователя через данные пользователя
     *
     * @param array<string, mixed> $userData
     * @param integer $filesOffset
     * @param integer|null $filesLimit
     * @return User
     */
    private function createByUserData(
        array $userData,
        int $filesOffset = 0,
        ?int $filesLimit = null
    ): User {
        $id = $userData['id'] ?? throw new Exception('Отсутствует ключ id в массиве');
        $authToken = $userData['auth_token'] ?? throw new Exception('Отсутствует ключ auth_token в массиве');
        $files = $this->uploadedFileFactory->createManyByUserId(
            userId: $id,
            offset: $filesOffset,
            limit: $filesLimit
        );
        return new User(
            authToken: $authToken,
            files: $files,
            id: $id
        );
    }
}

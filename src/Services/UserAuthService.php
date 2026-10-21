<?php

declare(strict_types=1);

namespace App\Services;

use App\Database\UserGateway;
use App\Entities\User;
use App\Factories\UserFactory;
use App\Services\UuidService;
use Exception;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Cookies;
use Slim\Routing\RouteContext;

/**
 * Сервис для аутентификации пользователей
 */
class UserAuthService
{
    private const string AUTH_TOKEN_COOKIE_KEY = 'auth_token';
    private UuidService $uuidService;
    private UserFactory $userFactory;
    private UserGateway $userGateway;

    /**
     * Конструктор
     *
     * @param UuidService $uuidService
     * @param UserFactory $userFactory
     * @param UserGateway $userGateway
     */
    public function __construct(
        UuidService $uuidService,
        UserFactory $userFactory,
        UserGateway $userGateway
    ) {
        $this->uuidService = $uuidService;
        $this->userFactory = $userFactory;
        $this->userGateway = $userGateway;
    }

    /**
     * Авторизовать пользователя
     *
     * @param User $user
     * @param Response $response
     * @return Response
     */
    public function authorizeUser(User $user, Response $response): Response
    {
        $cookies = new Cookies();
        $expires = strtotime('+1 year');
        $cookies->setDefaults([
            'path' => '/',
            'expires' => (string)$expires
        ]);
        $cookies->set(self::AUTH_TOKEN_COOKIE_KEY, $user->authToken);
        return $response->withHeader('Set-Cookie', $cookies->toHeaders());
    }

    /**
     * Получить пользователя
     *
     * @param Request $request     
     * @param integer $filesOffset
     * @param integer|null $filesLimit
     * @return User
     */
    public function getUser(
        Request $request, 
        int $filesOffset = 0,
        ?int $filesLimit = null): User
    {
        $authToken = $this->getAuthToken($request);

        if (is_null($authToken)) {
            $isUserRegistered = false;
            $authToken = $this->createAuthToken();
        } else {
            $isUserRegistered = true;
        }

        return $this->userFactory->createByAuthToken(
            authToken: $authToken, 
            isUserRegistered: $isUserRegistered,
            filesOffset: $filesOffset,
            filesLimit: $filesLimit
        );
    }

    /**
     * Зарегистрировать пользователя
     *
     * @param User $user
     * @return void
     */
    public function registerUser(User $user): void
    {
        $id = $this->userGateway->addUser($user);
        $user->setId($id);
    }

    /**
     * Есть доступ к пользователю?
     *     
     * @param Request $request
     * @return boolean
     */
    public function canAccessUser(Request $request): bool {
        $routeContext = RouteContext::fromRequest($request);        
        $route = $routeContext->getRoute();
        $args = $route->getArguments();
        $userId = $args['id'] ?? throw new Exception('Запрос без идентификатора пользователя'); 
        $user = $this->getUser($request);
        
        if ($user->isRegistered() && 
            $user->getId() == $userId) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Создать токен авторизации
     *
     * @return string
     */
    private function createAuthToken(): string
    {
        return $this->uuidService->createUuid();
    }

    /**
     * Получить токен авторизации
     *
     * @param Request $request
     * @return string|null
     */
    private function getAuthToken(Request $request): ?string
    {
        $cookies = new Cookies($request->getCookieParams());
        return $cookies->get(self::AUTH_TOKEN_COOKIE_KEY);
    }
}

<?php

declare(strict_types=1);

namespace App\Factories;

use App\Utilities\DsnBuilder;
use Exception;
use Workerman\RedisQueue\Client;

/**
 * Фабрика клиентов очередей Redis
 */
class RedisQueueClientFactory {
    /**
     * Настройки
     *
     * @var array<string, mixed>
     */
    private array $settings = [];

    /**
     * Конструктор
     *
     * @param array<string, mixed> $settings
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Создать клиент очередей Redis
     *
     * @return Client
     */
    public function create(): Client {
        $host = $this->getHost();
        $auth = $this->settings['auth'] ?? throw new Exception('Отсутствует ключ auth в массиве');
        $database = $this->settings['database'] ??
            throw new Exception('Отсутствует ключ database в массиве');
        $maxAttempts = $this->settings['max_attempts'] ??
            throw new Exception('Отсутствует ключ max_attempts в массиве'); 
        $retrySeconds = $this->settings['retry_seconds'] ??
            throw new Exception('Отсутствует ключ retry_seconds в массиве');
        return new Client($host, [            
            'auth' => $auth,
            'database' => $database,
            'max_attempts' => $maxAttempts,
            'retry_seconds' => $retrySeconds            
        ]);
    }

    /**
     * Получить хост
     *
     * @return string
     */
    private function getHost(): string {
        $host = $this->settings['host'] ?? throw new Exception('Отсутствует ключ host в массиве');
        $port = $this->settings['port'] ?? throw new Exception('Отсутствует ключ port в массиве');
        $scheme = $this->settings['scheme'] ?? 
            throw new Exception('Отсутствует ключ scheme в массиве');
        return DsnBuilder::getDsn(
            scheme: $scheme,
            host: $host,
            port: $port
        );
    }
}
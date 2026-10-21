<?php

declare(strict_types=1);

namespace App\Factories;

use Exception;
use Predis\Client;

/**
 * Фабрика клиентов Predis
 */
class PredisClientFactory {
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
     * Создать клиент Predis
     *
     * @return Client
     */
    public function create(): Client {
        $scheme = $this->settings['scheme'] ?? 
            throw new Exception('Отсутствует ключ scheme в массиве');
        $host = $this->settings['host'] ?? throw new Exception('Отсутствует ключ host в массиве');
        $port = $this->settings['port'] ?? throw new Exception('Отсутствует ключ port в массиве');                
        $auth = $this->settings['auth'] ?? throw new Exception('Отсутствует ключ auth в массиве');
        $database = $this->settings['database'] ??
            throw new Exception('Отсутствует ключ database в массиве');
        return new Client([
            'scheme' => $scheme,
            'host' => $host,
            'port' => $port,
            'password' => $auth,
            'database' => $database           
        ]);
    }
}
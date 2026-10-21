<?php

declare(strict_types=1);

namespace App\Loggers;

use Psr\Log\LoggerTrait;
use Psr\Log\LoggerInterface as Logger;
use Monolog\Logger as MonologLogger; 
use Stringable;

class AppLogger implements Logger {
    use LoggerTrait;

    protected MonologLogger $logger;

    public function __construct(MonologLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Добавить запись в лог
     *     
     * @param mixed             $level  
     * @param string|Stringable $message
     * @param array<mixed>      $context
     * @return void
     */
    public function log(mixed $level, string|Stringable $message, array $context = []): void {
        $this->logger->log(
            level: $level, 
            message: $message,
            context: $context
        );
    }
}
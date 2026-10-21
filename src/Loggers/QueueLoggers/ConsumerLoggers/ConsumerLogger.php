<?php

declare(strict_types=1);

namespace App\Loggers\QueueLoggers\ConsumerLoggers;

use App\Loggers\AppLogger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\TestHandler;
use Monolog\Level;
use Monolog\Logger as MonologLogger;
use Monolog\Handler\AbstractProcessingHandler;

/**
 * Логгер потребителя
 */
abstract class ConsumerLogger extends AppLogger
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        $logger = $this->getLogger();
        parent::__construct($logger);
    }

    /**
     * Получить логгер
     *
     * @return MonologLogger
     */
    protected function getLogger(): MonologLogger
    {
        $channel = $this->getChannel();
        $logger = new MonologLogger($channel);
        $handlers = $this->getHandlers();

        foreach ($handlers as $handler) {
            $logger->pushHandler($handler);
        }
        
        return $logger;
    }

    /**
     * Получить канал
     *
     * @return string
     */
    abstract protected function getChannel(): string;

    /**
     * Получить путь
     *
     * @return string
     */
    abstract protected function getPath(): string;

    /**
     * Получить обработчиков
     *
     * @return array<AbstractProcessingHandler>
     */
    private function getHandlers(): array {
        $handlers = [];
        $path = $this->getPath();

        $infoHandler = new StreamHandler($path, Level::Info);
        $handlers[] = $infoHandler;

        $errorHandler = new StreamHandler($path, Level::Error);
        $handlers[] = $errorHandler;

        $testHandler = new TestHandler();
        $handlers[] = $testHandler;
        
        $rotatingFileHandler= new RotatingFileHandler(
            filename: $path,
            maxFiles: 30,
            level: Level::Debug
        );
        $handlers[] = $rotatingFileHandler;

        $infoStdoutHandler = new StreamHandler('php://stdout', Level::Info);
        $handlers[] = $infoStdoutHandler;

        $errorStdoutHandler = new StreamHandler('php://stdout', Level::Error);
        $handlers[] = $errorStdoutHandler;        

        return $handlers;
    }
}

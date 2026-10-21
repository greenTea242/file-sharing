<?php

declare(strict_types=1);

namespace App\Queue\Consumers;

use Workerman\Worker;
use App\Factories\RedisQueueClientFactory;
use App\Loggers\QueueLoggers\ConsumerLoggers\ConsumerLogger;
use Throwable;

/**
 * Потребитель 
 * 
 * Очереди задач
 */
abstract class Consumer
{
    public readonly bool $isEnabled;
    protected RedisQueueClientFactory $redisQueueClientFactory;
    protected ConsumerLogger $logger;

    /**
     * Конструктор
     *
     * @param boolean $isEnabled
     * @param RedisQueueClientFactory $redisQueueClientFactory
     * @param ConsumerLogger $logger
     */
    public function __construct(
        bool $isEnabled,
        RedisQueueClientFactory $redisQueueClientFactory,
        ConsumerLogger $logger
    ) {
        $logPath = $this->getWorkerLogPath();
        $this->setWorkerLogPath($logPath);
        $this->isEnabled = $isEnabled;
        $this->redisQueueClientFactory = $redisQueueClientFactory;
        $this->logger = $logger;
    }

    /**
     * Запустить потребителя
     *
     * @return void
     */
    public function process(): void
    {
        if (!$this->isEnabled) {
            $this->logger->info('Воркер выключен в настройках');
            exit(0);
        }
        $this->logger->info('Запускаем воркер');
        $worker = new Worker();        
        $worker->onWorkerStart = function() {
            $client = $this->redisQueueClientFactory->create();
            $queue = $this->getQueue();

            $client->subscribe($queue, function(mixed $data) {
                $this->processInternal($data);
            });

            $client->onConsumeFailure(function (Throwable $exception, mixed $package) { 
                $packageAsText = var_export($package, true);
                $message = "Ошибка в воркере с текстом: {$exception->getMessage()}. " .
                    "Пакет: {$packageAsText}";
                $this->logger->error($message);
            });
        };
        Worker::runAll();
    }

    /**
     * Получить очередь
     *
     * @return string
     */
    abstract public function getQueue(): string;

    /**
     * Запустить потребителя (внутренняя реализация)
     *
     * @return void
     */
    abstract protected function processInternal(mixed $data): void;

    /**
     * Получить путь к лог-фай воркера
     *
     * @return string
     */
    abstract protected function getWorkerLogPath(): string;

    /**
     * Установить путь к лог-файлу воркера
     *
     * @param string $logPath
     * @return void
     */
    protected function setWorkerLogPath(string $logPath): void
    {
        Worker::$logFile = $logPath;
    }
}

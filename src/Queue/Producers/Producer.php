<?php

declare(strict_types=1);

namespace App\Queue\Producers;

use App\Factories\PredisClientFactory;
use App\Queue\Consumers\Consumer;
use Exception;
use Psr\Log\LoggerInterface as Logger;

/**
 * Продюсер
 * 
 * Очереди задач
 */
class Producer
{
    public readonly bool $isEnabled;
    private Consumer $consumer;
    private PredisClientFactory $predisClientFactory;
    protected Logger $logger;

    /**
     * Конструктор
     *
     * @param boolean $isEnabled
     * @param Consumer $consumer
     * @param PredisClientFactory $predisClientFactory
     * @param Logger $logger
     */
    public function __construct(
        bool $isEnabled,
        Consumer $consumer,
        PredisClientFactory $predisClientFactory,
        Logger $logger
    ) {
        $this->isEnabled = $isEnabled;
        $this->consumer = $consumer;
        $this->predisClientFactory = $predisClientFactory;
        $this->logger = $logger;
    }

    /**
     * Отправить задачу в воркер (внутреняя реализация)
     *
     * @param mixed $data
     * @return void
     */
    protected function sendInternal(mixed $data, int $delay = 0): void
    {
        $client = $this->predisClientFactory->create();
        $queue = $this->consumer->getQueue();
        $queueWaiting = '{redis-queue}-waiting';
        $queueDelay = '{redis-queue}-delayed';
        $now = time();
        $packageStr = json_encode([
            'id'       => rand(),
            'time'     => $now,
            'delay'    => $delay,
            'attempts' => 0,
            'queue'    => $queue,
            'data'     => $data
        ]);
        if ($delay) {
            $status = $client->zAdd($queueDelay, $packageStr);
        } else {
            $status = $client->lPush($queueWaiting . $queue, $packageStr);
        }
        if ($status !== 1) {
            throw new Exception('Задача не отправлена');
        }
    }
}

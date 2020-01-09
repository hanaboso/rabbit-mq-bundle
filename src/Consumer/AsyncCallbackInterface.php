<?php declare(strict_types=1);

namespace RabbitMqBundle\Consumer;

use PhpAmqpLib\Message\AMQPMessage;
use RabbitMqBundle\Connection\Connection;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;

/**
 * Interface AsyncCallbackInterface
 *
 * @package RabbitMqBundle\Consumer
 */
interface AsyncCallbackInterface
{

    /**
     * @param AMQPMessage   $message
     * @param Connection    $connection
     * @param int           $channelId
     * @param LoopInterface $loop
     *
     * @return PromiseInterface
     */
    public function processMessage(
        AMQPMessage $message,
        Connection $connection,
        int $channelId,
        LoopInterface $loop
    ): PromiseInterface;

}

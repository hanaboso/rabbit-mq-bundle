<?php declare(strict_types=1);

namespace RabbitMqBundle\Consumer;

use Bunny\Message;
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
     * @param Message       $message
     * @param Connection    $connection
     * @param int           $channelId
     * @param LoopInterface $loop
     *
     * @return PromiseInterface
     */
    public function processMessage(Message $message, Connection $connection, int $channelId, LoopInterface $loop): PromiseInterface;

}
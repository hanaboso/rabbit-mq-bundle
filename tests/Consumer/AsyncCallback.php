<?php declare(strict_types=1);

namespace Tests\Consumer;

use Bunny\Message;
use RabbitMqBundle\Connection\Connection;
use RabbitMqBundle\Consumer\AsyncCallbackInterface;
use React\EventLoop\LoopInterface;
use React\Promise\Promise;
use React\Promise\PromiseInterface;

/**
 * Class AsyncCallback
 *
 * @package Tests\Consumer
 */
final class AsyncCallback implements AsyncCallbackInterface
{

    /**
     * @param Message       $message
     * @param Connection    $connection
     * @param int           $channelId
     * @param LoopInterface $loop
     *
     * @return PromiseInterface
     */
    public function processMessage(
        Message $message,
        Connection $connection,
        int $channelId,
        LoopInterface $loop
    ): PromiseInterface
    {
        $loop;
        $connection->getChannel($channelId)->ack($message);

        return new Promise(function(): void {});
    }

}
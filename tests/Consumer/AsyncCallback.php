<?php declare(strict_types=1);

namespace RabbitBundleTests\Consumer;

use Exception;
use PhpAmqpLib\Message\AMQPMessage;
use RabbitMqBundle\Connection\Connection;
use RabbitMqBundle\Consumer\AsyncCallbackInterface;
use RabbitMqBundle\Utils\Message;
use React\EventLoop\LoopInterface;
use React\Promise\Promise;
use React\Promise\PromiseInterface;

/**
 * Class AsyncCallback
 *
 * @package RabbitBundleTests\Consumer
 */
final class AsyncCallback implements AsyncCallbackInterface
{

    /**
     * @param AMQPMessage   $message
     * @param Connection    $connection
     * @param int           $channelId
     * @param LoopInterface $loop
     *
     * @return PromiseInterface
     * @throws Exception
     */
    public function processMessage(
        AMQPMessage $message,
        Connection $connection,
        int $channelId,
        LoopInterface $loop
    ): PromiseInterface
    {
        $loop;

        Message::ack($message, $connection, $channelId);

        return new Promise(
            function (): void {

            }
        );
    }

}

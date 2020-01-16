<?php declare(strict_types=1);

namespace RabbitMqBundle\Consumer\Callback;

use Exception;
use PhpAmqpLib\Message\AMQPMessage;
use RabbitMqBundle\Connection\Connection;
use RabbitMqBundle\Consumer\AsyncCallbackInterface;
use RabbitMqBundle\Utils\Message;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use function React\Promise\resolve;

/**
 * Class DumpAsyncCallback
 *
 * @package RabbitMqBundle\Consumer\Callback
 */
final class DumpAsyncCallback implements AsyncCallbackInterface
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

        var_dump(['body' => Message::getBody($message), 'headers' => Message::getHeaders($message)]);

        Message::ack($message, $connection, $channelId);

        return resolve();
    }

}

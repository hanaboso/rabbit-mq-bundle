<?php declare(strict_types=1);

namespace RabbitMqBundle\Consumer\Callback;

use Exception;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use PhpAmqpLib\Message\AMQPMessage;
use RabbitMqBundle\Connection\Connection;
use RabbitMqBundle\Consumer\AsyncCallbackInterface;
use RabbitMqBundle\Utils\Message;

/**
 * Class NullAsyncCallback
 *
 * @package RabbitMqBundle\Consumer\Callback
 */
final class NullAsyncCallback implements AsyncCallbackInterface
{

    /**
     * @param AMQPMessage $message
     * @param Connection  $connection
     * @param int         $channelId
     *
     * @return PromiseInterface
     * @throws Exception
     */
    public function processMessage(AMQPMessage $message, Connection $connection, int $channelId): PromiseInterface
    {
        $promise = new Promise();
        $promise
            ->then(
                static function (AMQPMessage $message) use ($connection, $channelId): void {
                    Message::ack($message, $connection, $channelId);
                }
            )
            ->resolve($message);

        return $promise;
    }

}

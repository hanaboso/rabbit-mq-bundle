<?php declare(strict_types=1);

namespace RabbitMqBundle\Consumer\Callback;

use Exception;
use PhpAmqpLib\Message\AMQPMessage;
use RabbitMqBundle\Connection\Connection;
use RabbitMqBundle\Consumer\CallbackInterface;
use RabbitMqBundle\Utils\Message;

/**
 * Class DumpCallback
 *
 * @package RabbitMqBundle\Consumer\Callback
 */
final class DumpCallback implements CallbackInterface
{

    /**
     * @param AMQPMessage $message
     * @param Connection  $connection
     * @param int         $channelId
     *
     * @throws Exception
     */
    public function processMessage(AMQPMessage $message, Connection $connection, int $channelId): void
    {
        var_dump(['body' => Message::getBody($message), 'headers' => Message::getHeaders($message)]);

        Message::ack($message, $connection, $channelId);
    }

}

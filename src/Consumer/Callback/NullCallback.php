<?php declare(strict_types=1);

namespace RabbitMqBundle\Consumer\Callback;

use Bunny\Message;
use RabbitMqBundle\Connection\Connection;
use RabbitMqBundle\Consumer\CallbackInterface;

/**
 * Class NullCallback
 *
 * @package RabbitMqBundle\Consumer\Callback
 */
class NullCallback implements CallbackInterface
{

    /**
     * @param Message    $message
     * @param Connection $connection
     * @param int        $channelId
     */
    public function processMessage(Message $message, Connection $connection, int $channelId): void
    {
        $connection->getChannel($channelId)->ack($message);
    }

}

<?php declare(strict_types=1);

namespace RabbitMqBundle\Consumer\Callback;

use Bunny\Message;
use RabbitMqBundle\Connection\Connection;
use RabbitMqBundle\Consumer\CallbackInterface;

/**
 * Class DumpCallback
 *
 * @package RabbitMqBundle\Consumer\Callback
 */
class DumpCallback implements CallbackInterface
{

    /**
     * @param Message    $message
     * @param Connection $connection
     * @param int        $channelId
     */
    public function processMessage(Message $message, Connection $connection, int $channelId): void
    {
        var_dump($message);

        $connection->getChannel($channelId)->ack($message);
    }

}

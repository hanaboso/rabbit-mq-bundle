<?php declare(strict_types=1);

namespace RabbitMqBundle\Consumer;

use Bunny\Message;
use RabbitMqBundle\Connection\Connection;

/**
 * Interface CallbackInterface
 *
 * @package RabbitMqBundle\Consumer
 */
interface CallbackInterface
{

    /**
     * @param Message    $message
     * @param Connection $connection
     * @param int        $channelId
     */
    public function processMessage(Message $message, Connection $connection, int $channelId): void;

}

<?php declare(strict_types=1);

namespace RabbitMqBundle\Consumer;

use PhpAmqpLib\Message\AMQPMessage;
use RabbitMqBundle\Connection\Connection;

/**
 * Interface CallbackInterface
 *
 * @package RabbitMqBundle\Consumer
 */
interface CallbackInterface
{

    /**
     * @param AMQPMessage $message
     * @param Connection  $connection
     * @param int         $channelId
     */
    public function processMessage(AMQPMessage $message, Connection $connection, int $channelId): void;

}

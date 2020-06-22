<?php declare(strict_types=1);

namespace RabbitMqBundle\Consumer;

use GuzzleHttp\Promise\PromiseInterface;
use PhpAmqpLib\Message\AMQPMessage;
use RabbitMqBundle\Connection\Connection;

/**
 * Interface AsyncCallbackInterface
 *
 * @package RabbitMqBundle\Consumer
 */
interface AsyncCallbackInterface
{

    /**
     * @param AMQPMessage $message
     * @param Connection  $connection
     * @param int         $channelId
     *
     * @return PromiseInterface
     */
    public function processMessage(AMQPMessage $message, Connection $connection, int $channelId): PromiseInterface;

}

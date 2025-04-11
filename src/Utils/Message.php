<?php declare(strict_types=1);

namespace RabbitMqBundle\Utils;

use Exception;
use Hanaboso\Utils\String\Json;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use RabbitMqBundle\Connection\Connection;

/**
 * Class Message
 *
 * @package RabbitMqBundle\Utils
 */
final class Message
{

    public const string APPLICATION_HEADERS = 'application_headers';

    private const string UNDERSCORE = '_';
    private const string DASH       = '-';
    private const array PROPERTIES  = [
        'app-id',
        'cluster-id',
        'content-encoding',
        'content-type',
        'correlation-id',
        'delivery-mode',
        'expiration',
        'message-id',
        'priority',
        'reply-to',
        'timestamp',
        'type',
        'user-id',
    ];

    /**
     * @param AMQPMessage $message
     *
     * @return string
     */
    public static function getBody(AMQPMessage $message): string
    {
        return $message->getBody();
    }

    /**
     * @param AMQPMessage $message
     *
     * @return mixed[]
     */
    public static function getHeaders(AMQPMessage $message): array
    {
        $properties = $message->get_properties();
        /** @var AMQPTable<mixed> $headers */
        $headers = $properties[self::APPLICATION_HEADERS] ?? new AMQPTable();
        unset($properties[self::APPLICATION_HEADERS]);

        foreach ($properties as $key => $value) {
            if (str_contains($key, self::UNDERSCORE)) {
                unset($properties[$key]);
                $properties[str_replace(self::UNDERSCORE, self::DASH, $key)] = $value;
            }
        }

        return array_merge($properties, $headers->getNativeData());
    }

    /**
     * @param mixed[]|string $body
     * @param mixed[]        $properties
     *
     * @return AMQPMessage
     */
    public static function create(mixed $body, array $properties = []): AMQPMessage
    {
        $message = new AMQPMessage(is_array($body) ? Json::encode($body) : $body);
        $headers = [];

        foreach ($properties as $key => $value) {
            if (in_array($key, self::PROPERTIES, TRUE)) {
                $message->set(str_replace(self::DASH, self::UNDERSCORE, $key), $value);
            } else {
                $headers[$key] = $value;
            }
        }

        $message->set(self::APPLICATION_HEADERS, new AMQPTable($headers));

        return $message;
    }

    /**
     * @param AMQPMessage $message
     * @param Connection  $connection
     * @param int         $channel
     *
     * @throws Exception
     */
    public static function ack(AMQPMessage $message, Connection $connection, int $channel): void
    {
        $connection->getChannel($channel)->basic_ack($message->getDeliveryTag());
    }

    /**
     * @param AMQPMessage $message
     * @param Connection  $connection
     * @param int         $channel
     * @param bool        $requeue
     *
     * @throws Exception
     */
    public static function nack(AMQPMessage $message, Connection $connection, int $channel, bool $requeue = FALSE): void
    {
        $connection->getChannel($channel)->basic_nack($message->getDeliveryTag(), FALSE, $requeue);
    }

    /**
     * @param AMQPMessage $message
     * @param Connection  $connection
     * @param int         $channel
     * @param bool        $requeue
     *
     * @throws Exception
     */
    public static function reject(
        AMQPMessage $message,
        Connection $connection,
        int $channel,
        bool $requeue = FALSE,
    ): void
    {
        $connection->getChannel($channel)->basic_reject($message->getDeliveryTag(), $requeue);
    }

}

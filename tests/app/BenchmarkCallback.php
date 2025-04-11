<?php declare(strict_types=1);

namespace RabbitBundleTests\app;

use Exception;
use PhpAmqpLib\Message\AMQPMessage;
use RabbitMqBundle\Connection\Connection;
use RabbitMqBundle\Consumer\CallbackInterface;
use RabbitMqBundle\Utils\Message;

/**
 * Class BenchmarkCallback
 *
 * @package RabbitBundleTests\app
 */
final class BenchmarkCallback implements CallbackInterface
{

    private const int COUNT = 250_000;

    /**
     * @var int
     */
    private static int $counter = 0;

    /**
     * @var int|float
     */
    private static int|float $start = 0;

    /**
     * BenchmarkCallback constructor.
     */
    public function __construct()
    {
        self::$start = hrtime(TRUE);
    }

    /**
     * @param AMQPMessage $message
     * @param Connection  $connection
     * @param int         $channelId
     *
     * @throws Exception
     */
    public function processMessage(AMQPMessage $message, Connection $connection, int $channelId): void
    {
        if (self::$counter++ === self::COUNT) {
            $time = hrtime(TRUE) - self::$start;

            echo sprintf(
                'Consumed %s messages in %ss: %s messages per second%s',
                self::COUNT,
                $time / 1e9,
                self::COUNT / $time * 1e9,
                PHP_EOL,
            );

            Message::ack($message, $connection, $channelId);

            exit(0);
        }

        Message::ack($message, $connection, $channelId);
    }

}

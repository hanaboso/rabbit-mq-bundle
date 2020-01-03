<?php declare(strict_types=1);

namespace RabbitBundleTests\app;

use Bunny\Message;
use RabbitMqBundle\Connection\Connection;
use RabbitMqBundle\Consumer\CallbackInterface;

/**
 * Class BenchmarkCallback
 *
 * @package RabbitBundleTests\app
 */
final class BenchmarkCallback implements CallbackInterface
{

    private const COUNT = 250000;

    /**
     * @var int
     */
    private static $counter = 0;

    /**
     * @var int|float
     */
    private static $start = 0;

    /**
     * BenchmarkCallback constructor.
     */
    public function __construct()
    {
        self::$start = hrtime(TRUE);
    }

    /**
     * @param Message    $message
     * @param Connection $connection
     * @param int        $channelId
     */
    public function processMessage(Message $message, Connection $connection, int $channelId): void
    {
        if (self::$counter++ === self::COUNT) {
            $time = hrtime(TRUE) - self::$start;

            echo sprintf(
                'Consumed %s messages in %ss: %s messages per second%s',
                self::COUNT,
                $time / 1e9,
                self::COUNT / $time * 1e9,
                PHP_EOL
            );

            $connection->getChannel($channelId)->ack($message);

            exit(0);
        }

        $connection->getChannel($channelId)->ack($message);
    }

}
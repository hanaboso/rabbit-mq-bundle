<?php declare(strict_types=1);

namespace RabbitBundleTests;

use Closure;
use Exception;
use Hanaboso\PhpCheckUtils\PhpUnit\Traits\CustomAssertTrait;
use Hanaboso\PhpCheckUtils\PhpUnit\Traits\PrivateTrait;
use PhpAmqpLib\Channel\AMQPChannel;
use PHPUnit\Framework\MockObject\MockObject;
use RabbitMqBundle\Connection\Connection;
use RabbitMqBundle\Consumer\AsyncCallbackInterface;
use RabbitMqBundle\Consumer\CallbackInterface;
use RabbitMqBundle\Consumer\ConsumerAbstract;
use RabbitMqBundle\Publisher\Publisher;
use React\EventLoop\LoopInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class KernelTestCaseAbstract
 *
 * @package RabbitBundleTests
 */
abstract class KernelTestCaseAbstract extends KernelTestCase
{

    use PrivateTrait;
    use CustomAssertTrait;

    protected const QUEUE = 'my-queue';

    /**
     * @var Connection
     */
    protected Connection $connection;

    /**
     * @var AMQPChannel
     */
    protected AMQPChannel $channel;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();

        $this->connection = self::$container->get('connection')->getConnection();
        $this->channel    = $this->connection->getChannel($this->connection->createChannel());

        try {
            $this->channel->queue_delete(static::QUEUE);
        } catch (Exception $exception) {
            $exception;

            $this->channel = $this->connection->getChannel($this->connection->createChannel());
        }
    }

    /**
     * @param int $messages
     */
    protected function createQueueWithMessages(int $messages = 1): void
    {
        for ($i = 0; $i < $messages; $i++) {
            self::assertEmpty(exec('tests/bin/console rabbit_mq:publisher:my-publisher'));
        }

        self::sleep();
        self::assertMessages($messages);
    }

    /**
     * @param int $count
     */
    protected function assertMessages(int $count): void
    {
        /** @var mixed[] $result */
        $result = $this->channel->queue_declare(static::QUEUE, TRUE);

        self::assertEquals($count, $result[1]);
    }

    /**
     * @param Closure|null $isConsuming
     * @param Closure|null $publish
     * @param int|null     $channelId
     *
     * @return AMQPChannel
     */
    protected function prepareChannel(
        ?Closure $isConsuming = NULL,
        ?Closure $publish = NULL,
        ?int $channelId = NULL
    ): AMQPChannel
    {
        /** @var AMQPChannel|MockObject $channel */
        $channel = self::createMock(AMQPChannel::class);

        if ($isConsuming) {
            $channel->method('is_consuming')->willReturnCallback($isConsuming);
        } else {
            $this->useRealChannel($channel, 'is_consuming');
        }

        if ($publish) {
            $channel->method('basic_publish')->willReturnCallback($publish);
        } else {
            $this->useRealChannel($channel, 'basic_publish');
        }

        if ($channelId) {
            $channel->method('getChannelId')->willReturn($channelId);
        }

        $this->useRealChannel($channel, 'exchange_declare');
        $this->useRealChannel($channel, 'exchange_bind');
        $this->useRealChannel($channel, 'queue_declare');
        $this->useRealChannel($channel, 'queue_bind');
        $this->useRealChannel($channel, 'basic_consume');

        return $channel;
    }

    /**
     * @param ConsumerAbstract                              $consumer
     * @param Closure|null                                  $isConsuming
     * @param CallbackInterface|AsyncCallbackInterface|null $callback
     * @param LoopInterface|null                            $loop
     *
     * @return ConsumerAbstract
     * @throws Exception
     */
    protected function prepareConsumer(
        ConsumerAbstract $consumer,
        ?Closure $isConsuming = NULL,
        $callback = NULL,
        ?LoopInterface $loop = NULL
    ): ConsumerAbstract
    {
        $manager     = $this->getProperty($consumer, 'connectionManager');
        $connections = $this->getProperty($manager, 'connections');

        /** @var Connection|MockObject $connection */
        $connection = self::createMock(Connection::class);
        $connection->method('getChannel')->willReturn($this->prepareChannel($isConsuming));
        $connection->method('createChannel')->willReturn(1);

        $this->setProperty($connection, 'clientFactory', $this->getProperty($connections['default'], 'clientFactory'));
        $this->setProperty($connection, 'logger', $this->getProperty($connections['default'], 'logger'));
        $this->setProperty($connection, 'name', $this->getProperty($connections['default'], 'name'));
        $connections['default'] = $connection;

        $this->setProperty($manager, 'connections', $connections);
        $this->setProperty($consumer, 'connectionManager', $manager);

        if ($callback) {
            $this->setProperty($consumer, 'callback', $callback);
        }

        if ($loop) {
            $this->setProperty($consumer, 'loop', $loop);
            $this->setProperty($consumer, 'timer', 9);
        }

        return $consumer;
    }

    /**
     * @param Publisher $publisher
     *
     * @return Publisher
     * @throws Exception
     */
    protected function preparePublisher(Publisher $publisher): Publisher
    {
        $manager     = $this->getProperty($publisher, 'connectionManager');
        $connections = $this->getProperty($manager, 'connections');

        /** @var Connection|MockObject $connection */
        $connection = self::createMock(Connection::class);
        $connection->method('getChannel')->willReturn($this->prepareChannel(NULL, $this->prepareOneException()));
        $connection->method('createChannel')->willReturn(1);

        $this->setProperty($connection, 'clientFactory', $this->getProperty($connections['default'], 'clientFactory'));
        $this->setProperty($connection, 'logger', $this->getProperty($connections['default'], 'logger'));
        $this->setProperty($connection, 'name', $this->getProperty($connections['default'], 'name'));
        $connections['default'] = $connection;

        $this->setProperty($manager, 'connections', $connections);
        $this->setProperty($publisher, 'connectionManager', $manager);

        return $publisher;
    }

    /**
     * @return Closure
     */
    protected function prepareOneException(): Closure
    {
        $iterator = 0;

        return static function () use (&$iterator): void {
            if ($iterator++ === 0) {
                throw new Exception('Something gone wrong!');
            }
        };
    }

    /**
     * @param bool $consume
     *
     * @return Closure
     */
    protected function prepareConsumerWait($consume = FALSE): Closure
    {
        $iterator = 0;

        return function () use ($consume, &$iterator): bool {
            if ($consume && $iterator++ === 0) {
                return TRUE;
            }

            $this->channel->wait(NULL, TRUE);

            return FALSE;
        };
    }

    /**
     *
     */
    protected static function sleep(): void
    {
        usleep(100_000);
    }

    /**
     * @param AMQPChannel|MockObject $channel
     * @param string                 $method
     */
    private function useRealChannel($channel, string $method): void
    {
        $channel->method($method)->willReturnCallback(fn(...$arguments) => $this->channel->$method(...$arguments));
    }

}

<?php declare(strict_types=1);

namespace RabbitBundleTests\Integration\Publisher;

use Exception;
use PhpAmqpLib\Channel\AMQPChannel;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;
use RabbitBundleTests\KernelTestCaseAbstract;
use RabbitMqBundle\Connection\Configurator;
use RabbitMqBundle\Connection\Connection;
use RabbitMqBundle\Publisher\Publisher;

/**
 * Class PublisherTest
 *
 * @package RabbitBundleTests\Integration\Publisher
 *
 * @covers  \RabbitMqBundle\Publisher\Publisher
 */
final class PublisherTest extends KernelTestCaseAbstract
{

    /**
     * @var Publisher
     */
    private Publisher $publisher;

    /**
     * Publisher
     */
    private Publisher $safePublisher;

    /**
     * @covers \RabbitMqBundle\Publisher\Publisher::setLogger
     */
    public function testLogger(): void
    {
        $this->publisher->setLogger(new NullLogger());

        self::assertFake();
    }

    /**
     * @throws Exception
     *
     * @covers \RabbitMqBundle\Publisher\Publisher::publish
     */
    public function testPublish(): void
    {
        $this->publisher
            ->setExchange($this->publisher->getExchange())
            ->setRoutingKey($this->publisher->getRoutingKey())
            ->publish('{}');

        self::sleep();
        self::assertMessages(1);
    }

    /**
     * @throws Exception
     *
     * @covers \RabbitMqBundle\Publisher\Publisher::publish
     */
    public function testSavePublish(): void
    {
        $this->safePublisher
            ->setExchange($this->safePublisher->getExchange())
            ->setRoutingKey($this->safePublisher->getRoutingKey())
            ->publish('{}');

        self::sleep();
        self::assertMessages(1);
    }

    /**
     * @throws Exception
     *
     * @covers \RabbitMqBundle\Publisher\Publisher::publish
     */
    public function testPublishException(): void
    {
        $this->preparePublisher($this->publisher)
            ->setExchange($this->publisher->getExchange())
            ->setRoutingKey($this->publisher->getRoutingKey())
            ->publish('{}');

        self::sleep();
        self::assertMessages(0);
    }

    /**
     * @throws Exception
     *
     * @covers \RabbitMqBundle\Publisher\Publisher::publish
     */
    public function testSavePublishException(): void
    {
        $this->prepareSafePublisher($this->safePublisher)
            ->setExchange($this->safePublisher->getExchange())
            ->setRoutingKey($this->safePublisher->getRoutingKey())
            ->publish('{}');

        self::sleep();
        self::assertMessages(2);
    }

    /**
     * @covers \RabbitMqBundle\Publisher\Publisher::setup
     */
    public function testSetup(): void
    {
        $this->publisher->setup();

        self::assertFake();
    }

    /**
     * @throws Exception
     *
     * @covers \RabbitMqBundle\Publisher\Publisher::setup
     */
    public function testSetupException(): void
    {
        /** @var Configurator|MockObject $configurator */
        $configurator = self::createMock(Configurator::class);
        $configurator->method('setup')->willReturnCallback($this->prepareOneException());

        $this->setProperty($this->publisher, 'configurator', $configurator);
        $this->publisher->setup();

        self::assertFake();
    }

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->publisher     = self::$container->get('publisher');
        $this->safePublisher = self::$container->get('publisher-safe');
    }

    /**
     * @param Publisher $publisher
     *
     * @return Publisher
     * @throws Exception
     */
    private function prepareSafePublisher(Publisher $publisher): Publisher
    {
        $manager     = $this->getProperty($publisher, 'connectionManager');
        $connections = $this->getProperty($manager, 'connections');

        $channel = self::createPartialMock(
            AMQPChannel::class,
            [
                'exchange_declare',
                'exchange_bind',
                'queue_declare',
                'queue_bind',
                'basic_consume',
                'wait_for_pending_acks',
                'confirm_select',
                'basic_publish',
            ]
        );

        $this->useRealChannel($channel, 'exchange_declare');
        $this->useRealChannel($channel, 'exchange_bind');
        $this->useRealChannel($channel, 'queue_declare');
        $this->useRealChannel($channel, 'queue_bind');
        $this->useRealChannel($channel, 'basic_consume');
        $this->useRealChannel($channel, 'confirm_select');
        $this->useRealChannel($channel, 'basic_publish');

        $i = 1;

        $channel->method('wait_for_pending_acks')->willReturnCallback(
            function () use ($channel, &$i): void {
                $ack  = $this->getProperty($channel, 'ack_handler');
                $nack = $this->getProperty($channel, 'nack_handler');

                $this->invokeMethod($channel, 'dispatch_to_handler', [$i++ === 1 ? $nack : $ack, []]);
            }
        );

        /** @var Connection|MockObject $connection */
        $connection = self::createMock(Connection::class);
        $connection->method('getChannel')->willReturn($channel);
        $connection->method('createChannel')->willReturn(1);

        $this->setProperty($connection, 'clientFactory', $this->getProperty($connections['default'], 'clientFactory'));
        $this->setProperty($connection, 'logger', $this->getProperty($connections['default'], 'logger'));
        $this->setProperty($connection, 'name', $this->getProperty($connections['default'], 'name'));
        $connections['default'] = $connection;

        $this->setProperty($manager, 'connections', $connections);
        $this->setProperty($publisher, 'connectionManager', $manager);

        return $publisher;
    }

}

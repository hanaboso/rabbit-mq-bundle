<?php declare(strict_types=1);

namespace RabbitBundleTests\Integration\Connection;

use Exception;
use InvalidArgumentException;
use PhpAmqpLib\Connection\AMQPSocketConnection;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;
use RabbitBundleTests\KernelTestCaseAbstract;
use RabbitMqBundle\Connection\ClientFactory;
use RabbitMqBundle\Connection\ConnectionManager;

/**
 * Class ConnectionTest
 *
 * @package RabbitBundleTests\Integration\Connection
 *
 * @covers  \RabbitMqBundle\Connection\Connection
 */
final class ConnectionTest extends KernelTestCaseAbstract
{

    /**
     * @covers \RabbitMqBundle\Connection\Connection::setLogger
     */
    public function testLogger(): void
    {
        $this->connection->setLogger(new NullLogger());

        self::assertFake();
    }

    /**
     * @throws Exception
     *
     * @covers \RabbitMqBundle\Connection\Connection::getClient
     */
    public function testGetClient(): void
    {
        $this->connection->getClient();

        self::assertFake();
    }

    /**
     * @throws Exception
     *
     * @covers \RabbitMqBundle\Connection\Connection::getChannel
     */
    public function testGetChannel(): void
    {
        $this->connection->getChannel($this->connection->createChannel());

        self::assertFake();
    }

    /**
     * @throws Exception
     *
     * @covers \RabbitMqBundle\Connection\Connection::getChannel
     */
    public function testGetChannelNotConnected(): void
    {
        $this->setProperty($this->connection->getClient(), 'is_connected', FALSE);

        $this->connection->getChannel($this->connection->createChannel());

        self::assertFake();
    }

    /**
     * @throws Exception
     *
     * @covers \RabbitMqBundle\Connection\Connection::getChannel
     */
    public function testGetChannelUnknown(): void
    {
        self::expectException(InvalidArgumentException::class);

        $this->connection->getChannel(0);
    }

    /**
     * @throws Exception
     *
     * @covers \RabbitMqBundle\Connection\Connection::getChannel
     */
    public function testGetChannelException(): void
    {
        $channel = $this->connection->createChannel();

        /** @var AMQPSocketConnection|MockObject $connection */
        $connection = self::createMock(AMQPSocketConnection::class);
        $connection->method('reconnect')->willThrowException(new Exception('Something gone wrong!'));
        $connection->expects(self::at(0))->method('isConnected')->willReturn(FALSE);
        $connection->expects(self::at(1))->method('isConnected')->willReturn(TRUE);
        $this->setProperty($this->connection, 'client', $connection);

        $this->connection->getChannel($channel);

        self::assertFake();
    }

    /**
     * @throws Exception
     *
     * @covers \RabbitMqBundle\Connection\Connection::createChannel
     */
    public function testCreateChannel(): void
    {
        self::assertEquals(2, $this->connection->createChannel());
    }

    /**
     * @throws Exception
     *
     * @covers \RabbitMqBundle\Connection\Connection::createChannel
     */
    public function testCreateChannelNotConnected(): void
    {
        $this->setProperty($this->connection->getClient(), 'is_connected', FALSE);

        self::assertEquals(1, $this->connection->createChannel());
    }

    /**
     * @covers \RabbitMqBundle\Connection\Connection::reconnect
     *
     * @throws Exception
     */
    public function testConnection(): void
    {
        $connection = self::createMock(AMQPSocketConnection::class);
        $connection->expects(self::exactly(6))->method('channel')->willReturnOnConsecutiveCalls(
            $this->prepareChannel(NULL, NULL, 1),
            $this->prepareChannel(NULL, NULL, 2),
            $this->prepareChannel(NULL, NULL, 3),
            $this->prepareChannel(NULL, NULL, 1),
            $this->prepareChannel(NULL, NULL, 2),
            $this->prepareChannel(NULL, NULL, 3)
        );

        $connection->expects(self::exactly(10))->method('isConnected')->willReturnOnConsecutiveCalls(
            FALSE,
            TRUE,
            TRUE,
            TRUE,
            FALSE,
            TRUE,
            TRUE,
            TRUE,
            TRUE,
            TRUE
        );

        //            FALSE, // createChannel - 1
        //            TRUE,  // createChannel - 2
        //            TRUE,  // createChannel - 3
        //            TRUE,  // getChannel - 1
        //            FALSE, // getChannel - 2
        //            TRUE,  // restore
        //            TRUE,  // getChannel - 3
        //            TRUE,  // getChannel - 1 - after reconnect
        //            TRUE,  // getChannel - 2 - after reconnect
        //            TRUE   // getChannel - 3 - after reconnect

        $iterator = 0;
        $connection->method('reconnect')->willReturnCallback(
            static function () use (&$iterator): bool {
                if ($iterator++ === 1) {
                    throw new Exception('Something gone wrong!');
                }

                return TRUE;
            }
        );

        $clientFactory = self::createMock(ClientFactory::class);
        $clientFactory->method('getConfig')->willReturn(
            [
                ClientFactory::RECONNECT_TIMEOUT => 1,
                ClientFactory::RECONNECT_TRIES   => 1,
            ]
        );
        $clientFactory->method('create')->willReturn($connection);

        $connection = (new ConnectionManager($clientFactory))->getConnection();

        $channelOne   = $connection->createChannel();
        $channelTwo   = $connection->createChannel();
        $channelThree = $connection->createChannel();

        $connection->getChannel($channelOne);
        // Reconnecting...
        $connection->getChannel($channelTwo);
        $connection->getChannel($channelThree);

        $connection->getChannel($channelOne);
        $connection->getChannel($channelTwo);
        $connection->getChannel($channelThree);

        self::assertEquals(3, $iterator);
    }

    /**
     * @throws Exception
     *
     * @covers \RabbitMqBundle\Connection\Connection::reconnect
     */
    public function testReconnectException(): void
    {
        $connection = self::createMock(AMQPSocketConnection::class);
        $connection->method('isConnected')->willReturn(FALSE);

        $factory = self::createMock(ClientFactory::class);
        $factory->expects(self::at(0))->method('create')->willThrowException(new Exception('Something gone wrong!'));
        $factory->expects(self::at(1))->method('create')->willReturn($connection);
        $factory->expects(self::at(2))->method('create')->willReturn($this->connection->getClient());
        $factory
            ->method('getConfig')
            ->willReturn([ClientFactory::RECONNECT_TRIES => 1, ClientFactory::RECONNECT_TIMEOUT => 1]);
        $this->setProperty($this->connection, 'clientFactory', $factory);

        $this->connection->createChannel();
        $this->connection->reconnect();

        self::assertFake();
    }

    /**
     * @throws Exception
     *
     * @covers \RabbitMqBundle\Connection\Connection::reconnect
     */
    public function testReconnectExceptionExceeded(): void
    {
        $factory = self::createMock(ClientFactory::class);
        $factory->expects(self::at(0))->method('create')->willThrowException(new Exception('Something gone wrong!'));
        $factory
            ->method('getConfig')
            ->willReturn([ClientFactory::RECONNECT_TRIES => 1, ClientFactory::RECONNECT_TIMEOUT => 1]);
        $this->setProperty($this->connection, 'clientFactory', $factory);

        $this->connection->reconnect();
    }

}

<?php declare(strict_types=1);

namespace RabbitBundleTests\Connection;

use Exception;
use Hanaboso\PhpCheckUtils\PhpUnit\Traits\PrivateTrait;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPSocketConnection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RabbitMqBundle\Connection\ClientFactory;
use RabbitMqBundle\Connection\ConnectionManager;

/**
 * Class ConnectionTest
 *
 * @package RabbitBundleTests\Connection
 */
final class ConnectionTest extends TestCase
{

    use PrivateTrait;

    /**
     * @throws Exception
     */
    public function testConnection(): void
    {
        /** @var AMQPSocketConnection|MockObject $connection */
        $connection = self::createMock(AMQPSocketConnection::class);
        $connection->expects(self::exactly(6))->method('channel')->willReturnOnConsecutiveCalls(
            $this->prepareChannel(1),
            $this->prepareChannel(2),
            $this->prepareChannel(3),
            $this->prepareChannel(1),
            $this->prepareChannel(2),
            $this->prepareChannel(3)
        );

        $connection->expects(self::exactly(10))->method('isConnected')->willReturnOnConsecutiveCalls(
            FALSE, // createChannel - 1
            TRUE,  // createChannel - 2
            TRUE,  // createChannel - 3
            TRUE,  // getChannel - 1
            FALSE, // getChannel - 2
            TRUE,  // restore
            TRUE,  // getChannel - 3
            TRUE,  // getChannel - 1 - after reconnect
            TRUE,  // getChannel - 2 - after reconnect
            TRUE   // getChannel - 3 - after reconnect
        );

        $i = 0;

        $connection->method('reconnect')->willReturnCallback(
            function () use (&$i): bool {
                if ($i++ === 1) {
                    throw new Exception('Something gone wrong!');
                }

                return TRUE;
            }
        );

        /** @var ClientFactory|MockObject $clientFactory */
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
        $connection->getChannel($channelTwo); // Reconnecting...
        $connection->getChannel($channelThree);

        $connection->getChannel($channelOne);
        $connection->getChannel($channelTwo);
        $connection->getChannel($channelThree);

        self::assertEquals(3, $i);
    }

    /**
     * @param int $id
     *
     * @return AMQPChannel
     */
    private function prepareChannel(int $id): AMQPChannel
    {
        /** @var AMQPChannel|MockObject $channel */
        $channel = self::createMock(AMQPChannel::class);
        $channel->method('getChannelId')->willReturn($id);

        return $channel;
    }

}

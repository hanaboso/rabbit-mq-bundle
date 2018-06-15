<?php declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: venca
 * Date: 1/9/18
 * Time: 11:02 AM
 */

namespace Tests\Connection;

use Bunny\Channel;
use Bunny\Client;
use Bunny\Exception\ClientException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RabbitMqBundle\Connection\ClientFactory;
use RabbitMqBundle\Connection\Connection;

/**
 * Class ConnectionTest
 *
 * @package Tests\Connection
 */
final class ConnectionTest extends TestCase
{

    /**
     *
     */
    public function testReconnect(): void
    {

        /**
         * @var ClientFactory|MockObject $clientFactory
         */
        $clientFactory = $this->createMock(ClientFactory::class);

        /** @var Client|MockObject $client */
        $client = $this->createMock(Client::class);

        $client->expects($this->exactly(6))->method('channel')->willReturnOnConsecutiveCalls(
            new Channel($client, 1),
            new Channel($client, 2),
            new Channel($client, 3),
            new Channel($client, 1),
            new Channel($client, 2),
            new Channel($client, 3)
        );

        $client->expects($this->exactly(10))->method('isConnected')->willReturnOnConsecutiveCalls(
            FALSE, // createChannel - 1
            TRUE, // createChannel - 2
            TRUE, // createChannel - 3

            TRUE, // getChannel - 1
            FALSE, // getChannel - 2
            TRUE, // restore
            TRUE, // getChannel - 3

            TRUE, // getChannel - 1 - after reconnect
            TRUE, // getChannel - 2 - after reconnect
            TRUE // getChannel - 3 - after reconnect
        );

        $i = 0;
        $client
            ->method('connect')
            ->willReturnCallback(function () use (&$i) {

                if ($i === 1) {
                    $i++;
                    throw new ClientException('Bla');
                }

                $i++;

                return TRUE;
            }); // createChannel - 1

        $clientFactory->method('create')->willReturn($client);

        $conn = new Connection('default', $clientFactory);

        $id  = $conn->createChannel();
        $id2 = $conn->createChannel();
        $id3 = $conn->createChannel();

        // Try get new channel by id
        $conn->getChannel($id);
        $conn->getChannel($id2); // call reconnect
        $conn->getChannel($id3);

        // Try get channel after reconnect
        $conn->getChannel($id);
        $conn->getChannel($id2);
        $conn->getChannel($id3);

        $this->assertSame(3, $i);
    }

}
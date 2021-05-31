<?php declare(strict_types=1);

namespace RabbitBundleTests\Integration\Utils;

use Exception;
use PhpAmqpLib\Message\AMQPMessage;
use RabbitBundleTests\KernelTestCaseAbstract;
use RabbitMqBundle\Utils\Message;

/**
 * Class MessageTest
 *
 * @package RabbitBundleTests\Integration\Utils
 *
 * @covers  \RabbitMqBundle\Utils\Message
 */
final class MessageTest extends KernelTestCaseAbstract
{

    /**
     * @covers \RabbitMqBundle\Utils\Message::getBody
     */
    public function testBody(): void
    {
        self::assertEquals('{}', Message::getBody(Message::create('{}')));
    }

    /**
     * @covers \RabbitMqBundle\Utils\Message::create
     * @covers \RabbitMqBundle\Utils\Message::getHeaders
     */
    public function testHeaders(): void
    {
        self::assertEquals(
            [
                'key'      => 'Value',
                'reply-to' => 'Reply To',
            ],
            Message::getHeaders(
                Message::create(
                    '{}',
                    [
                        'key'      => 'Value',
                        'reply-to' => 'Reply To',
                    ],
                ),
            ),
        );
    }

    /**
     * @throws Exception
     *
     * @covers \RabbitMqBundle\Utils\Message::ack
     */
    public function testAck(): void
    {
        Message::ack($this->createMessage(), $this->connection, $this->channel->getChannelId());

        self::assertFake();
    }

    /**
     * @throws Exception
     *
     * @covers \RabbitMqBundle\Utils\Message::nack
     */
    public function testNack(): void
    {
        Message::nack($this->createMessage(), $this->connection, $this->channel->getChannelId());

        self::assertFake();
    }

    /**
     * @throws Exception
     *
     * @covers \RabbitMqBundle\Utils\Message::reject
     */
    public function testReject(): void
    {
        Message::reject($this->createMessage(), $this->connection, $this->channel->getChannelId());

        self::assertFake();
    }

    /**
     * @return AMQPMessage
     */
    private function createMessage(): AMQPMessage
    {
        $message = Message::create('{}');
        $message->setDeliveryInfo(1, FALSE, 'exchange', 'routingKey');

        return $message;
    }

}

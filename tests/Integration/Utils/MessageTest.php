<?php declare(strict_types=1);

namespace RabbitBundleTests\Integration\Utils;

use Exception;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\Attributes\CoversClass;
use RabbitBundleTests\KernelTestCaseAbstract;
use RabbitMqBundle\Utils\Message;

/**
 * Class MessageTest
 *
 * @package RabbitBundleTests\Integration\Utils
 */
#[CoversClass(Message::class)]
final class MessageTest extends KernelTestCaseAbstract
{

    /**
     * @return void
     */
    public function testBody(): void
    {
        self::assertSame('{}', Message::getBody(Message::create('{}')));
    }

    /**
     * @return void
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
     */
    public function testAck(): void
    {
        Message::ack($this->createMessage(), $this->connection, $this->channel->getChannelId() ?? 1);

        self::assertFake();
    }

    /**
     * @throws Exception
     */
    public function testNack(): void
    {
        Message::nack($this->createMessage(), $this->connection, $this->channel->getChannelId() ?? 1);

        self::assertFake();
    }

    /**
     * @throws Exception
     */
    public function testReject(): void
    {
        Message::reject($this->createMessage(), $this->connection, $this->channel->getChannelId() ?? 1);

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

<?php declare(strict_types=1);

namespace RabbitBundleTests\Integration\Consumer;

use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use PhpAmqpLib\Message\AMQPMessage;
use RabbitBundleTests\KernelTestCaseAbstract;
use RabbitMqBundle\Connection\Connection;
use RabbitMqBundle\Consumer\AsyncCallbackInterface;
use RabbitMqBundle\Consumer\AsyncConsumer;
use RabbitMqBundle\Consumer\Callback\Exception\CallbackException;

/**
 * Class AsyncConsumerTest
 *
 * @package RabbitBundleTests\Integration\Consumer
 *
 * @covers  \RabbitMqBundle\Consumer\AsyncConsumer
 */
final class AsyncConsumerTest extends KernelTestCaseAbstract
{

    /**
     * @var AsyncConsumer
     */
    private AsyncConsumer $consumer;

    /**
     * @throws Exception
     *
     * @covers \RabbitMqBundle\Consumer\AsyncConsumer::consume
     */
    public function testConsume(): void
    {
        $this->createQueueWithMessages();
        $this->prepareConsumer($this->consumer, $this->prepareConsumerWait())->consume();

        self::assertMessages(0);
    }

    /**
     * @throws Exception
     *
     * @covers \RabbitMqBundle\Consumer\AsyncConsumer::consume
     */
    public function testConsumeException(): void
    {
        $this->createQueueWithMessages();

        $this->prepareConsumer($this->consumer, $this->prepareConsumerWait(TRUE))->consume();

        self::assertFake();
    }

    /**
     * @throws Exception
     *
     * @covers \RabbitMqBundle\Consumer\AsyncConsumer::consume
     */
    public function testConsumeCallbackException(): void
    {
        self::expectException(CallbackException::class);

        $this->createQueueWithMessages();
        $this->prepareConsumer(
            $this->consumer,
            $this->prepareConsumerWait(),
            new class implements AsyncCallbackInterface {

                /**
                 * @param AMQPMessage $message
                 * @param Connection  $connection
                 * @param int         $channelId
                 *
                 * @return PromiseInterface
                 */
                public function processMessage(
                    AMQPMessage $message,
                    Connection $connection,
                    int $channelId,
                ): PromiseInterface
                {
                    $message;
                    $connection;
                    $channelId;

                    throw new Exception('Something gone wrong!');
                }

            },
        )->consume();
    }

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->consumer = self::getContainer()->get('consumer-async');
    }

}

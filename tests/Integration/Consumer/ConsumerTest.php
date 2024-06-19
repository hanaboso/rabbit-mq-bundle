<?php declare(strict_types=1);

namespace RabbitBundleTests\Integration\Consumer;

use Exception;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Log\NullLogger;
use RabbitBundleTests\KernelTestCaseAbstract;
use RabbitMqBundle\Connection\Configurator;
use RabbitMqBundle\Connection\Connection;
use RabbitMqBundle\Consumer\CallbackInterface;
use RabbitMqBundle\Consumer\Consumer;
use RabbitMqBundle\Consumer\ConsumerAbstract;

/**
 * Class ConsumerTest
 *
 * @package RabbitBundleTests\Integration\Consumer
 */
#[CoversClass(Consumer::class)]
#[CoversClass(ConsumerAbstract::class)]
final class ConsumerTest extends KernelTestCaseAbstract
{

    /**
     * @var Consumer
     */
    private Consumer $consumer;

    /**
     * @return void
     */
    public function testLogger(): void
    {
        $this->consumer->setLogger(new NullLogger());

        self::assertFake();
    }

    /**
     * @throws Exception
     */
    public function testConsume(): void
    {
        $this->createQueueWithMessages();
        $this->prepareConsumer($this->consumer, $this->prepareConsumerWait())->consume();

        self::assertMessages(0);
    }

    /**
     * @throws Exception
     */
    public function testConsumeException(): void
    {
        $this->createQueueWithMessages();
        $this->prepareConsumer(
            $this->consumer,
            $this->prepareConsumerWait(TRUE),
            new class implements CallbackInterface {

                /**
                 * @param AMQPMessage $message
                 * @param Connection  $connection
                 * @param int         $channelId
                 */
                public function processMessage(AMQPMessage $message, Connection $connection, int $channelId): void
                {
                    $message;
                    $connection;
                    $channelId;

                    throw new Exception('Something gone wrong!');
                }

            },
        )->consume();

        self::assertMessages(0);
    }

    /**
     * @return void
     */
    public function testSetup(): void
    {
        $this->consumer->setup();

        self::assertFake();
    }

    /**
     * @throws Exception
     */
    public function testSetupException(): void
    {
        $configurator = self::createMock(Configurator::class);
        $configurator->method('setup')->willReturnCallback($this->prepareOneException());

        $this->setProperty($this->consumer, 'configurator', $configurator);
        $this->consumer->setup();

        self::assertFake();
    }

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->consumer = self::getContainer()->get('consumer');
    }

}

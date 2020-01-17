<?php declare(strict_types=1);

namespace RabbitBundleTests\Integration\Publisher;

use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;
use RabbitBundleTests\KernelTestCaseAbstract;
use RabbitMqBundle\Connection\Configurator;
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
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->publisher = self::$container->get('publisher');
    }

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

}

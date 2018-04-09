<?php declare(strict_types=1);

namespace Tests\DI;

use Nette\Configurator;
use PHPUnit\Framework\TestCase;
use RabbitMqBundle\Command\ConsumerCommand;
use RabbitMqBundle\Command\PublisherCommand;
use RabbitMqBundle\Connection\ClientFactory;
use RabbitMqBundle\Connection\Configurator as RabbitMqConfigurator;
use RabbitMqBundle\Connection\ConnectionManager;
use RabbitMqBundle\Consumer\Callback\DumpCallback;
use RabbitMqBundle\Consumer\Callback\NullCallback;
use RabbitMqBundle\Consumer\Consumer;
use RabbitMqBundle\Publisher\Publisher;

/**
 * Class RabbitMqExtensionTest
 *
 * @package Tests\DI
 */
final class RabbitMqExtensionTest extends TestCase
{

    /**
     *
     */
    public function testExtension(): void
    {

        $container = (new Configurator())
            ->setTempDirectory(__DIR__ . '/../../temp')
            ->addConfig(__DIR__ . '/config.neon')
            ->createContainer();

        $this->assertInstanceOf(ClientFactory::class, $container->getByType(ClientFactory::class));
        $this->assertInstanceOf(ConnectionManager::class, $container->getByType(ConnectionManager::class));
        $this->assertInstanceOf(Consumer::class, $container->getByType(Consumer::class));
        $this->assertInstanceOf(ConsumerCommand::class, $container->getByType(ConsumerCommand::class));
        $this->assertInstanceOf(Publisher::class, $container->getByType(Publisher::class));
        $this->assertInstanceOf(PublisherCommand::class, $container->getByType(PublisherCommand::class));
        $this->assertInstanceOf(RabbitMqConfigurator::class, $container->getByType(RabbitMqConfigurator::class));
        $this->assertInstanceOf(NullCallback::class, $container->getByType(NullCallback::class));
        $this->assertInstanceOf(DumpCallback::class, $container->getByType(DumpCallback::class));

        $this->assertTrue($container->hasService('rabbit_mq.publisher.my_publisher'));
        $this->assertTrue($container->hasService('rabbit_mq.consumer.my_consumer'));

        $this->assertTrue($container->hasService('rabbit_mq.publisher.command.my_publisher'));
        $this->assertTrue($container->hasService('rabbit_mq.consumer.command.my_consumer'));

        $this->assertTrue($container->hasService('monolog.logger.rabbit_mq'));
        $this->assertTrue($container->hasService('monolog.handler.rabbit_mq'));
    }

}
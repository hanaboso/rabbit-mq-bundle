<?php declare(strict_types=1);

namespace RabbitBundleTests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use RabbitMqBundle\Command\ConsumerCommand;
use RabbitMqBundle\Connection\ClientFactory;
use RabbitMqBundle\Connection\Configurator;
use RabbitMqBundle\Connection\ConnectionManager;
use RabbitMqBundle\Consumer\Consumer;
use RabbitMqBundle\DependencyInjection\RabbitMqExtension;
use RabbitMqBundle\Publisher\Publisher;
use RabbitMqBundle\RabbitMqBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ConfiguratorTest
 *
 * @package RabbitBundleTests\DependencyInjection
 */
final class ConfiguratorTest extends TestCase
{

    /**
     * @covers Configuration::getConfigTreeBuilder()
     */
    public function testConfig(): void
    {
        $config    = Yaml::parseFile(__DIR__ . '/config.yml');
        $container = new ContainerBuilder();

        // Load config
        $extension = new RabbitMqExtension();
        $extension->load($config, $container);

        // Register extension
        $container->registerExtension($extension);

        // Build bundle
        $bundle = new RabbitMqBundle();
        $bundle->build($container);

        $container->compile();

        // Get all config
        $config = $container->getParameter('rabbit_mq');

        // Test default keys
        self::assertCount(15, $config);
        self::assertArrayHasKey('connections', $config);
        self::assertArrayHasKey('queues', $config);
        self::assertArrayHasKey('exchanges', $config);
        self::assertArrayHasKey('publishers', $config);
        self::assertArrayHasKey('consumers', $config);

        // Test default classes
        self::assertArrayHasKey('client_factory', $config);
        self::assertSame(ClientFactory::class, $config['client_factory']);
        self::assertArrayHasKey('connection_manager', $config);
        self::assertSame(ConnectionManager::class, $config['connection_manager']);
        self::assertArrayHasKey('consumer', $config);
        self::assertSame(Consumer::class, $config['consumer']);
        self::assertArrayHasKey('consumer_command', $config);
        self::assertSame(ConsumerCommand::class, $config['consumer_command']);
        self::assertArrayHasKey('publisher', $config);
        self::assertSame(Publisher::class, $config['publisher']);
        self::assertArrayHasKey('logger', $config);
        self::assertNull($config['logger']);
        self::assertArrayHasKey('configurator', $config);
        self::assertSame(Configurator::class, $config['configurator']);

        // Test connections
        $connection = [
            'host'              => 'rabbitmq',
            'user'              => 'guest',
            'password'          => 'guest',
            'port'              => 5672,
            'vhost'             => '/',
            'heartbeat'         => 30,
            'timeout'           => 60,
            'reconnect'         => TRUE,
            'reconnect_tries'   => 3600,
            'reconnect_timeout' => 1,
        ];
        $arguments  = $container->getDefinition('rabbit_mq.connection_manager')->getArguments()[0]->getArguments()[0];

        self::assertArrayHasKey('default', $arguments);
        self::assertSame($connection, $arguments['default']);

        // Test queues
        $queue = [
            'arguments'   => [
                'my-arg' => 'my-value',
            ],
            'bindings'    => [
                [
                    'exchange'    => 'my-exchange',
                    'routing_key' => 'routing-key',
                    'arguments'   => [
                        'my-arg' => 'my-value',
                    ],
                    'no_wait'     => FALSE,
                ],
            ],
            'durable'     => FALSE,
            'exclusive'   => FALSE,
            'auto_delete' => FALSE,
            'passive'     => FALSE,
            'no_wait'     => FALSE,
        ];
        self::assertArrayHasKey('my-queue', $config['queues']);
        self::assertSame($queue, $config['queues']['my-queue']);

        // Test exchanges
        $exchange = [
            'type'        => 'direct',
            'passive'     => FALSE,
            'durable'     => FALSE,
            'auto_delete' => FALSE,
            'internal'    => FALSE,
            'no_wait'     => FALSE,
            'arguments'   => [
                'my-arg' => 'my-value',
            ],
            'bindings'    => [
                [
                    'exchange'    => 'my-exchange',
                    'routing_key' => 'routing-key',
                    'arguments'   => [
                        'my-arg' => 'my-value',
                    ],
                    'no_wait'     => FALSE,
                ],
            ],
        ];
        self::assertArrayHasKey('my-exchange', $config['exchanges']);
        self::assertSame($exchange, $config['exchanges']['my-exchange']);

        // Test publishers
        $producer = [
            'routing_key' => 'routing-key',
            'exchange'    => 'my-exchange',
            'mandatory'   => FALSE,
            'immediate'   => FALSE,
            'class'       => Publisher::class,
            'connection'  => 'default',
        ];
        self::assertArrayHasKey('my-publisher', $config['publishers']);
        self::assertSame($producer, $config['publishers']['my-publisher']);

        // Test consumers
        $consumer = [
            'queue'          => 'my-queue',
            'callback'       => 'rabbit_mq.null_callback',
            'class'          => Consumer::class,
            'connection'     => 'default',
            'consumer_tag'   => '',
            'async'          => FALSE,
            'no_local'       => FALSE,
            'no_ack'         => FALSE,
            'exclusive'      => FALSE,
            'no_wait'        => FALSE,
            'prefetch_count' => 0,
            'prefetch_size'  => 0,
            'tick_method'    => NULL,
            'tick_seconds'   => NULL,
            'max_messages'   => NULL,
            'max_seconds'    => NULL,
            'arguments'      => [],
        ];
        self::assertArrayHasKey('my-consumer', $config['consumers']);
        self::assertSame($consumer, $config['consumers']['my-consumer']);
    }

}

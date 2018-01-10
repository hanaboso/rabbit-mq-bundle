<?php declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: venca
 * Date: 4.1.18
 * Time: 9:08
 */

namespace Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use RabbitMqBundle\Command\ConsumerCommand;
use RabbitMqBundle\Connection\ClientFactory;
use RabbitMqBundle\Connection\Configurator;
use RabbitMqBundle\Connection\ConnectionManager;
use RabbitMqBundle\Consumer\Consumer;
use RabbitMqBundle\DependencyInjection\Configuration;
use RabbitMqBundle\DependencyInjection\RabbitMqExtension;
use RabbitMqBundle\Publisher\Publisher;
use RabbitMqBundle\RabbitMqBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ConfiguratorTest
 *
 * @package Tests\DependencyInjection\Compiler
 */
class ConfiguratorTest extends TestCase
{

    /**
     * @covers Configuration::getConfigTreeBuilder()
     */
    public function testConfig(): void
    {
        $config = Yaml::parse(file_get_contents(__DIR__ . '/config.yml'));

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
        $this->assertCount(12, $config);
        $this->assertArrayHasKey('connections', $config);
        $this->assertArrayHasKey('queues', $config);
        $this->assertArrayHasKey('exchanges', $config);
        $this->assertArrayHasKey('publishers', $config);
        $this->assertArrayHasKey('consumers', $config);

        // Test default classes
        $this->assertArrayHasKey('client_factory', $config);
        $this->assertSame(ClientFactory::class, $config['client_factory']);
        $this->assertArrayHasKey('connection_manager', $config);
        $this->assertSame(ConnectionManager::class, $config['connection_manager']);
        $this->assertArrayHasKey('consumer', $config);
        $this->assertSame(Consumer::class, $config['consumer']);
        $this->assertArrayHasKey('consumer_command', $config);
        $this->assertSame(ConsumerCommand::class, $config['consumer_command']);
        $this->assertArrayHasKey('publisher', $config);
        $this->assertSame(Publisher::class, $config['publisher']);
        $this->assertArrayHasKey('logger', $config);
        $this->assertNull($config['logger']);
        $this->assertArrayHasKey('configurator', $config);
        $this->assertSame(Configurator::class, $config['configurator']);

        // Test connections
        $connection = [
            'host'              => 'rabbitmq',
            'port'              => 5672,
            'vhost'             => '/',
            'user'              => 'guest',
            'password'          => 'guest',
            'heartbeat'         => 60,
            'timeout'           => 1,
            'reconnect'         => TRUE,
            'reconnect_tries'   => NULL,
            'reconnect_timeout' => 1,
        ];
        $this->assertArrayHasKey('default', $config['connections']);
        $this->assertSame($connection, $config['connections']['default']);

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
        $this->assertArrayHasKey('my-queue', $config['queues']);
        $this->assertSame($queue, $config['queues']['my-queue']);

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
        $this->assertArrayHasKey('my-exchange', $config['exchanges']);
        $this->assertSame($exchange, $config['exchanges']['my-exchange']);

        // Test publishers
        $producer = [
            'routing_key' => 'routing-key',
            'exchange'    => 'my-exchange',
            'mandatory'   => FALSE,
            'immediate'   => FALSE,
            'class'       => Publisher::class,
            'connection'  => 'default',
        ];
        $this->assertArrayHasKey('my-publisher', $config['publishers']);
        $this->assertSame($producer, $config['publishers']['my-publisher']);

        // Test consumers
        $consumer = [
            'queue'          => 'my-queue',
            'callback'       => 'rabbit_mq.null_callback',
            'class'          => Consumer::class,
            'connection'     => 'default',
            'consumer_tag'   => '',
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
        $this->assertArrayHasKey('my-consumer', $config['consumers']);
        $this->assertSame($consumer, $config['consumers']['my-consumer']);
    }

}
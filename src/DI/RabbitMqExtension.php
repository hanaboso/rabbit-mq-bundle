<?php declare(strict_types=1);

namespace RabbitMqBundle\DI;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Nette\DI\CompilerExtension;
use RabbitMqBundle\Command\ConsumerCommand;
use RabbitMqBundle\Command\PublisherCommand;
use RabbitMqBundle\Connection\ClientFactory;
use RabbitMqBundle\Connection\Configurator;
use RabbitMqBundle\Connection\ConnectionManager;
use RabbitMqBundle\Consumer\Callback\DumpCallback;
use RabbitMqBundle\Consumer\Callback\NullCallback;
use RabbitMqBundle\Consumer\Consumer;
use RabbitMqBundle\Publisher\Publisher;

/**
 * Class RabbitMqExtension
 *
 * @package RabbitMqBundle\DI
 */
final class RabbitMqExtension extends CompilerExtension
{

    /**
     * @var array
     */
    private $defaultConfig = [
        'client_factory'     => ClientFactory::class,
        'connection_manager' => ConnectionManager::class,
        'consumer'           => Consumer::class,
        'consumer_command'   => ConsumerCommand::class,
        'publisher'          => Publisher::class,
        'logger'             => NULL,
        'configurator'       => Configurator::class,
        'connections'        => [
            'default' => [
                'host'              => 'localhost',
                'port'              => 5672,
                'vhost'             => '/',
                'user'              => 'guest',
                'password'          => 'guest',
                'heartbeat'         => 60,
                'timeout'           => 1,
                'reconnect'         => TRUE,
                'reconnect_tries'   => NULL,
                'reconnect_timeout' => 1,
            ],
        ],
        'queues'             => [],
        'exchanges'          => [],
        'publishers'         => [],
        'consumers'          => [],
    ];

    /**
     * @var array
     */
    private $queueConfig = [
        'durable'     => FALSE,
        'exclusive'   => FALSE,
        'auto_delete' => FALSE,
        'passive'     => FALSE,
        'no_wait'     => FALSE,
        'arguments'   => [],
        'bindings'    => [],
    ];

    /**
     * @var array
     */
    private $exchangeConfig = [
        'type'        => 'direct',
        'durable'     => FALSE,
        'auto_delete' => FALSE,
        'interval'    => FALSE,
        'passive'     => FALSE,
        'no_wait'     => FALSE,
        'arguments'   => [],
        'bindings'    => [],
    ];

    /**
     * @var array
     */
    private $publishersConfig = [
        'class'       => Publisher::class,
        'connection'  => 'default',
        'exchange'    => '',
        'routing_key' => '',
        'mandatory'   => FALSE,
        'immediate'   => FALSE,
    ];

    /**
     * @var array
     */
    private $consumersConfig = [
        'class'          => Consumer::class,
        'connection'     => 'default',
        'queue'          => '',
        'callback'       => '',
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

    /**
     * @var array
     */
    private $bindingsConfig = [
        'exchange'    => '',
        'routing_key' => '',
        'no_wait'     => FALSE,
        'arguments'   => [],
    ];

    /**
     *
     */
    public function loadConfiguration(): void
    {
        $this->queueConfig; // CodeSniffer & PHPStan
        $this->exchangeConfig; // CodeSniffer & PHPStan
        $this->bindingsConfig; // CodeSniffer & PHPStan

        $builder = $this->getContainerBuilder();
        $config  = $this->validateConfig($this->defaultConfig);

        $builder
            ->addDefinition($this->prefix('client_factory'))
            ->setFactory($config['client_factory'])
            ->setArguments([$config['connections']]);

        $manager = $builder
            ->addDefinition($this->prefix('connection_manager'))
            ->setFactory($config['connection_manager']);

        if ($config['logger']) {
            $manager->addSetup('setLogger', [$config['logger']]);
        }

        $configurator = $builder
            ->addDefinition($this->prefix('configurator'))
            ->setFactory($config['configurator'])
            ->setArguments([$config]);

        if ($config['logger']) {
            $configurator->addSetup('setLogger', [$config['logger']]);
        }

        foreach ($config['publishers'] as $key => $publisher) {
            $key         = str_replace('-', '_', $key);
            $innerConfig = $this->validateConfig($this->publishersConfig, $publisher);

            $publisher = $builder
                ->addDefinition($this->prefix(sprintf('publisher.%s', $key)))
                ->setFactory($config['publisher'])
                ->setArguments([
                    $manager,
                    $configurator,
                    $innerConfig['routing_key'],
                    $innerConfig['exchange'],
                    $innerConfig['mandatory'],
                    $innerConfig['immediate'],
                ]);

            if ($config['logger']) {
                $publisher->addSetup('setLogger', [$config['logger']]);
            }

            $builder
                ->addDefinition($this->prefix(sprintf('publisher.command.%s', $key)))
                ->setFactory(PublisherCommand::class)
                ->setArguments([$publisher, sprintf('%s:publisher:%s', $this->name, $key)])
                ->addTag('kdyby.console.command');
        }

        $builder
            ->addDefinition($this->prefix('null_callback'))
            ->setFactory(NullCallback::class);

        $builder
            ->addDefinition($this->prefix('dump_callback'))
            ->setFactory(DumpCallback::class);

        foreach ($config['consumers'] as $key => $consumer) {
            $key         = str_replace('-', '_', $key);
            $innerConfig = $this->validateConfig($this->consumersConfig, $consumer);

            $consumer = $builder
                ->addDefinition($this->prefix(sprintf('consumer.%s', $key)))
                ->setFactory($config['consumer'])
                ->setArguments([
                    $manager,
                    $configurator,
                    $innerConfig['callback'],
                    $innerConfig['queue'],
                    $innerConfig['consumer_tag'],
                    $innerConfig['no_local'],
                    $innerConfig['no_ack'],
                    $innerConfig['exclusive'],
                    $innerConfig['no_wait'],
                    $innerConfig['prefetch_count'],
                    $innerConfig['prefetch_size'],
                ]);

            if ($config['logger']) {
                $consumer->addSetup('setLogger', [$config['logger']]);
            }

            $builder
                ->addDefinition($this->prefix(sprintf('consumer.command.%s', $key)))
                ->setFactory($config['consumer_command'])
                ->setArguments([$consumer, sprintf('%s:consumer:%s', $this->name, $key)])
                ->addTag('kdyby.console.command');
        }
    }

    /**
     *
     */
    public function beforeCompile(): void
    {
        $builder = $this->getContainerBuilder();

        if (class_exists(Logger::class)) {
            $builder
                ->addDefinition('monolog.logger.rabbit_mq')
                ->setFactory(Logger::class)
                ->setArguments([
                    'rabbitmq',
                    [
                        $builder
                            ->addDefinition('monolog.handler.rabbit_mq')
                            ->setFactory(StreamHandler::class)
                            ->setArguments(['php://stdout', Logger::INFO]),
                    ],
                ]);
        }
    }

}
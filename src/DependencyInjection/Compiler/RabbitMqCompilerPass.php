<?php declare(strict_types=1);

namespace RabbitMqBundle\DependencyInjection\Compiler;

use Hanaboso\CommonsBundle\Utils\DsnParser;
use RabbitMqBundle\Command\PublisherCommand;
use RabbitMqBundle\Consumer\Callback\DumpCallback;
use RabbitMqBundle\Consumer\Callback\NullCallback;
use RabbitMqBundle\RabbitMqBundle;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class RabbitMqCompilerPass
 *
 * @package RabbitMqBundle\DependencyInjection\Compiler
 */
class RabbitMqCompilerPass implements CompilerPassInterface
{

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container): void
    {
        $config      = $container->getParameter(RabbitMqBundle::KEY);
        $connections = $config['connections']['default']['dsn'] ?? 'amqp://rabbitmq';

        $clientFactory = new Definition(
            $config['client_factory'],
            [$this->setupRabbitMqSettings($connections)]
        );
        $container->setDefinition($this->createKey('client_factory'), $clientFactory);

        $connectionManager = new Definition($config['connection_manager'], [new Reference('rabbit_mq.client_factory')]);
        // Add logger
        if ($config['logger'] !== NULL) {
            $connectionManager->addMethodCall('setLogger', [new Reference($config['logger'])]);
        }
        $container->setDefinition($this->createKey('connection_manager'), $connectionManager);

        $configurator = new Definition($config['configurator'], [$config]);
        // Add logger
        if ($config['logger'] !== NULL) {
            $configurator->addMethodCall('setLogger', [new Reference($config['logger'])]);
        }
        $container->setDefinition($this->createKey('configurator'), $configurator);

        // Publishers
        foreach ($config['publishers'] as $key => $publisher) {
            $publisherName = $this->createKey(sprintf('publisher.%s', $key));
            $publisherDef  = new Definition(
                $config['publisher'],
                [
                    new Reference($this->createKey('connection_manager')),
                    new Reference($this->createKey('configurator')),
                    $publisher['routing_key'],
                    $publisher['exchange'],
                    $publisher['mandatory'],
                    $publisher['immediate'],
                ]
            );

            // Add logger
            if ($config['logger'] !== NULL) {
                $publisherDef->addMethodCall('setLogger', [new Reference($config['logger'])]);
            }

            $container->setDefinition($publisherName, $publisherDef);

            $publisherCommand = new Definition(PublisherCommand::class, [new Reference($publisherName)]);
            $publisherCommand->addTag('console.command', ['command' => sprintf('rabbit_mq:publisher:%s', $key)]);
            $container->setDefinition(sprintf('rabbit_mq.publisher.command.%s', $key), $publisherCommand);
        }

        // Callbacks
        $nullCallback = new Definition(NullCallback::class);
        $container->setDefinition($this->createKey('null_callback'), $nullCallback);
        $dumpCallback = new Definition(DumpCallback::class);
        $container->setDefinition($this->createKey('dump_callback'), $dumpCallback);

        // Consumers
        foreach ($config['consumers'] as $key => $consumer) {
            $consumerName = $this->createKey(sprintf('consumer.%s', $key));
            $consumerDef  = new Definition(
                $consumer['async'] ? $config['async_consumer'] : $config['consumer'],
                [
                    new Reference($this->createKey('connection_manager')),
                    new Reference($this->createKey('configurator')),
                    new Reference($consumer['callback']),
                    $consumer['queue'],
                    $consumer['consumer_tag'],
                    $consumer['no_local'],
                    $consumer['no_ack'],
                    $consumer['exclusive'],
                    $consumer['no_wait'],
                    $consumer['prefetch_count'],
                    $consumer['prefetch_size'],
                ]
            );

            // Add logger
            if ($config['logger'] !== NULL) {
                $consumerDef->addMethodCall('setLogger', [new Reference($config['logger'])]);
            }

            $container->setDefinition($consumerName, $consumerDef);

            $command         = $consumer['async'] ? $config['async_consumer_command'] : $config['consumer_command'];
            $consumerCommand = new Definition($command, [new Reference($consumerName)]);
            $consumerCommand->addTag('console.command', ['command' => sprintf('rabbit_mq:consumer:%s', $key)]);
            $container->setDefinition(sprintf('rabbit_mq.consumer.command.%s', $key), $consumerCommand);
        }

    }

    /**
     * @param string $name
     *
     * @return string
     */
    private function createKey(string $name): string
    {
        return sprintf('%s.%s', RabbitMqBundle::KEY, $name);
    }

    /**
     * @param string $amqpUri
     *
     * @return mixed[]
     */
    private function setupRabbitMqSettings(string $amqpUri): array
    {
        $settings = DsnParser::rabbitParser($amqpUri);

        $settings['user']              = isset($settings['user']) && !empty($settings['user']) ? $settings['user'] : 'guest';
        $settings['password']          = isset($settings['password']) && !empty($settings['password']) ? $settings['password'] : 'guest';
        $settings['port']              = isset($settings['port']) && !empty($settings['port']) ? $settings['port'] : 5672;
        $settings['vhost']             = isset($settings['vhost']) && !empty($settings['vhost']) ? $settings['vhost'] : '/';
        $settings['heartbeat']         = isset($settings['heartbeat']) && !empty($settings['heartbeat']) ? $settings['heartbeat'] : 30;
        $settings['timeout']           = isset($settings['timeout']) && !empty($settings['timeout']) ? $settings['timeout'] : 60;
        $settings['reconnect']         = isset($settings['reconnect']) && !empty($settings['reconnect']) ? $settings['reconnect'] : TRUE;
        $settings['reconnect_tries']   = isset($settings['reconnect_tries']) && !empty($settings['reconnect_tries']) ? $settings['reconnect_tries'] : 3600;
        $settings['reconnect_timeout'] = isset($settings['reconnect_timeout']) && !empty($settings['reconnect_timeout']) ? $settings['reconnect_timeout'] : 1;

        return ['default' => $settings];
    }

}

<?php declare(strict_types=1);

namespace RabbitMqBundle\DependencyInjection\Compiler;

use RabbitMqBundle\Command\PublisherCommand;
use RabbitMqBundle\Consumer\Callback\DumpAsyncCallback;
use RabbitMqBundle\Consumer\Callback\DumpCallback;
use RabbitMqBundle\Consumer\Callback\NullAsyncCallback;
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
 *
 * @codeCoverageIgnore
 */
final class RabbitMqCompilerPass implements CompilerPassInterface
{

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container): void
    {
        $config        = $container->getParameter(RabbitMqBundle::KEY);
        $clientFactory = new Definition($config['client_factory'], [$config['connections']]);
        $container->setDefinition($this->createKey('client_factory'), $clientFactory);

        /** @var string|NULL $logger */
        $logger = $config['logger'];

        $connectionManager = new Definition($config['connection_manager'], [new Reference('rabbit_mq.client_factory')]);
        // Add logger
        if ($logger !== NULL) {
            $connectionManager->addMethodCall('setLogger', [new Reference($logger)]);
        }
        $container->setDefinition($this->createKey('connection_manager'), $connectionManager);

        $configurator = new Definition($config['configurator'], [$config]);
        // Add logger
        if ($logger !== NULL) {
            $configurator->addMethodCall('setLogger', [new Reference($logger)]);
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
                    $publisher['persistent'],
                    $publisher['acknowledge'],
                ],
            );

            // Add logger
            if ($logger !== NULL) {
                $publisherDef->addMethodCall('setLogger', [new Reference($logger)]);
            }

            $container->setDefinition($publisherName, $publisherDef);

            $commandName      = sprintf('rabbit_mq:publisher:%s', $key);
            $publisherCommand = new Definition(PublisherCommand::class, [new Reference($publisherName), $commandName]);
            $publisherCommand->addTag('console.command', ['command' => $commandName]);
            $container->setDefinition(sprintf('rabbit_mq.publisher.command.%s', $key), $publisherCommand);
        }

        // Callbacks
        $nullCallback = new Definition(NullCallback::class);
        $container->setDefinition($this->createKey('null_callback'), $nullCallback);
        $nullAsyncCallback = new Definition(NullAsyncCallback::class);
        $container->setDefinition($this->createKey('null_async_callback'), $nullAsyncCallback);
        $dumpCallback = new Definition(DumpCallback::class);
        $container->setDefinition($this->createKey('dump_callback'), $dumpCallback);
        $dumpAsyncCallback = new Definition(DumpAsyncCallback::class);
        $container->setDefinition($this->createKey('dump_async_callback'), $dumpAsyncCallback);

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
                ],
            );

            // Add logger
            if ($logger !== NULL) {
                $consumerDef->addMethodCall('setLogger', [new Reference($logger)]);
            }

            $container->setDefinition($consumerName, $consumerDef);

            $command         = $consumer['async'] ? $config['async_consumer_command'] : $config['consumer_command'];
            $commandName     = sprintf('rabbit_mq:consumer:%s', $key);
            $consumerCommand = new Definition($command, [new Reference($consumerName), $commandName]);
            $consumerCommand->addTag('console.command', ['command' => $commandName]);
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

}

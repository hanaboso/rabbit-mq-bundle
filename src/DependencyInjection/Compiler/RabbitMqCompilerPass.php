<?php declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: venca
 * Date: 12/18/17
 * Time: 3:00 PM
 */

namespace RabbitMqBundle\DependencyInjection\Compiler;

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
        $config = $container->getParameter(RabbitMqBundle::KEY);

        $clientFactory = new Definition($config['client_factory'], [$config['connections']]);
        $container->setDefinition($this->createKey('client_factory'), $clientFactory);

        $connectionManager = new Definition($config['connection_manager'], [new Reference('rabbit_mq.client_factory')]);
        $container->setDefinition($this->createKey('connection_manager'), $connectionManager);

        // Publishers
        foreach ($config['publishers'] as $key => $publisher) {
            $publisherName = $this->createKey('publisher.' . $key);
            $publisherDef  = new Definition($config['publisher'], [
                new Reference($this->createKey('connection_manager')),
                $publisher['routing_key'],
                $publisher['exchange'],
                $publisher['mandatory'],
                $publisher['immediate'],
            ]);
            $container->setDefinition($publisherName, $publisherDef);

            $publisherCommand = new Definition(PublisherCommand::class, [new Reference($publisherName)]);
            $publisherCommand->addTag('console.command', ['command' => 'rabbit_mq:publisher:' . $key]);
            $container->setDefinition('rabbit_mq.publisher.command.' . $key, $publisherCommand);
        }

        // Callbacks
        $nullCallback = new Definition(NullCallback::class);
        $container->setDefinition($this->createKey('null_callback'), $nullCallback);
        $dumpCallback = new Definition(DumpCallback::class);
        $container->setDefinition($this->createKey('dump_callback'), $dumpCallback);

        // Consumers
        foreach ($config['consumers'] as $key => $consumer) {
            $consumerName = $this->createKey('consumer.' . $key);
            $consumerDef  = new Definition($config['consumer'], [
                new Reference($this->createKey('connection_manager')),
                new Reference($consumer['callback']),
                $consumer['queue'],
                $consumer['consumer_tag'],
                $consumer['no_local'],
                $consumer['no_ack'],
                $consumer['exclusive'],
                $consumer['no_wait'],
                $consumer['prefetch_count'],
                $consumer['prefetch_size'],
            ]);
            $container->setDefinition($consumerName, $consumerDef);

            $consumerCommand = new Definition($config['consumer_command'], [new Reference($consumerName)]);
            $consumerCommand->addTag('console.command', ['command' => 'rabbit_mq:consumer:' . $key]);
            $container->setDefinition('rabbit_mq.consumer.command.' . $key, $consumerCommand);
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
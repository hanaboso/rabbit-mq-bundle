<?php declare(strict_types=1);

namespace RabbitMqBundle\DependencyInjection;

use RabbitMqBundle\Command\AsyncConsumerCommand;
use RabbitMqBundle\Command\ConsumerCommand;
use RabbitMqBundle\Connection\ClientFactory;
use RabbitMqBundle\Connection\Configurator;
use RabbitMqBundle\Connection\ConnectionManager;
use RabbitMqBundle\Consumer\AsyncConsumer;
use RabbitMqBundle\Consumer\Consumer;
use RabbitMqBundle\Publisher\Publisher;
use RabbitMqBundle\RabbitMqBundle;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 *
 * @package RabbitMqBundle\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{

    /**
     * @param string $name
     *
     * @return ArrayNodeDefinition|NodeDefinition
     */
    private function createNode(string $name)
    {
        return (new TreeBuilder($name))->getRootNode();
    }

    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(RabbitMqBundle::KEY);

        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        // Default classes
        $rootNode->children()->scalarNode('client_factory')->defaultValue(ClientFactory::class);
        $rootNode->children()->scalarNode('connection_manager')->defaultValue(ConnectionManager::class);
        $rootNode->children()->scalarNode('consumer')->defaultValue(Consumer::class);
        $rootNode->children()->scalarNode('async_consumer')->defaultValue(AsyncConsumer::class);
        $rootNode->children()->scalarNode('consumer_command')->defaultValue(ConsumerCommand::class);
        $rootNode->children()->scalarNode('async_consumer_command')->defaultValue(AsyncConsumerCommand::class);
        $rootNode->children()->scalarNode('publisher')->defaultValue(Publisher::class);
        $rootNode->children()->scalarNode('logger')->defaultNull();
        $rootNode->children()->scalarNode('configurator')->defaultValue(Configurator::class);
        $rootNode->children()->booleanNode('configure_monolog')->defaultValue(FALSE);

        $rootNode->append($this->getConnections());
        $rootNode->append($this->getQueues());
        $rootNode->append($this->getExchanges());
        $rootNode->append($this->getProducers());
        $rootNode->append($this->getConsumers());

        return $treeBuilder;
    }

    /**
     * @return NodeDefinition
     */
    protected function getConnections(): NodeDefinition
    {
        /** @var ArrayNodeDefinition $node */
        $node        = $this->createNode('connections');
        $connections = $node
            ->useAttributeAsKey('key')
            ->normalizeKeys(FALSE)
            ->defaultValue([])
            ->arrayPrototype();

        $connections->children()->scalarNode('dsn')->isRequired();

        return $node;
    }

    /**
     * @return NodeDefinition
     */
    protected function getQueues(): NodeDefinition
    {
        /** @var ArrayNodeDefinition $node */
        $node   = $this->createNode('queues');
        $queues = $node
            ->useAttributeAsKey('key')
            ->normalizeKeys(FALSE)
            ->defaultValue([])
            ->arrayPrototype();

        $queues->children()->booleanNode('durable')->defaultFalse();
        $queues->children()->booleanNode('exclusive')->defaultFalse();
        $queues->children()->booleanNode('auto_delete')->defaultFalse();
        $queues->children()->booleanNode('passive')->defaultFalse();
        $queues->children()->booleanNode('no_wait')->defaultFalse();

        $queues->append($this->getArguments());
        $queues->append($this->getBindings());

        return $node;
    }

    /**
     * @return NodeDefinition
     */
    protected function getBindings(): NodeDefinition
    {
        /** @var ArrayNodeDefinition $node */
        $node     = $this->createNode('bindings');
        $bindings = $node
            ->normalizeKeys(FALSE)
            ->defaultValue([])
            ->arrayPrototype();

        $bindings->children()->scalarNode('exchange')->isRequired();
        $bindings->children()->scalarNode('routing_key')->defaultValue('');
        $bindings->children()->scalarNode('no_wait')->defaultFalse();
        $bindings->append($this->getArguments());

        return $node;
    }

    /**
     * @return NodeDefinition
     */
    protected function getArguments(): NodeDefinition
    {
        /** @var ArrayNodeDefinition $node */
        $node = $this->createNode('arguments');
        $node
            ->normalizeKeys(FALSE)
            ->scalarPrototype()
            ->defaultValue([]);

        return $node;
    }

    /**
     * @return NodeDefinition
     */
    protected function getExchanges(): NodeDefinition
    {
        /** @var ArrayNodeDefinition $node */
        $node      = $this->createNode('exchanges');
        $exchanges = $node
            ->useAttributeAsKey('key')
            ->normalizeKeys(FALSE)
            ->defaultValue([])
            ->arrayPrototype();

        $exchanges->children()->enumNode('type')->values(['direct', 'topic', 'fanout']);
        $exchanges->children()->booleanNode('durable')->defaultFalse();
        $exchanges->children()->booleanNode('auto_delete')->defaultFalse();
        $exchanges->children()->booleanNode('internal')->defaultFalse();
        $exchanges->children()->booleanNode('passive')->defaultFalse();
        $exchanges->children()->booleanNode('no_wait')->defaultFalse();

        $exchanges->append($this->getArguments());
        $exchanges->append($this->getBindings());

        return $node;
    }

    /**
     * @return NodeDefinition
     */
    protected function getProducers(): NodeDefinition
    {
        /** @var ArrayNodeDefinition $node */
        $node       = $this->createNode('publishers');
        $publishers = $node->useAttributeAsKey('key')
            ->normalizeKeys(FALSE)
            ->defaultValue([])
            ->arrayPrototype();

        $publishers->children()->scalarNode('class')->defaultValue(Publisher::class);
        $publishers->children()->scalarNode('connection')->defaultValue('default');
        $publishers->children()->scalarNode('exchange')->defaultValue('');
        $publishers->children()->scalarNode('routing_key')->defaultValue('');
        $publishers->children()->booleanNode('mandatory')->defaultFalse();
        $publishers->children()->booleanNode('immediate')->defaultFalse();

        return $node;
    }

    /**
     * @return NodeDefinition
     */
    protected function getConsumers(): NodeDefinition
    {
        /** @var ArrayNodeDefinition $node */
        $node      = $this->createNode('consumers');
        $consumers = $node->useAttributeAsKey('key')
            ->normalizeKeys(FALSE)
            ->defaultValue([])
            ->arrayPrototype();

        $consumers->children()->scalarNode('class')->defaultValue(Consumer::class);
        $consumers->children()->scalarNode('connection')->defaultValue('default');
        $consumers->children()->scalarNode('queue')->isRequired();
        $consumers->children()->scalarNode('callback')->isRequired();
        $consumers->children()->scalarNode('consumer_tag')->defaultValue('');
        $consumers->children()->booleanNode('async')->defaultFalse();
        $consumers->children()->booleanNode('no_local')->defaultFalse();
        $consumers->children()->booleanNode('no_ack')->defaultFalse();
        $consumers->children()->booleanNode('exclusive')->defaultFalse();
        $consumers->children()->booleanNode('no_wait')->defaultFalse();
        $consumers->children()->scalarNode('prefetch_count')->defaultValue(0);
        $consumers->children()->scalarNode('prefetch_size')->defaultValue(0);
        $consumers->children()->scalarNode('tick_method')->defaultNull();
        $consumers->children()->scalarNode('tick_seconds')->defaultNull();
        $consumers->children()->scalarNode('tick_seconds')->defaultNull();
        $consumers->children()->scalarNode('max_messages')->defaultNull();
        $consumers->children()->scalarNode('max_seconds')->defaultNull();
        $consumers->append($this->getArguments());

        return $node;
    }

}

<?php declare(strict_types=1);

namespace RabbitMqBundle\Connection;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Wire\AMQPTable;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class Configurator
 *
 * @package RabbitMqBundle\Connection
 */
final class Configurator implements LoggerAwareInterface
{

    /**
     * @var mixed[]
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var bool
     */
    private $configured = FALSE;

    /**
     * Configurator constructor.
     *
     * @param mixed[] $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->logger = new NullLogger();
    }

    /**
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger
     *
     * @return void
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @param bool $configured
     */
    public function setConfigured(bool $configured): void
    {
        $this->configured = $configured;
    }

    /**
     * @param AMQPChannel $channel
     */
    public function setup(AMQPChannel $channel): void
    {
        if ($this->configured === TRUE) {
            $this->logger->info('The Rabbit MQ has been already configured.');

            return;
        }

        foreach ($this->config['exchanges'] as $name => $exchange) {
            $this->logger->info(sprintf('RabbitMQ setup: declare exchange "%s".', $name));
            /** @var mixed[] $arguments */
            $arguments = new AMQPTable($exchange['arguments'] ?? []);
            $channel->exchange_declare(
                $name,
                $exchange['type'] ?? 'direct',
                $exchange['passive'] ?? FALSE,
                $exchange['durable'] ?? FALSE,
                $exchange['auto_delete'] ?? FALSE,
                $exchange['internal'] ?? FALSE,
                $exchange['nowait'] ?? FALSE,
                $arguments
            );
        }

        foreach ($this->config['exchanges'] as $name => $exchange) {
            foreach ($exchange['bindings'] as $bind) {
                $this->logger->info(
                    sprintf(
                        'RabbitMQ setup: binding exchange "%s" to exchange "%s".',
                        $name,
                        $bind['exchange']
                    )
                );
                $channel->exchange_bind(
                    $name,
                    $bind['exchange'],
                    $bind['routing_key'],
                    $bind['no_wait'] ?? FALSE
                );
            }
        }

        foreach ($this->config['queues'] as $name => $queue) {
            $this->logger->info(sprintf('RabbitMQ setup: declare queue "%s".', $name));
            /** @var mixed[] $arguments */
            $arguments = new AMQPTable($queue['arguments'] ?? []);
            $channel->queue_declare(
                $name,
                $queue['passive'] ?? FALSE,
                $queue['durable'] ?? FALSE,
                $queue['exclusive'] ?? FALSE,
                $queue['auto_delete'] ?? FALSE,
                $queue['no_wait'] ?? FALSE,
                $arguments
            );

            foreach ($queue['bindings'] ?? [] as $bind) {
                $this->logger->info(
                    sprintf(
                        'RabbitMQ setup: binding queue "%s" to exchange "%s".',
                        $name,
                        $bind['exchange']
                    )
                );
                /** @var mixed[] $arguments */
                $arguments = new AMQPTable($bind['arguments'] ?? []);
                $channel->queue_bind(
                    $name,
                    $bind['exchange'],
                    $bind['routing_key'],
                    $bind['no_wait'] ?? FALSE,
                    $arguments
                );
            }
        }

        $this->configured = TRUE;
    }

}

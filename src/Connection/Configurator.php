<?php declare(strict_types=1);

namespace RabbitMqBundle\Connection;

use Bunny\Channel;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class Configurator
 *
 * @package RabbitMqBundle\Connection
 */
class Configurator implements LoggerAwareInterface
{

    /**
     * @var array
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
     * @param array $config
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
     * @param Channel $channel
     */
    public function setup(Channel $channel): void
    {
        if ($this->configured === TRUE) {
            $this->logger->info('The Rabbit MQ has been already configured.');

            return;
        }

        foreach ($this->config['exchanges'] as $name => $exchange) {
            $this->logger->info(sprintf('RabbitMQ setup: declare exchange "%s".', $name));
            $channel->exchangeDeclare(
                $name,
                $exchange['type'] ?? 'direct',
                $exchange['passive'] ?? FALSE,
                $exchange['durable'] ?? FALSE,
                $exchange['auto_delete'] ?? FALSE,
                $exchange['internal'] ?? FALSE,
                $exchange['nowait'] ?? FALSE,
                $exchange['arguments'] ?? []
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
                $channel->exchangeBind(
                    $name,
                    $bind['exchange'],
                    $bind['routing_key'],
                    $bind['no_wait'] ?? FALSE
                );
            }
        }

        foreach ($this->config['queues'] as $name => $queue) {
            $this->logger->info(sprintf('RabbitMQ setup: declare queue "%s".', $name));
            $channel->queueDeclare(
                $name,
                $queue['passive'] ?? FALSE,
                $queue['durable'] ?? FALSE,
                $queue['exclusive'] ?? FALSE,
                $queue['auto_delete'] ?? FALSE,
                $queue['no_wait'] ?? FALSE,
                $queue['arguments'] ?? []
            );

            foreach ($queue['bindings'] ?? [] as $bind) {
                $this->logger->info(
                    sprintf(
                        'RabbitMQ setup: binding queue "%s" to exchange "%s".',
                        $name,
                        $bind['exchange']
                    )
                );
                $channel->queueBind(
                    $name,
                    $bind['exchange'],
                    $bind['routing_key'],
                    $bind['no_wait'] ?? FALSE,
                    $bind['arguments'] ?? []
                );
            }
        }

        $this->configured = TRUE;
    }

}

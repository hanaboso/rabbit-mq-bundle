<?php declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: venca
 * Date: 19.12.17
 * Time: 16:56
 */

namespace RabbitMqBundle\Consumer;

use Bunny\Channel;
use Bunny\Message;
use Exception;
use Psr\Log\LoggerInterface;
use RabbitMqBundle\Connection\Configurator;
use RabbitMqBundle\Connection\ConnectionManager;
use RabbitMqBundle\Consumer\Callback\Exception\CallbackException;
use Throwable;

/**
 * Class Consumer
 *
 * @package RabbitMqBundle\Consumer
 */
class Consumer extends ConsumerAbstract
{

    /**
     * Consumer constructor.
     *
     * @param ConnectionManager $connectionManager
     * @param Configurator      $configurator
     * @param CallbackInterface $callback
     * @param string            $queue
     * @param string            $consumerTag
     * @param bool              $noLocal
     * @param bool              $noAck
     * @param bool              $exclusive
     * @param bool              $nowait
     * @param int               $prefetchCount
     * @param int               $prefetchSize
     */
    public function __construct(
        ConnectionManager $connectionManager,
        Configurator $configurator,
        CallbackInterface $callback,
        string $queue = '',
        string $consumerTag = '',
        bool $noLocal = FALSE,
        bool $noAck = FALSE,
        bool $exclusive = FALSE,
        bool $nowait = FALSE,
        int $prefetchCount = 0,
        int $prefetchSize = 0
    )
    {
        parent::__construct(
            $connectionManager,
            $configurator,
            $queue,
            $consumerTag,
            $noLocal,
            $noAck,
            $exclusive,
            $nowait,
            $prefetchCount,
            $prefetchSize
        );
        $this->callback = $callback;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @throws Exception
     */
    public function consume(): void
    {
        try {
            $this->getChannel()->consume(
                function (Message $message): void {
                    try {
                        $this->callback->processMessage(
                            $message,
                            $this->connectionManager->getConnection(),
                            $this->channelId
                        );
                    } catch (Throwable $e) {
                        throw new CallbackException(
                            sprintf('RabbitMq callback error: %s', $e->getMessage()),
                            $e->getCode(),
                            $e
                        );
                    }
                },
                $this->queue,
                $this->consumerTag,
                $this->noLocal,
                $this->noAck,
                $this->exclusive,
                $this->nowait,
                $this->arguments
            );
            $this->connectionManager->getConnection()->getClient()->run();
        } catch (Throwable $e) {
            $this->logger->error(sprintf('Consume error: %s', $e->getMessage()), ['exception' => $e]);
            $this->connectionManager->getConnection()->reconnect();
            $this->configurator->setConfigured(FALSE);
            $this->setup();
            $this->consume();
        }
    }

    /**
     * @return Channel
     * @throws Exception
     */
    protected function getChannel(): Channel
    {
        if ($this->channelId === NULL) {
            $this->channelId = $this->connectionManager->getConnection()->createChannel();
        }

        return $this->connectionManager->getConnection()->getChannel($this->channelId);
    }

    /**
     *
     */
    public function setup(): void
    {
        $this->logger->info('Rabbit MQ setup - consumer.');

        try {
            $this->configurator->setup($this->getChannel());
            $this->getChannel()->qos($this->prefetchSize, $this->prefetchCount);
        } catch (Throwable $e) {
            $this->logger->error(sprintf('Consumer setup error: %s', $e->getMessage()), ['exception' => $e]);
            $this->connectionManager->getConnection()->reconnect();
            $this->setup();
        }
    }

}

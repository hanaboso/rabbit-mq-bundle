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
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RabbitMqBundle\Connection\Configurator;
use RabbitMqBundle\Connection\ConnectionManager;
use RabbitMqBundle\Connection\SetupInterface;
use RabbitMqBundle\Consumer\Callback\Exception\CallbackException;
use Throwable;

/**
 * Class Consumer
 *
 * @package RabbitMqBundle\Consumer
 */
class Consumer implements ConsumerInterface, SetupInterface, LoggerAwareInterface
{

    /**
     * @var ConnectionManager
     */
    private $connectionManager;

    /**
     * @var Configurator
     */
    private $configurator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var int
     */
    private $channelId;

    /**
     * @var CallbackInterface
     */
    private $callback;

    /**
     * @var string
     */
    private $queue;

    /**
     * @var string
     */
    private $consumerTag = '';

    /**
     * @var bool
     */
    private $noLocal = FALSE;

    /**
     * @var bool
     */
    private $noAck = FALSE;

    /**
     * @var bool
     */
    private $exclusive = FALSE;

    /**
     * @var bool
     */
    private $nowait = FALSE;

    /**
     * @var array
     */
    private $arguments = [];

    /**
     * @var int
     */
    private $prefetchCount = 0;

    /**
     * @var int
     */
    private $prefetchSize = 0;

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
        $this->connectionManager = $connectionManager;
        $this->configurator      = $configurator;
        $this->callback          = $callback;
        $this->queue             = $queue;
        $this->consumerTag       = $consumerTag;
        $this->noLocal           = $noLocal;
        $this->noAck             = $noAck;
        $this->exclusive         = $exclusive;
        $this->nowait            = $nowait;
        $this->prefetchCount     = $prefetchCount;
        $this->prefetchSize      = $prefetchSize;
        $this->logger            = new NullLogger();
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @throws \Exception
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
        } catch (CallbackException $e) {
            throw $e;
        } catch (Throwable $e) {
            $this->logger->error('Consume error: ' . $e->getMessage(), ['exception' => $e]);
            $this->connectionManager->getConnection()->reconnect();
            $this->configurator->setConfigured(FALSE);
            $this->setup();
            $this->consume();
        }
    }

    /**
     * @return Channel
     * @throws \Exception
     */
    private function getChannel(): Channel
    {
        if ($this->channelId === NULL) {
            $this->channelId = $this->connectionManager->getConnection()->createChannel();
        }

        return $channel = $this->connectionManager->getConnection()->getChannel($this->channelId);
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
            $this->logger->error('Consumer setup error: ' . $e->getMessage(), ['exception' => $e]);
            $this->connectionManager->getConnection()->reconnect();
            $this->setup();
        }
    }

}
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
use RabbitMqBundle\Connection\ConnectionManager;
use RabbitMqBundle\Connection\SetupInterface;
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
                    $this->callback->processMessage(
                        $message,
                        $this->connectionManager->getConnection(),
                        $this->channelId
                    );
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
            //@todo add logger
            $this->connectionManager->getConnection()->reconnect();
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
        // Queue declare
        // Exchange declare
        // Binding
        $this->logger->info('Rabbit MQ setup.');

        try {
            $this->getChannel()->queueDeclare($this->queue);
            $this->getChannel()->qos($this->prefetchSize, $this->prefetchCount);
        } catch (Throwable $e) {
            //@todo add logger
            $this->connectionManager->getConnection()->reconnect();
            $this->setup();
        }
    }

}
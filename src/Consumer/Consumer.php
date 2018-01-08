<?php declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: venca
 * Date: 19.12.17
 * Time: 16:56
 */

namespace RabbitMqBundle\Consumer;

use Bunny\Channel;
use Bunny\Client;
use Bunny\Message;
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
class Consumer implements ConsumerInterface, SetupInterface
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
     * @var Client
     */
    private $client;

    /**
     * @var Channel
     */
    private $channel;

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
     *
     */
    public function consume(): void
    {
        $this->channel->consume(
            function (Message $message, Channel $channel, Client $client): void {
                $this->callback->processMessage($message, $channel, $client);
            },
            $this->queue,
            $this->consumerTag,
            $this->noLocal,
            $this->noAck,
            $this->exclusive,
            $this->nowait,
            $this->arguments
        );
        $this->client->run();
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
        $this->client = $this->connectionManager->getClient();

        try {
            /**
             * @var Channel $channel
             */
            $channel = $this->client->connect()->channel();

            $this->channel = $channel;

            $this->channel->queueDeclare($this->queue);
            $this->channel->qos($this->prefetchSize, $this->prefetchCount);
        } catch (Throwable $e) {
            // reconnect
        }
    }

}
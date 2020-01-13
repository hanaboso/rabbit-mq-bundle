<?php declare(strict_types=1);

namespace RabbitMqBundle\Consumer;

use Exception;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RabbitMqBundle\Connection\Configurator;
use RabbitMqBundle\Connection\ConnectionManager;
use RabbitMqBundle\Connection\SetupInterface;
use RabbitMqBundle\Consumer\Callback\Exception\CallbackException;
use Throwable;

/**
 * Class ConsumerAbstract
 *
 * @package RabbitMqBundle\Consumer
 */
abstract class ConsumerAbstract implements ConsumerInterface, SetupInterface, LoggerAwareInterface
{

    /**
     * @var ConnectionManager
     */
    protected ConnectionManager $connectionManager;

    /**
     * @var Configurator
     */
    protected Configurator $configurator;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var int|null
     */
    protected ?int $channelId = NULL;

    /**
     * @var CallbackInterface
     */
    protected $callback;

    /**
     * @var string
     */
    protected string $queue;

    /**
     * @var string
     */
    protected string $consumerTag = '';

    /**
     * @var bool
     */
    protected bool $noLocal = FALSE;

    /**
     * @var bool
     */
    protected bool $noAck = FALSE;

    /**
     * @var bool
     */
    protected bool $exclusive = FALSE;

    /**
     * @var bool
     */
    protected bool $nowait = FALSE;

    /**
     * @var mixed[]
     */
    protected array $arguments = [];

    /**
     * @var int
     */
    protected int $prefetchCount = 0;

    /**
     * @var int
     */
    protected int $prefetchSize = 0;

    /**
     * ConsumerAbstract constructor.
     *
     * @param ConnectionManager $connectionManager
     * @param Configurator      $configurator
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
     * @throws Exception
     */
    public function consume(): void
    {
        try {
            /** @var mixed[] $arguments */
            $arguments = new AMQPTable($this->arguments);
            $channel   = $this->getChannel();
            $channel->basic_consume(
                $this->queue,
                $this->consumerTag,
                $this->noLocal,
                $this->noAck,
                $this->exclusive,
                $this->nowait,
                function (AMQPMessage $message): void {
                    try {
                        $this->callback->processMessage(
                            $message,
                            $this->connectionManager->getConnection(),
                            (int) $this->channelId
                        );
                    } catch (Throwable $e) {
                        throw new CallbackException(
                            sprintf('RabbitMq callback error: %s', $e->getMessage()),
                            $e->getCode(),
                            $e
                        );
                    }
                },
                NULL,
                $arguments
            );

            while ($channel->is_consuming()) {
                $channel->wait();
            }
        } catch (Throwable $e) {
            $this->logger->error(sprintf('Consume error: %s', $e->getMessage()), ['exception' => $e]);
            $this->connectionManager->getConnection()->reconnect();
            $this->configurator->setConfigured(FALSE);
            $this->setup();
            $this->consume();
        }
    }

    /**
     * @return AMQPChannel
     * @throws Exception
     */
    protected function getChannel(): AMQPChannel
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
            $this->getChannel()->basic_qos($this->prefetchSize, $this->prefetchCount, FALSE);
        } catch (Throwable $e) {
            $this->logger->error(sprintf('Consumer setup error: %s', $e->getMessage()), ['exception' => $e]);
            $this->connectionManager->getConnection()->reconnect();
            $this->setup();
        }
    }

}

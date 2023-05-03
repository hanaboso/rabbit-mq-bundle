<?php declare(strict_types=1);

namespace RabbitMqBundle\Consumer;

use Exception;
use Hanaboso\Utils\System\PipesHeaders;
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
use RabbitMqBundle\Utils\Message;
use Throwable;

/**
 * Class ConsumerAbstract
 *
 * @package RabbitMqBundle\Consumer
 */
abstract class ConsumerAbstract implements ConsumerInterface, SetupInterface, LoggerAwareInterface
{

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var int|NULL
     */
    protected ?int $channelId = NULL;

    /**
     * @var CallbackInterface|AsyncCallbackInterface
     */
    protected CallbackInterface|AsyncCallbackInterface $callback;

    /**
     * @var mixed[]
     */
    protected array $arguments = [];

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
        protected ConnectionManager $connectionManager,
        protected Configurator $configurator,
        protected string $queue = '',
        protected string $consumerTag = '',
        protected bool $noLocal = FALSE,
        protected bool $noAck = FALSE,
        protected bool $exclusive = FALSE,
        protected bool $nowait = FALSE,
        protected int $prefetchCount = 0,
        protected int $prefetchSize = 0,
    )
    {
        $this->logger = new NullLogger();
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
                            (int) $this->channelId,
                        );
                    } catch (Throwable $e) {
                        $m = sprintf('RabbitMq callback error: %s', $e->getMessage());
                        $this->logger->error(
                            $m,
                            array_merge(
                                ['message' => $message],
                                PipesHeaders::debugInfo(Message::getHeaders($message)),
                            ),
                        );
                        Message::nack(
                            $message,
                            $this->connectionManager->getConnection(),
                            (int) $this->channelId,
                            TRUE,
                        );

                        throw new CallbackException($m, $e->getCode(), $e);
                    }
                },
                NULL,
                $arguments,
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

}

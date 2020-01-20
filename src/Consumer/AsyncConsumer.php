<?php declare(strict_types=1);

namespace RabbitMqBundle\Consumer;

use Exception;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use RabbitMqBundle\Connection\Configurator;
use RabbitMqBundle\Connection\ConnectionManager;
use RabbitMqBundle\Consumer\Callback\Exception\CallbackException;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use Throwable;

/**
 * Class AsyncConsumer
 *
 * @package RabbitMqBundle\Consumer
 */
class AsyncConsumer extends ConsumerAbstract
{

    use DebugMessageTrait;

    /**
     * @var AsyncCallbackInterface
     */
    protected $callback;

    /**
     * @var LoopInterface
     */
    private LoopInterface $loop;

    /**
     * @var int
     */
    private int $timer = 2;

    /**
     * AsyncConsumer constructor.
     *
     * @param ConnectionManager      $connectionManager
     * @param Configurator           $configurator
     * @param AsyncCallbackInterface $callback
     * @param string                 $queue
     * @param string                 $consumerTag
     * @param bool                   $noLocal
     * @param bool                   $noAck
     * @param bool                   $exclusive
     * @param bool                   $nowait
     * @param int                    $prefetchCount
     * @param int                    $prefetchSize
     */
    public function __construct(
        ConnectionManager $connectionManager,
        Configurator $configurator,
        AsyncCallbackInterface $callback,
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
        $this->loop     = Factory::create();
    }

    /**
     * @throws Exception
     */
    public function consume(): void
    {
        $this->runAsyncConsumer();

        try {
            $this->loop->run();
        } catch (Exception $e) {
            $this->logger->error(sprintf('Loop crashed: %s', $e->getMessage()), ['exception' => $e]);

            $this->restart();
        }
    }

    /**
     * @throws Exception
     */
    public function restart(): void
    {
        $this->loop->stop();
        $this->wait();
        $this->loop = Factory::create();
        $this->consume();
    }

    /**
     * @throws Exception
     */
    private function runAsyncConsumer(): void
    {
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
                        (int) $this->channelId,
                        $this->loop
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
    }

    /**
     *
     */
    private function wait(): void
    {
        sleep($this->timer);

        if ($this->timer < 10) {
            $this->timer += 2;

            if ($this->timer > 10) {
                $this->timer = 10;
            }
        }
    }

}

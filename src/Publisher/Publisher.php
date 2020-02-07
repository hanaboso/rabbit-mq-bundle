<?php declare(strict_types=1);

namespace RabbitMqBundle\Publisher;

use Exception;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RabbitMqBundle\Connection\Configurator;
use RabbitMqBundle\Connection\ConnectionManager;
use RabbitMqBundle\Connection\SetupInterface;
use RabbitMqBundle\Utils\Message;
use Throwable;

/**
 * Class Publisher
 *
 * @package RabbitMqBundle\Publisher
 */
class Publisher implements PublisherInterface, SetupInterface, LoggerAwareInterface
{

    /**
     * @var ConnectionManager
     */
    private ConnectionManager $connectionManager;

    /**
     * @var Configurator
     */
    private Configurator $configurator;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var int|NULL
     */
    private ?int $channelId = NULL;

    /**
     * @var bool
     */
    private bool $mandatory;

    /**
     * @var bool
     */
    private bool $immediate;

    /**
     * @var string
     */
    private string $routingKey;

    /**
     * @var string
     */
    private string $exchange;

    /**
     * @var bool
     */
    private bool $persistent;

    /**
     * @var bool
     */
    private bool $acknowledge;

    /**
     * @var bool[]
     */
    private array $isAcknowledged;

    /**
     * Publisher constructor.
     *
     * @param ConnectionManager $connectionManager
     * @param Configurator      $configurator
     * @param string            $routingKey
     * @param string            $exchange
     * @param bool              $mandatory
     * @param bool              $immediate
     * @param bool              $persistent
     * @param bool              $acknowledge
     */
    public function __construct(
        ConnectionManager $connectionManager,
        Configurator $configurator,
        string $routingKey = '',
        string $exchange = '',
        bool $mandatory = FALSE,
        bool $immediate = FALSE,
        bool $persistent = FALSE,
        bool $acknowledge = FALSE
    )
    {
        $this->connectionManager = $connectionManager;
        $this->configurator      = $configurator;
        $this->routingKey        = $routingKey;
        $this->exchange          = $exchange;
        $this->mandatory         = $mandatory;
        $this->immediate         = $immediate;
        $this->persistent        = $persistent;
        $this->acknowledge       = $acknowledge;
        $this->isAcknowledged    = [];
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
     * @return string
     */
    public function getRoutingKey(): string
    {
        return $this->routingKey;
    }

    /**
     * @param string $routingKey
     *
     * @return Publisher
     */
    public function setRoutingKey(string $routingKey): Publisher
    {
        $this->routingKey = $routingKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getExchange(): string
    {
        return $this->exchange;
    }

    /**
     * @param string $exchange
     *
     * @return Publisher
     */
    public function setExchange(string $exchange): Publisher
    {
        $this->exchange = $exchange;

        return $this;
    }

    /**
     * @param mixed   $content
     * @param mixed[] $headers
     */
    public function publish($content, array $headers = []): void
    {
        $this->setup();

        $content                  = $this->beforePublishContent($content);
        $headers                  = $this->beforePublishHeaders($headers);
        $headers['delivery-mode'] = $this->persistent ? AMQPMessage::DELIVERY_MODE_PERSISTENT : AMQPMessage::DELIVERY_MODE_NON_PERSISTENT;

        try {
            $key                        = bin2hex(random_bytes(25));
            $this->isAcknowledged[$key] = FALSE;

            $channel = $this->getChannel($key);
            $channel->basic_publish(
                Message::create($content, $headers),
                $this->exchange,
                $this->routingKey,
                $this->mandatory,
                $this->immediate,
                NULL
            );

            if ($this->acknowledge) {
                $channel->wait_for_pending_acks();

                if (!$this->isAcknowledged[$key]) {
                    $this->logger->error('Publish error: Message was not acknowledged!');
                    $this->publish($content, $headers);
                }
            }
        } catch (Throwable $e) {
            $this->logger->error(sprintf('Publish error: %s', $e->getMessage()), ['exception' => $e]);
            $this->connectionManager->getConnection()->reconnect();
            $this->configurator->setConfigured(FALSE);
            $this->setup();
            $this->publish($content, $headers);
        }
    }

    /**
     *
     */
    public function setup(): void
    {
        $this->logger->info('Rabbit MQ setup - publisher.');

        try {
            $this->configurator->setup($this->getChannel());
        } catch (Throwable $e) {
            // reconnect
            $this->logger->error(sprintf('Publisher setup error: %s', $e->getMessage()), ['exception' => $e]);
            $this->connectionManager->getConnection()->reconnect();
            $this->setup();
        }
    }

    /**
     * @param mixed $content
     *
     * @return string
     */
    protected function beforePublishContent($content): string
    {
        return (string) $content;
    }

    /**
     * @param mixed[] $headers
     *
     * @return mixed[]
     */
    protected function beforePublishHeaders(array $headers): array
    {
        return $headers;
    }

    /**
     * @param string $key
     *
     * @return AMQPChannel
     * @throws Exception
     */
    private function getChannel(?string $key = NULL): AMQPChannel
    {
        if ($this->channelId === NULL) {
            $this->channelId = $this->connectionManager->getConnection()->createChannel();

            if ($this->acknowledge) {
                $channel = $this->connectionManager->getConnection()->getChannel($this->channelId);
                $channel->confirm_select();
            }
        }

        $channel = $this->connectionManager->getConnection()->getChannel($this->channelId);

        if ($this->acknowledge && $key) {
            $channel->set_ack_handler(
                function () use ($key): void {
                    $this->isAcknowledged[$key] = TRUE;
                }
            );
            $channel->set_nack_handler(
                function () use ($key): void {
                    $this->isAcknowledged[$key] = FALSE;
                }
            );
        }

        return $channel;
    }

}

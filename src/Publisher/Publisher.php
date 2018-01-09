<?php declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: venca
 * Date: 2.1.18
 * Time: 15:06
 */

namespace RabbitMqBundle\Publisher;

use Bunny\Channel;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RabbitMqBundle\Connection\ConnectionManager;
use RabbitMqBundle\Connection\SetupInterface;
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
    private $connectionManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var int
     */
    private $channelId;

    /***
     * @var bool
     */
    private $setUp = FALSE;

    /**
     * @var bool
     */
    private $mandatory;

    /**
     * @var bool
     */
    private $immediate;

    /**
     * @var string
     */
    private $routingKey;

    /**
     * @var string
     */
    private $exchange;

    /**
     * Publisher constructor.
     *
     * @param ConnectionManager $connectionManager
     * @param string            $routingKey
     * @param string            $exchange
     * @param bool              $mandatory
     * @param bool              $immediate
     */
    public function __construct(
        ConnectionManager $connectionManager,
        string $routingKey = '',
        string $exchange = '',
        bool $mandatory = FALSE,
        bool $immediate = FALSE
    )
    {
        $this->connectionManager = $connectionManager;
        $this->routingKey        = $routingKey;
        $this->exchange          = $exchange;
        $this->mandatory         = $mandatory;
        $this->immediate         = $immediate;
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
     * @param mixed $content
     *
     * @return string
     */
    protected function beforePublishContent($content): string
    {
        return (string) $content;
    }

    /**
     * @param array $headers
     *
     * @return array
     */
    protected function beforePublishHeaders(array $headers): array
    {
        return $headers;
    }

    /**
     * @return Channel
     */
    private function getChannel(): Channel
    {
        if ($this->channelId === NULL) {
            $this->channelId = $this->connectionManager->getConnection()->createChannel();
        }

        return $channel = $this->connectionManager->getConnection()->getChannel($this->channelId);
    }

    /**
     * @param mixed $content
     * @param array $headers
     */
    public function publish($content, array $headers = []): void
    {
        if ($this->setUp === FALSE) {
            $this->setup();
        }

        $content = $this->beforePublishContent($content);
        $headers = $this->beforePublishHeaders($headers);

        try {
            $this->getChannel()->publish(
                $content,
                $headers,
                $this->exchange,
                $this->routingKey,
                $this->mandatory,
                $this->immediate
            );
        } catch (Throwable $e) {
            $this->logger->error('Publish error: ' . $e->getMessage(), ['exception' => $e]);
            $this->connectionManager->getConnection()->reconnect();
            $this->setup();
            $this->publish($content, $headers);
        }
    }

    /**
     *
     */
    public function setup(): void
    {
        $this->setUp = FALSE;
        // Queue declare
        // Exchange declare
        // Binding
        $this->logger->info('Rabbit MQ setup - publisher.');

        try {

            $this->getChannel()->queueDeclare($this->routingKey);

            if ($this->exchange !== '') {
                $this->getChannel()->exchangeDeclare($this->exchange);
            }
        } catch (Throwable $e) {
            // reconnect
            $this->logger->error('Publisher setup error: ' . $e->getMessage(), ['exception' => $e]);
            $this->connectionManager->getConnection()->reconnect();
            $this->setup();
        }

        $this->setUp = TRUE;
    }

}
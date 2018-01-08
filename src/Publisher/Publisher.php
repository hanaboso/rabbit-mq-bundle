<?php declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: venca
 * Date: 2.1.18
 * Time: 15:06
 */

namespace RabbitMqBundle\Publisher;

use Bunny\Channel;
use Bunny\Client;
use Psr\Log\LoggerInterface;
use RabbitMqBundle\Connection\ConnectionManager;
use RabbitMqBundle\Connection\SetupInterface;
use Throwable;

/**
 * Class Publisher
 *
 * @package RabbitMqBundle\Publisher
 */
class Publisher implements PublisherInterface, SetupInterface
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
     * @param mixed $content
     * @param array $headers
     */
    public function publish($content, array $headers = []): void
    {
        $content = $this->beforePublishContent($content);
        $headers = $this->beforePublishHeaders($headers);

        $this->channel->publish(
            $content,
            $headers,
            $this->exchange,
            $this->routingKey,
            $this->mandatory,
            $this->immediate
        );
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

            $this->channel->queueDeclare($this->routingKey);
            $this->channel->exchangeDeclare($this->exchange);
        } catch (Throwable $e) {
            // reconnect
        }
    }

}
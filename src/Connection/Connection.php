<?php declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: venca
 * Date: 1/8/18
 * Time: 1:51 PM
 */

namespace RabbitMqBundle\Connection;

use Bunny\Channel;
use Bunny\Client;
use Bunny\Exception\ClientException;
use Exception;
use InvalidArgumentException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;

/**
 * Class Connection
 *
 * @package RabbitMqBundle\Connection
 */
class Connection implements LoggerAwareInterface
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var ClientFactory
     */
    private $clientFactory;

    /**
     * @var Client|null
     */
    private $client;

    /**
     * @var array
     */
    private $channels = [];

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Connection constructor.
     *
     * @param string        $name
     * @param ClientFactory $clientFactory
     */
    public function __construct(string $name, ClientFactory $clientFactory)
    {
        $this->name          = $name;
        $this->clientFactory = $clientFactory;
        $this->logger        = new NullLogger();
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        if ($this->client === NULL) {
            $this->client = $this->clientFactory->create($this->name);
        }

        return $this->client;
    }

    /**
     * @param int $id
     *
     * @return Channel
     */
    public function getChannel(int $id): Channel
    {
        if (!$this->getClient()->isConnected()) {
            $this->connect();
        }

        if (!array_key_exists($id, $this->channels)) {
            throw new InvalidArgumentException(
                sprintf('The channel with id "%s" does not exist. You must call createChannel.', $id)
            );
        }

        return $this->channels[$id];
    }

    /**
     * @return int Channel ID
     */
    public function createChannel(): int
    {
        if (!$this->getClient()->isConnected()) {
            $this->connect();
        }

        /** @var Channel $channel */
        $channel                                  = $this->getClient()->channel();
        $this->channels[$channel->getChannelId()] = $channel;

        return $channel->getChannelId();
    }

    /**
     * Close connection and its channels
     */
    private function close(): void
    {
        // Close client and channel
        if ($this->client !== NULL) {
            $this->logger->info('Close connection and its channels.');
            $this->client->disconnect();
            $this->client = NULL;
        }

        // Remove channels
        $ids            = array_keys($this->channels);
        $this->channels = [];
        foreach ($ids as $id) {
            $this->channels[$id] = [];
        }
    }

    /**
     *
     */
    private function restore(): void
    {
        if (!$this->getClient()->isConnected()) {
            throw new RuntimeException('Restore error - the rabbit mq is not connected.');
        }

        foreach (array_keys($this->channels) as $id) {
            /** @var Channel $channel */
            $channel             = $this->getClient()->channel();
            $this->channels[$id] = $channel;
        }
    }

    /**
     *
     */
    private function connect(): void
    {
        try {
            $this->getClient()->connect();
        } catch (Exception $e) {
            $this->logger->info('RabbitMQ is not connected.', ['exception' => $e]);
            $this->reconnect();
        }
    }

    /**
     *
     */
    public function reconnect(): void
    {
        do {
            $wait = 2;
            sleep($wait);
            $this->logger->info(sprintf('Waiting for reconnect %ss.', $wait));
            try {

                $this->close();
                $this->getClient()->connect();
                $this->restore();

                $connect = TRUE;
                $this->logger->info('RabbitMQ is connected.');
            } catch (ClientException | Exception $e) {
                $connect = FALSE;
                $this->logger->info('RabbitMQ is not connected.', ['exception' => $e]);
            }

        } while (!$connect);
    }

}
<?php declare(strict_types=1);

namespace RabbitMqBundle\Connection;

use Exception;
use InvalidArgumentException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPSocketConnection;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;

/**
 * Class Connection
 *
 * @package RabbitMqBundle\Connection
 */
final class Connection implements LoggerAwareInterface
{

    /**
     * @var string
     */
    private string $name;

    /**
     * @var ClientFactory
     */
    private ClientFactory $clientFactory;

    /**
     * @var AMQPSocketConnection|NULL
     */
    private ?AMQPSocketConnection $client = NULL;

    /**
     * @var mixed[]
     */
    private array $channels = [];

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

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
     * @return AMQPSocketConnection
     * @throws Exception
     */
    public function getClient(): AMQPSocketConnection
    {
        if ($this->client === NULL) {
            $this->client = $this->clientFactory->create($this->name);
        }

        return $this->client;
    }

    /**
     * @param int $id
     *
     * @return AMQPChannel
     * @throws Exception
     */
    public function getChannel(int $id): AMQPChannel
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
     * @throws Exception
     */
    public function createChannel(): int
    {
        if (!$this->getClient()->isConnected()) {
            $this->connect();
        }

        $channel                    = $this->getClient()->channel();
        $channelId                  = (int) $channel->getChannelId();
        $this->channels[$channelId] = $channel;

        return $channelId;
    }

    /**
     * Close connection and its channels
     *
     * @throws Exception
     */
    private function close(): void
    {
        // Close client and channel
        if ($this->client !== NULL) {
            $this->logger->info('Close connection and its channels.');
            $this->client->close();
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
     * @throws Exception
     */
    private function restore(): void
    {
        if (!$this->getClient()->isConnected()) {
            throw new RuntimeException('Restore error - the rabbit mq is not connected.');
        }

        foreach (array_keys($this->channels) as $id) {
            $this->channels[$id] = $this->getClient()->channel();
        }
    }

    /**
     *
     */
    private function connect(): void
    {
        try {
            $this->getClient()->reconnect();
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
        $config  = $this->clientFactory->getConfig();
        $counter = 0;

        do {
            sleep($config[ClientFactory::RECONNECT_TIMEOUT]);
            $this->logger->info(sprintf('Waiting for reconnect %ss.', $config[ClientFactory::RECONNECT_TIMEOUT]));

            try {
                $this->close();
                $this->getClient()->reconnect();
                $this->restore();

                $connect = TRUE;
                $this->logger->info('RabbitMQ is connected.');
            } catch (Exception $e) {
                $counter++;
                $connect = FALSE;
                $this->logger->info('RabbitMQ is not connected.', ['exception' => $e]);

                if ($counter++ > $config[ClientFactory::RECONNECT_TRIES]) {
                    break;
                }
            }

        } while (!$connect);
    }

}

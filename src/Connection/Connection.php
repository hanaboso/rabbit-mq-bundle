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
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class Connection
 *
 * @package RabbitMqBundle\Connection
 */
class Connection
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
     * @var array|Channel[]
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
     *
     */
    public function removeClient(): void
    {
        var_dump('remove client - disconnect');

        if ($this->client !== NULL) {
            $this->client->disconnect();
            $this->client = NULL;
        }
    }

    /**
     * @param int|null $id
     *
     * @return Channel
     */
    public function getChannel(?int $id = NULL): Channel
    {
        if (!$this->getClient()->isConnected()) {
            $this->connect();
            /** @var Channel $channel */
            $channel             = $this->getClient()->channel();
            $id                  = $channel->getChannelId();
            $this->channels[$id] = $channel;
        }

        if (!array_key_exists($id, $this->channels)) {
            /** @var Channel $channel */
            $channel             = $this->getClient()->channel();
            $id                  = $channel->getChannelId();
            $this->channels[$id] = $channel;
        }

        return $this->channels[$id];
    }

    /**
     *
     */
    public function connect(): void
    {
        try {
            $this->getClient()->connect();
        } catch (Exception $clientException) {
            $this->internalReconnect(function (): void {
                // close old client
                $this->removeClient();

                // create new client
                $this->getClient()->connect();

                // recreate all channels
                foreach (array_keys($this->channels) as $id) {
                    $this->getChannel($id);
                }
            });
        }
    }

    /**
     *
     */
    public function reconnect(): void
    {
        $this->internalReconnect(function (): void {
            // close old client
            $this->removeClient();

            // create new client
            $this->getClient();

            // recreate all channels
            foreach (array_keys($this->channels) as $id) {
                $this->getChannel($id);
            }
        });
    }

    /**
     * @param callable $reconnect
     */
    private function internalReconnect(callable $reconnect): void
    {
        do {
            $wait = 2;
            sleep($wait);
            $this->logger->info(sprintf('Waiting for %ss.', $wait));
            try {
                var_dump('reconnect');

                $reconnect();

                $connect = TRUE;
                $this->logger->info('RabbitMQ is connected.');
            } catch (ClientException $e) {
                $connect = FALSE;
                $this->logger->info('RabbitMQ is not connected.', ['exception' => $e]);
            }

        } while (!$connect);
    }

}
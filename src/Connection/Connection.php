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
     * @var Client
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
     * @param int|null $id
     *
     * @return Channel
     * @throws \Exception
     */
    public function getChannel(int $id = NULL): Channel
    {
        if (!$this->getClient()->isConnected()) {
            /** @var Channel $channel */
            $channel             = $this->getClient()->connect()->channel();
            $id                  = $channel->getChannelId();
            $this->channels[$id] = $channel;
        }

        if (!array_key_exists($id, $this->channels)) {
            $channel             = $this->getClient()->channel();
            $id                  = $channel->getChannelId();
            $this->channels[$id] = $channel;
        }

        return $this->channels[$id];
    }

    /**
     *
     */
    public function reconnect(): void
    {
        do {
            $wait = 2;
            sleep($wait);
            $this->logger->info(sprintf('Waiting for %ss.', $wait));
            try {
                var_dump(array_keys($this->channels));
                $connect = TRUE;
                $this->logger->info('RabbitMQ is connected.');
            } catch (ClientException $e) {
                $connect = FALSE;
                $this->logger->info('RabbitMQ is not connected.', ['exception' => $e]);
            }

        } while (!$connect);
    }

}
<?php declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: venca
 * Date: 2.1.18
 * Time: 14:58
 */

namespace RabbitMqBundle\Connection;

use Bunny\Client;
use Bunny\Exception\ClientException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class ConnectionStore
 *
 * @package RabbitMqBundle\Connection
 */
class ConnectionManager
{

    /**
     * @var ClientFactory
     */
    private $clientFactory;

    /**
     * @var array
     */
    private $clients = [];

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ConnectionStore constructor.
     *
     * @param ClientFactory $clientFactory
     */
    public function __construct(ClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
        $this->logger        = new NullLogger();
    }

    /**
     * @param string $name
     *
     * @return Client
     */
    public function getClient(string $name = 'default'): Client
    {
        if (!array_key_exists($name, $this->clients)) {
            $this->clients[$name] = $this->clientFactory->create();
        }

        return $this->clients[$name];
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

                $connect = TRUE;
                $this->logger->info('RabbitMQ is connected.');
            } catch (ClientException $e) {
                $connect = FALSE;
                $this->logger->info('RabbitMQ is not connected.', ['exception' => $e]);
            }

        } while (!$connect);
    }

}
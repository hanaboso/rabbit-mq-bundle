<?php declare(strict_types=1);

namespace RabbitMqBundle\Connection;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class ConnectionManager
 *
 * @package RabbitMqBundle\Connection
 */
final class ConnectionManager implements LoggerAwareInterface
{

    /**
     * @var ClientFactory
     */
    private $clientFactory;

    /**
     * @var array|Connection[]
     */
    private $connections = [];

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ConnectionManager constructor.
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
     * @return Connection
     */
    private function createConnection(string $name): Connection
    {
        $conn = new Connection($name, $this->clientFactory);
        $conn->setLogger($this->logger);

        return $conn;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @param string $name
     *
     * @return Connection
     */
    public function getConnection(string $name = 'default'): Connection
    {
        if (!array_key_exists($name, $this->connections)) {
            $this->connections[$name] = $this->createConnection($name);
        }

        return $this->connections[$name];
    }

}

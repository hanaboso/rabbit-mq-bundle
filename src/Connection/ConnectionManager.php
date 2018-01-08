<?php declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: venca
 * Date: 2.1.18
 * Time: 14:58
 */

namespace RabbitMqBundle\Connection;

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
     * @var array|Connection
     */
    private $connections = [];

    /**
     * ConnectionStore constructor.
     *
     * @param ClientFactory $clientFactory
     */
    public function __construct(ClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
    }

    /**
     * @param string $name
     *
     * @return Connection
     */
    private function createConnection(string $name): Connection
    {
        return new Connection($name, $this->clientFactory);
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
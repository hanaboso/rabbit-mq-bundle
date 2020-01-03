<?php declare(strict_types=1);

namespace RabbitMqBundle\Connection;

use Bunny\Async\Client as AsyncClient;
use Bunny\Client;
use React\EventLoop\LoopInterface;

/**
 * Class ClientFactory
 *
 * @package RabbitMqBundle\Connection
 */
class ClientFactory
{

    // Config keys
    public const HOST              = 'host';
    public const PORT              = 'port';
    public const VHOST             = 'vhost';
    public const USER              = 'user';
    public const PASSWORD          = 'password';
    public const HEARTBEAT         = 'heartbeat';
    public const TIMEOUT           = 'timeout';
    public const RECONNECT         = 'reconnect';
    public const RECONNECT_TRIES   = 'reconnect_tries';
    public const RECONNECT_TIMEOUT = 'reconnect_timeout';

    /**
     * @var mixed[]
     */
    private $config;

    /**
     * ClientFactory constructor.
     *
     * @param mixed[] $config
     */
    public function __construct($config = [])
    {
        $this->config = $config;
    }

    /**
     * @param string $name
     *
     * @return mixed[]
     */
    public function getConfig(string $name = 'default'): array
    {
        return $this->config[$name];
    }

    /**
     * @param string $name
     * @param string $key
     *
     * @return string
     */
    public function getConfigByKey(string $name, string $key): string
    {
        return $this->config[$name][$key] ?? '';
    }

    /**
     * @param string $name
     *
     * @return Client
     */
    public function create(string $name = 'default'): Client
    {
        return new Client($this->config[$name]);
    }

    /**
     * @param string             $name
     * @param null|LoopInterface $loop
     *
     * @return AsyncClient
     */
    public function createAsync(string $name = 'default', ?LoopInterface $loop = NULL): AsyncClient
    {
        return new AsyncClient($loop, $this->config[$name]);
    }

}

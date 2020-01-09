<?php declare(strict_types=1);

namespace RabbitMqBundle\Connection;

use Exception;
use PhpAmqpLib\Connection\AMQPSocketConnection;

/**
 * Class ClientFactory
 *
 * @package RabbitMqBundle\Connection
 */
final class ClientFactory
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
     * @return AMQPSocketConnection
     * @throws Exception
     */
    public function create(string $name = 'default'): AMQPSocketConnection
    {
        return new AMQPSocketConnection(
            $this->config[$name][self::HOST],
            $this->config[$name][self::PORT],
            $this->config[$name][self::USER],
            $this->config[$name][self::PASSWORD],
            $this->config[$name][self::VHOST],
            FALSE,
            'AMQPLAIN',
            NULL,
            'en_US',
            $this->config[$name][self::TIMEOUT],
            FALSE,
            $this->config[$name][self::TIMEOUT],
            $this->config[$name][self::HEARTBEAT],
            $this->config[$name][self::TIMEOUT],
        );
    }

}

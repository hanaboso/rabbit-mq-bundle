<?php declare(strict_types=1);

namespace RabbitMqBundle\Connection;

use Exception;
use Hanaboso\Utils\String\DsnParser;
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
    private array $config;

    /**
     * ClientFactory constructor.
     *
     * @param mixed $connections
     */
    public function __construct($connections = [])
    {
        foreach ($connections as $name => $connectionDsn) {
            $settings                      = DsnParser::rabbitParser($connectionDsn['dsn']);
            $settings['user']              = isset($settings['user']) && !empty($settings['user']) ? $settings['user'] : 'guest';
            $settings['password']          = isset($settings['password']) && !empty($settings['password']) ? $settings['password'] : 'guest';
            $settings['port']              = isset($settings['port']) && !empty($settings['port']) ? $settings['port'] : 5_672;
            $settings['vhost']             = isset($settings['vhost']) && !empty($settings['vhost']) ? $settings['vhost'] : '/';
            $settings['heartbeat']         = isset($settings['heartbeat']) && !empty($settings['heartbeat']) ? $settings['heartbeat'] : 30;
            $settings['timeout']           = isset($settings['timeout']) && !empty($settings['timeout']) ? $settings['timeout'] : 60;
            $settings['reconnect']         = isset($settings['reconnect']) && !empty($settings['reconnect']) ? $settings['reconnect'] : TRUE;
            $settings['reconnect_tries']   = isset($settings['reconnect_tries']) && !empty($settings['reconnect_tries']) ? $settings['reconnect_tries'] : 3_600;
            $settings['reconnect_timeout'] = isset($settings['reconnect_timeout']) && !empty($settings['reconnect_timeout']) ? $settings['reconnect_timeout'] : 1;

            $this->config[$name] = $settings;
        }
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

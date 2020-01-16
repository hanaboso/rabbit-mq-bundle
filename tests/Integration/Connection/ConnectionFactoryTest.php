<?php declare(strict_types=1);

namespace RabbitBundleTests\Integration\Connection;

use Exception;
use Hanaboso\Utils\String\DsnParser;
use PhpAmqpLib\Connection\AMQPSocketConnection;
use RabbitBundleTests\KernelTestCaseAbstract;
use RabbitMqBundle\Connection\ClientFactory;

/**
 * Class ConnectionFactoryTest
 *
 * @package RabbitBundleTests\Integration\Connection
 *
 * @covers  \RabbitMqBundle\Connection\ClientFactory
 */
final class ConnectionFactoryTest extends KernelTestCaseAbstract
{

    /**
     * @var ClientFactory
     */
    private ClientFactory $factory;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = self::$container->get('factory');
    }

    /**
     * @covers \RabbitMqBundle\Connection\ClientFactory::getConfig
     */
    public function testGetConfig(): void
    {
        $parsed = DsnParser::rabbitParser(getenv('RABBITMQ_DSN') ?: '');
        self::assertEquals(
            [
                'user'              => 'guest',
                'password'          => 'guest',
                'host'              => $parsed['host'],
                'port'              => '5672',
                'vhost'             => '/',
                'heartbeat'         => 30,
                'timeout'           => 60,
                'reconnect'         => TRUE,
                'reconnect_tries'   => 3_600,
                'reconnect_timeout' => 1,
            ],
            $this->factory->getConfig()
        );
    }

    /**
     * @covers \RabbitMqBundle\Connection\ClientFactory::getConfigByKey
     */
    public function testGetConfigByKey(): void
    {
        self::assertEquals('guest', $this->factory->getConfigByKey('default', 'user'));
    }

    /**
     * @throws Exception
     *
     * @covers \RabbitMqBundle\Connection\ClientFactory::create
     */
    public function testCreate(): void
    {
        self::assertInstanceOf(AMQPSocketConnection::class, $this->factory->create());
    }

}

<?php declare(strict_types=1);

namespace RabbitBundleTests\Integration\Connection;

use Exception;
use Hanaboso\Utils\String\DsnParser;
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
     * @covers \RabbitMqBundle\Connection\ClientFactory::getConfig
     */
    public function testGetConfig(): void
    {
        $parsed = DsnParser::rabbitParser(getenv('RABBITMQ_DSN') ?: '');
        self::assertEquals(
            [
                'heartbeat'         => 30,
                'host'              => $parsed['host'],
                'password'          => 'guest',
                'port'              => '5672',
                'reconnect'         => TRUE,
                'reconnect_timeout' => 1,
                'reconnect_tries'   => 3_600,
                'timeout'           => 60,
                'user'              => 'guest',
                'vhost'             => '/',
            ],
            $this->factory->getConfig(),
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
        $this->factory->create();

        self::assertFake();
    }

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = self::getContainer()->get('factory');
    }

}

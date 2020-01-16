<?php declare(strict_types=1);

namespace RabbitBundleTests\Integration\Connection;

use Exception;
use Psr\Log\NullLogger;
use RabbitBundleTests\KernelTestCaseAbstract;
use RabbitMqBundle\Connection\Connection;
use RabbitMqBundle\Connection\ConnectionManager;

/**
 * Class ConnectionManagerTest
 *
 * @package RabbitBundleTests\Integration\Connection
 *
 * @covers  \RabbitMqBundle\Connection\ConnectionManager
 */
final class ConnectionManagerTest extends KernelTestCaseAbstract
{

    /**
     * @var ConnectionManager
     */
    private ConnectionManager $manager;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = self::$container->get('connection');
    }

    /**
     * @covers \RabbitMqBundle\Connection\ConnectionManager::setLogger
     */
    public function testLogger(): void
    {
        $this->manager->setLogger(new NullLogger());

        self::assertSuccess();
    }

    /**
     * @throws Exception
     *
     * @covers \RabbitMqBundle\Connection\ConnectionManager::getConnection
     */
    public function testGetConnection(): void
    {
        self::assertInstanceOf(Connection::class, $this->manager->getConnection());
    }

}

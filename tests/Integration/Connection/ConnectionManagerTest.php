<?php declare(strict_types=1);

namespace RabbitBundleTests\Integration\Connection;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Log\NullLogger;
use RabbitBundleTests\KernelTestCaseAbstract;
use RabbitMqBundle\Connection\ConnectionManager;

/**
 * Class ConnectionManagerTest
 *
 * @package RabbitBundleTests\Integration\Connection
 */
#[CoversClass(ConnectionManager::class)]
final class ConnectionManagerTest extends KernelTestCaseAbstract
{

    /**
     * @var ConnectionManager
     */
    private ConnectionManager $manager;

    /**
     * @return void
     */
    public function testLogger(): void
    {
        $this->manager->setLogger(new NullLogger());

        self::assertFake();
    }

    /**
     * @throws Exception
     */
    public function testGetConnection(): void
    {
        $this->manager->getConnection();

        self::assertFake();
    }

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = self::getContainer()->get('connection');
    }

}

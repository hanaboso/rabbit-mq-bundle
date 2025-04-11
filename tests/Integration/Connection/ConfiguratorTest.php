<?php declare(strict_types=1);

namespace RabbitBundleTests\Integration\Connection;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Log\NullLogger;
use RabbitBundleTests\KernelTestCaseAbstract;
use RabbitMqBundle\Connection\Configurator;

/**
 * Class ConfiguratorTest
 *
 * @package RabbitBundleTests\Integration\Connection
 */
#[CoversClass(Configurator::class)]
final class ConfiguratorTest extends KernelTestCaseAbstract
{

    private const string CONFIGURED = 'configured';

    /**
     * @var Configurator
     */
    private Configurator $configurator;

    /**
     * @return void
     */
    public function testLogger(): void
    {
        $this->configurator->setLogger(new NullLogger());

        self::assertFake();
    }

    /**
     * @throws Exception
     */
    public function testSetConfigured(): void
    {
        $this->configurator->setConfigured(TRUE);

        self::assertTrue($this->getProperty($this->configurator, self::CONFIGURED));
    }

    /**
     * @throws Exception
     */
    public function testSetup(): void
    {
        $this->configurator->setup($this->channel);

        self::assertTrue($this->getProperty($this->configurator, self::CONFIGURED));
    }

    /**
     * @throws Exception
     */
    public function testSetupConfigured(): void
    {
        $this->setProperty($this->configurator, self::CONFIGURED, TRUE);

        $this->configurator->setup($this->channel);

        self::assertTrue($this->getProperty($this->configurator, self::CONFIGURED));
    }

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->configurator = self::getContainer()->get('configurator');
    }

}

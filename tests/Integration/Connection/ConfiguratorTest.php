<?php declare(strict_types=1);

namespace RabbitBundleTests\Integration\Connection;

use Exception;
use Psr\Log\NullLogger;
use RabbitBundleTests\KernelTestCaseAbstract;
use RabbitMqBundle\Connection\Configurator;

/**
 * Class ConfiguratorTest
 *
 * @package RabbitBundleTests\Integration\Connection
 *
 * @covers  \RabbitMqBundle\Connection\Configurator
 */
final class ConfiguratorTest extends KernelTestCaseAbstract
{

    private const CONFIGURED = 'configured';

    /**
     * @var Configurator
     */
    private Configurator $configurator;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->configurator = self::$container->get('configurator');
    }

    /**
     * @covers \RabbitMqBundle\Connection\Configurator::setLogger
     */
    public function testLogger(): void
    {
        $this->configurator->setLogger(new NullLogger());

        self::assertSuccess();
    }

    /**
     * @throws Exception
     *
     * @covers \RabbitMqBundle\Connection\Configurator::setConfigured
     */
    public function testSetConfigured(): void
    {
        $this->configurator->setConfigured(TRUE);

        self::assertTrue($this->getProperty($this->configurator, self::CONFIGURED));
    }

    /**
     * @throws Exception
     *
     * @covers \RabbitMqBundle\Connection\Configurator::setup
     */
    public function testSetup(): void
    {
        $this->configurator->setup($this->channel);

        self::assertTrue($this->getProperty($this->configurator, self::CONFIGURED));
    }

    /**
     * @throws Exception
     *
     * @covers \RabbitMqBundle\Connection\Configurator::setup
     */
    public function testSetupConfigured(): void
    {
        $this->setProperty($this->configurator, self::CONFIGURED, TRUE);

        $this->configurator->setup($this->channel);

        self::assertTrue($this->getProperty($this->configurator, self::CONFIGURED));
    }

}

<?php declare(strict_types=1);

namespace RabbitBundleTests;

use PHPUnit\Framework\TestCase;
use RabbitMqBundle\DependencyInjection\RabbitMqExtension;
use RabbitMqBundle\RabbitMqBundle;

/**
 * Class RabbitMqBundleTest
 *
 * @package RabbitBundleTests
 */
final class RabbitMqBundleTest extends TestCase
{

    /**
     *
     */
    public function testGetName(): void
    {
        $bundle = new RabbitMqBundle();

        self::assertSame('RabbitMqBundle', $bundle->getName());
    }

    /**
     *
     */
    public function testGetExtension(): void
    {
        $bundle = new RabbitMqBundle();

        self::assertInstanceOf(RabbitMqExtension::class, $bundle->getContainerExtension());
    }

}

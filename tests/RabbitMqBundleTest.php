<?php declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: venca
 * Date: 12/18/17
 * Time: 11:28 AM
 */

namespace Tests;

use PHPUnit\Framework\TestCase;
use RabbitMqBundle\DependencyInjection\RabbitMqExtension;
use RabbitMqBundle\RabbitMqBundle;

/**
 * Class RabbitMqBundleTest
 *
 * @package Tests
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

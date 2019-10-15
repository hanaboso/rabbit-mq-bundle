<?php declare(strict_types=1);

namespace RabbitBundleTests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use RabbitMqBundle\DependencyInjection\RabbitMqExtension;

/**
 * Class RabbitMqExtensionTest
 *
 * @package RabbitBundleTests\DependencyInjection
 */
final class RabbitMqExtensionTest extends TestCase
{

    /**
     *
     */
    public function testGetAlias(): void
    {
        $extension = new RabbitMqExtension();

        self::assertSame('rabbit_mq', $extension->getAlias());
    }

}

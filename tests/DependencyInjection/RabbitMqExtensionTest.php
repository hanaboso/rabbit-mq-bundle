<?php declare(strict_types=1);

namespace Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use RabbitMqBundle\DependencyInjection\RabbitMqExtension;

/**
 * Class RabbitMqExtensionTest
 *
 * @package Tests\DependencyInjection
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

<?php declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: venca
 * Date: 12/18/17
 * Time: 3:30 PM
 */

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

        $this->assertSame('rabbit_mq', $extension->getAlias());
    }

}
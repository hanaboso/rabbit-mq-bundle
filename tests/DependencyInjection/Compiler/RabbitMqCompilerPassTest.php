<?php declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: venca
 * Date: 12/18/17
 * Time: 4:24 PM
 */

namespace Tests\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use RabbitMqBundle\DependencyInjection\RabbitMqExtension;
use RabbitMqBundle\RabbitMqBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Yaml;

/**
 * Class RabbitMqCompilerPassTest
 *
 * @package Tests\DependencyInjection\Compiler
 */
class RabbitMqCompilerPassTest extends TestCase
{

    /**
     *
     */
    public function testCompilerPass(): void
    {
        $config = Yaml::parse(file_get_contents(__DIR__ . '/config.yml'));

        $container = new ContainerBuilder();

        // Load config
        $extension = new RabbitMqExtension();
        $extension->load($config, $container);

        // Register extension
        $container->registerExtension($extension);

        // Build bundle
        $bundle = new RabbitMqBundle();
        $bundle->build($container);

        $container->compile();

        $this->assertCount(10, $container->getRemovedIds());
    }

}
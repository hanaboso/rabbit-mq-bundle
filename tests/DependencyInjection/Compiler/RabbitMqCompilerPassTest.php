<?php declare(strict_types=1);

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
final class RabbitMqCompilerPassTest extends TestCase
{

    /**
     *
     */
    public function testCompilerPass(): void
    {
        $config    = Yaml::parseFile(__DIR__ . '/config.yml');
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

        self::assertCount(12, $container->getRemovedIds());
    }

}

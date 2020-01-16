<?php declare(strict_types=1);

namespace RabbitMqBundle;

use RabbitMqBundle\DependencyInjection\Compiler\MonologCompilerPass;
use RabbitMqBundle\DependencyInjection\Compiler\RabbitMqCompilerPass;
use Symfony\Bundle\MonologBundle\DependencyInjection\Compiler\LoggerChannelPass;
use Symfony\Component\Console\DependencyInjection\AddConsoleCommandPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class RabbitMqBundle
 *
 * @package RabbitMqBundle
 *
 * @codeCoverageIgnore
 */
final class RabbitMqBundle extends Bundle
{

    public const KEY = 'rabbit_mq';

    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        if ($container->hasExtension('monolog')) {
            $container->addCompilerPass(new MonologCompilerPass());
            $container->addCompilerPass(new LoggerChannelPass());
        }

        $container->addCompilerPass(new RabbitMqCompilerPass());
        $container->addCompilerPass(new AddConsoleCommandPass());
    }

}

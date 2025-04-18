<?php declare(strict_types=1);

namespace RabbitMqBundle;

use RabbitMqBundle\DependencyInjection\Compiler\RabbitMqCompilerPass;
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

    public const string KEY = 'rabbit_mq';

    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new RabbitMqCompilerPass());
        $container->addCompilerPass(new AddConsoleCommandPass());
    }

}

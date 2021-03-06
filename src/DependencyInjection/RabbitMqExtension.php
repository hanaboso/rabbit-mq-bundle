<?php declare(strict_types=1);

namespace RabbitMqBundle\DependencyInjection;

use RabbitMqBundle\RabbitMqBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

/**
 * Class RabbitMqExtension
 *
 * @package RabbitMqBundle\DependencyInjection
 *
 * @codeCoverageIgnore
 */
final class RabbitMqExtension extends Extension
{

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return RabbitMqBundle::KEY;
    }

    /**
     * Loads a specific configuration.
     *
     * @param mixed[]          $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $container->setParameter(RabbitMqBundle::KEY, $config);
    }

}

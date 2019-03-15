<?php declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: venca
 * Date: 12/18/17
 * Time: 2:09 PM
 */

namespace RabbitMqBundle\DependencyInjection;

use RabbitMqBundle\RabbitMqBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

/**
 * Class RabbitMqExtension
 *
 * @package RabbitMqBundle\DependencyInjection
 */
class RabbitMqExtension extends Extension
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
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $container->setParameter(RabbitMqBundle::KEY, $config);

    }

}

<?php declare(strict_types=1);

namespace RabbitMqBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class MonologCompilerPass
 *
 * @package RabbitMqBundle\DependencyInjection\Compiler
 */
final class MonologCompilerPass implements CompilerPassInterface
{

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container): void
    {
        if ($container->hasExtension('monolog')) {
            if ($container->getParameter('rabbit_mq')['configure_monolog']) {
                $container->getExtension('monolog')->load(
                    [
                        'monolog' => [
                            'channels' => ['rabbit_mq'],
                            'handlers' => [
                                'rabbit_mq' => [
                                    'type'  => 'stream',
                                    'path'  => 'php://stdout',
                                    'level' => 'info',
                                ],
                            ],
                        ],
                    ],
                    $container
                );
            } else {
                $container->setParameter('monolog.additional_channels', []);
            }
        }
    }

}

<?php declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: venca
 * Date: 1/9/18
 * Time: 3:40 PM
 */

namespace RabbitMqBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class MonologCompilerPass
 *
 * @package RabbitMqBundle\DependencyInjection\Compiler
 */
class MonologCompilerPass implements CompilerPassInterface
{

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container): void
    {
        if ($container->hasExtension('monolog')) {
            if ($container->hasExtension('monolog')) {

                $container->getExtension('monolog')->load([
                    'monolog' => [
                        'channels' => ['rabbit_mq'],
                        'handlers' => [
                            'rabbit_mq' => [
                                'type'     => 'stream',
                                'path'     => 'php://stdout',
                                'level'    => 'info',
                            ],
                        ],
                    ],
                ], $container);
            }
        }
    }

}
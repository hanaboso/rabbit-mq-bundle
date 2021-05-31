<?php declare(strict_types=1);

namespace RabbitMqBundle\Consumer;

use RabbitMqBundle\Connection\Configurator;
use RabbitMqBundle\Connection\ConnectionManager;

/**
 * Class Consumer
 *
 * @package RabbitMqBundle\Consumer
 */
class Consumer extends ConsumerAbstract
{

    /**
     * Consumer constructor.
     *
     * @param ConnectionManager                        $connectionManager
     * @param Configurator                             $configurator
     * @param CallbackInterface|AsyncCallbackInterface $callback
     * @param string                                   $queue
     * @param string                                   $consumerTag
     * @param bool                                     $noLocal
     * @param bool                                     $noAck
     * @param bool                                     $exclusive
     * @param bool                                     $nowait
     * @param int                                      $prefetchCount
     * @param int                                      $prefetchSize
     */
    public function __construct(
        ConnectionManager $connectionManager,
        Configurator $configurator,
        protected CallbackInterface|AsyncCallbackInterface $callback,
        string $queue = '',
        string $consumerTag = '',
        bool $noLocal = FALSE,
        bool $noAck = FALSE,
        bool $exclusive = FALSE,
        bool $nowait = FALSE,
        int $prefetchCount = 0,
        int $prefetchSize = 0,
    )
    {
        parent::__construct(
            $connectionManager,
            $configurator,
            $queue,
            $consumerTag,
            $noLocal,
            $noAck,
            $exclusive,
            $nowait,
            $prefetchCount,
            $prefetchSize,
        );
    }

}

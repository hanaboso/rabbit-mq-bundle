<?php declare(strict_types=1);

namespace RabbitMqBundle\Connection;

/**
 * Interface SetupInterface
 *
 * @package RabbitMqBundle\Connection
 */
interface SetupInterface
{

    /**
     *
     */
    public function setup(): void;

}

<?php declare(strict_types=1);

namespace RabbitMqBundle\Consumer;

/**
 * Interface ConsumerInterface
 *
 * @package RabbitMqBundle\Consumer
 */
interface ConsumerInterface
{

    /**
     *
     */
    public function consume(): void;

}

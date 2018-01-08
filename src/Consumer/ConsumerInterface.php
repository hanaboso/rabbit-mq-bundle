<?php declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: venca
 * Date: 2.1.18
 * Time: 14:57
 */

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
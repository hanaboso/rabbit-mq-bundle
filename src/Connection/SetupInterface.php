<?php declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: venca
 * Date: 2.1.18
 * Time: 14:55
 */

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

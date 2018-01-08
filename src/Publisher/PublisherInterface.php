<?php declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: venca
 * Date: 2.1.18
 * Time: 15:02
 */

namespace RabbitMqBundle\Publisher;

/**
 * Interface PublisherInterface
 *
 * @package RabbitMqBundle\Publisher
 */
interface PublisherInterface
{

    /**
     * @param mixed $content
     * @param array $headers
     */
    public function publish($content, array $headers = []): void;

}
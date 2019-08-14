<?php declare(strict_types=1);

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

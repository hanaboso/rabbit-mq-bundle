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
     * @param mixed   $content
     * @param mixed[] $headers
     */
    public function publish(mixed $content, array $headers = []): void;

}

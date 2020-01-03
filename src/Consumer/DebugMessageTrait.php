<?php declare(strict_types=1);

namespace RabbitMqBundle\Consumer;

use Bunny\Message;
use DateTime;

/**
 * Trait DebugMessageTrait
 *
 * @package RabbitMqBundle\Consumer
 */
trait DebugMessageTrait
{

    /**
     * @param string|null  $string
     * @param string|null  $exchange
     * @param string|null  $routingKey
     * @param mixed[]|null $headers
     *
     * @return mixed[]
     */
    public function prepareMessage(
        ?string $string = NULL,
        ?string $exchange = NULL,
        ?string $routingKey = NULL,
        ?array $headers = []
    ): array
    {
        $context = [];
        if ($string) {
            $context['body'] = $string;
        }

        if ($exchange) {
            $context['exchange'] = $exchange;
        }

        if ($routingKey) {
            $context['routing_key'] = $routingKey;
        }

        if (!empty($headers)) {
            $result = [];
            foreach ($headers as $key => $value) {
                if ($key === 'timestamp' && !is_scalar($value)) {
                    /** @var DateTime $value */
                    $value = $value->getTimestamp();
                }
                $result[] = sprintf('%s=%s', $key, $value);
            }
            $context['headers'] = implode('@', $result);
        }

        return $context;
    }

    /**
     * @param Message $message
     *
     * @return mixed[]
     */
    public function prepareBunnyMessage(Message $message): array
    {
        return $this->prepareMessage($message->content, $message->exchange, $message->routingKey, $message->headers);
    }

}

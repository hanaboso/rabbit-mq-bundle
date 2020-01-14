<?php declare(strict_types=1);

namespace RabbitMqBundle\Consumer;

use DateTime;
use PhpAmqpLib\Message\AMQPMessage;
use RabbitMqBundle\Utils\Message;

/**
 * Trait DebugMessageTrait
 *
 * @package RabbitMqBundle\Consumer
 */
trait DebugMessageTrait
{

    /**
     * @param string|NULL  $string
     * @param string|NULL  $exchange
     * @param string|NULL  $routingKey
     * @param mixed[]|NULL $headers
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
     * @param AMQPMessage $message
     *
     * @return mixed[]
     */
    public function prepareBunnyMessage(AMQPMessage $message): array
    {
        return $this->prepareMessage(
            Message::getBody($message),
            // phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps
            $message->delivery_info['exchange'] ?? '',
            $message->delivery_info['routing_key'] ?? '',
            // phpcs:enable
            Message::getHeaders($message)
        );
    }

}

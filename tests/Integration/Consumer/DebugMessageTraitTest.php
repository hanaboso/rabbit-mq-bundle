<?php declare(strict_types=1);

namespace RabbitBundleTests\Integration\Consumer;

use Exception;
use Hanaboso\Utils\Date\DateTimeUtils;
use RabbitBundleTests\KernelTestCaseAbstract;
use RabbitMqBundle\Consumer\DebugMessageTrait;
use RabbitMqBundle\Utils\Message;

/**
 * Class DebugMessageTraitTest
 *
 * @package RabbitBundleTests\Integration\Consumer
 */
final class DebugMessageTraitTest extends KernelTestCaseAbstract
{

    /**
     * @throws Exception
     */
    public function testPrepareMessage(): void
    {
        $message = Message::create('{}', ['key' => 'value', 'timestamp' => DateTimeUtils::getUtcDateTime('today')]);
        // phpcs:disable Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
        $message->delivery_info['exchange']    = 'exchange';
        $message->delivery_info['routing_key'] = 'routingKey';
        // phpcs:enable

        self::assertEquals(
            [
                'body'        => '{}',
                'exchange'    => 'exchange',
                'routing_key' => 'routingKey',
                'headers'     => sprintf(
                    'timestamp=%s@key=value',
                    DateTimeUtils::getUtcDateTime('today')->getTimestamp(),
                ),
            ],
            (new class {

                use DebugMessageTrait;

            })->prepareBunnyMessage($message),
        );
    }

}

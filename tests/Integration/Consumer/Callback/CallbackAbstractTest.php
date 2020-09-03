<?php declare(strict_types=1);

namespace RabbitBundleTests\Integration\Consumer\Callback;

use PhpAmqpLib\Message\AMQPMessage;
use RabbitBundleTests\KernelTestCaseAbstract;
use RabbitMqBundle\Utils\Message;

/**
 * Class CallbackAbstractTest
 *
 * @package RabbitBundleTests\Integration\Consumer\Callback
 */
abstract class CallbackAbstractTest extends KernelTestCaseAbstract
{

    /**
     * @return AMQPMessage
     */
    protected function createMessage(): AMQPMessage
    {
        $message = Message::create('{}');
        $message->setDeliveryTag(1);

        return $message;
    }

}

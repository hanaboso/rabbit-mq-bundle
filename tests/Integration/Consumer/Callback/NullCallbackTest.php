<?php declare(strict_types=1);

namespace RabbitBundleTests\Integration\Consumer\Callback;

use Exception;
use RabbitMqBundle\Consumer\Callback\NullCallback;

/**
 * Class NullCallbackTest
 *
 * @package RabbitBundleTests\Integration\Consumer\Callback
 *
 * @covers  \RabbitMqBundle\Consumer\Callback\NullCallback
 */
final class NullCallbackTest extends CallbackAbstractTest
{

    /**
     * @var NullCallback
     */
    private NullCallback $callback;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->callback = new NullCallback();
    }

    /**
     * @throws Exception
     */
    public function testProcessMessage(): void
    {
        $this->callback->processMessage($this->createMessage(), $this->connection, $this->connection->createChannel());

        self::assertSuccess();
    }

}

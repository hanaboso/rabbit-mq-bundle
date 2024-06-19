<?php declare(strict_types=1);

namespace RabbitBundleTests\Integration\Consumer\Callback;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use RabbitMqBundle\Consumer\Callback\NullAsyncCallback;

/**
 * Class NullAsyncCallbackTest
 *
 * @package RabbitBundleTests\Integration\Consumer\Callback
 */
#[CoversClass(NullAsyncCallback::class)]
final class NullAsyncCallbackTest extends CallbackTestAbstract
{

    /**
     * @var NullAsyncCallback
     */
    private NullAsyncCallback $callback;

    /**
     * @throws Exception
     */
    public function testProcessMessage(): void
    {
        $this->callback->processMessage(
            $this->createMessage(),
            $this->connection,
            $this->connection->createChannel(),
        );

        self::assertFake();
    }

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->callback = new NullAsyncCallback();
    }

}

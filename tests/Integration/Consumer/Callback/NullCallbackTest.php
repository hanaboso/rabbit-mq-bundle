<?php declare(strict_types=1);

namespace RabbitBundleTests\Integration\Consumer\Callback;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use RabbitMqBundle\Consumer\Callback\NullCallback;

/**
 * Class NullCallbackTest
 *
 * @package RabbitBundleTests\Integration\Consumer\Callback
 */
#[CoversClass(NullCallback::class)]
final class NullCallbackTest extends CallbackTestAbstract
{

    /**
     * @var NullCallback
     */
    private NullCallback $callback;

    /**
     * @throws Exception
     */
    public function testProcessMessage(): void
    {
        $this->callback->processMessage($this->createMessage(), $this->connection, $this->connection->createChannel());

        self::assertFake();
    }

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->callback = new NullCallback();
    }

}

<?php declare(strict_types=1);

namespace RabbitBundleTests\Integration\Consumer\Callback;

use Exception;
use RabbitMqBundle\Consumer\Callback\DumpAsyncCallback;

/**
 * Class DumpAsyncCallbackTest
 *
 * @package RabbitBundleTests\Integration\Consumer\Callback
 *
 * @covers  \RabbitMqBundle\Consumer\Callback\DumpAsyncCallback
 */
final class DumpAsyncCallbackTest extends CallbackTestAbstract
{

    /**
     * @var DumpAsyncCallback
     */
    private DumpAsyncCallback $callback;

    /**
     * @throws Exception
     */
    public function testProcessMessage(): void
    {
        ob_start();
        $this->callback->processMessage(
            $this->createMessage(),
            $this->connection,
            $this->connection->createChannel(),
        );

        self::assertEquals(
            'array(2) {
  ["body"]=>
  string(2) "{}"
  ["headers"]=>
  array(0) {
  }
}
',
            ob_get_clean(),
        );
    }

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->callback = new DumpAsyncCallback();
    }

}

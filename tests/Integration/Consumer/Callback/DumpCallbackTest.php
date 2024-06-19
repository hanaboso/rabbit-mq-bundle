<?php declare(strict_types=1);

namespace RabbitBundleTests\Integration\Consumer\Callback;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use RabbitMqBundle\Consumer\Callback\DumpCallback;

/**
 * Class DumpCallbackTest
 *
 * @package RabbitBundleTests\Integration\Consumer\Callback
 */
#[CoversClass(DumpCallback::class)]
final class DumpCallbackTest extends CallbackTestAbstract
{

    /**
     * @var DumpCallback
     */
    private DumpCallback $callback;

    /**
     * @throws Exception
     */
    public function testProcessMessage(): void
    {
        ob_start();
        $this->callback->processMessage($this->createMessage(), $this->connection, $this->connection->createChannel());

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

        $this->callback = new DumpCallback();
    }

}

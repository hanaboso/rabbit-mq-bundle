<?php declare(strict_types=1);

namespace RabbitBundleTests\Integration\Command;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use RabbitBundleTests\KernelTestCaseAbstract;
use RabbitMqBundle\Command\AsyncConsumerCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class AsyncConsumerCommandTest
 *
 * @package RabbitBundleTests\Integration\Command
 */
#[CoversClass(AsyncConsumerCommand::class)]
final class AsyncConsumerCommandTest extends KernelTestCaseAbstract
{

    private const COMMAND = 'rabbit_mq:consumer:my-second-async-consumer';

    /**
     * @var CommandTester
     */
    private CommandTester $tester;

    /**
     * @throws Exception
     */
    public function testExecute(): void
    {
        $this->createQueueWithMessages();
        $command  = $this->getProperty($this->tester, 'command');
        $consumer = $this->getProperty($command, 'consumer');
        $this->setProperty($command, 'consumer', $this->prepareConsumer($consumer, $this->prepareConsumerWait()));

        $this->tester->execute([]);

        self::assertEmpty($this->tester->getDisplay());
        self::assertMessages(0);
    }

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        /** @var KernelInterface $kernel */
        $kernel       = self::$kernel;
        $this->tester = new CommandTester((new Application($kernel))->get(self::COMMAND));
    }

}

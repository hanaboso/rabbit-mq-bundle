<?php declare(strict_types=1);

namespace RabbitBundleTests\Integration\Command;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use RabbitBundleTests\KernelTestCaseAbstract;
use RabbitMqBundle\Command\ConsumerCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class ConsumerCommandTest
 *
 * @package RabbitBundleTests\Integration\Command
 */
#[CoversClass(ConsumerCommand::class)]
final class ConsumerCommandTest extends KernelTestCaseAbstract
{

    private const string COMMAND = 'rabbit_mq:consumer:my-second-consumer';

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

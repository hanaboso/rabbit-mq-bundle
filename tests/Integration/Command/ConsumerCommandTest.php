<?php declare(strict_types=1);

namespace RabbitBundleTests\Integration\Command;

use Exception;
use RabbitBundleTests\KernelTestCaseAbstract;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class ConsumerCommandTest
 *
 * @package RabbitBundleTests\Integration\Command
 *
 * @covers  \RabbitMqBundle\Command\ConsumerCommand
 */
final class ConsumerCommandTest extends KernelTestCaseAbstract
{

    private const COMMAND = 'rabbit_mq:consumer:my-second-consumer';

    /**
     * @var CommandTester
     */
    private CommandTester $tester;

    /**
     * @throws Exception
     *
     * @covers \RabbitMqBundle\Command\ConsumerCommand::execute
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

        $this->tester = new CommandTester((new Application(self::$kernel))->get(self::COMMAND));
    }

}

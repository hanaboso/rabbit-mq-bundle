<?php declare(strict_types=1);

namespace RabbitBundleTests\Integration\Command;

use Exception;
use RabbitBundleTests\KernelTestCaseAbstract;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class PublisherCommandTest
 *
 * @package RabbitBundleTests\Integration\Command
 *
 * @covers  \RabbitMqBundle\Command\PublisherCommand
 */
final class PublisherCommandTest extends KernelTestCaseAbstract
{

    private const COMMAND = 'rabbit_mq:publisher:my-publisher';

    /**
     * @var CommandTester
     */
    private CommandTester $tester;

    /**
     * @throws Exception
     *
     * @covers \RabbitMqBundle\Command\PublisherCommand::execute
     */
    public function testExecute(): void
    {
        $this->tester->execute([]);

        self::sleep();
        self::assertEmpty($this->tester->getDisplay());
        self::assertMessages(1);
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

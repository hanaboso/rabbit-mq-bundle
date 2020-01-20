<?php declare(strict_types=1);

namespace RabbitMqBundle\Command;

use Exception;
use RabbitMqBundle\Consumer\AsyncConsumer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AsyncConsumerCommand
 *
 * @package RabbitMqBundle\Command
 */
final class AsyncConsumerCommand extends Command
{

    /**
     * @var AsyncConsumer
     */
    private AsyncConsumer $consumer;

    /**
     * AsyncConsumerCommand constructor.
     *
     * @param AsyncConsumer $consumer
     * @param string|NULL   $name
     */
    public function __construct(AsyncConsumer $consumer, ?string $name = NULL)
    {
        parent::__construct();

        $this->consumer = $consumer;

        if ($name) {
            $this->setName($name);
        }
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws Exception
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $input;
        $output;

        $this->consumer->setup();
        $this->consumer->consume();

        return 0;
    }

}

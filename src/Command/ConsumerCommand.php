<?php declare(strict_types=1);

namespace RabbitMqBundle\Command;

use Exception;
use RabbitMqBundle\Consumer\Consumer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ConsumerCommand
 *
 * @package RabbitMqBundle\Command
 */
final class ConsumerCommand extends Command
{

    /**
     * ConsumerCommand constructor.
     *
     * @param Consumer    $consumer
     * @param string|NULL $name
     */
    public function __construct(private Consumer $consumer, ?string $name = NULL)
    {
        parent::__construct();

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

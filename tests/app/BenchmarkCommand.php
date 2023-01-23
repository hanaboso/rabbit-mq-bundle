<?php declare(strict_types=1);

namespace RabbitBundleTests\app;

use Hanaboso\Utils\String\Json;
use RabbitMqBundle\Publisher\Publisher;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BenchmarkCommand
 *
 * @package RabbitBundleTests\app
 */
final class BenchmarkCommand extends Command
{

    private const COUNT = 'count';

    /**
     * BenchmarkCommand constructor.
     *
     * @param Publisher $publisher
     */
    public function __construct(private readonly Publisher $publisher)
    {
        parent::__construct();
    }

    /**
     *
     */
    protected function configure(): void
    {
        $this->addArgument(self::COUNT, InputArgument::OPTIONAL, 'Count', '1000000');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output;

        $time  = hrtime(TRUE);
        $count = $input->getArgument(self::COUNT);
        $count = is_array($count) ? $count[0] : (int) $count;

        for ($i = 0; $i < $count; $i++) {
            $this->publisher->publish(Json::encode(['content' => sprintf('Content #%s', $i)]));
        }

        $time = hrtime(TRUE) - $time;

        $output->writeln(
            sprintf('Published %s messages in %ss: %s messages per second', $count, $time / 1e9, $count / $time * 1e9),
        );

        return 0;
    }

}

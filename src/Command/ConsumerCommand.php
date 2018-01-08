<?php declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: venca
 * Date: 2.1.18
 * Time: 13:46
 */

namespace RabbitMqBundle\Command;

use RabbitMqBundle\Consumer\Consumer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ConsumerCommand
 *
 * @package RabbitMqBundle\Command
 */
class ConsumerCommand extends Command
{

    /**
     * @var Consumer
     */
    private $consumer;

    /**
     * ConsumerCommand constructor.
     *
     * @param Consumer $consumer
     */
    public function __construct(Consumer $consumer)
    {
        parent::__construct();
        $this->consumer = $consumer;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->consumer->setup();

        $this->consumer->consume();
    }

}
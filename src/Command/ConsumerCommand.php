<?php declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: venca
 * Date: 2.1.18
 * Time: 13:46
 */

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
class ConsumerCommand extends Command
{

    /**
     * @var Consumer
     */
    private $consumer;

    /**
     * ConsumerCommand constructor.
     *
     * @param Consumer    $consumer
     * @param null|string $name
     */
    public function __construct(Consumer $consumer, ?string $name = NULL)
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
     * @return int|null|void
     * @throws Exception
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $input;
        $output;

        $this->consumer->setup();

        $this->consumer->consume();
    }

}
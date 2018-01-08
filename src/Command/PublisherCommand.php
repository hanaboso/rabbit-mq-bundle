<?php declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: venca
 * Date: 1/8/18
 * Time: 11:02 AM
 */

namespace RabbitMqBundle\Command;

use RabbitMqBundle\Publisher\Publisher;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class PublisherCommand
 *
 * @package RabbitMqBundle\Command
 */
class PublisherCommand extends Command
{

    /**
     * @var Publisher
     */
    private $publisher;

    /**
     * PublisherCommand constructor.
     *
     * @param Publisher $publisher
     */
    public function __construct(Publisher $publisher)
    {
        parent::__construct();
        $this->publisher = $publisher;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->publisher->publish('Test content');
    }

}
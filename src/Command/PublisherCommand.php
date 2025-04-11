<?php declare(strict_types=1);

namespace RabbitMqBundle\Command;

use Hanaboso\Utils\String\Json;
use RabbitMqBundle\Publisher\Publisher;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class PublisherCommand
 *
 * @package RabbitMqBundle\Command
 */
final class PublisherCommand extends Command
{

    private const string CONTENT = 'content';
    private const string HEADERS = 'headers';

    /**
     * PublisherCommand constructor.
     *
     * @param Publisher   $publisher
     * @param string|NULL $name
     */
    public function __construct(private readonly Publisher $publisher, ?string $name = NULL)
    {
        parent::__construct();

        if ($name) {
            $this->setName($name);
        }
    }

    /**
     *
     */
    protected function configure(): void
    {
        $this->addArgument(self::CONTENT, InputArgument::OPTIONAL, 'Message content', '{}');
        $this->addArgument(self::HEADERS, InputArgument::OPTIONAL, 'Message headers in JSON format', '{}');
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

        $content = $input->getArgument(self::CONTENT);
        $headers = $input->getArgument(self::HEADERS);

        $content = is_array($content) ? $content[0] : (string) $content;
        $headers = is_array($headers) ? $headers[0] : (string) $headers;

        $this->publisher->publish($content, Json::decode($headers));

        return 0;
    }

}

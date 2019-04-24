<?php declare(strict_types=1);

namespace Tests\Consumer;

use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use RabbitMqBundle\Connection\ClientFactory;
use RabbitMqBundle\Connection\Configurator;
use RabbitMqBundle\Connection\ConnectionManager;
use RabbitMqBundle\Consumer\AsyncConsumer;
use RabbitMqBundle\Publisher\Publisher;

/**
 * Class AsyncConsumerTest
 *
 * @package Tests\Consumer
 */
final class AsyncConsumerTest extends TestCase
{

    /**
     * @throws Exception
     */
    public function testConsumer(): void
    {
        $this->markTestSkipped('Endless loop consumer');
        $conn = new ConnectionManager(
            new ClientFactory([
                'default' => [
                    'host'              => 'rabbitmq',
                    'port'              => 5672,
                    'user'              => 'guest',
                    'password'          => 'guest',
                    'heartbeat'         => 60,
                    'timeout'           => 1,
                    'reconnect'         => TRUE,
                    'reconnect_tries'   => NULL,
                    'reconnect_timeout' => 1,
                ],
            ])
        );
        $conf = new Configurator([
            'exchanges' => [],
            'queues'    => [
                'que' => [
                    'arguments' => [
                        'bindings' => [
                            'exchange'    => 'que',
                            'routing_key' => 'que',
                        ],
                    ],
                ],
            ],
        ]);

        $publisher = new Publisher(
            $conn,
            $conf,
            'que'
        );

        $consumer = new AsyncConsumer(
            $conn,
            $conf,
            new AsyncCallback(),
            'que'
        );
        $consumer->setLogger(new Logger('nae', [new StreamHandler('php://stdout', Logger::DEBUG)]));

        $publisher->setup();
        $publisher->publish('asd');

        $consumer->setup();
        $consumer->consume();

        self::assertTrue(TRUE);
    }

}

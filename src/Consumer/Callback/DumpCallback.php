<?php declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: venca
 * Date: 8.1.18
 * Time: 7:09
 */

namespace RabbitMqBundle\Consumer\Callback;

use Bunny\Channel;
use Bunny\Client;
use Bunny\Message;
use RabbitMqBundle\Consumer\CallbackInterface;

/**
 * Class DumpCallback
 *
 * @package RabbitMqBundle\Consumer\Callback
 */
class DumpCallback implements CallbackInterface
{

    /**
     * @param Message $message
     * @param Channel $channel
     * @param Client  $client
     */
    public function processMessage(Message $message, Channel $channel, Client $client): void
    {
        var_dump($message);

        $channel->ack($message);
    }

}
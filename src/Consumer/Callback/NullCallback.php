<?php declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: venca
 * Date: 8.1.18
 * Time: 7:07
 */

namespace RabbitMqBundle\Consumer\Callback;

use Bunny\Channel;
use Bunny\Client;
use Bunny\Message;
use RabbitMqBundle\Consumer\CallbackInterface;

/**
 * Class NullCallback
 *
 * @package RabbitMqBundle\Consumer\Callback
 */
class NullCallback implements CallbackInterface
{

    /**
     * @param Message $message
     * @param Channel $channel
     * @param Client  $client
     */
    public function processMessage(Message $message, Channel $channel, Client $client): void
    {
        $channel->ack($message);
    }

}
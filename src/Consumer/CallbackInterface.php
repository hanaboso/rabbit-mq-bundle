<?php declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: venca
 * Date: 8.1.18
 * Time: 7:02
 */

namespace RabbitMqBundle\Consumer;

use Bunny\Channel;
use Bunny\Client;
use Bunny\Message;

/**
 * Interface CallbackInterface
 *
 * @package RabbitMqBundle\Consumer
 */
interface CallbackInterface
{

    /**
     * @param Message $message
     * @param Channel $channel
     * @param Client  $client
     */
    public function processMessage(Message $message, Channel $channel, Client $client): void;

}
<?php declare(strict_types=1);

use RabbitMqBundle\RabbitMqBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;

return [
    FrameworkBundle::class => ['all' => TRUE],
    RabbitMqBundle::class  => ['all' => TRUE],
];

Hanaboso RabbitMQ Bundle
========================

[![Build Status](https://travis-ci.org/hanaboso/rabbit-mq-bundle.svg?branch=master)](https://travis-ci.org/hanaboso/rabbit-mq-bundle)
[![Coverage Status](https://coveralls.io/repos/github/hanaboso/rabbit-mq-bundle/badge.svg?branch=master)](https://coveralls.io/github/hanaboso/rabbit-mq-bundle?branch=master)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%208-brightgreen)](https://img.shields.io/badge/PHPStan-level%208-brightgreen)
[![Downloads](https://img.shields.io/packagist/dt/hanaboso/rabbit-mq-bundle)](https://packagist.org/packages/hanaboso/rabbit-mq-bundle)

Installation
-----------
* Download package via composer
```bash
composer require hanaboso/rabbit-mq-bundle
```

Logger
------
* default stdout logger - monolog.logger.rabbit_mq

Config
------
```yaml
rabbit_mq:
    logger: 'monolog.logger.rabbit_mq'
    connections:
        default:
            dsn: amqp://rabbitmq:5672/

    queues:
        my-queue:
            arguments:
                my-arg: 'my-value'
            bindings:
                - exchange: 'my-exchange'
                  routing_key: 'routing-key'
                  arguments:
                    my-arg: 'my-value'
        my-safe-queue:
            durable: true
            arguments:
                x-queue-type: 'quorum'

    exchanges:
        my-exchange:
            type: 'direct'
            passive: false
            durable: false
            auto_delete: false
            internal: false
            no_wait: false
            arguments:
                my-arg: 'my-value'
            bindings:
               - exchange: 'my-exchange'
                 routing-key: 'routing-key'
                 arguments:
                    my-arg: 'my-value'

    publishers:
       my-publisher:
            routing_key: 'routing-key' # queue name or routing key
            exchange: 'my-exchange'
        my-safe-publisher:
            routing_key: 'routing-key'
            exchange: 'my-exchange'
            persistent: true
            acknowledge: true

    consumers:
        my-consumer:
            queue: 'my-queue'
            callback: rabbit_mq.null_callback            
```
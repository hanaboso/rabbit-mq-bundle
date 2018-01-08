RABBITMQ BUNDLE
===============

Config
------
```yaml
rabbit_mq:
    connections:
        default:
            host: rabbitmq

    queues:
        my-queue:
            arguments:
                my-arg: 'my-value'
            bindings:
                - exchange: 'my-exchange'
                  routing_key: 'routing-key'
                  arguments:
                    my-arg: 'my-value'

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
            mandatory: false
            immediate: false
            logger: 'rabbit-mq.logger'

    consumers:
        my-consumer:
            queue: 'my-queue'
            callback: rabbit_mq.null_callback            
```
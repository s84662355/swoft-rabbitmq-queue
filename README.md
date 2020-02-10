swoft框架 用rabbitmq实现队列

有普通的队列，延迟队列
生产者配置

            'rabbitmq-queue-producer' => [
                'class' => Producer::class,
                'rabbitmq_pool' => 'rabbitmq.pool',
                'connection_name' => 'default',
                'default_config'  => 'default',
                '__option' => [
                    'scope' => Bean::REQUEST
                ],
                'config'  => [

                    '1' => [
                        'durable' => true,
                        'delayed' => true,
                        'queue' => [

                            'name' => 'aaaaa423',
                        ]
                    ],


                    '2' => [
                        'durable' => true,
                        'exchange' => [
                            'name' => '22222',
                            'type' => 'direct',
                            'durable' => true,
                            'routing_key' => '2222',
                        ],
                        'queue' => [
                            'durable' => true,
                            'name' => '',
                        ]
                    ],

                    '3' => [

                        'durable' => true,
                        'delayed' => true,
                        'queue' => [
                            'durable' => true,
                            'name' => '1111',

                        ]
                    ],


                ],
            ],  
 
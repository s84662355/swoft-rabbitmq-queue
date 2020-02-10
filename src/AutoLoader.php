<?php declare(strict_types=1);


namespace cjhswoftRabbitmqQueue;


use Swoft\Bean\Annotation\Mapping\Bean;
use ReflectionException;
use Swoft\Bean\Exception\ContainerException;
use Swoft\SwoftComponent;

/**
 * Class AutoLoader
 *
 * @since 2.0
 */
class AutoLoader extends SwoftComponent
{
    /**
     * @return array
     */
    public function getPrefixDirs(): array
    {
        return [
            __NAMESPACE__ => __DIR__,
        ];
    }

    /**
     * @return array
     */
    public function metadata(): array
    {
        return [];
    }

    /**
     * @return array
     * @throws ReflectionException
     * @throws ContainerException
     */
    public function beans(): array
    {
        return [
            'rabbitmq-queue-controller'      => [
                'class'          => QueueController::class,
                'rabbitmq_pool'  => 'rabbitmq.pool',
                '__option' => [
                    'scope' => Bean::REQUEST
                ],
            ],

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
        ];
    }
}

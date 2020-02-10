<?php declare(strict_types=1);


namespace cjhswoftRabbitmqQueue;
use Throwable;
use Exception;
use CJHRabbitmq\MQDriver;
use cjhswoftRabbitmq\Connection\RabbitmqConnection;
use cjhswoftRabbitmq\Rabbitmq;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Concern\PrototypeTrait;

/**
 * Class TxPublisher
 *
 * @Bean(scope=Bean::PROTOTYPE)
 *
 * @since 2.0
 */
class TxPublisher
{
    use PrototypeTrait;
    public $producer = null;

    public function send(string  $body , $msg_driver_name = false){
        $this->producer->tx_send($body,$msg_driver_name);
    }

    /**
     *
     * @param array $items
     *
     * @return static
     */
    public static function new(array $items = []): self
    {
        $self        = self::__instance();
        [ $producer] = $items;
        $self->producer = $producer;
        return $self;
    }


}
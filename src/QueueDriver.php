<?php declare(strict_types=1);


namespace cjhswoftRabbitmqQueue;
use Throwable;
use Exception;
use cjhswoftRabbitmq\Connection\RabbitmqConnection;
use CJHRabbitmq\TxPublisher;
use CJHRabbitmq\MQPublisher;
use CJHRabbitmq\MQMessage;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Concern\PrototypeTrait;
use PhpAmqpLib\Wire\AMQPTable;
/**
 * Class QueueDriver  
 *
 * @Bean(scope=Bean::PROTOTYPE)
 *
 * @since 2.0
 */
class QueueDriver  
{
    use PrototypeTrait;

    public $connection = null;

    protected $exchange_pool = [];

    protected $queue_pool = [];

    protected $redis = null;

    public $connection_name = '';

    protected $config = null;

    public $channel = null;

    protected $publisher_instance = [];

    protected $publish_driver_config = [];

    protected $default_publish ;

    protected $consume_driver_config = [];

    protected $default_consume ;

    /**
     *
     * @param array $items
     *
     * @return static
     */
    public static function new(RabbitmqConnection $connection,string $connection_name): self
    {
        $self        = self::__instance();
      
        $self ->connection = $connection ;

        $self ->connection_name = $connection_name ;

        $self ->channel = $self->connection->channel();
        return $self;
    }

    public function setReadTimeOut($time_out = -1)
    {
         $this->connection->setReadTimeOut($time_out );
    }



    public function exchange(string $name, $type = 'direct', $durable = true)
    {
        if (empty($this->exchange_pool[$name])) {
            $this->channel->exchange_declare($name, $type, false, $durable, false);
            $this->exchange_pool[$name] = true;
        }
        return $this;
    }

    public function queue(string $name,  $durable = true , array $config = [])
    {
        if (empty($this->queue_pool[$name])) {


            $table = false;
            if (!empty($config)) {
                $table = new AMQPTable();
                foreach ($config as $key => $value) {
                    $table->set($key, $value);
                }
            }

            if ($table) {
                $this->channel->queue_declare(
                    $name,
                    false,
                    $durable,
                    false,
                    false,
                    false,
                    $table);
            } else {
                $this->channel->queue_declare(
                    $name,
                    false,
                    $durable,
                    false,
                    false,
                    false);
            }
            $this->queue_pool[$name] = true;
        }
        return $this;

    }

    public function cache_queue(string $name,  $durable,string  $dead_ex, string  $dead_key,int $expires)
    {


        $table = [
            'x-dead-letter-exchange' => $dead_ex,
            'x-dead-letter-routing-key' => $dead_key,
            'x-message-ttl' => intval( $expires) ,
        ];

        return  $this->queue($name , $durable, $table);
    }

    public function QueueBind(string $queue, string $exchange, string $routing_key)
    {
        $this->channel->queue_bind($queue, $exchange, $routing_key);
        return $this;
    }

    public function getChannel() 
    {
        return $this->channel;
    }

    public function __destruct()
    {
        if($this->connection) 
            $this->connection->close();
    }


    public function publisher(string $type): MQPublisher
    {
        switch ($type) {
            case 'confirm':
                return $this->getPublisher($type);
            case 'transaction':
                return $this->getPublisher($type, false);
            case 'common' :
                return $this->getPublisher($type, false);
        }
    }

    public function getPublisher(string $type,$confirm = true) : MQPublisher
    {
        if (empty($this->publisher_instance[$type])) {
            $this->publisher_instance[$type] = new MQPublisher($this->connection->channel(),$confirm);
        }
        return $this->publisher_instance[$type];
    }

 
 



    public function consume(array $config){
        /*
        if($consume_config_name )
        {
            $config = $this->consume_driver_config[$consume_config_name ];
        }else{
            $config = $this->consume_driver_config[$this->default_consume ];
        }
        */

        $this->init_consume($config);

        $consume = new MQConsume(
            $this->channel,
            $config['queue'],
            empty($config['consumer_tag']) ? $config['listener'] :  $config['consumer_tag'] ,
            $config['listener']
        );


        if(empty($config['max_count'] )){
            $consume-> setRedis(
                $this->redis,
                $this->connection_name
            );
        }else{
            $consume-> setRedis(
                $this->redis,
                $this->connection_name,
                intval( $config['max_count'] )
            );
        }


        if(!empty($config['log_path'])){
            $consume->setLogPath($config['log_path']);
        }
        return $consume  ;
    }


 



}

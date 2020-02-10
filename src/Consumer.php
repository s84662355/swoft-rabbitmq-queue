<?php declare(strict_types=1);


namespace cjhswoftRabbitmqQueue;
use Throwable;
use Exception;
use cjhswoftRabbitmq\Connection\RabbitmqConnection;
use cjhswoftRabbitmq\Rabbitmq ;
use CJHRabbitmq\TxPublisher;
use CJHRabbitmq\MQPublisher;
use CJHRabbitmq\MQMessage;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Concern\PrototypeTrait;
use PhpAmqpLib\Message\AMQPMessage;


/**
 * Class  Consumer 
 *
 * @Bean(scope=Bean::PROTOTYPE)
 *
 * @since 2.0
 */
class Consumer {

    use PrototypeTrait;
    private $callback = '';
    public $channel = null;
    public $queue = '';
    public $consumer_tag = '';

    private $message_id_Arr = [];
    private $redis = null;
    private $prefix = '';
    private $max_count = 5;

    private $log_path = false;

    public $driver ;

    /**
     *
     * @param array $items
     *
     * @return static
     */
    public static function new(QueueDriver  $driver,string $queue,string $consumer_tag ): self
    {
          $self        = self::__instance();
          $self->driver =  $driver;
          $self->channel =   $driver -> getChannel() ;
          $self->queue = $queue;
          $self->consumer_tag = $consumer_tag;
          $self->channel->basic_qos(
              null,
              1,
              null);

          $self->driver->setReadTimeOut();

          return $self;
    }



    public function basic_consume($listen)
    {
        $this->callback = $listen;

        call_user_func_array([$this->callback,'prepare'],[]);

        $this->channel->basic_consume
        (
            $this->queue, $this->consumer_tag,
            false,
            false,
            false,
            false,
            [$this,'process_message']
        );
        while($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }


    public function process_message(AMQPMessage $msg)
    {

        $res = AckStatus::ACK;
        $log_str = '  ';

        try{
            $body = $msg->getBody();

            $body = json_decode($body,true);

            $body_data =  base64_decode($body['body']);
            $log_str.= $body_data;



            if(
                (
                    !empty( $body['message_id'] )
                    ||
                     $msg->has('message_id')
                )

                &&
                !empty($this->redis))
            {

                if($msg->has('message_id'))
                {
                    $message_key = $this->prefix.$msg->get('message_id');
                }else{
                    $message_key = $this->prefix.$body['message_id'];
                }


 

                if($this->redis->incrby($message_key,1) > $this->max_count )
                {
                    $this->redis->del($message_key);//true
                    return   $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                }
                $this->redis->expire($message_key, 200);
                $this->callback->message_id = $body['message_id'];

            }

            $res = call_user_func_array([$this->callback,'process_message'],[ $body_data,$body['config']]);
            if(empty($res))
                $res =  AckStatus::REJECT_OUT;

            $log_str.= '   return '.$res;

        }catch (Throwable $e)
        {
           // $log_str.=$this->ErrorLog($e);

            $res = call_user_func_array([$this->callback,'error'],[ $body_data,$body['config'],$e]);

            if(empty($res ))
                $res =  AckStatus::REJECT;
        }



        ///   basicAck：成功消费，消息从队列中删除
        //   basicNack：requeue=true，消息重新进入队列，false被删除
        //   basicReject：等同于basicNack
        //   basicRecover：消息重入队列，requeue=true，发送给新的consumer，false发送给相同的consumer

        /*
 *    basicAck：成功消费，消息从队列中删除
   basicNack：requeue=true，消息重新进入队列，false被删除
   basicReject：等同于basicNack
   basicRecover：消息重入队列，requeue=true，发送给新的consumer，false发送给相同的consumer
 * */



        switch ($res){

            case   AckStatus::ACK: ///出列
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                break;

            case   AckStatus::REJECT://拒绝  回列
                $msg->delivery_info['channel']->basic_reject($msg->delivery_info['delivery_tag'],true);
                break;

            case   AckStatus::CANCEL://删除当前的消费者
                $msg->delivery_info['channel']->basic_cancel($msg->delivery_info['consumer_tag']);
                break;

            case   AckStatus::REJECT_OUT: //拒绝 并且出列
                $msg->delivery_info['channel']->basic_reject($msg->delivery_info['delivery_tag'],false);
                break;

            case  AckStatus::RECOVERTRUE://回列 发送给新的consumer
                $msg->delivery_info['channel']->basic_recover(true);
                break;

            case  AckStatus::RECOVERFALSE://回列 发送给相同的consumer
                $msg->delivery_info['channel']->basic_recover(false);
                break;

        }

      ///  if($this->log_path)
       //     LogService::instance($this->log_path)->info($log_str);


        //echo date('Y-m-d h:i:s');
        //echo $log_str;
        //echo PHP_EOL;

    }



    public function setRedisCounter($redis,$connection_name,$max_count = 1)
    {
         $this->redis = $redis;
         $this->connection_name = $connection_name;
         $this->max_count = $max_count;
         return $this;
    }


    public function setLogPath($log_path)
    {
         $this->log_path = $log_path;
         return $this;
    }



 


    public function init_consume(array  $config)
    {

        if(!empty($config['timedelay'])){

            $this->driver->exchange(
                'dead-exchange',
                'direct' ,
                true);

            $this->driver->cache_queue(
                $config['queue'],
                true ,
                'dead-exchange',
                'dead_'.$config['queue'].'_key',
                 intval($config['timedelay'])
            );
             

            $this->driver->queue
            (
                'cache_'.$config['queue'],
                $config['durable'],
                $config['arguments']
            );

             $this->driver->QueueBind(
                'cache_'.$config['queue'],
                'dead-exchange',
                'dead_'.$config['queue'].'_key'
            );

             $this->queue =  'cache_'.$config['queue'];



            /*
            $this->driver->cache_queue(
                'cache_'.$config['queue'],
                true ,
                'dead-exchange',
                'dead_'.$config['queue'].'_key',
                 intval($config['timedelay'])
            );
             

            $this->driver->queue
            (
                $config['queue'],
                $config['durable'],
                $config['arguments']
            );

             $this->driver->QueueBind(
                $config['queue'],
                'dead-exchange',
                'dead_'.$config['queue'].'_key'
            );
            */
        }else{
             $this->driver->queue
            (
                $config['queue'],
                $config['durable'],
                $config['arguments']
            );

            if(!empty($config['exchange'])){
                $this->driver->QueueBind(
                    $config['queue'],
                    $config['exchange'],
                    $config['queue']
                );
            }
        }

    }

}
<?php declare(strict_types=1);


namespace cjhswoftRabbitmqQueue;
use Throwable;
use Exception;
use cjhswoftRabbitmq\Connection\RabbitmqConnection;
use cjhswoftRabbitmq\Rabbitmq ;
use CJHRabbitmq\MQPublisher;
use CJHRabbitmq\MQMessage;
use Closure;
class  Producer extends AbstractClient {

    public function setDriver( QueueDriver $driver)
    {
         $this->driver = $driver;
    }

    public function setConfig(array $config)
    {
         $this->config = $config;
    }


    public function send(string $body,string $msg_driver_name ,  $confirm = true)
    {
        if($msg_driver_name ) {
            $config = $this->config[$msg_driver_name];
        }else{
            $config = $this->config[$this->default_config];
        }
       
        if (  $confirm  )
            return $this->message($body, $config ,$this->getDriver()->getPublisher('confirm'));

        return $this->message($body, $config ,$this->getDriver()->getPublisher('common'));
    }


    public function transaction(Closure $callback)
    {
        $publisher = $this->getDriver()->publisher('transaction');
        try{
            $publisher->getChannel()->tx_select();

           // $callback(new TxPublisher($this));
            $callback( TxPublisher::new([$this]));

            $publisher->getChannel()->tx_commit();
        }catch (Throwable $throwable){
            $publisher->getChannel()->tx_rollback();
            throw  $throwable;
        }
    }


    public function message(string $body, array $config , MQPublisher $publisher)
    {
        $msg_config = [];
        /*
        if(!empty($config['delayed']))
        {

             $msg_config['queue'] = 'cache_'.$config['queue']['name'];
        }else 
        */

        if(!empty($config['exchange'])){


            $this->getDriver()->exchange($config['exchange']['name'], $config['exchange']['type']  );
            #####################
           /// 交换机需要提前创建，代码里面不创建交换机
            #####################

            
            $this->getDriver()->QueueBind(
                $config['queue']['name'],
                $config['exchange']['name'],
                  $config['exchange']['routing_key']
            );
         

            $msg_config = [
               'routing_key' => $config['exchange']['routing_key'],
                'exchange' => $config['exchange']['name'],
            ];


        }else if(!empty($config['queue'])){

            $msg_config = [
                'queue' => $config['queue']['name'],
            ];
        }
        return $publisher->send(new MQMessage($body,$msg_config));
    }


    public function tx_send(string $body, string $msg_driver_name )
    {
        
        if($msg_driver_name ) {
            $config = $this->config[$msg_driver_name];
        }else{
            $config = $this->config[$this->default_config];
        }
     
        return $this->message($body, $config ,$this->getDriver()->publisher('transaction'));


    }
}
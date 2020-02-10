<?php declare(strict_types=1);


namespace cjhswoftRabbitmqQueue;
use Throwable;
use Exception;
use CJHRabbitmq\MQDriver;
use cjhswoftRabbitmq\Connection\RabbitmqConnection;
use cjhswoftRabbitmq\Rabbitmq;

/**
 * Class QueueController
 *
 * @since 2.0
 */
class QueueController  
{
    /**
     * @var string
     */ 
    private $rabbitmq_pool = 'rabbitmq.pool';

    /**
     * @var string
     */ 
    private $connection_name = 'default' ;


    /**
     * @var array
     */ 
    private $config  ; 



    private $queue_driver = null ;

    private function getDriver()
    {
         if(!empty($this->queue_driver))
         {
             return $this->queue_driver;
         }
         
         $this->queue_driver =  new QueueDriver( Rabbitmq::connection($this->rabbitmq_pool),$this->connection_name,$this->config ) ;
         return $this->queue_driver ;
    }

    /**
     * Pass other method calls down to the underlying client.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        $queue_driver = $this->getDriver();
        
        return call_user_func_array(array(  $queue_driver, $method), $parameters);
    }
 

}

<?php

namespace cjhswoftRabbitmqQueue;

use App\Model\Entity\User;
use Swoft\Db\Exception\DbException;
use Swoft\Log\Helper\CLog;
use Swoft\Process\Annotation\Mapping\Process;
use Swoft\Process\Contract\ProcessInterface;
use Swoft\Redis\Redis;
use Swoole\Coroutine;
use Swoole\Process\Pool;
use Swoft\Co;
use function context;
use function bean;
use Swoft\Bean\BeanFactory;
use Swoft;
use cjhswoftRabbitmq\Rabbitmq ;
use Swoft\Redis\Redis as SwoftRedis;

/**
 * Class  AbstractProcess 
 *
 * @since 2.0
 *
 */
abstract class  AbstractProcess  implements ProcessInterface{

    /**
     * @var string
     */ 
    protected $consumer_tag = '';

    /**
     * @var string
     */ 
    protected $redis_pool = '';
    

    /**
     * @var string
     */ 
    protected $rabbitmq_pool = '';

    /**
     * @var string
     */ 
    protected $connection_name = 'default' ;

    /**
     * @var Pool 
     */ 
    protected $pool  ;


    /**
     * @var int
     */ 
    protected $workerId  ;

    /**
     * @var array
     */  
    protected $queue_config = [];

    /**
     * @var int
     */ 
    protected $max_count = 1;


	abstract public function process_message($body,$config) ;

    abstract public function error($body,$config,$e) ;

    abstract public function prepare();


    /**
     * @param Pool $pool
     * @param int  $workerId
     */
    public function run(Pool $pool, int $workerId): void
    {
       
        $this->pool = $pool;
        $this->workerId = $workerId;
  
        $consumer = Consumer::new(QueueDriver::new( Rabbitmq::connection($this->rabbitmq_pool ) ,$this->connection_name ), $this->queue_config['queue']   , $this->consumer_tag); 

        if(!empty($this->redis_pool)){
           $consumer->setRedisCounter(  SwoftRedis::connection($this->redis_pool) ,$this->connection_name ,$this->max_count  );
        }

        $consumer->init_consume( $this->queue_config);


        $consumer-> basic_consume($this);
    
 
    }


}

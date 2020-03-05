<?php

namespace cjhswoftRabbitmqQueue;

use App\Model\Entity\User;
use Swoft\Db\Exception\DbException;
use Swoft\Log\Helper\CLog;
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
use Swoft\Process\UserProcess;
use Swoft\Process\Process;

/**
 * Class  AbstractUserProcess
 *
 * @since 2.0
 *
 */
abstract class  AbstractUserProcess extends UserProcess{

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

    protected $coroutine_count = 1;

    public  $process;


	abstract public function process_message($body,$config,$msgid) ;

    abstract public function error($body,$config,$msgid,$e) ;

    abstract public function prepare();


    /**
     * @param Process $process
     *
     */
    public function run(Process $process): void
    {
        $this->process = $process;
       
        \Swoole\Runtime::enableCoroutine(true);

        $that = $this;


        for ($i=0; $i < $this->coroutine_count ; $i++) { 
            Co::create(function () use ($that){
                  $that->createConsumer();
            });
        }

        while(true){
            sleep(5);
            CLog::info( "  rabbitmq queue UserProcess master  Coroutine   " );
        }
 
    }

    public function createConsumer()
    {
        $consumer = Consumer::new(QueueDriver::new( Rabbitmq::connection($this->rabbitmq_pool ) ,$this->connection_name ), $this->queue_config['queue']   , $this->consumer_tag); 

        if(!empty($this->redis_pool)){
           $consumer->setRedisCounter(  SwoftRedis::connection($this->redis_pool) ,$this->connection_name ,$this->max_count  );
        }


        $consumer->init_consume( $this->queue_config);
        $consumer-> basic_consume($this);

    }
}

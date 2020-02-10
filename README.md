swoft框架 用rabbitmq实现队列
这个composer 是依赖chenjiahao/swoft-rabbitmq-pool 所以先学会配置这个rabbitmq的连接池

composer require chenjiahao/swoft-rabbitmq-queue
里面是用的协程客户端连接rabbitmq服务器

有普通的队列，使用交换机绑定队列 ，延迟队列 ， 事务提交，确认机制，应答机制
所有消息默认持久化

生产者bean配置
 
    
    'rabbitmq-queue-producer' => [
        'class' => cjhswoftRabbitmqQueue\Producer::class,
        'rabbitmq_pool' => 'rabbitmq.pool', ///rabbitmq连接池的key
        'connection_name' => 'default', //连接名称。可以自定义一个名称
        '__option' => [
            'scope' => Swoft\Bean\Annotation\Mapping\Bean::REQUEST //bean的类型
        ],
        'config'  => [ //发送消息的配置
         
            'default-queue' => [     //发送给队列aaaaa423
                'queue' => [
                    'name' => 'aaaaa423',
                ]
            ],

            'test-exchange' => [ //发送到交换机22222, 并且绑定队列 2222
               
                'exchange' => [
                    'name' => '22222',
                    'type' => 'direct',
                    'durable' => true,
                    'routing_key' => '2222',
                ],
                'queue' => [
                    'durable' => true,
                    'name' => '2222',
                ]
            ],
        ],
    ],

生产者使用

$obj = BeanFactory::getRequestBean('rabbitmq-queue-producer' , (string)$tid);

$body需要发送的字符串，$msg_driver_name 发送的配置key,  $confirm 是否启动确认机制默认启动，如果发送失败会抛出异常
// send(string $body,string $msg_driver_name ,  $confirm = true)
$obj->send("default",'default-queue');


事务提交形式发送
$obj-> transaction(function($txPublisher){
    for($i = 0;$i<1;$i++){
    /// send(string $body,string $msg_driver_name  )
        $txPublisher->send("vsvsd",'default-queue');
    }
});





消费者配置
这里是使用swoft的进程池 首先需要自定义进程 继承的是 cjhswoftRabbitmqQueue\AbstractProcess
例如这样


use Swoft\Log\Helper\CLog;
use Swoft\Process\Annotation\Mapping\Process;
use Swoft\Process\Contract\ProcessInterface;
use Swoole\Coroutine;
use Swoole\Process\Pool;
use cjhswoftRabbitmqQueue\AbstractProcess;
use cjhswoftRabbitmqQueue\AckStatus;
use Exception;

/**
 * Class TestProcess
 *
 * @since 2.0
 *
 * @Process(workerId={0,1})
 */
class TestProcess extends AbstractProcess
{
  

    /**
     * @var string
     */ 
    protected $consumer_tag = '33333'; ///自定义一个消费者的名称

    /**
     * @var string
     */ 
    protected $redis_pool = 'redis.pool'; //redis的连接池
    

    /**
     * @var string
     */ 
    protected $rabbitmq_pool = 'rabbitmq.pool'; //rabbitmq的连接池

    /**
     * @var string
     */ 
    protected $connection_name = 'default1111111111' ; //连接名称
 

    /**
     * @var array
     */  
    protected $queue_config = [
        'durable' => true, //队列持久化
        'queue' => '3333',//队列名称 
        'timedelay' => 10000,//延迟队列，需要使用时才配置
        'arguments' => [] //队列的其他参数，例如队列长度
    ]  ;


    /**
     * @var Pool 
     */ 
    protected $pool  ; //这个是进程池


    /**
     * @var int
     */ 
    protected $workerId  ; //进程的工作id



    /**
     * @var int
     */ 
    protected $max_count = 1; //消息重试次数，默认为1，即是消息只会被处理1次，如果想失败重试可以设置为2，3.。。。

    
    //进程开始时执行的方法 
    public function prepare()
    {
    
          CLog::info(" sfdjlgjdkfljgkfdljgdfkljgdflkgdfjklgdfjlgdfjkl");
    
    }


 //消息回调的方法
  public function process_message( $body,$config)
  {
    
      CLog::info("   --worker--{$this->workerId}--" .$body);
      
     /// throw new Exception("dsadsa");


     /*

  const ACK = 200; ///处理完成 出列
  const REJECT = 300;//拒绝  并且回列
  const CANCEL = 400;//删除当前的消费者
  const REJECT_OUT = 500;//拒绝 并且出列
  const RECOVERTRUE = 600;//回列 发送给新的consume
  const RECOVERFALSE = 700;//回列 发送给相同的consumer


     */
      
      return AckStatus::ACK ;
    
  }
  

  //如果process_message方法抛出异常 就会执行这个方法
  public function error( $body,$config,$e) 
  {
     /*

  const ACK = 200; ///处理完成 出列
  const REJECT = 300;//拒绝  并且回列
  const CANCEL = 400;//删除当前的消费者
  const REJECT_OUT = 500;//拒绝 并且出列
  const RECOVERTRUE = 600;//回列 发送给新的consume
  const RECOVERFALSE = 700;//回列 发送给相同的consumer


     */
      
      return AckStatus::ACK ;
  }
  
  
 
}
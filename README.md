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
    protected $consumer_tag = '33333';

    /**
     * @var string
     */ 
    protected $redis_pool = 'redis.pool';
    

    /**
     * @var string
     */ 
    protected $rabbitmq_pool = 'rabbitmq.pool';

    /**
     * @var string
     */ 
    protected $connection_name = 'default1111111111' ;


    /**
     * @var array
     */  
    protected $queue_config = [
        'durable' => true,
        'queue' => '1111',
        'timedelay' => 10000,
        'arguments' => []
    ]  ;


    /**
     * @var int
     */ 
    protected $max_count = 1;
    
    public function prepare(){
    
              CLog::info(" sfdjlgjdkfljgkfdljgdfkljgdflkgdfjklgdfjlgdfjkl");
    
    }


    public function process_message( $body,$config)
    {
        
          CLog::info("   --worker--{$this->workerId}--" .$body);
          
          throw new Exception("dsadsa");
          
          return AckStatus::ACK ;
        
    }
    
    public function error( $body,$config,$e) 
    {
          CLog::info(" error  --worker--{$this->workerId}--" .$body);
          
          return AckStatus::ACK ;
    }
    
    
 
}
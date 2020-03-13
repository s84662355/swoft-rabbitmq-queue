<?php declare(strict_types=1);

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


/**
 * Class  AbstractClient
 *
 * @since 2.0
 *
 */
abstract class  AbstractClient {

    /**
     * @var string
     */ 
    protected $rabbitmq_pool = 'rabbitmq.pool';

    /**
     * @var string
     */ 
    protected $connection_name = 'default' ;

    /**
     * @var string
     */ 
    protected $default_config = 'default' ;

    /**
     * @var array
     */ 
    protected $config  ; 

    protected $driver = null;

    protected function getDriver()
    {
        if(!empty($this->driver)){
            return $this->driver;
        }
 

        $this->driver =  QueueDriver::new( Rabbitmq::connection($this->rabbitmq_pool ),  $this->connection_name ); 

        return  $this->driver;
    }

    

}

swoft框架的 rabbitmq 连接池 

composer require chenjiahao/swoft-rabbitmq-pool


            'rabbitmq-config'      => [
                'class'    => RabbitmqConfig::class, //   /cjhswoftRabbitmq/RabbitmqConfig::class
                'host'     => '127.0.0.1',
                'port'     => 5672,
                'vhost'    => '/',
                'username' => 'guest',
                'password' => 'guest',
             
            ],


            'rabbitmq.pool' => [
                'class'   => Pool::class, //   /cjhswoftRabbitmq/Pool::class
                'rabbitmqConfig' => bean('rabbitmq-config'),
                'mark'  => 'rabbitmq_pool', //连接池的唯一标识符，必须唯一 ， 不能重复
                'minActive'   => 10,
                'maxActive'   => 20,
                'maxWait'     => 0,
                'maxWaitTime' => 0,
                'maxIdleTime' => 40,
            ]



使用::

获取连接
\cjhswoftRabbitmq\Rabbitmq::connection(string $pool =  'rabbitmq.pool' )
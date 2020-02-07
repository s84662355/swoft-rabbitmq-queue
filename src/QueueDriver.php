<?php declare(strict_types=1);


namespace cjhswoftRabbitmqQueue;
use Throwable;
use Exception;
use CJHRabbitmq\MQDriver;
use cjhswoftRabbitmq\Connection\RabbitmqConnection;

/**
 * Class QueueDriver
 *
 * @since 2.0
 */
class QueueDriver extends MQDriver
{

    public function __construct(RabbitmqConnection $connection , string $connection_name   , array $config)
    {
        $this->connection = $connection ;

        $this->connection_name =   $connection_name ;

        $this->config = $config;

        $this->channel = $this->connection->channel();

        $this->publish_driver_config = $config['publish']['driver'];

        $this->default_publish = $config['publish']['default'];

        $this->consume_driver_config = $config['consume']['driver'];

        $this->default_consume = $config['consume']['default'];

    }

}

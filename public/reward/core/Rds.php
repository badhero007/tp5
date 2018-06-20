<?php
/**
 * Created by PhpStorm.
 * User: lego
 * Date: 2017/7/27
 * Time: 14:15
 */


class Rds
{
    private $redishost = '127.0.0.1';
    private $redisport = '6379';
    private $timeout = 3;

    //-覆盖-单例模式Start
    private static $instance = [];

    public static function getInstance($configName = 'REDIS')
    {
        if(empty(self::$instance['configName'])) new self($configName);
        return self::$instance[$configName];
    }

    public function __construct($configName = 'REDIS_CLUSTER')
    {
        if (!empty(self::$instance[$configName])) {
            return self::$instance[$configName];
        }
        $redis = new \Redis();
        if($redis->connect($this->redishost, $this->redisport, $this->timeout)) {

            $redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
            self::$instance[$configName] = $redis;
        } else {
            throw new \Exception("redis is down");
        }
    }

    public function __clone() { throw new \Exception('Clone is not allowed !'); }
    //-覆盖-单例模式End

}
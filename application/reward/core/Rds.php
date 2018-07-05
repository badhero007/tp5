<?php
/**
 * Created by PhpStorm.
 * User: lego
 * Date: 2017/7/27
 * Time: 14:15
 */

namespace app\reward\core;


class Rds
{
    //-覆盖-单例模式Start
    private static $instance = [];

    public static function getInstance($configName = 'REDIS_CLUSTER')
    {
        if(empty(self::$instance['configName'])) new self($configName);
        return self::$instance[$configName];
    }

    public function __construct($configName = 'REDIS_CLUSTER')
    {
        if (!empty(self::$instance[$configName])) {
            return self::$instance[$configName];
        }
        if (!config('REDIS.'.$configName)) {
            throw new \Exception("redis config not exists", 6000);
        }

        $configInfo = config('REDIS.'.$configName);
        $redis = new \Predis\Client($configInfo['host'], array('cluster' => 'redis'));

        self::$instance[$configName] = $redis;
    }

    public function __clone() { throw new \Exception('Clone is not allowed !'); }
    //-覆盖-单例模式End

}
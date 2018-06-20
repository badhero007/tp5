<?php
/**
 * Created by PhpStorm.
 * User: lego
 * Date: 2017/12/13
 * Time: 11:20
 */

return array(
    
    // +----------------------------------------------------------------------
    // | redis设置
    // +----------------------------------------------------------------------
    'REDIS' => array(
        'REDIS_CLUSTER' => array(
            'host' =>  array(
                "tcp://dj-redis-1.daojia.com.cn:6379",
                "tcp://dj-redis-2.daojia.com.cn:6379",
                "tcp://dj-redis-3.daojia.com.cn:6379",
                "tcp://dj-redis-4.daojia.com.cn:6379",
                "tcp://dj-redis-5.daojia.com.cn:6379",
                "tcp://dj-redis-6.daojia.com.cn:6379",
            ),
        ),
        'REDIS_CLUSTER_HOST_DATA'    =>  array(
            'host' => array(
                "tcp://business-redis-1.daojia.com.cn:20000",
                "tcp://business-redis-2.daojia.com.cn:20000",
                "tcp://business-redis-3.daojia.com.cn:20000",
                "tcp://business-redis-4.daojia.com.cn:20000",
                "tcp://business-redis-5.daojia.com.cn:20000",
                "tcp://business-redis-6.daojia.com.cn:20000",
            ),
        ),
    ),
);
<?php
/**
 * Created by PhpStorm.
 * User: lego
 * Date: 2017/12/13
 * Time: 11:03
 */

return array(
    
    // +----------------------------------------------------------------------
    // | redis设置
    // +----------------------------------------------------------------------
    'REDIS' => array(
        'REDIS_CLUSTER' => array(
            'host' =>  array(
                "tcp://192.168.0.101:7000",
                "tcp://192.168.0.101:7001",
                "tcp://192.168.0.102:7000",
                "tcp://192.168.0.102:7001",
                "tcp://192.168.0.103:7000",
                "tcp://192.168.0.103:7001",
            ),
        ),
        'REDIS_CLUSTER_HOST_DATA'    =>  array(
            'host' => array(
                "tcp://192.168.0.101:7000",
                "tcp://192.168.0.101:7001",
                "tcp://192.168.0.102:7000",
                "tcp://192.168.0.102:7001",
                "tcp://192.168.0.103:7000",
                "tcp://192.168.0.103:7001",
            ),
        ),
    ),
);


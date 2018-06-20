<?php
/**
 * Created by PhpStorm.
 * User: lego
 * Date: 2017/12/21
 * Time: 10:45
 */

require_once '../core/Rds.php';
require_once '../core/common.php';

$redis = Rds::getInstance();
if(!$redis->get('rewards')){
    $rewards = [];
    $redis->set('rewards',serialize($rewards));
}

$rewards = unserialize($redis->get('rewards'));
if(!$_POST['sum'] || !$_POST['duration'] || !$_POST['type'] || !$_POST['times'] || !$_POST['date_start'] || !$_POST['date_end']){
    return '缺少必要参数';
}

//生成唯一ID
$id = generateNum();
$reward = [
    'id' => $id,
    'sum' => $_POST['sum'],
    'type' => $_POST['type'],
    'duration' => $_POST['duration'],
    'times' => $_POST['times'],
    'date_start' => $_POST['date_start'],
    'date_end' => $_POST['date_end'],
];
$rewards[$id] = $reward;

//存储奖励
if($redis->set('rewards',serialize($rewards))){
    return 'ok';
}
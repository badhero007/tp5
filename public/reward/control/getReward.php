<?php
/**
 * Created by PhpStorm.
 * User: lego
 * Date: 2017/12/20
 * Time: 17:42
 */

    require_once '../core/Rds.php';
    require_once '../core/common.php';

    $uid = $_POST['uid'];
    $rid = $_POST['rid'];
    if(!request()->isPost()){
        return '无效请求';
    }

//	$rid = $_GET['rid'];
//  $uid = $_GET['uid'];

    //参数完整性校验
    if(!$uid || !$rid){
        return '缺少必要参数';
    }

    //校验奖励是否可用
    $data = json_decode(checkReward($rid),true);
    if(!$data['data']){
        echo $data['msg'];
    }
    $reward = $data['data'];

    //校验用户是否可领取
    $record = json_decode(checkUserReward($rid,$uid,$reward),true);
    if(!$record['data']){
        echo $record['msg'];
    }
    $record = $record['data']['record'];

    //所有领取记录
    $redis = Rds::getInstance();
    $allRecord = $redis->get('allrecord');
    if(!$allRecord){
        $allRecord = [];
        $redis->set('allrecord',serialize($allRecord));
    } else {
        $allRecord = unserialize($allRecord);
    }

    //获取该次领取积分
    $points = current($record['rewards']);
    array_shift($record['rewards']);
    $data = [
        'rid' => $rid,
        'todayTime'=>$record['todayTime'],//当天次数
        'lastTime'=>time(),
        'rewards'=>$record['rewards'],
        'days'=>$record['days']
    ];
    //记录领取记录
    array_push($allRecord,[
        'rid' => $rid,
        'time' => date('Y-m-d H:i:s'),
        'user' => $uid,
        'points' => $points
    ]);

    $redis->set('allrecord',serialize($allRecord));
    $key = md5($uid.$rid);
    $redis->set($key,serialize($data));

	function checkReward($rid){
        $redis = Rds::getInstance();
        $rewards = unserialize($redis->get('rewards'));
        $result = ['data' => false,'msg' => 'success'];
        if(!$rewards){
            $result['msg'] = '奖励不存在';
            return json_encode($result);
        }

        if(!isset($rewards[$rid])){
            $result['msg'] = '奖励不存在';
            return json_encode($result);
        }

        $reward = $rewards[$rid];

        if(strtotime($reward['date_start']) > time()){
            $result['msg'] = '奖励未生效';
            return json_encode($result);
        }

        if(strtotime($reward['date_end']) < time()){
            $result['msg'] = '奖励已过期';
            return json_encode($result);
        }

        $result['data'] = $reward;
        $result['msg'] = 'success';

        return json_encode($result);
    }

    function checkUserReward($rid,$uid,$reward){
        $result = ['data' => false , 'msg' => 'success'];

        $redis = Rds::getInstance();
        $key = md5($uid.$rid);
        //首次领取增加缓存记录
        if(!$redis->get($key)){

            switch ($reward['type']) {
                case 'type':
                    $arr = distribute($reward['duration']*$reward['times'],$reward['sum'],0);
                    break;
                case 'average':
                    $arr = average($reward['sum'],$reward['duration']*$reward['times']);
                    break;
                default:
                    $arr = distribute($reward['duration']*$reward['times'],$reward['sum'],0);
            }

            $data = [
                'rid' => $rid,
                'rewards'=>array_values($arr),
                'todayTime'=>0,//当天次数
                'lastTime'=>time(),//上次领取时间
                'days'=>1,//累计领取天数
            ];
            $redis->set($key,serialize($data));
        }
        //用户领取记录
        $record = unserialize($redis->get($key));

        //上次领取时间小于今天,初始化今天领取记录
        if($record['lastTime'] < strtotime(date('Y-m-d'))){
            $record['days'] += 1;
            $record['todayTime'] = 1;
        } else {
            $record['todayTime'] += 1;
        }

        if($record['days'] > $reward['duration']){
            $result['msg'] = '已超过领取天数';
            return json_encode($result);
        }

        if($record['todayTime'] > $reward['times']){
            $result['msg'] = '已达到当天领取上限,明天再来吧';
            return json_encode($result);
        }

        $result['data'] = ['record'=>$record];

        return json_encode($result);
    }

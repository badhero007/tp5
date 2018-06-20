<?php

/**
 * Created by PhpStorm.
 * User: lego
 * Date: 2017/12/12
 * Time: 20:40
 */
namespace app\reward\controller;

use app\reward\Core\Rds;
use think\Controller;

class Reward extends Controller
{

    public function createReward(){

        $redis = Rds::getInstance();
        if(!$redis->get('rewards')){
            $rewards = [];
            $redis->set('rewards',serialize($rewards));
        }

        $rewards = unserialize($redis->get('rewards'));
        if(!request()->post('sum') || !request()->post('duration') || !request()->post('times') || !request()->post('date_start') || !request()->post('date_end')){
            return '缺少必要参数';
        }

        //生成唯一ID
        $id = generateNum();
        $reward = [
            'id' => $id,
            'sum' => request()->post('sum'),
            'type' => request()->post('type'),
            'duration' => request()->post('duration'),
            'times' => request()->post('times'),
            'date_start' => request()->post('date_start'),
            'date_end' => request()->post('date_end'),
        ];
        $rewards[$id] = $reward;

        //存储奖励
        if($redis->set('rewards',serialize($rewards))){
            return 'ok';
        }
    }

    public function create(){
        return $this->fetch('create');
    }

    public function export(){
        $redis = Rds::getInstance();
        $data = unserialize($redis->get('allrecord'));
        $finalData = [
            'title' => '领取明细',
            'rows' => $data,
            'map' => [
                ['key' => '领取时间','value' => 'time'],
                ['key' => '用户','value' => 'user'],
                ['key' => '领取积分','value' => 'points'],
                ['key' => '奖励id','value' => 'rid']
            ]
        ];
        exportExcel($finalData);
    }

    public function index(){
        $params = request()->param();
        $nowpage = isset($params['page']) ? $params['page'] : 1;
        $rid = isset($_POST['rid']) ? $_POST['rid'] : '';
        $uid = isset($_POST['uid']) ? $_POST['uid'] : '';
        $export = isset($_POST['export']) ? $_POST['export'] : '';
        $redis = Rds::getInstance();
        $data = unserialize($redis->get('allrecord'));
        if($rid){
            foreach ($data as $key => $val) {
                if ($data[$key]['rid'] != $rid) unset($data[$key]);
            }
        }
        if($uid){
            foreach ($data as $key => $val) {
                if ($data[$key]['user'] != $uid) unset($data[$key]);
            }
        }
        if($export){
            $finalData = [
                'title' => '领取明细',
                'rows' => $data,
                'map' => [
                    ['key' => '领取时间','value' => 'time'],
                    ['key' => '用户','value' => 'user'],
                    ['key' => '领取积分','value' => 'points'],
                    ['key' => '奖励id','value' => 'rid']
                ]
            ];
            exportExcel($finalData);
        }

        $limits = 20;
        $pages = ceil(count($data)/$limits);
        if(!$data){
            $records = [];
        } else {
            $records = array_slice($data,($nowpage-1)*$limits,$limits);
        }

        return $this->fetch('index',['records'=>$records,'pages'=>$pages,'nowpage'=>$nowpage,'rid'=>$rid,'uid'=>$uid]);
    }
    
    public function rewards(){
        $redis = Rds::getInstance();
        $rewards = unserialize($redis->get('rewards'));
        return $this->fetch('rewards',['rewards'=>$rewards]);
    }

    public function getReward(){
//        $uid = request()->post('uid');
//        $rid = request()->post('rid');
//        if(!request()->isPost()){
//            return '无效请求';
//        }

        $rid = request()->get('rid');
        $uid = request()->get('uid');

        //参数完整性校验
        if(!$uid || !$rid){
            return '缺少必要参数';
        }

        //校验奖励是否可用
        $data = json_decode($this->checkReward($rid),true);
        if(!$data['data']){
            return $data['msg'];
        }
        $reward = $data['data'];

        //校验用户是否可领取
        $record = json_decode($this->checkUserReward($rid,$uid,$reward),true);
        if(!$record['data']){
            return json_encode($record['msg']);
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
        var_dump($data);
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

    }

    public function checkReward($rid){
        $redis = Rds::getInstance();
        $rewards = unserialize($redis->get('rewards'));
        $result = ['data' => false,'msg' => 'success'];
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

    public function checkUserReward($rid,$uid,$reward){
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
        var_dump(unserialize($redis->get($key)));
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

    public function delete(){
        $params = request()->param();

        $rid = isset($params['rid']) ? $params['rid'] : '';
        if($rid){
            $redis = Rds::getInstance();
            $rewards = unserialize($redis->get('rewards'));
            if($rewards[$rid]){
                unset($rewards[$rid]);
                $redis->set('rewards',serialize($rewards));
            }
        }
        $this->redirect('/reward/reward/rewards');
    }

    public function edit(){
        $params = request()->param();
        $rid = isset($params['rid']) ? $params['rid'] : '';
        if($rid){
            $redis = Rds::getInstance();
            $rewards = unserialize($redis->get('rewards'));
            if($rewards[$rid]){
                return $this->fetch('edit',['reward' => $rewards[$rid]]);
            }
        }
    }
    
    public function editReward(){
        $rid = request()->post('rid');
        $redis = Rds::getInstance();
        $rewards = unserialize($redis->get('rewards'));
        $reward = $rewards[$rid];
        $dateStart = request()->post('date_start');
        $dateEnd = request()->post('date_end');

        $days = getDays($dateStart,$dateEnd);
        if($days < $reward['duration']){
            echo '有效期不能小于奖励持续天数';
        } else {
            $reward['date_start'] = $dateStart;
            $reward['date_end'] = $dateEnd;
            unset($rewards[$rid]);
            array_push($rewards,$reward);
            $redis->set('rewards',serialize($rewards));
            $this->redirect('/reward/reward/rewards');
        }

    }

    public function demo(){
        //防止执行超时
        set_time_limit(0);
        //需要循环的数据
        for($i = 0; $i < 188; $i++)
        {
            $users[] = 'Tom_' . $i;
        }

        //计算数据的长度
        $total = count($users);

        //显示的进度条长度，单位 px
        $width = 100;

        //每条记录的操作所占的进度条单位长度
        $pix = $width / $total;
        //默认开始的进度条百分比
        $progress = 0;
        return $this->fetch('demo',['users' => $users,'pix' => $pix,'width' => $width,'progress' => $progress]);
    }

}
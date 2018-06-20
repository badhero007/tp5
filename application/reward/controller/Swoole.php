<?php
/**
 * Created by PhpStorm.
 * User: legolas
 * Date: 2017/11/30
 * Time: 15:14
 */

namespace app\reward\controller;

use app\reward\core\LegoLog;
use app\reward\core\swooleClient;
use app\reward\core\swooleServer;

class Swoole
{
    public function testFork(){
        $pc = 10;
        $to = 4;
        $ts = 4;

        if(!function_exists('pcntl_fork')){
            die('pcntl_fork not existing');
        }

        $sPipePath = "my_pipe".posix_getpid();

        if(!posix_mkfifo($sPipePath,0666)){
            die("make pipe".$sPipePath.' error');
        }

        for ($i = 0;$i < $pc;$i++) {
            $nPid = pcntl_fork();
            if ($nPid == 0) {//子进程写入管道
                sleep(rand(1,$ts));
                $oW = fopen($sPipePath,'w');
                fwrite($oW,$i.'-');
                fclose($oW);
                exit(0);
            }
        }

        $oR = fopen($sPipePath,'r');
        stream_set_blocking($oR,false);
        $sData = '';
        $nLine = 0;
        $nStart = time();
        while ($nLine < $pc && (time() - $nStart) < $to) {
            $sLine = fread($oR,1024);
            if (empty($sLine)) {
                continue;
            }

            echo 'current line:'.$sLine.PHP_EOL;

            foreach (str_split($sLine) as $c ) {
                if ('-' == $c) {
                    ++$sLine;
                }
            }

            $sData .= $sLine;
        }

        echo 'final line count:'.$nLine.PHP_EOL;
        fclose($oR);
        unlink($sPipePath);

        // 回收子进程，避免僵尸进程
        $n = 0;
        while ($n < $pc) {
            $nStatus = -1;
            $nPID = pcntl_wait($nStatus, WNOHANG);
            if ($nPID > 0) {
                echo "{$nPID} exit".PHP_EOL;
                ++$n;
            }
        }

        // 验证结果，主要查看结果中是否每个任务都完成了
        $arr2 = array();

        foreach(explode("-", $sData) as $i) {// trim all
            if (is_numeric(trim($i))) {
                array_push($arr2, $i);
            }
        }

        $arr2 = array_unique($arr2);

        if ( count($arr2) == $pc) {
            echo 'ok';
        } else {
            echo  "error count " . count($arr2) . PHP_EOL;
            var_dump($arr2);
        }
    }

    public function start(){
        $swooleServer = new swooleServer();
        $swooleServer->start();
    }


    public function testSwoole(){
        //创建Server对象，监听 127.0.0.1:9501端口
        $serv = new swoole_server("127.0.0.1", 9501);

        //监听连接进入事件
        $serv->on('connect', function ($serv, $fd) {
            echo "Client: Connect.\n";
        });

        //监听数据接收事件
        $serv->on('receive', function ($serv, $fd, $from_id, $data) {
            $serv->send($fd, "Server: ".$data);
        });

        //监听连接关闭事件
        $serv->on('close', function ($serv, $fd) {
            echo "Client: Close.\n";
        });

        //启动服务器
        $serv->start();
    }

    public function send(){
        $client = swooleClient::getInstance();

        for ($i = 0;$i < 10;$i++) {
            $client->send('hello world'.$i);
        }
    }
}

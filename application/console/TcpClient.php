<?php
/**
 * Created by PhpStorm.
 * User: lego
 * Date: 2018/6/7
 * Time: 18:35
 */

namespace app\console;


use app\reward\core\swooleClient;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class TcpClient extends Command
{
    protected function configure()
    {
        $this->setName('tcpClient')->setDescription('swoole客户端');
    }

    protected function execute(Input $input, Output $output)
    {
        $swooleClient = new swooleClient();
        $swooleClient->setHost('127.0.0.1');
        $swooleClient->setPort(9501);
        $swooleClient->setTimeout(5);
        $swooleClient->setKeep(false);
        for ($i = 0;$i < 2 ;$i++) {
            $swooleClient->send('ASYNC','hello world!');
        }
    }
}
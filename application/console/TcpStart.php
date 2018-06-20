<?php
namespace app\console;
use app\reward\Core\swooleClient;
use app\reward\Core\swooleServer;
use think\console\Command;
use think\console\Input;
use think\console\Output;

/**
 * Created by PhpStorm.
 * User: lego
 * Date: 2017/7/28
 * Time: 16:50
 */

class TcpStart extends Command
{
    protected function configure()
    {
        $this->setName('tcpStart')->setDescription('swoole启动');
    }

    protected function execute(Input $input, Output $output)
    {
        $swooleServer = new swooleServer();
        $swooleServer->start();

    }
}

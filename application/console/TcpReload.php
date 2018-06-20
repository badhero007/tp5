<?php
/**
 * Created by PhpStorm.
 * User: lego
 * Date: 2018/6/8
 * Time: 11:38
 */

namespace app\console;


use app\reward\Core\swooleServer;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class TcpReload extends Command
{
    protected function configure()
    {
        $this->setName('tcpReload')->setDescription('swoole重新加载');
    }

    protected function execute(Input $input, Output $output)
    {
        $swooleServer = new swooleServer();
        $swooleServer->reload();

    }
}
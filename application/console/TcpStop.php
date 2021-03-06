<?php
namespace app\console;
use app\reward\core\swooleClient;
use app\reward\core\swooleServer;
use think\console\Command;
use think\console\Input;
use think\console\Output;

/**
 * Created by PhpStorm.
 * User: lego
 * Date: 2017/7/28
 * Time: 16:50
 */

class TcpStop extends Command
{
    protected function configure()
    {
        $this->setName('tcpStop')->setDescription('swoole停止');
    }

    protected function execute(Input $input, Output $output)
    {
        $swooleServer = new swooleServer();
        $swooleServer->stop();

    }
}

<?php
namespace app\console;

use app\reward\Core\newWebSocket;
use app\reward\Core\webSocket;
use think\console\Command;
use think\console\Input;
use think\console\Output;

/**
 * Created by PhpStorm.
 * User: lego
 * Date: 2017/7/28
 * Time: 16:50
 */

class WebSocketStart extends Command
{
    protected function configure()
    {
        $this->setName('webSocketStart')->setDescription('webSocket启动');
    }

    protected function execute(Input $input, Output $output)
    {
        $webSocket = new newWebSocket();
        $webSocket->start();
    }
}

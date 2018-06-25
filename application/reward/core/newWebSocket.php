<?php
/**
 * Created by PhpStorm.
 * User: lego
 * Date: 2017/12/5
 * Time: 16:01
 */

namespace app\reward\Core;


class newWebSocket
{

    private $server;

    /**
     * 服务主进程名称
     * @var null
     */
    static $sw_process_name = 'lego_websocket_process';

    /**
     * 主进程PID文件
     */
    static $sw_master_pid_file = "/tmp/lego_websocket.pid";

    /**
     * 管理进程PID文件
     */
    static $sw_manager_pid_file = "/tmp/lego_websocket_manager.pid";

    private $host = '127.0.0.1';//ip

    private $port = 9501;//监听端口swoole默认9501

    private $mode = SWOOLE_PROCESS;//默认多进程模式

    private $sock_type = SWOOLE_SOCK_TCP;//socket类型默认tcp

    private $max_conn = 256;//最大连接数

    private $daemonize = true;//守护进程化,加入此参数后，执行php server.php将转入后台作为守护进程运行

    private $reactor = 4;//通过此参数来调节poll线程的数量，以充分利用多核,reactor_num和writer_num默认设置为CPU核数

    private $max_request = 2000;//此参数表示worker进程在处理完n次请求后结束运行。manager会重新创建一个worker进程。此选项用来防止worker进程内存溢出。

    private $dispatch_mode = 1;//进程数据包分配模式,1平均分配，2按FD取模固定分配，3抢占式分配，默认为取模(dispatch=2)

    private $log_file = '/data/logs/swoole_websocket.log';

    private $worker_num = 8;//设置启动的worker进程数量,worker_num配置为CPU核数的1-4倍即可

    private $heartbeat_check_interval = 30;//每隔多少秒检测一次,单位秒，Swoole会轮询所有TCP连接，将超过心跳时间的连接关闭掉

    private $heartbeat_idle_time = 60; //TCP连接的最大闲置时间，单位s , 如果某fd最后一次发包距离现在的时间超过heartbeat_idle_time会把这个连接关闭。

    private $sw_config = [];

    /**
     * @param array $sw_config
     */
    public function setSwConfig($sw_config)
    {
        $this->sw_config = $sw_config;
    }

    public function start()
    {
        $this->server = new \swoole_websocket_server($this->host, $this->port);

        $self_config = array(
            'reactor_num' => $this->reactor,
            'worker_num' => $this->worker_num,
            'backlog' => 128,
            'max_request' => 50,
            'dispatch_mode' => $this->dispatch_mode,
            'heartbeat_check_interval' => $this->heartbeat_check_interval,
            'heartbeat_idle_time' => $this->heartbeat_idle_time,
            'max_conn' => $this->max_conn,
            'daemonize' => $this->daemonize,
            'log_file' => $this->log_file,
        );

        $this->setSwConfig($self_config);

        $this->server->set($self_config);

        $this->server->on('handshake', [$this,'onHandshake']);

        $this->server->on('open', [$this,'onOpen']);

        $this->server->on('message', [$this,'onMessage']);

        $this->server->on('close', [$this,'onClose']);

        $this->server->on('request', [$this,'onRequest']);

        $this->server->start();

    }

    /**
     * 握手
     * @param \swoole_http_request $request
     * @param \swoole_http_response $response
     * @return bool
     */
    public function onHandshake(\swoole_http_request $request, \swoole_http_response $response) {
        // print_r($request->header );
        // if (如果不满足我某些自定义的需求条件，那么返回end输出，返回false，握手失败) {
        //    $response->end();
        //     return false;
        // }

        // websocket握手连接算法验证
        $secWebSocketKey = $request->header['sec-websocket-key'];
        $patten = '#^[+/0-9A-Za-z]{21}[AQgw]==$#';
        if (0 === preg_match($patten, $secWebSocketKey) || 16 !== strlen(base64_decode($secWebSocketKey))) {
            $response->end();
            return false;
        }

        echo $request->header['sec-websocket-key'];
        $key = base64_encode(sha1(
            $request->header['sec-websocket-key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11',
            true
        ));

        $headers = [
            'Upgrade' => 'websocket',
            'Connection' => 'Upgrade',
            'Sec-WebSocket-Accept' => $key,
            'Sec-WebSocket-Version' => '13',
        ];
        // WebSocket connection to 'ws://127.0.0.1:9502/'
        // failed: Error during WebSocket handshake:
        // Response must not include 'Sec-WebSocket-Protocol' header if not present in request: websocket
        if (isset($request->header['sec-websocket-protocol'])) {
            $headers['Sec-WebSocket-Protocol'] = $request->header['sec-websocket-protocol'];
        }

        foreach ($headers as $key => $val) {
            $response->header($key, $val);
        }

        $response->status(101);

        $response->end();
        echo "connected!" . PHP_EOL;
        return true;
    }

    public function onOpen(\swoole_websocket_server $server, $request){
        echo "server: handshake success with fd{$request->fd}\n";
    }

    public function onMessage(\swoole_websocket_server $server, $frame){
        echo "[".date('Y-m-d H:i:s')."]"."receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
        $server->push($frame->fd, "[".date('Y-m-d H:i:s')."]"."this is server");
    }

    public function onClose($ser, $fd){
        echo "client {$fd} closed\n";
    }

    public function onRequest($request, $response){
        // 接收http请求从get获取message参数的值，给用户推送
        // $this->server->connections 遍历所有websocket连接用户的fd，给所有用户推送
        foreach ($this->server->connections as $fd) {
            $this->server->push($fd, $request->get['message']);
        }
    }
}
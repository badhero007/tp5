<?php
/**
 * Created by PhpStorm.
 * User: lego
 * Date: 2017/12/5
 * Time: 16:01
 */

namespace app\reward\core;


class swooleServer
{
    /**
     * 服务主进程名称
     * @var null
     */
    static $sw_process_name = 'lego_process';

    /**
     * 主进程PID文件
     */
    static $sw_master_pid_file = "/tmp/lego_swoole_master_sw.pid";

    /**
     * 管理进程PID文件
     */
    static $sw_manager_pid_file = "/tmp/lego_swoole_manager_sw.pid";

    private $host = '127.0.0.1';//ip

    private $port = 9501;//监听端口swoole默认9501

    private $mode = SWOOLE_PROCESS;//默认多进程模式

    private $sock_type = SWOOLE_SOCK_TCP;//socket类型默认tcp

    private $task_worker_num = 8;//工作进程数

    private $max_conn = 256;//最大连接数

    private $daemonize = false;//守护进程化,加入此参数后，执行php server.php将转入后台作为守护进程运行

    private $reactor = 4;//通过此参数来调节poll线程的数量，以充分利用多核,reactor_num和writer_num默认设置为CPU核数

    private $max_request = 2000;//此参数表示worker进程在处理完n次请求后结束运行。manager会重新创建一个worker进程。此选项用来防止worker进程内存溢出。

    private $dispatch_mode = 1;//进程数据包分配模式,1平均分配，2按FD取模固定分配，3抢占式分配，默认为取模(dispatch=2)

    private $log_file = '/data/logs/swoole.log';

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

    /**
     * 启动服务
     */
    public function start()
    {
        $serv = new \swoole_server($this->host,$this->port);
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
            'task_worker_num' => $this->task_worker_num
        );

        $this->setSwConfig($self_config);

        $serv->set($self_config);

        if(isset($self_config["task_worker_num"])){
            //在task_worker进程内被调用。worker进程可以使用swoole_server_task函数向task_worker进程投递新的任务
            $serv->on('Task',array($this,"onTask"));

            //当worker进程投递的任务在task_worker中完成时，task进程会通过swoole_server->finish()方法将任务处理的结果发送给worker进程
            $serv->on('Finish', array($this,"onFinish"));
        }

        $serv->on('start', array($this, 'onStart'));
        $serv->on('connect', array($this,'onConnect'));
        $serv->on('receive', array($this,'onReceive'));

        $serv->start();

    }

    /**
     * 停止运行
     */
    public function stop(){
        if(file_exists(self::$sw_master_pid_file)){

            $master_pid = file_get_contents(self::$sw_master_pid_file);
            if(posix_kill($master_pid,SIGTERM)){
                if(PHP_OS == "Darwin"){
                    //                    posix_kill($master_pid,SIGKILL);
                    exec("kill -9 ".$master_pid);
                }
                echo "THE SERVER IS STOP SUCCESS.".PHP_EOL;
            }else{
                echo "THE SERVER IS STOP ERROR.".PHP_EOL;
            }
            unlink(self::$sw_master_pid_file);
            unlink(self::$sw_manager_pid_file);
        }else{
            echo "ERROR : THE SERVER NOT EXISTS.".PHP_EOL;
        }

    }

    /**
     * 重新加载
     */
    public function reload(){
        if(file_exists(self::$sw_manager_pid_file)){
            $manager_pid = file_get_contents(self::$sw_manager_pid_file);

            posix_kill($manager_pid,SIGUSR1);

            echo "THE SERVER IS RELOAD SUCCESS.".PHP_EOL;
        }else{
            echo "ERROR : THE SERVER SW_MANAGER_PID NOT EXISTS.".PHP_EOL;
        }
    }

    /**
     * 启动服务
     * @param $server
     */
    public function onStart($server){
        file_put_contents(self::$sw_master_pid_file, $server->master_pid,LOCK_EX);

        file_put_contents(self::$sw_manager_pid_file, $server->manager_pid,LOCK_EX);

        if(! is_null(self::$sw_process_name)){
            if(function_exists("cli_set_process_title")){
                @cli_set_process_title(self::$sw_process_name);
            }else{
                swoole_set_process_name(self::$sw_process_name);
            }
            echo "[" . date("Y-m-d H:i:s") . "]\t"."the server master process_name : " . self::$sw_process_name . PHP_EOL;;
        }

        echo "[" . date("Y-m-d H:i:s") . "]\t"."the server master pid : ". $server->master_pid . PHP_EOL;
        echo "[" . date("Y-m-d H:i:s") . "]\t"."the server manager pid : " . $server->manager_pid . PHP_EOL;

        file_put_contents(self::$sw_master_pid_file, $server->master_pid,LOCK_EX);
        file_put_contents(self::$sw_manager_pid_file, $server->manager_pid,LOCK_EX);
    }

    /**
     * 链接
     * @param $serv
     * @param $fd
     */
    public function onConnect($serv, $fd){
        echo "[" . date("Y-m-d H:i:s") . "] " . "Client:Connect.\n";
        echo "[" . date("Y-m-d H:i:s") . "] " . "master_pid : " . $serv->master_pid . PHP_EOL;
        echo "[" . date("Y-m-d H:i:s") . "] " . "manager_pid : " . $serv->manager_pid . PHP_EOL;
        echo "[" . date("Y-m-d H:i:s") . "] " . "worker_id : " . $serv->worker_id . PHP_EOL;
        echo "[" . date("Y-m-d H:i:s") . "] " . "worker_pid : " . $serv->worker_pid . PHP_EOL;
        echo "[" . date("Y-m-d H:i:s") . "] " . "fd : " . $fd . PHP_EOL;
    }

    /**
     * 接受数据处理
     * @param $serv
     * @param $fd
     * @param $from_id
     * @param $data
     */
    public function onReceive($serv, $fd, $from_id, $data){
        $fdinfo = $serv->connection_info($fd,$from_id,true);

        $log = "[".date("Y-m-d H:i:s")."]\t";
        $log .= $fdinfo['remote_ip']."\t";
        $log .= $serv->master_pid."\t";
        $log .= $serv->manager_pid."\t";
        $log .= $serv->taskworker."\t";
        $log .= $serv->worker_id."\t";
        $log .= $serv->worker_pid."\t";
        $log .= $fd."\t";
        $log .= var_export($data,1).PHP_EOL;

        echo $log;



        $newdata = array('d' => $data,'fd' => $fd);
        //投递异步任务
        if(isset($this->sw_config["task_worker_num"])) {
            $serv->task($newdata);
            $sendData = 'task';
        } else {
            $sendData = 'no task';
        }

        if($serv->exist($fd)){
            $serv->send($fd, $sendData);
        }
    }

    /**
     * 异步任务处理
     * @param $serv
     * @param $task_id
     * @param $from_id
     * @param $data
     */
    public function onTask($serv, $task_id, $from_id, $data){
        echo "[".date('Y-m-d H:i:s')."]\tNew AsyncTask[id=$task_id]".PHP_EOL;
    }

    /**
     * 结束
     * @param $serv
     * @param $task_id
     * @param $data
     */
    public function onFinish($serv, $task_id, $data){
        echo "AsyncTask[$task_id] Finish: $data".PHP_EOL;
    }


}
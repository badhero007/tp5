<?php
/**
 * Created by PhpStorm.
 * User: lego
 * Date: 2018/6/8
 * Time: 16:25
 */

namespace app\reward\Core;


class swooleClient
{
    private $_host = null;

    private $_port = null;

    private $_timeout = null;

    protected $_Keep = null;

    protected $_data = null;

    private static $instance;

    public static function getInstance(){
        if(empty(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    /**
     * @param null $host
     */
    public function setHost($host)
    {
        $this->_host = $host;
    }

    /**
     * @param null $port
     */
    public function setPort($port)
    {
        $this->_port = $port;
    }

    /**
     * @param null $timeout
     */
    public function setTimeout($timeout)
    {
        $this->_timeout = $timeout;
    }

    /**
     * @param null $Keep
     */
    public function setKeep($Keep)
    {
        $this->_Keep = $Keep;
    }

    /**
     * @param null $data
     */
    public function setData($data)
    {
        $this->_data = $data;
    }

    public function send($type,$msg){
        $this->setData($msg);
        if($type == 'ASYNC'){
            $client = new \swoole_client(SWOOLE_SOCK_TCP,SWOOLE_SOCK_ASYNC);//异步
            //绑定事件
            $client->on('connect', [$this, 'onConnect']);
            $client->on('receive', [$this, 'onReceive']);
            $client->on('close', [$this, 'onClose']);
            $client->on('error', [$this, 'onError']);
            $client->connect($this->_host, $this->_port, $this->_timeout, $this->_Keep);
            if (!$client->isConnected()) {
                //todo 记录链接失败日志
            }
        } else {
            $client = new \swoole_client(SWOOLE_SOCK_TCP);//同步
            $client->connect($this->_host, $this->_port, $this->_timeout, $this->_Keep);
            $client->send($msg);
            $data = @$client->recv();
            echo $data;
        }
    }

    public function onConnect($client){
        $client->send($this->_data);
    }

    public function onError($client){
        LegoLog::getInstance()->log('swoole',date('Ymd').'.log',"Connect failed");
    }

    public function onReceive($client,$data = ''){
        echo $data.PHP_EOL;
        LegoLog::getInstance()->log('swoole',date('Ymd').'.log',"Received: ".$data);
    }

    public function onClose($client){
        LegoLog::getInstance()->log('swoole',date('Ymd').'.log',"Connection close");
    }
}
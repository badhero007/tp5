<?php
/**
 * Created by PhpStorm.
 * User: lego
 * Date: 2017/12/7
 * Time: 15:32
 */

namespace app\reward\core;


class AsyncSwooleClient
{
    private $_host = null;
    
    private $_port = null;
    
    private $_timeout = null;

    protected $_Keep = null;

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

    public function send($msg){
        $client = new \swoole_client(SWOOLE_SOCK_TCP,SWOOLE_SOCK_ASYNC);//异步非阻塞
        $client->connect($this->_host, $this->_port, $this->_timeout, $this->_Keep);
        $client->send($msg);
        $data = @$client->recv();
        echo $data;
    }

    public function onConnect($client){
        $client->send("hi\n");
    }

    public function onError($client){
        LegoLog::getInstance()->log('swoole',date('Ymd').'.log',"Connect failed");
    }

    public function onReceive($client,$data = ''){
        echo $data.PHP_EOL;
        if(empty($data)){
            $client->close();
            echo "closed".PHP_EOL;
        } else {
            echo "received: $data".PHP_EOL;
            sleep(1);
            $client->send("hello\n");
        }
        LegoLog::getInstance()->log('swoole',date('Ymd').'.log',"Received: ".$data);

    }

    public function onClose($client){
        LegoLog::getInstance()->log('swoole',date('Ymd').'.log',"Connection close");
    }

}
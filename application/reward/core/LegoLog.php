<?php
/**
 * Created by PhpStorm.
 * User: lego
 * Date: 2017/12/7
 * Time: 11:18
 */

namespace app\reward\core;


class LegoLog
{
    //-覆盖-单例模式Start
    private static $instance;
    private $logPath = LOG_PATH;

    public static function getInstance()
    {
        if(empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function log($path,$fileName,$content){

        $logpath = $this->logPath.'/'.$path;
        $logfile = $this->logPath.'/'.$path.'/'.$fileName;
        if(!is_dir($logpath)) createDirectory($logpath);
        if(!is_file($logfile)) touchFile($logfile,0777);
        $content = date('Y-m-d H:i:s ').$content.PHP_EOL;
        $op = fopen($logfile,'a');
        fwrite($op,$content);
    }
}
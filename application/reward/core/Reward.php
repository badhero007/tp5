<?php
namespace app\reward\core;
class Reward{

    private static $instance;
    private $sum;
    private $duration;
    private $times;
    private $max;
    private $min;

    //单例模式
    public static function getInstance()
    {
        if(empty(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    public function getSum(){
        return $this->sum;
    }

    public function setSum($sum){
        $this->sum = $sum;
    }

    public function getDuration(){
        return $this->duration;
    }

    public function setDuration($duration){
        $this->duration = $duration;
    }

    public function getTimes(){
        return $this->times;
    }

    public function setTimes($times){
        $this->times = $times;
    }
    
    public function getMax(){
        return $this->max;
    }
    
    public function setMax($max){
        $this->max = $max;
    }
    
    public function getMin(){
        return $this->min;
    }
    
    public function setMin($min){
        $this->min = $min;
    }


    //防止拷贝
    public function __clone() { throw new \Exception('Clone is not allowed !'); }


}
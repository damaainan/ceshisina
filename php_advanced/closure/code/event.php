<?php
/*
 * PHP 事件机制
 */
class baseClass{

    private $_e;
    
    public function __set($name,$value){
        if( strncasecmp($name,"on",2) === 0 ){
            if(!isset($this->_e[$name]))
                $this->_e[$name] = array();
            return array_push($this->_e[$name] , $value);
        }
    }
    
    public function __get($name){
        if( strncasecmp($name,"on",2) === 0 ){
            if(!isset($this->_e[$name]))
                $this->_e[$name] = array();
            return $this->_e[$name];
        }
    }

    public function raiseEvent($name, $parse){
         if(isset($this->_e[$name])){
             print_r($this->_e[$name]);
             foreach($this->_e[$name] as $handler)
                call_user_func($handler,$parse);
         }
    }

    public function save(){
        //xxx
        $this->raiseEvent("onSave", array());
    }
}
$InsA = new baseClass();
//1
$InsA->onSave = function($e){
    echo $e;
};

//2
class Log{
   static public function saveLog($e){
        echo "save Log".$e;
   }
}
$InsA->onSave = array("Log" , "saveLog");

$InsA->raiseEvent('onClick','success');
<?php 

header("Content-type:text/html; Charset=utf-8");

/**
 *
 *
 *
 *
 * 
 */

//抽象化角色
abstract class MiPhone{
    protected $_audio;      //存放音频软件对象
    abstract function output();
    public function __construct(Audio $audio){
        $this->_audio = $audio;
    }
}
//具体手机
class Mix extends MiPhone{
    //语音输出功能
    public function output(){
        $this->_audio->output();
    }
}
class Note extends MiPhone{
    public function output(){
        $this->_audio->output();
    }
}
//实现化角色 功能实现者
abstract class Audio{
    abstract function output();
}
//具体音频实现者 -骨传导音频输出
class Osteophony extends Audio{
    public function output(){
        echo "骨传导输出的声音-----哈哈".PHP_EOL;
    }
}
//普通音频输出---声筒输出
class Cylinder extends Audio{
    public function output(){
        echo "声筒输出的声音-----呵呵".PHP_EOL;
    }
}

//让小米mix和小米note输出声音
$mix = new Mix(new Osteophony);
$mix->output();
$note = new Note(new Cylinder);
$note->output();
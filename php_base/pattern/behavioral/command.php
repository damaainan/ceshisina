<?php 
/**
 * 命令模式
-------

现实例子
> 一个普遍的例子是你在餐馆点餐。你 (即调用者 `Client`) 要求服务员 (即调用器 `Invoker`) 端来一些食物 (即命令 `Command`)，而服务员只是简单的把命令传达给知道怎么做菜的厨师 (即接收者 `Receiver`)。另一个例子是你 (即调用者 `Client`) 打开 (即命令 `Command`) 电视 (即接收者 `Receiver`)，通过使用遥控 (调用器 `Invoker`).

白话
> 允许你封装对象的功能。此模式的核心思想是分离调用者和接收者。
 */


//一个接收者，包含了每一个可执行的功能的实现
// Receiver
class Bulb {
    public function turnOn() {
        echo "Bulb has been lit";
    }
    
    public function turnOff() {
        echo "Darkness!";
    }
}

// 一个命令执行的接口，一个命令的集合
interface Command {
    public function execute();
    public function undo();
    public function redo();
}

// Command
class TurnOn implements Command {
    protected $bulb;
    
    public function __construct(Bulb $bulb) {
        $this->bulb = $bulb;
    }
    
    public function execute() {
        $this->bulb->turnOn();
    }
    
    public function undo() {
        $this->bulb->turnOff();
    }
    
    public function redo() {
        $this->execute();
    }
}

class TurnOff implements Command {
    protected $bulb;
    
    public function __construct(Bulb $bulb) {
        $this->bulb = $bulb;
    }
    
    public function execute() {
        $this->bulb->turnOff();
    }
    
    public function undo() {
        $this->bulb->turnOn();
    }
    
    public function redo() {
        $this->execute();
    }
}

// 一个执行器 `Invoker`，调用者可以通过它执行命令
// / Invoker
class RemoteControl {
    
    public function submit(Command $command) {
        $command->execute();
    }
}


// 使用
$bulb = new Bulb();

$turnOn = new TurnOn($bulb);
$turnOff = new TurnOff($bulb);

$remote = new RemoteControl();
$remote->submit($turnOn); // Bulb has been lit!
$remote->submit($turnOff); // Darkness!

// 命令模式也可以用来实现一个基础系统的事务。当你要一直在执行命令后马上维护日志。如果命令被正确执行，一切正常，否则沿日志迭代，一直对每个已执行的命令执行撤销 `undo` 。
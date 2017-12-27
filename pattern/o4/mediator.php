<?php

abstract class Colleague {
    protected $mediator;
    abstract public function sendMsg($who, $msg);
    abstract public function receiveMsg($msg);
    public function setMediator(Mediator $mediator) {
        $this->mediator = $mediator;
    }
}

class ColleagueA extends Colleague {
    public function sendMsg($toWho, $msg) {
        echo "Send Msg From ColleagueA To: " . $toWho . '<br>';
        $this->mediator->opreation($toWho, $msg);
    }

    public function receiveMsg($msg) {
        echo "ColleagueA Receive Msg: " . $msg . '<br>';
    }
}

class ColleagueB extends Colleague {
    public function sendMsg($toWho, $msg) {
        echo "Send Msg From ColleagueB To: " . $toWho . '<br>';
        $this->mediator->opreation($toWho, $msg);
    }

    public function receiveMsg($msg) {
        echo "ColleagueB Receive Msg: " . $msg . '<br>';
    }
}

abstract class Mediator {
    abstract public function opreation($id, $message);
    abstract public function register($id, Colleague $colleague);
}

class MyMediator extends Mediator {
    protected static $colleagues;
    function __construct() {
        if (!isset(self::$colleagues)) {
            self::$colleagues = [];
        }
    }

    public function opreation($id, $message) {
        if (!array_key_exists($id, self::$colleagues)) {
            echo "colleague not found";
            return;
        }
        $colleague = self::$colleagues[$id];
        $colleague->receiveMsg($message);
    }

    public function register($id, Colleague $colleague) {
        if (!in_array($colleague, self::$colleagues)) {
            self::$colleagues[$id] = $colleague;
        }
        $colleague->setMediator($this);
    }
}

$colleagueA = new ColleagueA();
$colleagueB = new ColleagueB();
$mediator = new MyMediator();
$mediator->register(1, $colleagueA);
$mediator->register(2, $colleagueB);
$colleagueA->sendMsg(2, 'hello admin');
$colleagueB->sendMsg(1, 'shiyanlou');
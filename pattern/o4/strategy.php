<?php
abstract class Strategy {
    abstract function use ();
}

class StrategyA extends Strategy {
    public function use () {
        echo "这是使用策略A的方法 <br>";
    }
}

class StrategyB extends Strategy {
    public function use () {
        echo "这是使用策略B的方法 <br>";
    }
}

class Context {
    protected $startegy;
    public function setStrategy(Strategy $startegy) {
        $this->startegy = $startegy;
    }

    public function use () {
        $this->startegy->use();
    }
}

$context = new Context();
$startegyA = new StrategyA();
$startegyB = new StrategyB();
$context->setStrategy($startegyA);
$context->use();

$context->setStrategy($startegyB);
$context->use();
<?php

class Adaptee {
    public function realRequest() {
        echo "这是被适配者真正的调用方法";
    }
}

interface Target {
    public function request();
}

class Adapter implements Target {
    protected $adaptee;
    function __construct(Adaptee $adaptee) {
        $this->adaptee = $adaptee;
    }

    public function request() {
        echo "适配器转换：";
        $this->adaptee->realRequest();
    }
}

$adaptee = new Adaptee();
$target = new Adapter($adaptee);
$target->request();
<?php
interface Subject {
    public function request();
}

class RealSubject implements Subject {
    public function request() {
        echo "RealSubject::request <br>";
    }
}

class Proxy implements Subject {
    protected $realSubject;
    function __construct() {
        $this->realSubject = new RealSubject();
    }

    public function beforeRequest() {
        echo "Proxy::beforeRequest <br>";
    }

    public function request() {
        $this->beforeRequest();
        $this->realSubject->request();
        $this->afterRequest();
    }

    public function afterRequest() {
        echo "Proxy::afterRequest <br>";
    }
}

$proxy = new Proxy();
$proxy->request();
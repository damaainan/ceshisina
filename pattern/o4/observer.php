<?php

abstract class Obeserver {
    abstract function update(Subject $sub);
}

abstract class Subject {
    protected static $obeservers;
    function __construct() {
        if (!isset(self::$obeservers)) {
            self::$obeservers = [];
        }
    }
    public function attach(Obeserver $obeserver) {
        if (!in_array($obeserver, self::$obeservers)) {
            self::$obeservers[] = $obeserver;
        }
    }
    public function deattach(Obeserver $obeserver) {
        if (in_array($obeserver, self::$obeservers)) {
            $key = array_search($obeserver, self::$obeservers);
            unset(self::$obeservers[$key]);
        }
    }
    abstract public function setState($state);
    abstract public function getState();
    public function notify() {
        foreach (self::$obeservers as $key => $value) {
            $value->update($this);
        }
    }
}

class MySubject extends Subject {
    protected $state;
    public function setState($state) {
        $this->state = $state;
    }

    public function getState() {
        return $this->state;
    }
}

class MyObeserver extends Obeserver {
    protected $obeserverName;
    function __construct($name) {
        $this->obeserverName = $name;
    }
    public function update(Subject $sub) {
        $state = $sub->getState();
        echo "Update Obeserver[" . $this->obeserverName . '] State: ' . $state . '<br>';
    }
}

$subject = new MySubject();
$one = new MyObeserver('one');
$two = new MyObeserver('two');

$subject->attach($one);
$subject->attach($two);
$subject->setState(1);
$subject->notify();
echo "--------------------- <br>";
$subject->setState(2);
$subject->deattach($two);
$subject->notify();
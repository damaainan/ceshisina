<?php 

header("Content-type:text/html; Charset=utf-8");

/**
 *
 *
 *
 *
 * 
 */


class person {
    public $age;
    public $speed;
    public $knowledge;
}
//抽象建造者类
abstract class Builder{
    public $_person;
    public abstract function setAge();
    public abstract function setSpeed();
    public abstract function setKnowledge();
    public function __construct(Person $person){
        $this->_person=$person;
    }
    public function getPerson(){
        return $this->_person;
    }
}
//长者建造者
class OlderBuider extends Builder{
    public function setAge(){
        $this->_person->age=70;
    }
    public function setSpeed(){
        $this->_person->speed="low";
    }
    public function setKnowledge(){
        $this->_person->knowledge='more';
    }
}
//小孩建造者
class ChildBuider extends Builder{
    public function setAge(){
        $this->_person->age=10;
    }
    public function setSpeed(){
        $this->_person->speed="fast";
    }
    public function setKnowledge(){
        $this->_person->knowledge='litte';
    }
}
//建造指挥者
class Director{
    private $_builder;
    public function __construct(Builder $builder){
        $this->_builder = $builder;
    }
    public function built(){
        $this->_builder->setAge();
        $this->_builder->setSpeed();
        $this->_builder->setKnowledge();
    }
}
//实例化一个长者建造者
$oldB = new OlderBuider(new Person);
//实例化一个建造指挥者
$director = new Director($oldB);
//指挥建造
$director->built();
//得到长者
$older = $oldB->getPerson();

var_dump($older);
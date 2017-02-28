<?php
/**
 * 建造者模式
--------------------------------------------
现实例子
> 想象你在麦当劳，你要一个“巨无霸”，他们马上就给你了，没有疑问，这是简单工厂的逻辑。但如果创建逻辑包含更多步骤。比如你想要一个自定义赛百味套餐，你有多种选择来制作汉堡，例如你要哪种面包？你要哪种调味酱？你要哪种奶酪？等。这种情况就需要建造者模式来处理。

白话
> 让你能创建不同特点的对象而避免构造函数污染。当一个对象都多种特点的时候比较实用。或者在创造逻辑里有许多步骤的时候。


**何时使用？**

当对象有多种特性而要避免构造函数变长。和工厂模式的核心区别是；当创建过程只有一个步骤的时候使用工厂模式，而当创建过程有多个步骤的时候使用创造者模式。

 */


class Burger {
    protected $size;

    protected $cheese = false;
    protected $pepperoni = false;
    protected $lettuce = false;
    protected $tomato = false;
    
    public function __construct(BurgerBuilder $builder) {
        $this->size = $builder->size;
        $this->cheese = $builder->cheese;
        $this->pepperoni = $builder->pepperoni;
        $this->lettuce = $builder->lettuce;
        $this->tomato = $builder->tomato;
    }
}



class BurgerBuilder {
    public $size;

    public $cheese = false;
    public $pepperoni = false;
    public $lettuce = false;
    public $tomato = false;

    public function __construct(int $size) {
        $this->size = $size;
    }
    
    public function addPepperoni() {
        $this->pepperoni = true;
        return $this;
    }
    
    public function addLettuce() {
        $this->lettuce = true;
        return $this;
    }
    
    public function addCheese() {
        $this->cheese = true;
        return $this;
    }
    
    public function addTomato() {
        $this->tomato = true;
        return $this;
    }
    
    public function build() : Burger {
        return new Burger($this);
    }
}


$burger = (new BurgerBuilder(14))
                    ->addPepperoni()
                    ->addLettuce()
                    ->addTomato()
                    ->build();

var_dump($burger);                    
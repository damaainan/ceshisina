<?php 
/**
 * 原型模式
------------
现实例子
> 记得多利吗？那只克隆羊！不要在意细节，现在的重点是克隆

白话
> 通过克隆已有的对象来创建新对象。

创建已有对象的拷贝，然后修改到你要的样子，而不是从头开始建造



你也可以使用魔法方法 `__clone` 来改变克隆逻辑。

**何时使用？**

当一个对象需要跟已有的对象相似，或者当创造过程比起克隆来太昂贵时。

 */


class Sheep {
    protected $name;
    protected $category;

    public function __construct(string $name, string $category = 'Mountain Sheep') {
        $this->name = $name;
        $this->category = $category;
    }
    
    public function setName(string $name) {
        $this->name = $name;
    }

    public function getName() {
        return $this->name;
    }

    public function setCategory(string $category) {
        $this->category = $category;
    }

    public function getCategory() {
        return $this->category;
    }
}

$original = new Sheep('Jolly');
echo $original->getName(); // Jolly
echo $original->getCategory(); // Mountain Sheep

// Clone and modify what is required
$cloned = clone $original;
$cloned->setName('Dolly');
echo $cloned->getName(); // Dolly
echo $cloned->getCategory(); // Mountain sheep
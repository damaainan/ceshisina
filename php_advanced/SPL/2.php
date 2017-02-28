<?php 
// IteratorAggregate接口

// IteratorAggregate接口是用来将Iterator接口要求实现的5个迭代器方法委托给其他类的.
// 这让你可以在类的外部实现迭代功能,并允许重新使用常用的迭代器方法,而不是在编写的每个可迭代类中重复使用这些方法

class MyIterableClass implements IteratorAggregate
{
    protected $arr;
    public function __construct()
    {
        $this->arr = array(1,2,3);
    }
    public function getIterator()
    {
        return new ArrayIterator($this->arr);
    }
}
 
//foreach循环遍历一个类
foreach(new MyIterableClass() as $value)
{
    echo $value . PHP_EOL;
}
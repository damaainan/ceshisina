<?php 

//  PHP SPL SplObjectStorage是用来存储一组对象的，特别是当你需要唯一标识对象的时候。
// PHP SPL SplObjectStorage类实现了Countable,Iterator,Serializable,ArrayAccess四个接口。可实现统计、迭代、序列化、数组式访问等功能


class A {
  public $i;
  public function __construct($i) {
    $this->i = $i;
  }
}
  
$a1 = new A(1);
$a2 = new A(2);
$a3 = new A(3);
$a4 = new A(4);
  
$container = new SplObjectStorage();
  
//SplObjectStorage::attach 添加对象到Storage中
$container->attach($a1);
$container->attach($a2);
$container->attach($a3);
  
//SplObjectStorage::detach 将对象从Storage中移除
$container->detach($a2);
  
//SplObjectStorage::contains用于检查对象是否存在Storage中
var_dump($container->contains($a1)); //true
var_dump($container->contains($a4)); //false
  
//遍历
$container->rewind();
while($container->valid()) {
  var_dump($container->current());
  $container->next();
}
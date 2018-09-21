## php中static静态属性和静态方法的调用

来源：[https://segmentfault.com/a/1190000007891555](https://segmentfault.com/a/1190000007891555)

本文介绍php面向对象中static静态属性和静态方法的调用,实例分析了static静态属性和静态方法的原理与调用技巧,需要的朋友可以参考下。
### 简介

这里分析了php面向对象中static静态属性和静态方法的调用。关于它们的调用（能不能调用，怎么样调用），需要弄明白了他们在内存中存放位置，这样就非常容易理解了。静态属性、方法（包括静态与非静态）在内存中，只有一个位置（而非静态属性，有多少实例化对象，就有多少个属性）。
### 示例

```php
<?php
header("content-type:text/html;charset=utf-8");
class Human{
 static public $name = "妹子";
 public $height = 180;
 public $age;
// 构造方法
public function __construct(){
   $this->age = "Corwien";
   // 测试调用静态方法时，不会执行构造方法，只有实例化对象时才会触发构造函数，输出下面的内容。
  echo __LINE__,__FILE__,'<br/>'; 
    
}
 static public function tell(){
 echo self::$name;//静态方法调用静态属性，使用self关键词
 //echo $this->height;//错。静态方法不能调用非静态属性
//因为 $this代表实例化对象，而这里是类，不知道 $this 代表哪个对象
 }
 public function say(){
 echo self::$name . "我说话了";
 //普通方法调用静态属性，同样使用self关键词
 echo $this->height;
 }
}
$p1 = new Human();
$p1->say(); 
$p1->tell();//对象可以访问静态方法
echo $p1::$name;//对象访问静态属性。不能这么访问$p1->name
//因为静态属性的内存位置不在对象里
Human::say();//错。say()方法有$this时出错；没有$this时能出结果
//但php5.4以上会提示

/* 
 调用类的静态函数时不会自动调用类的构造函数。
测试方法，在各个函数里分别写上下面的代码 echo __LINE__,__FILE__,'
'; 
根据输出的内容，就知道调用顺序了。
*/
// 调用静态方法，不会执行构造方法，只有实例化对象时才会触发构造函数，输出构造方法里的内容。
Human::tell();

```
### 总结

（1）、静态属性不需要实例化即可调用。因为静态属性存放的位置是在类里，调用方法为"类名::属性名"；
（2）、静态方法不需要实例化即可调用。同上
（3）、静态方法不能调用非静态属性。因为非静态属性需要实例化后，存放在对象里；
（4）、静态方法可以调用非静态方法，使用 self 关键词。php里，一个方法被self:: 后，它就自动转变为静态方法；
（5）、调用类的静态函数时不会自动调用类的构造函数。

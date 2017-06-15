<?php 
//简单工厂模式
class Cat
{
  function __construct()
  {
      echo "I am Cat class <br>";
  }
}
class Dog
{
  function __construct()
  {
      echo "I am Dog class <br>";
  }
}
class Factory
{
  public static function CreateAnimal($name){
      if ($name == 'cat') {
          return new Cat();
      } elseif ($name == 'dog') {
          return new Dog();
      }
  }
}

$cat = Factory::CreateAnimal('cat');
$dog = Factory::CreateAnimal('dog');
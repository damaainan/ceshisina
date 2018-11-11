## 论 PHP 中抽象类和接口的异同

来源：<https://juejin.im/post/5b18ab90e51d4506c556a7fe>

时间：2018年06月07日


## 相同点:


* 抽象类和接口本身都不能实例化


* 都可以指定某个类必须实现哪些方法，而不需要定义这些方法的具体内容


* 可扩展性：抽象类和接口都可以通过继承（extends）实现扩展


## 不同点:
### 抽象类:


* 抽象类可以拥有成员属性


```php
abstract class Foo  
{  
    public $name = 'Mike';  
}  
```


* 抽象类可以定义具体的成员方法


```php
abstract class Foo  
{  
    // 抽象方法，子类必须实现此方法  
    abstract public function say();  
      
    // 普通方法，子类可以直接使用此方法  
    public function hello(){  
        echo 'hello world';  
    }  
}  
```


* 可扩展，即一个抽象类可以继承另一个抽象类，但是只能单继承，不能继承多个


```php
abstract class Foo1  
{  
    abstract public function hello();  
}  
 
// 抽象类Foo2继承自Foo1  
abstract class Foo2 extends Foo1  
{  
    abstract public function word();  
}  
  
// MyClass继承抽象类Foo2，因为Foo2继承自Foo1，所以MyClass必须实现hello()方法和word()方法  
class MyClass extends Foo2  
{  
    public function hello()  
    {  
        //something  
    }  
    public function word()  
    {  
        // something  
    }  
}  
```


* 一个子类只能继承一个抽象类


```php
abstract class Foo1  
{  
    abstract public function hello1();  
}  
  
abstract class Foo2  
{  
    abstract public function hello2();  
}  
  
class MyClass extends Foo1  
{  
    //子类必须实现父类中的所有抽象方法  
    public function hello1();  
}  
  
class ErrorClass extends Foo1,Foo2  
{  
    //错误，一个子类只能继承一个父类，无法多继承  
}  
```
### 接口:


* 接口不能定义成员属性，只能定义接口常量


```php
interface Foo  
{  
    // 定义接口常量  
    const NAME = 'Mike';  
    // 错误，无法在接口中定义成员属性  
    public $name = 'Mike';  
}  
```


* 不能在接口内部定义具体的成员方法


```php
interface Foo  
{  
    public function hello();    //正确  
  
    // 错误，接口中不能实现具体的成员方法  
    public function sayHello(){  
        echo 'hello world';  
    }  
}  
```


* 可扩展，即一个接口可以继承另一个或者多个接口，即可以多继承


```php
// 接口1  
interface Foo1  
{  
public function foo1();  
}  
  
// 接口2  
interface Foo2  
{  
public function foo2();  
}  
  
// 接口3，继承接口1和接口2  
interface foo extends Foo1,Foo2  
{  
    public function say();  
}  
```


* 一个类可以实现（implements）多个接口


```php
// 定义一个类，实现接口1和接口2  
class MyClass implements Foo1,Foo2  
{  
    public function foo1(){  
        //something  
    }  
  
    public function foo2(){  
        //something  
    }  
}  
```

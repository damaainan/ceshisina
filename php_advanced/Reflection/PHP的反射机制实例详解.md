# PHP的反射机制实例详解


**介绍：**

PHP5添加了一项新的功能：Reflection。这个功能使得phper可以reverse-engineer class, interface,function,method and extension。通过PHP代码，就可以得到某object的所有信息，并且可以和它交互。

**反射是什么？**

它是指在PHP运行状态中，扩展分析PHP程序，导出或提取出关于类、方法、属性、参数等的详细信息，包括注释。这种动态获取的信息以及动态调用对象的方法的功能称为反射API。反射是操纵面向对象范型中元模型的API，其功能十分强大，可帮助我们构建复杂，可扩展的应用。

其用途如：自动加载插件，自动生成文档，甚至可用来扩充PHP语言。

php反射api由若干类组成，可帮助我们用来访问程序的元数据或者同相关的注释交互。借助反射我们可以获取诸如类实现了那些方法，创建一个类的实例（不同于用new创建），调用一个方法（也不同于常规调用），传递参数，动态调用类的静态方法。

反射api是php内建的oop技术扩展，包括一些类，异常和接口，综合使用他们可用来帮助我们分析其它类，接口，方法，属性，方法和扩展。这些oop扩展被称为反射。

通过ReflectionClass，我们可以得到Person类的以下信息：

1）常量 Contants  
2）属性 Property Names  
3）方法 Method Names静态  
4）属性 Static Properties  
5）命名空间 Namespace  
6)Person类是否为final或者abstract

**例子**

```php
<?php
class Person {
  /**
   * For the sake of demonstration, we"re setting this private
   */
  private $_allowDynamicAttributes = false;
  /** type=primary_autoincrement */
  protected $id = 0;
  /** type=varchar length=255 null */
  protected $name;
  /** type=text null */
  protected $biography;
    public function getId()
    {
      return $this->id;
    }
    public function setId($v)
    {
      $this->id = $v;
    }
    public function getName()
    {
      return $this->name;
    }
    public function setName($v)
    {
      $this->name = $v;
    }
    public function getBiography()
    {
      return $this->biography;
    }
    public function setBiography($v)
    {
      $this->biography = $v;
    }
}
```

接下来反射它，只要把类名"Person"传递给ReflectionClass就可以了：

```php
<?php
$class = new ReflectionClass('Person');//建立 Person这个类的反射类
$instance = $class->newInstanceArgs($args);//相当于实例化Person 类

```
1）获取属性(Properties)：**

```php
<?php
$properties = $class->getProperties();
foreach($properties as $property) {
  echo $property->getName()."\n";
}
// 输出:
// _allowDynamicAttributes
// id
// name
// biography

```
默认情况下，ReflectionClass会获取到所有的属性，private 和 protected的也可以。如果只想获取到private属性，就要额外传个参数：

    $private_properties=$class->getProperties(ReflectionProperty::IS_PRIVATE);

可用参数列表：

ReflectionProperty::IS_STATIC  
ReflectionProperty::IS_PUBLIC  
ReflectionProperty::IS_PROTECTED  
ReflectionProperty::IS_PRIVATE

如果要同时获取public 和private 属性，就这样写：ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED。

通过$property->getName()可以得到属性名。

**2）获取注释：**

通过getDocComment可以得到写给property的注释。

```php
<?php
foreach($properties as $property) {
  if($property->isProtected()) {
    $docblock = $property->getDocComment();
    preg_match('/ type\=([a-z_]*) /', $property->getDocComment(), $matches);
    echo $matches[1]."\n";
  }
}
// Output:
// primary_autoincrement
// varchar
// text

```
**3）获取类的方法**

获取方法(methods)：通过getMethods() 来获取到类的所有methods。

**4）执行类的方法：**
```php
$instance->getBiography(); //执行Person 里的方法getBiography
//或者：
$ec=$class->getmethod('getName'); //获取Person 类中的getName方法
$ec->invoke($instance);    //执行getName 方法
```

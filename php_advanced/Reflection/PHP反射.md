# PHP反射 

    发表于 2016-12-25   | 

# 反射介绍

PHP 反射机制，对类、接口、函数、方法和扩展进行反向工程的能力。  
分析类，接口，函数和方法的内部结构，方法和函数的参数，以及类的属性和方法。

反射中常用的几个类：

* ReflectionClass 解析类
* ReflectionProperty 类的属性的相关信息
* ReflectionMethod 类方法的有关信息
* ReflectionParameter 取回了函数或方法参数的相关信息
* ReflectionFunction 一个函数的相关信息

分析类：
```php
<?php
class Student
{
    public $id;
    
    public $name;
    const MAX_AGE = 200;
    public static $likes = [];
    public function __construct($id, $name = 'li')
    {
        $this->id = $id;
        $this->name = $name;
    }
    public function study()
    {
        echo 'learning...';
    }
    private function _foo()
    {
        echo 'foo';
    }
    protected function bar($to, $from = 'zh')
    {
        echo 'bar';
    }
}
```

# ReflectionClass

```php
<?php
$ref = new ReflectionClass('Student');
// 判断类是否可实例化
if ($ref->isInstantiable()) {
    echo '可实例化';
}
// 获取类构造函数
// 有返回 ReflectionMethod 对象，没有返回 NULL
$constructor = $ref->getConstructor();
print_r($constructor);
// 获取某个属性
if ($ref->hasProperty('id')) {
    $attr = $ref->getProperty('id');
    print_r($attr);
}
// 获取属性列表
$attributes = $ref->getProperties();
foreach ($attributes as $row) {
    // 这里的 $row 为 ReflectionProperty 的实例
    echo $row->getName() , "\n";
}
// 获取静态属性，返回数组
$static = $ref->getStaticProperties();
print_r($static);
// 获取某个常量
if ($ref->hasConstant('MAX_AGE')) {
    $const = $ref->getConstant('MAX_AGE');
    echo $const;
}
// 获取常量，返回数组
$constants = $ref->getConstants();
print_r($constants);
// 获取某个方法
if ($ref->hasMethod('bar')) {
    $method = $ref->getMethod('bar');
    print_r($method);
}
// 获取方法列表
$methods = $ref->getMethods();
foreach ($methods as $key => $value) {
    // 这里的 $row 为 ReflectionMethod 的实例
    echo $value->getName() . "\n";
}

```

# ReflectionProperty

```php
<?php

if ($ref->hasProperty('name')) {
    $attr = $ref->getProperty('name');
    // 属性名称
    echo $attr->getName();
    // 类定义时属性为真，运行时添加的属性为假
    var_dump($attr->isDefault());
    // 判断属性访问权限
    var_dump($attr->isPrivate());
    var_dump($attr->isProtected());
    var_dump($attr->isPublic());
    // 判断属性是否为静态
    var_dump($attr->isStatic());
}

```

# RefleactionMethod & ReflectionParameter

```php
<?php
if ($ref->hasMethod('bar')) {
    $method = $ref->getMethod('bar');
    echo $method->getName();
    // isAbstract  判断是否是抽象方法
    //isConstructor  判断是否是构造方法
    //isDestructor  判断是否是析构方法
    //isFinal  判断是否是 final 描述的方法
    //isPrivate  判断是否是 private 描述的方法
    //isProtected 判断是否是 protected 描述的方法
    //isPublic 判断是否是 public 描述的方法
    //isStatic 判断是否是 static 描述的方法
    
    // 获取参数列表
    $parameters = $method->getParameters();
    foreach ($parameters as $row) {
        // 这里的 $row 为 ReflectionParameter 实例
        echo $row->getName();
        echo $row->getClass();
        // 检查变量是否有默认值
        if ($row->isDefaultValueAvailable()) {
            echo $row->getDefaultValue();
        }
        // 获取变量类型
        if ($row->hasType()) {
            echo $row->getType();
        }
        
    }
}

```

# ReflectionFunction & ReflectionParameter

```php
<?php
$fun = new ReflectionFunction('demo');
echo $fun->getName();
$parameters = $fun->getParameters();
foreach ($parameters as $row) {
    // 这里的 $row 为 ReflectionParameter 实例
    echo $row->getName();
    echo $row->getClass();
    // 检查变量是否有默认值
    if ($row->isDefaultValueAvailable()) {
        echo $row->getDefaultValue();
    }
    // 获取变量类型
    if ($row->hasType()) {
        echo $row->getType();
    }
}

```

# 综合实例

下面用一个简单的示例：如果用反射实例化类。  
file: Student.php

```php
<?php
class Student
{
    public $id;
    
    public $name;
    public function __construct($id, $name)
    {
        $this->id = $id;
        $this->name = $name;
    }
    public function study()
    {
        echo 'learning.....';
    }
}
```

一般情况下，实例化类的时候，直接使用 new，但是我们现在不用这种方法，我们使用反射来实现。  
file: index.php

    
```php
<?php
require 'student.php';
function make($class, $vars = [])
{
    $ref = new ReflectionClass($class);
    // 检查类 Student 是否可实例化
    if ($ref->isInstantiable()) {
        // 获取构造函数
        $constructor = $ref->getConstructor();
        // 没有构造函数的话，直接实例化
        if (is_null($constructor)) {
            return new $class;
        }
        // 获取构造函数参数
        $params = $constructor->getParameters();
        $resolveParams = [];
        foreach ($params as $key => $value) {
            $name = $value->getName();
            if (isset($vars[$name])) {
                // 判断如果是传递的参数，直接使用传递参数
                $resolveParams[] = $vars[$name];
            } else {
                // 没有传递参数的话，检查是否有默认值，没有默认值的话，按照类名进行递归解析
                $default = $value->isDefaultValueAvailable() ? $value->getDefaultValue() : null;
                if (is_null($default)) {
                    if ($value->getClass()) {
                        $resolveParams[] = make($value->getClass()->name, $vars);
                    } else {
                        throw new Exception("{$name} 没有传值且没有默认值");
                    }
                } else {
                    $resolveParams[] = $default;
                }
            }
        }
        // 根据参数实例化
        return $ref->newInstanceArgs($resolveParams);
    } else {
        throw new Exception("类 {$class} 不存在!");
    }
}
## 情况一
try {
    $stu = make('Student', ['id' => 1]);
    print_r($stu);
    $stu->study();
} catch (Exception $e) {
    echo $e->getMessage();
}
## 情况二
try {
    $stu = make('Student', ['id' => 1, 'name' => 'li']);
    print_r($stu);
    $stu->study();
} catch (Exception $e) {
    echo $e->getMessage();
}

```
上面两种情况很明显第一种，缺少参数 name，无法实例化成功，第二种情况就可以实例化成功。

那么我们如果将类 Student 的构造函数修改为:

```php
<?php
public function __construct($id, $name = 'zhang')
{
    $this->id = $id;
    $this->name = $name;
}
```
这样设置 name 有默认值的情况下，那么第一种情况也可以实例化成功。

## 情况三

第三种情况：如果在类的构造函数中有其他类为参数的情况下，那么也可以解析：

```php
<?php
public function __construct($id, $name, Study $study)
{
    $this->id = $id;
    $this->name = $name;
    $this->study = $study;
}
```
那么这种情况下，在分析类的构造函数参数的时候，如果没有传递参数的话，就会递归调用 make 方法处理 Study 类，如果类存在的话，实例化。

file: study.php

```php
<?php
// 我们这里不写构造函数，测试下没有构造函数的情况
class Study
{
    public function show()
    {
        echo 'show';
    }
}
```
将 Student 类的方法 study 修改为：

```php
<?php
public function study()
{
    $this->name . ' ' . $this->study->show();
}
```
下面测试：

```php
<?php
try {
    $stu = make('Student', ['id' => 1]);
    print_r($stu);
    $stu->study();
} catch (Exception $e) {
    echo $e->getMessage();
}
```
PHP 的反射是一个很用的功能，我这里只能很简单的讲解了一点皮毛，详细介绍和用法可参看 [官方手册][0]。

[0]: http://php.net/manual/zh/book.reflection.php
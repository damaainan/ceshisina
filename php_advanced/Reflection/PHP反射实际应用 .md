# PHP反射实际应用 

    发表于 2016-12-27   | 

[上一节][0] 主要系统讲了反射的用法，虽然讲解了用法，但是没有对其在项目中的实际使用做讲解，不学以致用，不如不学。  
在好多框架底层实现上面使用了反射，所以要理解和分析框架底层源码的话，必须掌握反射，不然的话理解十分的困难。

下面我们讲下反射在实际开发中的应用。

* 自动生成文档
* 实现 MVC 架构
* 实现单元测试
* 配合 DI 容器解决依赖
* …

# 自动生成文档

根据反射的分析类，接口，函数和方法的内部结构，方法和函数的参数，以及类的属性和方法，可以自动生成文档。

```php
<?php
/**
 * 学生类
 *
 * 描述信息
 */
class Student
{
    const NORMAL = 1;
    const FORBIDDEN = 2;
    /**
     * 用户ID
     * @var 类型
     */
    public $id;
    /**
     * 获取id
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    public function setId($id = 1)
    {
        $this->id = $id;
    }
}
$ref = new ReflectionClass('Student');
$doc = $ref->getDocComment();
echo $ref->getName() . ':' . getComment($ref) , "\n";
echo "属性列表：\n";
printf("%-15s%-10s%-40s\n", 'Name', 'Access', 'Comment');
$attr = $ref->getProperties();
foreach ($attr as $row) {
    printf("%-15s%-10s%-40s\n", $row->getName(), getAccess($row), getComment($row));
}
echo "常量列表：\n";
printf("%-15s%-10s\n", 'Name', 'Value');
$const = $ref->getConstants();
foreach ($const as $key => $val) {
    printf("%-15s%-10s\n", $key, $val);
}
echo "\n\n";
echo "方法列表\n";
printf("%-15s%-10s%-30s%-40s\n", 'Name', 'Access', 'Params', 'Comment');
$methods = $ref->getMethods();
foreach ($methods as $row) {
    printf("%-15s%-10s%-30s%-40s\n", $row->getName(), getAccess($row), getParams($row), getComment($row));
}
// 获取权限
function getAccess($method)
{
    if ($method->isPublic()) {
        return 'Public';
    }
    if ($method->isProtected()) {
        return 'Protected';
    }
    if ($method->isPrivate()) {
        return 'Private';
    }
}
// 获取方法参数信息
function getParams($method)
{
    $str = '';
    $parameters = $method->getParameters();
    foreach ($parameters as $row) {
        $str .= $row->getName() . ',';
        if ($row->isDefaultValueAvailable()) {
            $str .= "Default: {$row->getDefaultValue()}";
        }
    }
    return $str ? $str : '';
}
// 获取注释
function getComment($var)
{
    $comment = $var->getDocComment();
    // 简单的获取了第一行的信息，这里可以自行扩展
    preg_match('/\* (.*) *?/', $comment, $res);
    return isset($res[1]) ? $res[1] : '';
}
```
运行 php file.php 就可以看到相应的文档信息。

# 实现 MVC 架构

现在好多框架都是 MVC 的架构，根据路由信息定位 控制器($controller) 和方法($method) 的名称，之后使用反射实现自动调用。
```php
<?php
$class = new ReflectionClass(ucfirst($controller) . 'Controller');
$controller = $class->newInstance();
if ($class->hasMethod($method)) {
    $method = $class->getMethod($method);
    $method->invokeArgs($controller, $arguments);
} else {
    throw new Exception("{$controller} controller method {$method} not exists!");
}
```
# 实现单元测试

一般情况下我们会对函数和类进行测试，判断其是否能够按我们预期返回结果，我们可以用反射实现一个简单通用的类测试用例。
```php
<?php
class Calc
{
    public function plus($a, $b)
    {
        return $a + $b;
    }
    public function minus($a, $b)
    {
        return $a - $b;
    }
}
function testEqual($method, $assert, $data)
{
    $arr = explode('@', $method);
    $class = $arr[0];
    $method = $arr[1];
    $ref = new ReflectionClass($class);
    if ($ref->hasMethod($method)) {
        $method = $ref->getMethod($method);
        $res = $method->invokeArgs(new $class, $data);
        var_dump($res === $assert);
    }
}
testEqual('Calc@plus', 3, [1, 2]);
testEqual('Calc@minus', -1, [1, 2]);
```
这是类的测试方法，也可以利用反射实现函数的测试方法。  
这里只是我简单写的一个测试用例，PHPUnit 单元测试框架很大程度上依赖了 Reflection 的特性，可以了解下。

# 配合 DI 容器解决依赖

Laravel 等许多框架都是使用 Reflection 解决依赖注入问题，具体可查看 Laravel 源码进行分析。  
下面我们代码简单实现一个 DI 容器演示 Reflection 解决依赖注入问题。

```php
<?php
class DI
{
    protected static $data = [];
    public function __set($k, $v)
    {
        self::$data[$k] = $v;
    }
    public function __get($k)
    {
        return $this->bulid(self::$data[$k]);
    }
    // 获取实例
    public function bulid($className)
    {
        // 如果是匿名函数，直接执行，并返回结果
        if ($className instanceof Closure) {
            return $className($this);
        }
        // 已经是实例化对象的话，直接返回
        if(is_object($className)) {
            return $className;
        }
        // 如果是类的话，使用反射加载
        $ref = new ReflectionClass($className);
        // 监测类是否可实例化
        if (!$ref->isInstantiable()) {
            throw new Exception('class' . $className . ' not find');
        }
        // 获取构造函数
        $construtor = $ref->getConstructor();
        // 无构造函数，直接实例化返回
        if (is_null($construtor)) {
            return new $className;
        }
        // 获取构造函数参数
        $params = $construtor->getParameters();
        // 解析构造函数
        $dependencies = $this->getDependecies($params);
        // 创建新实例
        return $ref->newInstanceArgs($dependencies);
    }
    // 分析参数，如果参数中出现依赖类，递归实例化
    public function getDependecies($params)
    {
        $data = [];
        foreach($params as $param)
        {
            $tmp = $param->getClass();
            if (is_null($tmp)) {
                $data[] = $this->setDefault($param);
            } else {
                $data[] = $this->bulid($tmp->name);
            }
        }
        return $data;
    }
    // 设置默认值
    public function setDefault($param)
    {
        if ($param->isDefaultValueAvailable()) {
            return $param->getDefaultValue();
        }
        throw new Exception('no default value!');
    }
}
class Demo
{
    public function __construct(Calc $calc)
    {
        echo $calc->plus(1, 2);
    }
}
$di = new DI();
$di->calc = 'Calc'; // 加载单元测试用例中 Calc 类
$di->demo = 'Demo';
$di->demo;
```
注意上面的 calc 和 demo 的顺序，不能颠倒，不然的话会报错，原因是由于 Demo 依赖 Calc，首先要定义依赖关系。  
在 Demo 实例化的时候，会用到 Calc 类，也就是说 Demo 依赖于 Calc，但是在 $data 上面找不到的话，会抛出错误，所以首先要定义 $di->calc = 'Calc'。

Reflection 是一个非常 Cool 的功能，使用它，但不要滥用它。

[0]: https://flyerboy.github.io/2016/12/27/php_reflection_use/
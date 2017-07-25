# [PHP中的反射](http://www.cnblogs.com/chenqionghe/p/4735753.html)


面向对象编辑中对象被赋予了自省的能力,而这个自省的过程就是反射.

反射,直观理解应时根据到达地找出出发地和来源.比方说,我给你一个光秃秃的对象,我可以仅仅通过这个对象就能知道它所属的类,拥有哪些方法.

反射指在PHP运行状态中,扩展分析PHP程序,导出或提取出关于类,方法,属性,参数等详细信息,包括注释.这种动态获取信息以及动态调用对象方法的功能称为反射API

## **如何使用反射API**

以下面代码为例 
```php
class HandsonBoy
{
    public $name = 'chenqionghe';
    public $age = 18;
    public function __set($name,$value)
    {
        echo '您正在设置私有属性'.$name.'<br >值为'.$value.'<br>';
        $this->$name = $value;
    }
    public function __get($name)
    {
        if(!isset($this->$name))
        {
            echo '未设置'.$name;
            $this->$name = "正在为你设置默认值".'<br>';
        }
        return $this->$name;
    }
}
$boy = new HandsonBoy();
echo $boy->name.'<br />';
$boy->hair = 'short';
```


现在,要获取这个student对象的方法和属性列表该怎么做?可以用反射来实现,代码如下
```php
$reflect = new ReflectionObject($boy);
$props = $reflect->getProperties();
//获取属性的名字
foreach($props as $prop)
{
    print $prop->getName().PHP_EOL;
}
//获取对象方法列表
$methos = $reflect->getMethods();
foreach($methos as $method)
{
    print $method->getName().PHP_EOL;
}
```

也可以不用反射API,使用class函数,返回对象属性的关联数组以及更多的信息:(针对于公开的属性和):
```php
//返回对象属性的关联数组
var_dump(get_object_vars($boy));
//类属性
var_dump(get_class_vars(get_class($boy)));
//返回由类的属性的方法名组成的数组
var_dump(get_class_methods(get_class($boy)));
```

反射API的功能显然更强大，甚至能还原这个类的原型,包括方法的访问权限,以下简单封装了一个打印类的代码

```php
/**
 * @param $classObject 对象或者类名
 */
function getClass($classObject)
{
    $object = new ReflectionClass($classObject);
    $className = $object->getName();
    $Methods = $Properties = array();
    foreach($object->getProperties() as $v)
    {
        $Properties[$v->getName()] = $v;
    }
    foreach($object->getMethods() as $v)
    {
        $Methods[$v->getName()] = $v;
    }
    echo "class {$className}\n{\n";
    is_array($Properties) && ksort($Properties);
    foreach($Properties as $k=>$v)
    {
        echo "\t";
        echo $v->isPublic() ? 'public' : '',$v->isPrivate() ? 'private' :'',$v->isProtected() ? 'protected' : '';
        $v->isStatic() ? 'static' : '';
        echo "\t{$k}\n";
    }
    echo "\n";
    if(is_array($Methods)) ksort($Methods);
    foreach($Methods as $k=>$v)
    {
        echo "\tfunction {$k}()\n";
    }
    echo "}\n";
}
```

不仅如此,PHP手册中关于反射API更是有几十个,可以说,反射完整地描述了一个类或者对象的原型.反射不仅可以用于类和对象,还可以用于函数,扩展模块,异常等.

## **反射有什么作用**

反射可以用于文档生成,因此可以用它对文件里的类进行扫描,逐个生成描述文档.

既然反射可以探知类内部结构, 那么是不是 可以用它做hook实现插件功能呢?或者是作动态代理呢?抛砖引玉,以下代码是个简单的例子 
```php
<?php
class mysql
{
    function connect($db)
    {
        echo "连接到数据库{$db[0]}".PHP_EOL;
    }
}
class sqlproxy
{
    private $target;
    public function __construct($tar)
    {
        $this->target[] = new $tar;
    }
    public function __call($name,$args)
    {
        foreach($this->target as $obj)
        {
            $r = new ReflectionClass($obj);
            if($method = $r->getMethod($name))
            {
                if($method->isPublic() && !$method->isAbstract())
                {
                    echo "方法前拦截记录LOG".PHP_EOL;
                    $method->invoke($obj,$args);
                    echo "方法后拦截".PHP_EOL;
                }
            }
        }
    }
}
$obj = new sqlproxy('mysql');
$obj->connect('chenqionghe');
```

这里真正操作类是mysql类,但是sqlproxy实现了根据动态传入参数,代替实际的类运行,并且在方法运行前后进行拦截,并且动态地改变类中的方法和属性.这就是简单的动态代理.

在平常的开发中用到反射的地方并不多: 一个是对对象进行调试,别一个是获取类的信息.在MVC和插件开发中,使用反射很常见,但是反射的消耗也很大,在可以找到替代方案的情况下,就不要滥用.

PHP有Token函数,可以通过这个机制实现一些反射功能.从简单灵活的角度讲,使用已经提供的反射API是可取的.

很多时候,善用反射能保持代码的优雅和简洁,但反射也会破坏类的封装性,因为反射可以使本不应该暴露的方法或属性被强制暴露了出来,这既是优点也是缺点.

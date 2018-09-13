# PHP反射机制实现自动依赖注入

 时间 2017-12-20 20:24:56

原文[http://www.jianshu.com/p/8615ff389bb0][1]


转自 [http://blog.csdn.net/qq_20678155/article/details/70158374][4]

依赖注入又叫控制反转，使用过框架的人应该都不陌生。很多人一看名字就觉得是非常高大上的东西，就对它望而却步，今天抽空研究了下，解开他它的神秘面纱。废话不多说，直接上代码；
```php
/**
*
* 工具类，使用该类来实现自动依赖注入。
*
*/
class Ioc {

    // 获得类的对象实例
    public static function getInstance($className) {

        $paramArr = self::getMethodParams($className);

        return (new ReflectionClass($className))->newInstanceArgs($paramArr);
    }

    /**
     * 执行类的方法
     * @param  [type] $className  [类名]
     * @param  [type] $methodName [方法名称]
     * @param  [type] $params     [额外的参数]
     * @return [type]             [description]
     */
    public static function make($className, $methodName, $params = []) {

        // 获取类的实例
        $instance = self::getInstance($className);

        // 获取该方法所需要依赖注入的参数
        $paramArr = self::getMethodParams($className, $methodName);

        return $instance->{$methodName}(...array_merge($paramArr, $params));
    }

    /**
     * 获得类的方法参数，只获得有类型的参数
     * @param  [type] $className   [description]
     * @param  [type] $methodsName [description]
     * @return [type]              [description]
     */
    protected static function getMethodParams($className, $methodsName = '__construct') {

        // 通过反射获得该类
        $class = new ReflectionClass($className);
        $paramArr = []; // 记录参数，和参数类型

        // 判断该类是否有构造函数
        if ($class->hasMethod($methodsName)) {
            // 获得构造函数
            $construct = $class->getMethod($methodsName);

            // 判断构造函数是否有参数
            $params = $construct->getParameters();

            if (count($params) > 0) {

                // 判断参数类型
                foreach ($params as $key => $param) {

                    if ($paramClass = $param->getClass()) {

                        // 获得参数类型名称
                        $paramClassName = $paramClass->getName();

                        // 获得参数类型
                        $args = self::getMethodParams($paramClassName);
                        $paramArr[] = (new ReflectionClass($paramClass->getName()))->newInstanceArgs($args);
                    }
                }
            }
        }

        return $paramArr;
    }
}
```

上面的代码使用php的反射函数，创建了一个容器类，使用该类来实现其他类的依赖注入功能。上面的依赖注入分为两种，一种是构造函数的依赖注入，一种是方法的依赖注入。 我们使用下面三个类来做下测试。

```php
class A {

    protected $cObj;

    /**
     * 用于测试多级依赖注入 B依赖A，A依赖C
     * @param C $c [description]
     */
    public function __construct(C $c) {

        $this->cObj = $c;
    }

    public function aa() {

        echo 'this is A->test';
    }

    public function aac() {

        $this->cObj->cc();
    }
}

class B {

    protected $aObj;

    /**
     * 测试构造函数依赖注入
     * @param A $a [使用引来注入A]
     */
    public function __construct(A $a) {

        $this->aObj = $a;
    }

    /**
     * [测试方法调用依赖注入]
     * @param  C      $c [依赖注入C]
     * @param  string $b [这个是自己手动填写的参数]
     * @return [type]    [description]
     */
    public function bb(C $c, $b) {

        $c->cc();
        echo "\r\n";

        echo 'params:' . $b;
    }

    /**
     * 验证依赖注入是否成功
     * @return [type] [description]
     */
    public function bbb() {

        $this->aObj->aac();
    }
}

class C {

    public function cc() {

        echo 'this is C->cc';
    }
}
```
#### 测试构造函数的依赖注入
```php
// 使用Ioc来创建B类的实例，B的构造函数依赖A类，A的构造函数依赖C类。
$bObj = Ioc::getInstance('B');
$bObj->bbb(); // 输出：this is C->cc ， 说明依赖注入成功。

// 打印$bObj
var_dump($bObj);

// 打印结果，可以看出B中有A实例，A中有C实例，说明依赖注入成功。
object(B)#3 (1) {
  ["aObj":protected]=>
  object(A)#7 (1) {
    ["cObj":protected]=>
    object(C)#10 (0) {
    }
  }
}
```
#### 测试方法依赖注入
```php
    Ioc::make('B', 'bb', ['this is param b']);
    
    // 输出结果，可以看出依赖注入成功。
    this is C->cc
    params:this is param b
```
从上面两个例子可以看出我们创建对象或者调用方法时，根本就不用知道该类或该方法依赖了那个类。使用反射机制可以轻松的为我们自动注入所需要的类。

### 总结

好了，看到上面的代码是不是觉得很简单，其实只要熟悉php的反射机制，依赖注入并不难实现，上面的代码为了方便理解，所以写的简单除暴，在实际的项目中肯定不会这么简单，比如：会对注入的类和参数进行配置，比如会缓存实例化过的类，下次需要该类的实例时，可以直接使用，而不用在重新初始化，等等。不过相信原理了解了，其他的可以随着项目的需求自己去完善。


[1]: http://www.jianshu.com/p/8615ff389bb0?utm_source=tuicool&utm_medium=referral

[4]: https://link.jianshu.com?t=http://blog.csdn.net/qq_20678155/article/details/70158374
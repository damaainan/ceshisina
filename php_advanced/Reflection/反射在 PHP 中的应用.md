## [反射在 PHP 中的应用](https://segmentfault.com/a/1190000012796909)

反射在每个面向对象的编程语言中都存在，它的主要目的就是在运行时分析类或者对象的状态，导出或提取出关于类、方法、属性、参数等的详细信息，包括注释。 反射是操纵面向对象范型中元模型的 API，可用于构建复杂，可扩展的应用。反射在日常的 Web 开发中其实用的不多，更多的是在偏向底层一些的代码中，比如说框架的底层中依赖注入、对象池、动态代理、自动获取插件列表、自动生成文档以及一些设计模式等等，都会大量运用到反射技术。  
PHP 的反射 API 很多，但是常用的一般都是 `ReflectionClass` 和 `ReflectionMethod`:  
1.ReflectionClass  
这个是用来获取类的信息，可以简单测试一下：

    class Student {
        private    $name;
    
        public function setName($name)
        {
             $this->name = $name;
        }
     
        protected function getName()
        {
            return $this->name;
        }
     }

获取类的方法列表：

    $ref = new ReflectionClass(Student::class);
    var_dump($ref->getMethods());

返回的是一个 ReflectionMethod 的数组：

    array(1) {
      [0]=>
      object(ReflectionMethod)#2 (2) {
        ["name"]=>
        string(7) "setName"
        ["class"]=>
        string(7) "Student"
      }
    }

附上一些常用方法，详细的可以查看文档：

    ReflectionClass::getMethods     获取方法的数组
    ReflectionClass::getName        获取类名
    ReflectionClass::hasMethod      检查方法是否已定义
    ReflectionClass::hasProperty    检查属性是否已定义
    ReflectionClass::isAbstract     检查类是否是抽象类（abstract）
    ReflectionClass::isFinal        检查类是否声明为 final
    ReflectionClass::isInstantiable 检查类是否可实例化
    ReflectionClass::newInstance    从指定的参数创建一个新的类实例

2.ReflectionMethod  
这个主要是针对方法的反射，我们可以简单执行一下：

    $stu = new Student();
    $ref = new ReflectionClass(Student::class);
    $method = $ref->getMethod('setName');
    $method->invoke($stu, 'john');
    var_dump($stu->name);

可以输出：

    john

附上一些常用的方法，详细的可以去看看文档：

    ReflectionMethod::invoke        执行
    ReflectionMethod::invokeArgs    带参数执行
    ReflectionMethod::isAbstract    判断方法是否是抽象方法
    ReflectionMethod::isConstructor 判断方法是否是构造方法
    ReflectionMethod::isDestructor  判断方法是否是析构方法
    ReflectionMethod::isFinal       判断方法是否定义 final
    ReflectionMethod::isPrivate     判断方法是否是私有方法
    ReflectionMethod::isProtected   判断方法是否是保护方法 (protected)
    ReflectionMethod::isPublic      判断方法是否是公开方法
    ReflectionMethod::isStatic      判断方法是否是静态方法
    ReflectionMethod::setAccessible 设置方法是否访问

接下来说一些反射在实际开发中比较常见的应用。

## 执行私有方法

其实反射不仅可以执行私有方法，还可以读取私有属性。这个主要应用在一些设计不合理的 SDK 里面，一些很好用的方法和属性却不对外开放。

    class Student {
        private    $name;
     
        private function setName($name)
        {
            $this->name = $name;
        }
    }

执行私有方法：

    $stu = new Student();
    $ref = new ReflectionClass($stu);
    $method = $ref->getMethod('setName');
    $method->setAccessible(true);
    $method->invoke($stu, 'john');

读取私有属性：

    $stu = new Student();
    $ref = new ReflectionClass($stu);
    $prop = $ref->getProperty('name');
    $prop->setAccessible(true);
    $val = $prop->getValue($stu);
    var_dump($val);

## 动态代理

其实 PHP 有魔术方法，所以实现动态代理已经很简单了，但是通过魔术方法来实现的都不完美，个人理解最好的实现应该还是 JDK 中的动态代理，基于一个接口进行扫描实现实在 PHP 中也可以实现。我们先来看看动态代理在 JDK 中是怎么使用的：  
1.首先定义一个实现类的接口，JDK 的动态代理必须基于接口(Cglib则不用)

    package com.yao.proxy;
    public interface Helloworld {
        void sayHello();
    }

2.定义一个实现类，这个类就是要被代理的对象

    package com.yao.proxy;
    import com.yao.HelloWorld;
    
    public class HelloworldImpl implements HelloWorld {
        public void sayHello() {
            System.out.print("hello world");
        }
    }

3.调用被代理对象方法的实现类

    package com.yao.proxy;
    import java.lang.reflect.InvocationHandler;
    import java.lang.reflect.Method;
    
    public class MyInvocationHandler implements InvocationHandler{
        private Object target;
        public MyInvocationHandler(Object target) {
            this.target=target;
        }
        public Object invoke(Object proxy, Method method, Object[] args) throws Throwable {
            System.out.println("前置工作!");
            Object obj = method.invoke(target,args);
            System.out.println("后置工作!");
            return obj;
        }

4.测试

    package com.yao.proxy;
    import java.lang.reflect.InvocationHandler;
    import java.lang.reflect.Proxy;
     
    public class Demo {
        public static void main(String[] args) {
            HelloworldImpl realSubject = new HelloworldImpl();
            MyInvocationHandler handler = new MyInvocationHandler(realSubject);
     
            ClassLoader loader = realSubject.getClass().getClassLoader();
            Class[] interfaces = realSubject.getClass().getInterfaces();
          
            HelloworldImpl proxySubject = (HelloworldImpl) Proxy.newProxyInstance(loader, interfaces, handler);
            String hello = proxySubject.sayHello();
        }
    }

JDK 的动态代理在底层实际上是扫描实现的接口，然后动态生成类的字节码文件。PHP 是动态语言，所以可以用 eval 来实现。  
1.定义调度器接口

    interface InvocationHandler  
    {  
        function invoke($method, array $arr_args);  
    }  

2.动态代理实现  
定义一个类的 stub：

    return new Class($handler,$target) implements %s {  
        private $handler;  
        private $target;
        public function __construct(InvocationHandler $handler, $target) {
            $this->handler = $handler;  
            $this->target = $target;
        }  
        %s  
    };

定义一个方法的 stub：

    public function %s(%s) {  
        $args = func_get_args();  
        $method = explode("::", __METHOD__);  
        $this->handler->invoke(new ReflectionMethod($this->target, $method[1]), $args);
    }

Proxy 实现：

    final class Proxy  
    {   
        const CLASS_TEMPLATE = class_stub;      //这里显示上面定义的，为了方便阅读
        const FUNCTION_TEMPLATE = function_stub;    //同上
     
        public static function newProxyInstance($target, array $interfaces, InvocationHandler $handler)  {
    
        }
        protected static function generateClass(array $interfaces) {
           
        }
        protected static function checkInterfaceExists(array $interfaces)  {
    
        }
    }  

其中 `newProxyInstance` 和 `generateClass` 代码：

    public static function newProxyInstance($target, array $interfaces, InvocationHandler $handler)  {
            self::checkInterfaceExists ($interfaces);
            $code = self::generateClass ($interfaces);
            return eval($code);
        }

    protected static function generateClass(array $interfaces)
        {
            $interfaceList = implode(',', $interfaces);
            $functionList = '';
            foreach ($interfaces as $interface) {
                $class = new ReflectionClass ($interface);
                $methods = $class->getMethods();
                foreach ($methods as $method){
                    $parameters = [];
                    foreach ($method->getParameters() as $parameter){
                        $parameters[] = '$' . $parameter->getName();
                    }
                    $functionList .= sprintf( self::FUNCTION_TEMPLATE, $method->getName(), implode( ',', $parameters ) );
                }
            }
            return sprintf ( self::CLASS_TEMPLATE, $interfaceList, $functionList );
        }

其中generateClass就是通过反射扫描接口方法，然后根据 stub 模板生成方法拼接成代码，最后通过 `eval` 执行。

2.测试

    interface Test1{
        public function t1();
    }
    interface Test2{
        public function t2();
    }
    class TestImpl implements Test1,Test2{
        public function t1(){
            echo 't1';
        }
        public function t2(){
            echo 't2';
        }
    }
    $impl = new TestImpl();
    class Handler implements InvocationHandler {
        private $target;
        public function __construct($impl){
            $this->target = $impl;
        }
        function invoke(ReflectionMethod $method, array $arr_args){
            echo '前置操作';
            $method->invokeArgs($this->target, $arr_args);
            echo '后置操作';
        }
    }
    
    $proxy = Proxy::newProxyInstance($impl, ['Test1', 'Test2'], new Handler($impl));
    $proxy->t1();

输出：

    前置操作
    t1
    后置操作

## 依赖注入

依赖注入是现代化框架中非常常见的一个功能，它必须和服务容器结合使用。用过 Laravel 框架的童鞋应该很熟悉，我们可以在任意需要服务的地方通过类型提示声明，运行时框架就会自动帮我们注入所需要的对象。以 Laravel 框架的源码简单解析下：  
在 Laravel 框架中，我们解析一个对象的方法可以这样：

    $obj = App::make(ClassName);

make方法实际上底层也是调用了Illuminate\Container\Contaiern::build($concrete)这个方法，整理一下源码就是：

    public function build($concrete){
            $reflector = new ReflectionClass($concrete);
            $constructor = $reflector->getConstructor();
    
            if (is_null($constructor)) {
                return new $concrete;
            }
    
            $dependencies = $constructor->getParameters();
            $instances = $this->resolveDependencies($dependencies);
            return $reflector->newInstanceArgs($instances);
        }

实际代码很简单，反射类获取构造方法，然后解析依赖参数，传入执行。


# 详解PHP反射API

 时间 2018-01-03 10:07:00  

原文[http://www.cnblogs.com/bndong/p/7988365.html][1]


PHP中的反射API就像Java中的java.lang.reflect包一样。它由一系列可以分析属性、方法和类的内置类组成。它在某些方面和对象函数相似，比如get_class_vars()，但是更加灵活，而且可以提供更多信息。反射API也可与PHP最新的面向对象特性一起工作，如访问控制、接口和抽象类。旧的类函数则不太容易与这些新特性一起使用。看过框架源码的朋友应该对PHP的反射机制有一定的了解，像是依赖注入，对象池，类加载，一些设计模式等等，都用到了反射机制。

## 1. 反射API的部分类

**类** | **描 述** 
-|-
Reflection | 为类的摘要信息提供静态函数export() 
ReflectionClass | 类信息和工具 
ReflectionMethod | 类方法信息和工具 
ReflectionParameter | 方法参数信息 
ReflectionProperty | 类属性信息 
ReflectionFunction | 函数信息和工具 
ReflectionExtension | PHP扩展信息 
ReflectionException | 错误类 

使用反射API这些类，我们可以获得在运行时访问对象、函数和脚本中的扩展的信息。通过这些信息我们可以用来分析类或者构建框架。

## 2. 获取类的信息

我们在工作中使用过一些用于检查类属性的函数，例如：get_class_methods、getProduct等。这些方法对获取详细类信息有很大的局限性。

我们可以通过反射API类：Reflection 和 ReflectionClass 提供的静态方法 export 来获取类的相关信息， export 可以提供类的几乎所有的信息，包括属性和方法的访问控制状态、每个方法需要的参数以及每个方法在脚本文档中的位置。 这两个工具类， export 静态方法输出结果是一致的，只是使用方式不同。

首先，构建一个简单的类

    <?php
    
    class Student {
        public    $name;
        protected $age;
        private   $sex;
    
        public function __construct($name, $age, $sex)
        {
            $this->setName($name);
            $this->setAge($age);
            $this->setSex($sex);
        }
    
        public function setName($name)
        {
            $this->name = $name;
        }
    
        protected function setAge($age)
        {
            $this->age = $age;
        }
    
        private function setSex($sex)
        {
            $this->sex = $sex;
        }
    }
    

## 2.1 使用 ReflectionClass::export() 获取类信息

    ReflectionClass::export('Student');
    

打印结果：

    Class [ class Student ] {
        @@ D:\wamp\www\test2.php 3-29
        - Constants [0] { }
        - Static properties [0] { }
        - Static methods [0] { }
        - Properties [3] {
            Property [ public $name ]
            Property [ protected $age ]
            Property [ private $sex ]
        }
        - Methods [4] {
            Method [ public method __construct ] {
                @@ D:\wamp\www\test2.php 8 - 13
                - Parameters [3] {
                    Parameter #0 [ $name ]
                    Parameter #1 [ $age ]
                    Parameter #2 [ $sex ]
                }
            }
            Method [ public method setName ] {
                @@ D:\wamp\www\test2.php 15 - 18
                - Parameters [1] {
                    Parameter #0 [ $name ]
                }
            }
            Method [ protected method setAge ] {
                @@ D:\wamp\www\test2.php 20 - 23
                - Parameters [1] {
                    Parameter #0 [ $age ]
                }
            }
            Method [ private method setSex ] {
                @@ D:\wamp\www\test2.php 25 - 28
                - Parameters [1] {
                    Parameter #0 [ $sex ]
                }
            }
        }
    }
    
    ReflectionClass::export() 输出

ReflectionClass类提供了非常多的工具方法，官方手册给的列表如下：

    ReflectionClass::__construct — 初始化 ReflectionClass 类
    ReflectionClass::export — 导出一个类
    ReflectionClass::getConstant — 获取定义过的一个常量
    ReflectionClass::getConstants — 获取一组常量
    ReflectionClass::getConstructor — 获取类的构造函数
    ReflectionClass::getDefaultProperties — 获取默认属性
    ReflectionClass::getDocComment — 获取文档注释
    ReflectionClass::getEndLine — 获取最后一行的行数
    ReflectionClass::getExtension — 根据已定义的类获取所在扩展的 ReflectionExtension 对象
    ReflectionClass::getExtensionName — 获取定义的类所在的扩展的名称
    ReflectionClass::getFileName — 获取定义类的文件名
    ReflectionClass::getInterfaceNames — 获取接口（interface）名称
    ReflectionClass::getInterfaces — 获取接口
    ReflectionClass::getMethod — 获取一个类方法的 ReflectionMethod。
    ReflectionClass::getMethods — 获取方法的数组
    ReflectionClass::getModifiers — 获取类的修饰符
    ReflectionClass::getName — 获取类名
    ReflectionClass::getNamespaceName — 获取命名空间的名称
    ReflectionClass::getParentClass — 获取父类
    ReflectionClass::getProperties — 获取一组属性
    ReflectionClass::getProperty — 获取类的一个属性的 ReflectionProperty
    ReflectionClass::getReflectionConstant — Gets a ReflectionClassConstant for a class's constant
    ReflectionClass::getReflectionConstants — Gets class constants
    ReflectionClass::getShortName — 获取短名
    ReflectionClass::getStartLine — 获取起始行号
    ReflectionClass::getStaticProperties — 获取静态（static）属性
    ReflectionClass::getStaticPropertyValue — 获取静态（static）属性的值
    ReflectionClass::getTraitAliases — 返回 trait 别名的一个数组
    ReflectionClass::getTraitNames — 返回这个类所使用 traits 的名称的数组
    ReflectionClass::getTraits — 返回这个类所使用的 traits 数组
    ReflectionClass::hasConstant — 检查常量是否已经定义
    ReflectionClass::hasMethod — 检查方法是否已定义
    ReflectionClass::hasProperty — 检查属性是否已定义
    ReflectionClass::implementsInterface — 接口的实现
    ReflectionClass::inNamespace — 检查是否位于命名空间中
    ReflectionClass::isAbstract — 检查类是否是抽象类（abstract）
    ReflectionClass::isAnonymous — 检查类是否是匿名类
    ReflectionClass::isCloneable — 返回了一个类是否可复制
    ReflectionClass::isFinal — 检查类是否声明为 final
    ReflectionClass::isInstance — 检查类的实例
    ReflectionClass::isInstantiable — 检查类是否可实例化
    ReflectionClass::isInterface — 检查类是否是一个接口（interface）
    ReflectionClass::isInternal — 检查类是否由扩展或核心在内部定义
    ReflectionClass::isIterateable — 检查是否可迭代（iterateable）
    ReflectionClass::isSubclassOf — 检查是否为一个子类
    ReflectionClass::isTrait — 返回了是否为一个 trait
    ReflectionClass::isUserDefined — 检查是否由用户定义的
    ReflectionClass::newInstance — 从指定的参数创建一个新的类实例
    ReflectionClass::newInstanceArgs — 从给出的参数创建一个新的类实例。
    ReflectionClass::newInstanceWithoutConstructor — 创建一个新的类实例而不调用它的构造函数
    ReflectionClass::setStaticPropertyValue — 设置静态属性的值
    ReflectionClass::__toString — 返回 ReflectionClass 对象字符串的表示形式。
    
    ReflectionClass

## 2.2 使用 Reflection::export() 获取类信息

    $prodClass = new ReflectionClass('Student');
    Reflection::export($prodClass);
    

打印结果

    Class [ class Student ] {
        @@ D:\wamp\www\test2.php 3-29
        - Constants [0] { }
        - Static properties [0] { }
        - Static methods [0] { }
        - Properties [3] {
            Property [ public $name ]
            Property [ protected $age ]
            Property [ private $sex ]
        }
        - Methods [4] {
            Method [ public method __construct ] {
                @@ D:\wamp\www\test2.php 8 - 13
                - Parameters [3] {
                    Parameter #0 [ $name ]
                    Parameter #1 [ $age ]
                    Parameter #2 [ $sex ]
                }
            }
            Method [ public method setName ] {
                @@ D:\wamp\www\test2.php 15 - 18
                - Parameters [1] {
                    Parameter #0 [ $name ]
                }
            }
            Method [ protected method setAge ] {
                @@ D:\wamp\www\test2.php 20 - 23
                - Parameters [1] {
                    Parameter #0 [ $age ]
                }
            }
            Method [ private method setSex ] {
                @@ D:\wamp\www\test2.php 25 - 28
                - Parameters [1] {
                    Parameter #0 [ $sex ]
                }
            }
        }
    }
    
    Reflection::export() 输出

创建 ReflectionClass对象后，就可以使用 Reflection 工具类输出 Student 类的相关信息。Reflection::export() 可以格式化和输出任何实现 Reflector 接口的类的实例。

## 3. 检查类

前面我们了解的 ReflectionClass 工具类，知道此类提供了很多的工具方法用于获取类的信息。例如，我们可以获取到 Student 类的类型，是否可以实例化

工具函数

    function classData(ReflectionClass $class) {
        $details = '';
        $name = $class->getName();          // 返回要检查的类名
        if ($class->isUserDefined()) {      // 检查类是否由用户定义
            $details .= "$name is user defined" . PHP_EOL;
        }
        if ($class->isInternal()) {         // 检查类是否由扩展或核心在内部定义
            $details .= "$name is built-in" . PHP_EOL;
        }
        if ($class->isInterface()) {        // 检查类是否是一个接口
            $details .= "$name is interface" . PHP_EOL;
        }
        if ($class->isAbstract()) {         // 检查类是否是抽象类
            $details .= "$name is an abstract class" . PHP_EOL;
        }
        if ($class->isFinal()) {            // 检查类是否声明为 final
            $details .= "$name is a final class" . PHP_EOL;
        }
        if ($class->isInstantiable()) {     // 检查类是否可实例化
            $details .= "$name can be instantiated" . PHP_EOL;
        } else {
            $details .= "$name can not be instantiated" . PHP_EOL;
        }
        return $details;
    }
    
    $prodClass = new ReflectionClass('Student');
    print classData($prodClass);
    

打印结果

    Student is user defined
    Student can be instantiated
    

除了获取类的相关信息，还可以获取 ReflectionClass 对象提供自定义类所在的文件名及文件中类的起始和终止行等相关源代码信息。

    function getClassSource(ReflectionClass $class) {
        $path  = $class->getFileName();  // 获取类文件的绝对路径
        $lines = @file($path);           // 获得由文件中所有行组成的数组
        $from  = $class->getStartLine(); // 提供类的起始行
        $to    = $class->getEndLine();   // 提供类的终止行
        $len   = $to - $from + 1;
        return implode(array_slice($lines, $from - 1, $len));
    }
    
    $prodClass = new ReflectionClass('Student');
    var_dump(getClassSource($prodClass));
    

打印结果

    string 'class Student {
        public    $name;
        protected $age;
        private   $sex;
    
        public function __construct($name, $age, $sex)
        {
            $this->setName($name);
            $this->setAge($age);
            $this->setSex($sex);
        }
    
        public function setName($name)
        {
            $this->name = $name;
        }
    
        protected function setAge($age)
        {
            $this->age = $age;
        }
    
        private function setSex($sex)
        {
            $this->sex = $sex;
        }
    }
    ' (length=486)
    

我们看到 getClassSource 接受一个 ReflectionClass 对象作为它的参数，并返回相应类的源代码。该函数忽略了错误处理，在实际中应该要检查参数和结果代码！

## 4. 检查方法

类似于检查类，ReflectionMethod 对象可以用于检查类中的方法。

获得 ReflectionMethod 对象的方法有两种：

第一种是通过 ReflectionClass::getMethods() 获得 ReflectionMethod 对象的数组，这种方式的好处是不用提前知道方法名，会返回类中所有方法的 ReflectionMethod 对象。

第二种是直接使用 ReflectionMethod 类实例化对象，这种方式只能获取一个类方法对象，需要提前知道方法名。

ReflectionMethod 对象的工具方法：

    ReflectionMethod::__construct — ReflectionMethod 的构造函数
    ReflectionMethod::export — 输出一个回调方法
    ReflectionMethod::getClosure — 返回一个动态建立的方法调用接口，译者注：可以使用这个返回值直接调用非公开方法。
    ReflectionMethod::getDeclaringClass — 获取反射函数调用参数的类表达
    ReflectionMethod::getModifiers — 获取方法的修饰符
    ReflectionMethod::getPrototype — 返回方法原型 (如果存在)
    ReflectionMethod::invoke — Invoke
    ReflectionMethod::invokeArgs — 带参数执行
    ReflectionMethod::isAbstract — 判断方法是否是抽象方法
    ReflectionMethod::isConstructor — 判断方法是否是构造方法
    ReflectionMethod::isDestructor — 判断方法是否是析构方法
    ReflectionMethod::isFinal — 判断方法是否定义 final
    ReflectionMethod::isPrivate — 判断方法是否是私有方法
    ReflectionMethod::isProtected — 判断方法是否是保护方法 (protected)
    ReflectionMethod::isPublic — 判断方法是否是公开方法
    ReflectionMethod::isStatic — 判断方法是否是静态方法
    ReflectionMethod::setAccessible — 设置方法是否访问
    ReflectionMethod::__toString — 返回反射方法对象的字符串表达
    
    ReflectionMethod

## 4.1 ReflectionClass::getMethods()

我们可以通过 ReflectionClass::getMethods() 获得 ReflectionMethod 对象的数组。

    $prodClass = new ReflectionClass('Student');
    $methods = $prodClass->getMethods();
    var_dump($methods);
    

打印结果

    array (size=4)
      0 => &
        object(ReflectionMethod)[2]
          public 'name' => string '__construct' (length=11)
          public 'class' => string 'Student' (length=7)
      1 => &
        object(ReflectionMethod)[3]
          public 'name' => string 'setName' (length=7)
          public 'class' => string 'Student' (length=7)
      2 => &
        object(ReflectionMethod)[4]
          public 'name' => string 'setAge' (length=6)
          public 'class' => string 'Student' (length=7)
      3 => &
        object(ReflectionMethod)[5]
          public 'name' => string 'setSex' (length=6)
          public 'class' => string 'Student' (length=7)
    

可以看到我们获取到了 Student 的 ReflectionMethod 对象数组，每个元素是一个对象，其中有两个公共的属性，name 为方法名，class 为所属类。我们可以调用对象方法来获取方法的信息。

## 4.2 ReflectionMethod

直接使用 ReflectionMethod 类获取类方法有关信息

    $method = new ReflectionMethod('Student', 'setName');
    var_dump($method);
    

打印结果

    object(ReflectionMethod)[1]
      public 'name' => string 'setName' (length=7)
      public 'class' => string 'Student' (length=7)
    

## 4.3 注意

在PHP5中，如果被检查的方法只返回对象（即使对象是通过引用赋值或传递的），那么 ReflectionMethod::retursReference() 不会返回 true。只有当被检测的方法已经被明确声明返回引用（在方法名前面有&符号）时，ReflectionMethod::returnsReference() 才返回 true。

## 5. 检查方法参数

在PHP5中，声明类方法时可以限制参数中对象的类型，因此检查方法的参数变得非常必要。

类似于检查方法，ReflectionParameter 对象可以用于检查类中的方法，该对象可以告诉你参数的名称，变量是否可以按引用传递，还可以告诉你参数类型提示和方法是否接受空值作为参数。

获得 ReflectionParameter 对象的方法有同样两种，这和获取 ReflectionMethod 对象非常类似：

第一种是通过 ReflectionMethod::getParameters() 方法返回 ReflectionParameter 对象数组，这种方法可以获取到一个方法的全部参数对象。

第二种是直接使用 ReflectionParameter 类实例化获取对象，这种方法只能获取到单一参数的对象。

ReflectionParameter 对象的工具方法：

    ReflectionParameter::allowsNull — Checks if null is allowed
    ReflectionParameter::canBePassedByValue — Returns whether this parameter can be passed by value
    ReflectionParameter::__clone — Clone
    ReflectionParameter::__construct — Construct
    ReflectionParameter::export — Exports
    ReflectionParameter::getClass — Get the type hinted class
    ReflectionParameter::getDeclaringClass — Gets declaring class
    ReflectionParameter::getDeclaringFunction — Gets declaring function
    ReflectionParameter::getDefaultValue — Gets default parameter value
    ReflectionParameter::getDefaultValueConstantName — Returns the default value's constant name if default value is constant or null
    ReflectionParameter::getName — Gets parameter name
    ReflectionParameter::getPosition — Gets parameter position
    ReflectionParameter::getType — Gets a parameter's type
    ReflectionParameter::hasType — Checks if parameter has a type
    ReflectionParameter::isArray — Checks if parameter expects an array
    ReflectionParameter::isCallable — Returns whether parameter MUST be callable
    ReflectionParameter::isDefaultValueAvailable — Checks if a default value is available
    ReflectionParameter::isDefaultValueConstant — Returns whether the default value of this parameter is constant
    ReflectionParameter::isOptional — Checks if optional
    ReflectionParameter::isPassedByReference — Checks if passed by reference
    ReflectionParameter::isVariadic — Checks if the parameter is variadic
    ReflectionParameter::__toString — To string
    
    ReflectionParameter

## 5.1 ReflectionMethod::getParameters()

同获取方法，此方法会返回一个数组，包含方法每个参数的 ReflectionParameter 对象

    $method = new ReflectionMethod('Student', 'setName');
    $params = $method->getParameters();
    var_dump($params);
    

打印结果

    array (size=1)
      0 => &
        object(ReflectionParameter)[2]
          public 'name' => string 'name' (length=4)
    

## 5.2 ReflectionParameter

我们来了解一下这种方式，为了更好的理解，我修改一下 Student 类的 setName方法，增加两个参数 a, b

    ...
        public function setName($name, $a, $b)
        {
            $this->name = $name;
        }
    ...
    

首先我们看一下 ReflectionParameter 类的构造方法

    public ReflectionParameter::__construct ( string $function , string $parameter )
    

可以看到该类实例化时接收两个参数：

 $function ：当需要获取函数为公共函数时只需传函数名称即可。当该函数是某个类方法时，需要传递一个数组，格式为：array('class', 'function')。 

 $parameter ：这个参数可以传递两种，第一种为参数名（无$符号），第二种为参数索引。注意：无论是参数名还是索引，该参数都必须存在，否则会报错。 

下面举例：

    $params = new ReflectionParameter(array('Student', 'setName'), 1);
    var_dump($params);
    

打印结果

    object(ReflectionParameter)[1]
      public 'name' => string 'a' (length=1)
    

我们再定义一个函数测试一下

    function foo($a, $b, $c) { }
    $reflect = new ReflectionParameter('foo', 'c');
    var_dump($reflect);
    

打印结果

    object(ReflectionParameter)[2]
      public 'name' => string 'c' (length=1)
    

## 6. 结语

php的反射API功能非常的强大，它可以将一个类的详细信息获取出来。我们可以通过反射API编写个类来动态调用Module对象，该类可以自由加载第三方插件并集成进已有的系统。而不需要把第三方的代码硬编码进原有的代码中。虽然实际开发中使用反射情况比较少，但了解反射API对工作中对代码结构的了解和开发业务模式帮助还是非常大的。此篇博文断断续续的写了很久（主要就是懒！），如有错误与不足欢迎指正，建议！！

[1]: http://www.cnblogs.com/bndong/p/7988365.html

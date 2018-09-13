# [PHP反射API][0] 

近期忙着写项目，没有学习什么特别新的东西，所以好长时间没有更新博客。我们的项目用的是lumen，是基于laravel的一个轻量级框架，我看到里面用到了一些反射API机制来帮助动态加载需要的类、判断方法等，所以本篇文章就把在PHP中经常用到的反射API给大家分享一下吧，想学习反射API的同学可以看看。

说起反射ApI，自我感觉PHP中的反射ApI和java中的java.lang.reflect包差不多，都是由可以打印和分析类成员属性、方法的一组内置类组成的。可能你已经学习过对象函数比如：get_class_vars()但是使用反射API会更加的灵活、输出信息会更加详细。

首先我们需要知道，反射API不仅仅是用来检查类的，它本身包括一组类，用来完成各种功能：常用的类如下：

 Reflection类  可以打印类的基本信息，（通过提供的静态export()函数）  ReflectionMethod类  见名知意，打印类方法、得到方法的具体信息等  ReflectionClass类  用于得到类信息，比如得到类包含的方法，类本的属性，是不是抽象类等  ReflectionParameter类  显示参数的信息，可以动态得到已知方法的传参情况  ReflectionException类  用于显示错误信息  ReflectionExtension类  得到PHP的扩展信息，可以判断扩展是否存在等

**传统的打印类信息与反射APi的区别**  
下面是一段我自己写的参数程序，用于演示反射的使用：
```php
<?php

class Person
{
    //成员属性
    public $name;
    public $age; 

    //构造方法
    public function __construct($name, $age)
    {
        $this->name = $name;
        $this->age = $age;
    }

    //成员方法
    public function set_name($name)
    {
        $this->$name = $name;
    }

    public function get_name()
    {
        return $this->$name;
    }

    public function get_age()
    {
        return $this->$age;
    }

    public function get_user_info()
    {
        $info = '姓名：' . $this->name;
        $info .= ' 年龄：' . $this->age;
        return $info;
    }
}

class Teacher extends Person
{
    private $salary = 0;

    public function __construct($name, $age, $salary)
    {
        parent::__construct($name, $age);
        $this->salary = $salary;
    }

    public function get_salary()
    {
        return $this->$salary;
    }

    public function get_user_info()
    {
        $info = parent::get_user_info();
        $info .= " 工资：" . $this->salary;
        return $info;
    }
}

class Student extends Person
{
    private $score = 0;

    public function __construct($name, $age, $score)
    {
        parent::__construct($name, $age);
        $this->score = $score;
    }

    public function get_score()
    {
        return $this->score;        
    }

    public function get_user_info()
    {
        $info = parent::get_user_info();
        $info .= " 成绩：" . $this->score;
        return $info;
    }
}

header("Content-type:text/html;charset=utf8;");
$te_obj = new Teacher('李老师', '36', '2000');
$te_info = $te_obj->get_user_info();

$st_obj = new Student('小明', '13', '80');
$st_info = $st_obj->get_user_info();
```

我们先用var_dump();打印类的信息，如下所示，可以看出只是打印出类的简单信息，甚至连方法也没有，所以从这样的信息中看不出其他游泳的信息。
```
var_dump( $te_obj );
```
```
object(Teacher)#1 (3) {
      ["salary":"Teacher":private]=>
          string(4) "2000"
      ["name"]=>
          string(9) "李老师"
      ["age"]=>
          string(2) "36"
}
```

 Reflection::export($obj);

我们利用Reflection提供的内置方法export来打印信息，如下所示：

打印出的信息比较完整，包括成员属性，成员方法，类的基本信息，文件路径，方法信息，方法属性，传参情况，所在文件的行数等等。比较全面的展示了类的信息。可以看出var_dump()或者print_r只能显示类的简要信息，好多信息根本显示不出来，所以他们只能做简单的调试之用，反射Api则提供的类更多的信息，可以很好地帮助我们知道调用类的情况，这对写接口，特别是调用别人的接口提供了极大的便利。如果出了问题，也可以帮助调试。

 
    object(Teacher)#1 (3) {
          ["salary":"Teacher":private]=>
              string(4) "2000"
          ["name"]=>
              string(9) "李老师"
          ["age"]=>
              string(2) "36"
    }
    Class [  class Person ] {
          @@ /usr/local/www/phptest/oop/reflaction.php 3-38
          - Constants [0] {
          }
          - Static properties [0] {
          }
          - Static methods [0] {
     }
      - Properties [2] {
            Property [  public $name ]
            Property [  public $age ]
      }
    
      - Methods [5] {
        Method [  public method __construct ] {
          @@ /usr/local/www/phptest/oop/reflaction.php 10 - 14
    
          - Parameters [2] {
            Parameter #0 [  $name ]
      
.....                


**反射API的具体使用:**

看过框架源码的同学都知道框架都可以加载第三方的插件、类库等等。下面这个例子咱们借助反射APi简单实现这个功能，该例子原型是我从书上学习的，我理解后按照自己的思路写了一套：要实现的功能：用一个类去动态的遍历调用Property类对象，类可以自由的加载其他的类的方法，而不用吧类嵌入到已有的代码，也不用手动去调用类库的代码。  
约定：每一个类要包含work方法，可以抽象出一个接口。可以把每个类的信息放在文件中，相当于各个类库信息，通过类保存的Property类库的对应对象，然后调用每个类库的work方法。

下面是基础代码：

```php
/*属性接口*/
interface Property
{
    function work();
}

class Person
{
    public $name;
    public function __construct($name)
    {
        $this->name = $name;
    }
}

class StudentController implements Property
{
    //set方法，但需要Person对象参数
    public function setPerson(Person $obj_person)
    {
        echo 'Student ' . $obj_person->name;
    }

    //work方法简单实现
    public function work()
    {
        echo 'student working!';
    }
}

class EngineController implements Property
{
    //set方法
    public function setWeight($weight)
    {
        echo 'this is engine -> set weight';
    }

    public function setPrice($price)
    {
        echo "this is engine -> set price";
    }

    //work方法简单实现
    public function work()
    {
        echo 'engine working!';
    }
}
```


这里定义了两个相似类实现Property接口，同时都简单实现work()方法 StudentController类稍微不同，参数需要Person对象，同时我们可以使用文件来保存各个类的信息，我们也可以用成员属性代替。

```php
class Run
{
    public static $mod_arr = [];
    public static $config = [
        'StudentController' => [
            'person' => 'xiao ming'
        ],
        'EngineController'  => [
            'weight' => '500kg',
            'price'  => '4000'
        ]
    ];

    //加载初始化
    public function __construct()
    {
        $config = self::$config;
        //用于检查是不是实现类
        $property = new ReflectionClass('Property');
        foreach ($config as $class_name => $params) {
            $class_reflect = new ReflectionClass($class_name);
            if(!$class_reflect->isSubclassOf($property)) {//用isSubclassOf方法检查是否是这个对象
                echo 'this is  error';
                continue;
            }

            //得到类的信息
            $class_obj = $class_reflect->newInstance();
            $class_method = $class_reflect->getMethods();

            foreach ($class_method as $method_name) {
                $this->handle_method($class_obj, $method_name, $params);
            }
            array_push(self::$mod_arr, $class_obj);
        }
    }

    //处理方法调用
    public function handle_method(Property $class_obj, ReflectionMethod $method_name, $params)
    {
        $m_name = $method_name->getName();
        $args = $method_name->getParameters();

        if(count($args) != 1 || substr($m_name, 0, 3) != 'set') {    
            return false;
        }
        //大小写转换，做容错处理
        $property = strtolower(substr($m_name, 3));
　　　　　
        if(!isset($params[$property])) {
            return false;
        }

        $args_class = $args[0]->getClass();
        echo '<pre>';
        if(empty($args_class)) {
            $method_name->invoke($class_obj, $params[$property]); //如果得到的类为空证明需要传递基础类型参数
        } else {
            $method_name->invoke($class_obj, $args_class->newInstance($params[$property])); //如果不为空说明需要传递真实对象
        }
    }
}

//程序开始
new Run();
```
到此程序结束，Run启动会自动调用构造方法，初始化要加载类库的其他成员属性，包括初始化和执行相应方法操作，这里只是完成了对应的set方法。其中 $mod_arr属性保存了所有调用类的对象，每个对象包含数据，可以遍历包含的对象来以此调用work()方法。

程序只做辅助理解反射PAI用，各个功能没有完善，里面用到了好多反射API的类，方法，下面会有各个方法的总结。

**反射API 提供的常用类和函数：**

下面提供的函数是常用的函数，不是全部，有的函数根本用不到，所以我们有往撒谎那个写，想看全部的可以到网上搜一下，比较多。提供的这组方法没有必要背下来，用到的时候可以查看。


     1：Reflection
    　　public static export(Reflector r [,bool return])//打印类或方法的详细信息
    　　public static  getModifierNames(int modifiers)  //取得修饰符的名字
    
    2：ReflectionMethod：
        public static string export()                       //打印该方法的信息
        public mixed invoke(stdclass object, mixed* args)   //调用对应的方法
        public mixed invokeArgs(stdclass object, array args)//调用对应的方法，传多参数
        public bool isFinal()        //方法是否为final
        public bool isAbstract()     //方法是否为abstract
        public bool isPublic()       //方法是否为public
        public bool isPrivate()      //方法是否为private
        public bool isProtected()    //方法是否为protected
        public bool isStatic()       //方法是否为static
        public bool isConstructor()  //方法是否为构造函数
    
    3：ReflectionClass：
        public static string export()  //打印类的详细信息
        public string getName()        //取得类名或接口名
        public bool isInternal()       //类是否为系统内部类
        public bool isUserDefined()    //类是否为用户自定义类
        public bool isInstantiable()   //类是否被实例化过
        public bool hasMethod(string name)  //类是否有特定的方法
        public bool hasProperty(string name)//类是否有特定的属性
        public string getFileName()         //获取定义该类的文件名，包括路径名
        public int getStartLine()           //获取定义该类的开始行
        public int getEndLine()             //获取定义该类的结束行
        public string getDocComment()       //获取该类的注释
        public ReflectionMethod getConstructor()           //取得该类的构造函数信息
        public ReflectionMethod getMethod(string name)     //取得该类的某个特定的方法信息
        public ReflectionMethod[] getMethods()             //取得该类的所有的方法信息
        public ReflectionProperty getProperty(string name) //取得某个特定的属性信息
        public ReflectionProperty[] getProperties()        //取得该类的所有属性信息
        public array getConstants()                        //取得该类所有常量信息
        public mixed getConstant(string name)              //取得该类特定常量信息
        public ReflectionClass[] getInterfaces()           //取得接口类信息
        public bool isInterface()  //测试该类是否为接口
        public bool isAbstract()   //测试该类是否为抽象类
    
    4：ReflectionParameter：
        public static string export()     //导出该参数的详细信息
        public string getName()           //取得参数名
        public bool isPassedByReference() //测试该参数是否通过引用传递参数
        public ReflectionClass getClass() //若该参数为对象，返回该对象的类名
        public bool isArray()             //测试该参数是否为数组类型
        public bool allowsNull()          //测试该参数是否允许为空
        public bool isOptional()          //测试该参数是否为可选的，当有默认参数时可选
        public bool isDefaultValueAvailable() //测试该参数是否为默认参数
        public mixed getDefaultValue()        //取得该参数的默认值
    
    5：ReflectionExtension类
        public static  export()    //导出该扩展的所有信息
        public string getName()    //取得该扩展的名字
        public string getVersion() //取得该扩展的版本
        public ReflectionFunction[] getFunctions()   //取得该扩展的所有函数
        public array getConstants()  //取得该扩展的所有常量
        public array getINIEntries() //取得与该扩展相关的，在php.ini中的指令信息




写的比较急，难免会有错误，还请大神们多多指正。

转载请注明出处：[http://www.cnblogs.com/zyf-zhaoyafei/p/4922893.html][0]

本博客同步更新到我的个人网站：[www.zhaoyafei.cn][1]

[0]: http://www.cnblogs.com/zyf-zhaoyafei/p/4922893.html
[1]: http://www.zhaoyafei.cn
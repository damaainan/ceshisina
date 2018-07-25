## PHP 依赖注入，从此不再考虑加载顺序

来源：[http://www.cnblogs.com/painsOnline/p/5138806.html](http://www.cnblogs.com/painsOnline/p/5138806.html)

时间 2016-01-18 13:49:00

说这个话题之前先讲一个比较高端的思想--' **`依赖倒置原则`** '

"依赖倒置是一种软件设计思想，在传统软件中，上层代码依赖于下层代码，当下层代码有所改动时，上层代码也要相应进行改动，因此维护成本较高。而依赖倒置原则的思想是，上层不应该依赖下层，应依赖接口。意为上层代码定义接口，下层代码实现该接口，从而使得下层依赖于上层接口，降低耦合度，提高系统弹性"

上面的解释有点虚，下面我们以实际代码来解释这个理论

比如有这么条需求，用户注册完成后要发送一封邮件，然后你有如下代码：

先有邮件类'Email.class.php'

```php

class Mail{
    public function send()
    {
        /*这里是如何发送邮件的代码*/
    }
}

```

然后又注册的类'Register.class.php'

```php

class Register{
    private $_emailObj;

    public function doRegister()
    {
        /*这里是如何注册*/

        $this->_emailObj = new Mail();
        $this->_emailObj->send();//发送邮件
    }
}

```

然后开始注册

```php

include 'Mail.class.php';
include 'Register.class.php';
$reg = new Register();
$reg->doRegister();

```

看起来事情很简单，你很快把这个功能上线了，看起来相安无事... xxx天过后，产品人员说发送邮件的不好，要使用发送短信的，然后你说这简单我把'Mail'类改下...

又过了几天，产品人员说发送短信费用太高，还是改用邮件的好...  此时心中一万个草泥马奔腾而过...

这种事情，常常在产品狗身上发生，无可奈何花落去...

以上场景的问题在于，你每次不得不对'Mail'类进行修改，代码复用性很低，高层过度依赖于底层。那么我们就考虑'依赖倒置原则'，让底层继承高层制定的接口，高层依赖于接口。

```php

interface Mail
{
    public function send();
}

```

```php

class Email implements Mail()
{
    public function send()
    {
        //发送Email
    }
}

```

```php

class SmsMail implements Mail()
{
    public function send()
    {
        //发送短信
    }
}

```

```php

class Register
{
    private $_mailObj;

    public function __construct(Mail $mailObj)
    {
        $this->_mailObj = $mailObj;
    }

    public function doRegister()
    {
        /*这里是如何注册*/
        $this->_mailObj->send();//发送信息
    }
}

```

下面开始发送信息

```php

/* 此处省略若干行 */
$reg = new Register();
$emailObj = new Email();
$smsObj = new SmsMail();

$reg->doRegister($emailObj);//使用email发送
$reg->doRegister($smsObj);//使用短信发送
/* 你甚至可以发完邮件再发短信 */

```

上面的代码解决了'Register'对信息发送类的依赖，使用构造函数注入的方法，使得它只依赖于发送短信的接口，只要实现其接口中的'send'方法，不管你怎么发送都可以。上例就使用了" **`注入`** "这个思想，就像注射器一样将一个类的实例注入到另一个类的实例中去，需要用什么就注入什么。当然" **`依赖倒置原则`** "也始终贯彻在里面。" **`注入`** "不仅可以通过构造函数注入，也可以通过属性注入，上面你可以可以通过一个"setter"来动态为"mailObj"这个属性赋值。

上面看了很多，但是有心的读者可能会发现标题中" **`从此不再考虑加载顺序`** "这个字眼，你上面的不还是要考虑加载顺序吗? 不还是先得引入信息发送类，然后在引入注册类，然后再实例化吗？ 如果类一多，不照样晕！

确实如此，现实中有许多这样的案例，一开始类就那么多，慢慢的功能越来越多，人员越来越多，编写了很多类，要使用这个类必须先引入那个类，而且一定要确保顺序正确。有这么个例子, "a 依赖于b, b 依赖于c, c 依赖于 d, d 依赖于e", 要获取'a'的实例，你必须依次引入 'e,d,c,b'然后依次进行实例化，老的员工知道这个坑，跳过去了。某天来了个新人，他想实例化'a' 可是一直报错，他都不造咋回事，此时只能看看看'a'的业务逻辑，然后知道要先获取'b'的实例，然后在看'b'的业务逻辑，然后... 一天过去了，他还是没有获取到'a'的实例，然后领导来了...

那这个事情到底是新人的技术低下，还是当时架构人员的水平低下了？

现在切入话题，来实现如何不考虑加载顺序，在实现前就要明白要是不考虑加载顺序就意味着让程序自动进行加载自动进行实例化。类要实例化，只要保证完整的传递给'__construct'函数所必须的参数就OK了，在类中如果要引用其他类，也必须在构造函数中注入，否则调用时仍然会发生错误。那么我们需要一个类，来保存类实例化所需要的参数，依赖的其他类或者对象以及各个类实例化后的引用

该类命名为盒子 'Container.class.php', 其内容如下：

```php

/**
*    依赖注入类
*/
class Container{
    /**
    *@var array 存储各个类的定义  以类的名称为键
    */
    private $_definitions = array();

    /**
    *@var array 存储各个类实例化需要的参数 以类的名称为键
    */
    private $_params = array();

    /**
    *@var array 存储各个类实例化的引用
    */
    private $_reflections = array();

    /**
    * @var array 各个类依赖的类
    */
    private $_dependencies = array();

    /**
    * 设置依赖
    * @param string $class 类、方法 名称
    * @param mixed $defination 类、方法的定义
    * @param array $params 类、方法初始化需要的参数
    */
    public function set($class, $defination = array(), $params = array())
    {
        $this->_params[$class] = $params;
        $this->_definitions[$class] = $this->initDefinition($class, $defination);
    }

    /**
    * 获取实例
    * @param string $class 类、方法 名称
    * @param array $params 实例化需要的参数
    * @param array $properties 为实例配置的属性
    * @return mixed
    */
    public function get($class, $params = array(), $properties = array())
    {
        if(!isset($this->_definitions[$class]))
        {//如果重来没有声明过 则直接创建
            return $this->bulid($class, $params, $properties);
        }

        $defination = $this->_definitions[$class];

        if(is_callable($defination, true))
        {//如果声明是函数
            $params = $this->parseDependencies($this->mergeParams($class, $params));
            $obj = call_user_func($defination, $this, $params, $properties);
        }
        elseif(is_array($defination))
        {
            $originalClass = $defination['class'];
            unset($definition['class']);

            //difinition中除了'class'元素外 其他的都当做实例的属性处理
            $properties = array_merge((array)$definition, $properties);

            //合并该类、函数声明时的参数
            $params = $this->mergeParams($class, $params);
            if($originalClass === $class)
            {//如果声明中的class的名称和关键字的名称相同 则直接生成对象
                $obj = $this->bulid($class, $params, $properties);
            }
            else
            {//如果不同则有可能为别名 则从容器中获取
                $obj = $this->get($originalClass, $params, $properties);
            }
        }
        elseif(is_object($defination))
        {//如果是个对象 直接返回
            return $defination;
        }
        else
        {
            throw new Exception($class . ' 声明错误!');
        }
        return $obj;
    }

    /**
    * 合并参数
    * @param string $class 类、函数 名称
    * @param array $params 参数
    * @return array
    */
    protected function mergeParams($class, $params = array())
    {
        if(empty($this->_params[$class]))
        {
            return $params;
        }
        if(empty($params))
        {
            return $this->_params;
        }

        $result = $this->_params[$class];
        foreach($params as $key => $value) 
        {
            $result[$key] = $value;
        }
        return $result;
    }

    /**
    * 初始化声明
    * @param string $class 类、函数 名称
    * @param array $defination 类、函数的定义
    * @return mixed
    */
    protected function initDefinition($class, $defination)
    {
        if(empty($defination))
        {
            return array('class' => $class);
        }
        if(is_string($defination))
        {
            return array('class' => $defination);
        }
        if(is_callable($defination) || is_object($defination))
        {
            return $defination;
        }
        if(is_array($defination))
        {
            if(!isset($defination['class']))
            {
                $definition['class'] = $class;
            }
            return $defination;
        }
        throw new Exception($class. ' 声明错误');
    }

    /**
    * 创建类实例、函数
    * @param string $class 类、函数 名称
    * @param array $params 初始化时的参数
    * @param array $properties 属性
    * @return mixed
    */
    protected function bulid($class, $params, $properties)
    {
        list($reflection, $dependencies) = $this->getDependencies($class);

        foreach ((array)$params as $index => $param) 
        {//依赖不仅有对象的依赖 还有普通参数的依赖
            $dependencies[$index] = $param;
        }

        $dependencies = $this->parseDependencies($dependencies, $reflection);

        $obj = $reflection->newInstanceArgs($dependencies);

        if(empty($properties))
        {
            return $obj;
        }

        foreach ((array)$properties as $name => $value) 
        {
            $obj->$name = $value;
        }

        return $obj;
    }

    /**
    * 获取依赖
    * @param string $class 类、函数 名称
    * @return array
    */
    protected function getDependencies($class)
    {
        if(isset($this->_reflections[$class]))
        {//如果已经实例化过 直接从缓存中获取
            return array($this->_reflections[$class], $this->_dependencies[$class]);
        }

        $dependencies = array();
        $ref = new ReflectionClass($class);//获取对象的实例
        $constructor = $ref->getConstructor();//获取对象的构造方法
        if($constructor !== null)
        {//如果构造方法有参数
            foreach($constructor->getParameters() as $param) 
            {//获取构造方法的参数
                if($param->isDefaultValueAvailable())
                {//如果是默认 直接取默认值
                    $dependencies[] = $param->getDefaultValue();
                }
                else
                {//将构造函数中的参数实例化
                    $temp = $param->getClass();
                    $temp = ($temp === null ? null : $temp->getName());
                    $temp = Instance::getInstance($temp);//这里使用Instance 类标示需要实例化 并且存储类的名字
                    $dependencies[] = $temp;
                }
            }
        }
        $this->_reflections[$class] = $ref;
        $this->_dependencies[$class] = $dependencies;
        return array($ref, $dependencies);
    }

    /**
    * 解析依赖
    * @param array $dependencies 依赖数组
    * @param array $reflection 实例
    * @return array $dependencies
    */
    protected function parseDependencies($dependencies, $reflection = null)
    {
        foreach ((array)$dependencies as $index => $dependency) 
        {
            if($dependency instanceof Instance)
            {
                if ($dependency->id !== null) 
                {
                    $dependencies[$index] = $this->get($dependency->id);
                } 
                elseif($reflection !== null) 
                {
                    $parameters = $reflection->getConstructor()->getParameters();
                    $name = $parameters[$index]->getName();
                    $class = $reflection->getName();
                    throw new Exception('实例化类 ' . $class . ' 时缺少必要参数:' . $name);
                }   
            }
        }
        return $dependencies;
    }
}

```

下面是'Instance'类的内容，该类主要用于记录类的名称，标示是否需要获取实例

```php

class Instance{
    /**
     * @var 类唯一标示
     */
    public $id;

    /**
     * 构造函数
     * @param string $id 类唯一ID
     * @return void
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * 获取类的实例
     * @param string $id 类唯一ID
     * @return Object Instance
     */
    public static function getInstance($id)
    {
        return new self($id);
    }
}

```

然后我们在'Container.class.php'中还是实现了为类的实例动态添加属性的功能，若要动态添加属性，需使用魔术方法'__set'来实现，因此所有使用依赖加载的类需要实现该方法，那么我们先定义一个基础类 'Base.class.php',内容如下

```php

class Base{
    /**
    * 魔术方法
    * @param string $name
    * @param string $value
    * @return void
    */
    public function __set($name, $value)
    {
        $this->{$name} = $value;
    }
}

```

然后我们来实现'A,B,C'类，A类的实例 依赖于 B类的实例，B类的实例依赖于C类的实例

'A.class.php'

```php

class A extends Base{
    private $instanceB;

    public function __construct(B $instanceB)
    {
        $this->instanceB = $instanceB;
    }

    public function test()
    {
        $this->instanceB->test();
    }
}

```

'B.class.php'

```php

class B  extends Base{
    private $instanceC;

    public function __construct(C $instanceC)
    {
        $this->instanceC = $instanceC;
    }

    public function test()
    {
        return $this->instanceC->test();
    }
}

```

'C.class.php'

```php

class C  extends Base{
    public function test()
    {
        echo 'this is C!';
    }
}de

```

然后我们在'index.php'中获取'A'的实例，要实现自动加载，需要使用SPL类库的'spl_autoload_register'方法，代码如下

```php

function autoload($className)
{
    include_once $className . '.class.php';
}
spl_autoload_register('autoload', true, true);
$container = new Container;

$a = $container->get('A');
$a->test();//输出 'this is C!'

```

上面的例子看起来是不是很爽，根本都不需要考虑'B','C' (当然，这里B，C 除了要使用相应类的实例外，没有其他参数，如果有其他参数，必须显要调用'$container->set(xx)'方法进行注册，为其制定实例化必要的参数)。有细心同学可能会思考，比如我在先获取了'A'的实例，我在另外一个地方也要获取'A'的实例，但是这个地方'A'的实例需要其中某个属性不一样，我怎么做到？

你可以看到'Container' 类的 'get' 方法有其他两个参数，'$params' 和 '$properties' ， 这个'$properties' 即可实现刚刚的需求，这都依赖'__set'魔术方法，当然这里你不仅可以注册类，也可以注册方法或者对象，只是注册方法时要使用回调函数，例如

```php

$container->set('foo', function($container, $params, $config){
    print_r($params);
    print_r($config);
});

$container->get('foo', array('name' => 'foo'), array('key' => 'test'));

```

还可以注册一个对象的实例，例如

```php

class Test
{
    public function mytest()
    {
        echo 'this is a test';
    }
}

$container->set('testObj', new Test());

$test = $container->get('testObj');
$test->mytest();

```

以上自动加载，依赖控制的大体思想就是将类所要引用的实例通过构造函数注入到其内部，在获取类的实例的时候通过PHP内建的反射解析构造函数的参数对所需要的类进行加载，然后进行实例化，并进行缓存以便在下次获取时直接从内存取得

以上代码仅仅用于学习和实验，未经严格测试，请不要用于生产环境，以免产生未知bug

鄙人才疏学浅，有不足之处，欢迎补足！


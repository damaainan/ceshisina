# 拦截器interceptor（魔术方法）

 时间 2016-06-20 22:58:57  神一样的少年's

原文[http://www.godblessyuan.com/2016/06/20/拦截器interceptor（魔术方法）/][1]


拦截器可以拦截发送到未定义方法和属性的消息。

    __get($property)          ----访问未定义的属性时调用
    __set($property,$value)   ----给未定义的属性赋值时被调用
    __isset($property)        ----对未定义的属性调用isset（）时被调用
    __unset($property)        ----对未定义的属性调用unsettle（）时调用
    __call($property)         ----对未定义的方法时调用
    
    其实还有一个
    __callstatic              ----对未定义的静态方法时调用
    

## __set 和 __get样例：

```php
    < ?php
    class Person {
        private $_name;     //设置了一个私有属性
    
        function __set($property,$value){   //这里是__set方法
            $method = "set{$property}";   //这里其实就是$method = "setname",{}是php的执行代码语法，所以直接解析变量
            if(method_exists($this, $method)){  //判断是否存在这个方法
                return $this->$method($value);  //这里->$method其实就是以一个变量值为变量名的变量，其实就是会直接解析这个变量，即$this->setname(解析$method出来就是setname)
            }
        }
        function __get($property) { //get差不多
            $method = "get{$property}";
            if(method_exists($this, $method)){
                return $this->$method();
            }
        }
    
        function test() {  //这里为了测试set之后的值
            echo $this->_name;
        }
    
        function setName($name) {
            $this->_name = $name;
    
        }
        function getName() {//因为php不区分大小写，所以getname和getName是一样的
            echo "bobbbbbb";
        }
    
    }
    
    $p = new Person();
    $p->name = "bob";
    $p->test();  
    结果是bob
    -----------------
    echo $p->name;
    结果是bobbbbbb
    ?>
```
    

## __unset 和 __set 相对应的，不过 `__unset` 只会在 unset() 方法调用的时候才会生效 

## `__call`接受两个参数，一个是方法的名称，一个是传递过要调用方法的所有参数（数组），`__call`返回的任何值都会返回给客户端，就好像调用一个真实存在的方法一样。

```php
    < ?php
    class PersonWriter {  //一个PersonWriter类是负责write的
        function writeName(Person $p) {  //这里的方法是writeName，需要传入一个person对象
            print $p->getName()."\n";
        }
    }
    
    class Person{  //这个是person类
        private $writer;
    
        function __construct(PersonWriter $writer) {  //构造方法首先需要传入一个personwriter的对象，然后赋值给$writer
            $this->writer = $writer;
        }
        function __call($methodname,$args) {  //这里就是__call方法
            if(method_exists($this->writer, $methodname)){
                return $this->writer->$methodname($this); 
                //这里有3个地方：第一，因为$this->writer其实一个PersonWriter对象，因为在实例person对象的时候传入的
                //第二就是$methodname($this)其实就是PersonWriter的writeName，因为第一和第二点是一个链式调用，所以他是直接去查找PersonWriter的writeName
                //第三就是$methodname($this)这个$this，因为writeName需要传入一个person对象，而因为目前的作用域就是person作用域，所以直接用$this就能使用person对象
            }
        }
    
        function getName() {
            return "bob";
        }
    }
    
    $person = new Person(new PersonWriter);
    $person->writeName();
    ?>
```

整个过程比较拗口，而且这里出现了类似委托的实现（委托是指一个对象转发或者委托一个请求给另外一个对象，被委托的一方提原先对象处理请求），首先实例化person对象，并且实例过程中传入了personwriter对象，（因为 `__construct` 需要），然后 `__construct` 将这个对象赋值给 $writer 变量，通过 `__call` 方法，调用 $writer 其实就是调用personwriter对象，然后链式再次调用这个对象里面的writeName，由于writeName方法需要传入一个person对象，所以传递了一个 $this 给他，成功执行writeName就能够成功执行getName了。 

输出：bob

部分参考： [http://blog.sina.com.cn/s/blog_978469a60101792i.html][3]


[1]: http://www.godblessyuan.com/2016/06/20/拦截器interceptor（魔术方法）/

[3]: http://blog.sina.com.cn/s/blog_978469a60101792i.html
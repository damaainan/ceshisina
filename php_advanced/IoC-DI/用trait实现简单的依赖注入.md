## 用trait实现简单的依赖注入

来源：[https://segmentfault.com/a/1190000013736327](https://segmentfault.com/a/1190000013736327)

这里先假设一个场景：

有一个工厂(Factory)，需要工人(Worker) 使用某个机器（Machine）来生产某种产品

即有这样的依赖关系： **`Factory --> Worker --> Machine`** 
## 不使用注入

代码大概是这样子：

```php
class Machine{
    function run(){
        echo '机器开动';
    }
}
class Worker {
    function work(){
        echo "工人开动机器 -> ";
        $machine = new Machine();
        $machine -> run();
    }
}
class Factory{
    function produce(){
        echo "工厂叫工人开工 -> ";
        $worker = new Worker();
        $worker -> work();
    }
}
$factory = new Factory();
$factory -> produce();
```

可以看出来，这里所依赖的对象都由类自己在内部实例化，是一种强耦合，不利于测试和维护。比如我现在要改成另一种工人来生产，那就要改工厂内部，这是不合理的。
## 手工注入

所谓的注入，就是将类所依赖的对象的实例化操作放在类外面，同时使用Interface来作出约束:

```php
Interface Machine{
    public function run();
}
Interface Worker{
    public function work();
}
class Machine1 implements Machine{
    function run(){
        echo '机器 1 开动';
    }
}
class Machine2 implements Machine{
    function run(){
        echo '机器 2 开动';
    }
}

class Worker1 implements Worker{
    private $machine;
    public function __construct(Machine $machine){
        $this -> machine = $machine;
    }
    function work(){
        echo "工人 1 开动机器 -> ";
        $this -> machine -> run();
    }
}
class Worker2 implements Worker{
    private $machine;
    public function __construct(Machine $machine){
        $this -> machine = $machine;
    }
    function work(){
        echo "工人 2 开动机器 -> ";
        $this -> machine -> run();
    }
}

class Factory{
    private $worker;
    public function __construct(Worker $worker){
        $this -> worker = $worker;
    }
    function produce(){
        echo "工厂叫工人开工 -> ";
        $this -> worker -> work();
    }
}

$machine = new Machine1();
$worker = new Worker2($machine);
$factory = new Factory($worker);

$factory -> produce();
```

可以看出来，这样的好处是解耦。比如：可以由工人1开动机器2来生产，也可以由工人2来开动机器1来生产，以后也可以随时用新的工人(只要他会work)、Worker也可以随时换其它的机器(只要它会run)来生产。这种转换都不需要修改工厂或工人的代码。

那么问题来了，现在只是把实例化从里面移到了外面，但如果依赖的东西多了，也是很麻烦的，这就需要一个 **`自动注入`** 的机制，也就是平时经常听到的 **`注入容器`** ，常见的容器都是用到反射来实现，而且要在构造函数中声明注入的类型，相对还是比较麻烦。

在本篇，我尝试用另一种方式实现。
## trait自动注入

trait可以简单理解为可以复用的方法，下面来看看怎么用trait来实现自动注入。

思路就是用trait来实现魔术方法__get,通过该方法来自动生成依赖的对象,先看完整代码

```php
trait DI{
    private $map = ['Worker' => 'Worker1','Machine'=>'Machine2'];

    function __get($k){
        if(preg_match('/^[A-Z]/', $k)) {
            $obj =  new $this -> map[$k];
            if($obj instanceof $k){
                return $obj;
            }else{
                exit("不符约定");
            }
        }
    }
}

Interface Machine{
    public function run();
}
Interface Worker{
    public function work();
}
class Machine1 implements Machine{
    function run(){
        echo '机器 1 开动';
    }
}
class Machine2 implements Machine{
    function run(){
        echo '机器 2 开动';
    }
}
class Worker1 implements Worker{
    use DI;
    function work(){
        echo "工人 1 开动机器 -> ";
        $this -> Machine -> run();
    }
}
class Worker2 implements Worker{
    use DI;
    function work(){
        echo "工人 2 开动机器 -> ";
        $this -> Machine -> run();
    }
}
class Factory{
    use DI;
    function produce(){
        echo "工厂叫工人开工 -> ";
        $this -> Worker -> work();
    }
}

$factory = new Factory();
$factory -> produce();

```



* trait中的map用来演示实现类和接口的绑定关系，以便进行类型约束，实际应用时可以用配置或其它方式实现.

* 类中要使用依赖注入时，需声明use DI, 同时，注入的对象为首字母大写（你也可以用其它规则，相应的修改trait中的判断）


当然了，这只是一个很粗糙的演示，只实现了基本的自动注入，它还有很多问题,比如原来的类如果也有__get方法时，就会产生覆盖。

有兴趣的可以尝试完善一下看能不能在项目中实际使用。

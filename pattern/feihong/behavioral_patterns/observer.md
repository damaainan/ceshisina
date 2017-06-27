### 观察者模式

>观察者模式(Observer)是对象的行为模式，又叫发布-订阅(Publish/Subscribe)模式、模型-视图(Model/View)模式、源-监听器(Source/Listener)模式或从属者(Dependents)模式。

  观察者模式定义了一种一对多的依赖关系，让多个观察者对象同时监听某一个主题对象。这个主题对象在状态上发生变化时，会通知所有观察者对象，使它们能够自动更新自己。

观察者模式UML图：  
![](http://images2015.cnblogs.com/blog/663847/201706/663847-20170625134129304-1061334559.png)

观察者模式需要有4个角色：

- Observer观察者抽象接口，只有一个待实现方法`update()`；
- 具体观察者(ConcreteObserver)角色：实现了抽象接口，实际应用里可能是日志观察者、短信推送观察者等等；
- 主题(Subject)抽象类，用于添加观察者、触发观察者；
- 具体主题的实现类(ConcreteSubject)，通过change触发nodifyObservers方法。实际change和nodifyObservers可以合成一个。

下面以订单为例：当状态变化，需要进行相关处理，例如写日志、短信通知。

Observer观察者抽象接口：
``` php
namespace Yjc\Observer;

interface IObserver
{
    public function update($data);
}
```

具体的观察者：
``` php
namespace Yjc\Observer;

class LogObserver implements IObserver
{

    public function update($data)
    {
        echo 'write log to file.';
    }
}

class SmsObserver implements IObserver
{
    public function update($data)
    {
        echo 'send sms';
    }
}
```

抽象主题类：
``` php
namespace Yjc\Observer;

abstract class ISubject
{
    private  $observers = [];//观察者集合

    public function attach(IObserver $observer){
        array_push($this->observers, $observer);
    }

    public function detach(IObserver $observer){
        return false;
    }

    public function nodifyObservers($data){
        if(count($this->observers) == 0) return false;
        foreach ($this->observers as $observer){
            $observer->update($data);
        }
    }

    public abstract function change($data);

}
```

实现了抽象主题的订单主题：
``` php
namespace Yjc\Observer;

class OrderSubject extends ISubject
{
    public function change($data)
    {
        $this->nodifyObservers($data);
    }
}
```

测试：
``` php
$order_subject = new OrderSubject();
$order_subject->attach(new LogObserver());//写日志
$order_subject->attach(new SmsObserver());//发短信
$order_subject->change(['oid' => '1', 'flag' => 3]);//订单状态变化，触发观察者
```

输出：
```
write log to file.
send sms
```

观察者实际还区分推模型和拉模型。上述讲的是推模型。推模型主动将观察者需要的数据`$data`通过`update()`传递过去；而拉模型是主题对象不知道观察者具体需要什么数据，没有办法的情况下，干脆把自身传递给观察者，让观察者自己去按需要取值。具体查看：[《JAVA与模式》之观察者模式](http://www.cnblogs.com/java-my-life/archive/2012/05/16/2502279.html)。
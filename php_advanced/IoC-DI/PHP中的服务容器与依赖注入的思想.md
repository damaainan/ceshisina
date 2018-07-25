## PHP中的服务容器与依赖注入的思想

来源：[https://segmentfault.com/a/1190000015449325](https://segmentfault.com/a/1190000015449325)


### 依赖注入

当A类需要依赖于B类，也就是说需要在A类中实例化B类的对象来使用时候，如果B类中的功能发生改变，也会导致A类中使用B类的地方也要跟着修改，导致A类与B类高耦合。这个时候解决方式是，A类应该去依赖B类的接口，把具体的类的实例化交给外部。
就拿我们业务中常用的通知模块来说。

```php
<?php

/**
 * 定义了一个消息类
 * Class Message 
 */
class  Message{

  public function seed()
  {
      return 'seed email';

  }
}
/*
 * 订单产生的时候 需要发送消息
 */
class Order{

    protected $messager = '';

    function __construct()
    {
        $this->messager = new Message();

    }

    public function seed_msg()
    {

        return $this->messager->seed();

    }
}
$Order = new Order();
$Order->seed_msg();
```

上面的代码是我们传统的写法。首先由个消息发送的类。然后在我们需要发送消息的地方，调用发送消息的接口。有一天你需要添加一个发送短信的接口以满足不同的需求。那么你会发现你要再`Message`类里面做修改。同样也要再`Order`类里面做修改。这样就显得很麻烦。这个时候就有了依赖注入的思路。下面把代码做一个调整

```php
<?php

/**
 * 为了约束我们先定义一个消息接口
 * Interface Message
 */
interface  Message{

  public function seed();
}

/**
 * 有一个发送邮件的类
 * Class SeedEmail
 */
class SeedEmail implements Message
{

    public function seed()
    {

        return  'seed email';

        // TODO: Implement seed() method.
    }

}

/** 
 *新增一个发送短信的类
 * Class SeedSMS
 */
class SeedSMS implements Message
{
    public function seed()
    {
        return 'seed sms';
        // TODO: Implement seed() method.
    }


}
/*
 * 订单产生的时候 需要发送消息
 */
class Order{

    protected $messager = '';

    function __construct(Message $message)
    {
        $this->messager = $message;

    }
    public function seed_msg()
    {
        return $this->messager->seed();
    }
}
//我们需要发送邮件的时候
$message = new SeedEmail();
//将邮件发送对象作为参数传递给Order
$Order = new Order($message);
$Order->seed_msg();


//我们需要发送短信的时候
$message = new SeedSMS();
$Order = new Order($message);
$Order->seed_msg();
```

这样我们就实现了依赖注入的思路,是不是很方便扩展了。
### 服务容器

我理解的服务容器就是一个自动产生类的工厂。
```php
<?php
/**
 * 为了约束我们先定义一个消息接口
 * Interface Message
 */
interface  Message{

    public function seed();
}

/**
 * 有一个发送邮件的类
 * Class SeedEmail
 */
class SeedEmail implements Message
{

    public function seed()
    {

        return  'seed email';

        // TODO: Implement seed() method.
    }

}

/**
 *新增一个发送短信的类
 * Class SeedSMS
 */
class SeedSMS implements Message
{
    public function seed()
    {
        return 'seed sms';
        // TODO: Implement seed() method.
    }

}


/**
 * 这是一个简单的服务容器
 * Class Container
 */
class Container
{
    protected $binds;

    protected $instances;

    public function bind($abstract, $concrete)
    {
        if ($concrete instanceof Closure) {
            $this->binds[$abstract] = $concrete;
        } else {
            $this->instances[$abstract] = $concrete;
        }
    }

    public function make($abstract, $parameters = [])
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        array_unshift($parameters, $this);

        return call_user_func_array($this->binds[$abstract], $parameters);
    }
}

//创建一个消息工厂
$message = new  Container();
//将发送短信注册绑定到工厂里面
$message->bind('SMS',function (){
     return   new  SeedSMS();
});
//将发送邮件注册绑定到工厂
$message->bind('EMAIL',function (){
   return new  SeedEmail();
});
//需要发送短信的时候
$SMS  = $message->make('SMS');
$SMS->seed();

```
`container`是一个简单的服务容器里面有`bind`,`make`两个方法
`bind`是向容器中绑定服务对象。`make`则是从容器中取出对象。
#### bind

在`bind`方法中需要传入一个`concrete`我们可以传入一个实例对象或者是一个闭包函数。
可以看到我这全使用的是闭包函数，其实也可以这样写

```php
$sms = new  SeedSMS();
$message->bind('SMS',$sms);
```

后面这种写法与闭包相比的区别就是我们需要先实例化对象才能往容易中绑定服务。而闭包则是我们使用这个服务的时候才去实例化对象。可以看出闭包是有很多的优势的。
#### make
`make`方法就从容器中出去方法。里面首先判断了`instances`变量中是否有当前以及存在的服务对象，如果有直接返回。如果没有那么会通过`call_user_func_array`返回一个对象。`call_user_func_array`的使用可以查看
[PHP 中 call_user_func 的使用][0]

[原文地址][1]

[0]: https://www.daisc.net/show/1.html
[1]: https://www.daisc.net/show/26.html
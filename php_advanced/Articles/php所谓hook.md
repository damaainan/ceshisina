## php-&gt;所谓"hook"

来源：[http://www.jianshu.com/p/d461da0fbe40](http://www.jianshu.com/p/d461da0fbe40)

时间 2018-05-09 15:03:27



hook,中文"钩子",原理是在函数内留下一个空白函数调用,为以后代码的拓展或者更改留下注入空间.

主要的应用场景是代码插件化,更改代码执行顺序.

      
#### 代码插件化

我们经常会遇到各种需求,并且需求不确定,当你不确定一个功能函数是否要拓展,你可以给这个函数加上一些钩子,便于以后的拓展,使得代码如同一个一个的积木,需要就拿来,不需要随时去除,降低代码的耦合度

``` 
假装有代码
```


代码我就不贴了,因为我看了一篇文章觉得他写的比我写的好.

参考文章:        [http://www.thinkphp.cn/extend/876.html][0]
(侵删)

      
#### 更改代码执行顺序

通过一个函数的返回值来改变代码的流程,有没有很熟悉?,bingo,我们经常使用的配置文件就是非常典型的运用场景.既然大家都很熟悉这种应用,话不多说,出道题,大家来举一反三


如题:

当子类需要继承父类的构造函数时,大多数人都会想到使用`parent::__construct();`但是当子类只需要父类构造函数的一部分时呢?

用户表分为管理员和普通用户,这时文章子类需要继承用户父类的普通用户,而无需继承管理员时,我们在实例化普通用户后中断构造,使其不继续往下走.

已知:子类继承并执行父类构造时,父类构造函数里如果出现与子类函数中相同命名的函数,子类的同名函数将覆盖父类的同名函数.


依题意得:

用户父类

```php
class User{
    public function __construct(){
          //实例化普通用户
          $this->normal_user = new normal_user();
          //钩子函数
          $this->hook();
           //实例化管理员用户
         $this->admin_user = new admin_user();
    }

  public function hook(){
       //测试变量
        $this->test = 1;
   }
}
```

文章子类

```php
class Article{
    public function __construct(){
          //执行父类构造函数
          parent::__construct();
          //打印测试变量
          var_dump($this->test);
    }

  public function hook(){
        //测试变量
        $this->test = 2;
         //因为子类只需要实例化普通用户类,所以中断父类构造的执行
        return false;
   }
}
```

执行结果:$this->test = 2 , 并且只实例化了normal_user类

  

[0]: https://link.jianshu.com?t=http%3A%2F%2Fwww.thinkphp.cn%2Fextend%2F876.html
## 巧用PHP中__get()魔术方法

来源：[http://www.cnblogs.com/LO-gin/p/9379279.html](http://www.cnblogs.com/LO-gin/p/9379279.html)

时间 2018-07-27 19:02:00


PHP中的魔术方法有很多，这些魔术方法可以让PHP脚本在某些特定的情况下自动调用。比如 `__construct()` 每次实例化一个类都会先调用该方法进行初始化。这里我们讲一下`__get()` 魔术方法的使用。读取不可访问属性的值时，`__get()` 会被调用。也就是，当想要获取一个类的私有属性，或者获取一个类并为定义的属性时。该魔术方法会被调用。

下面有段代码：

```php


class Model
{

 　　//不存在的成员属性自动调用
　　function __get($name) 
　　{
         //自动加载数据库
        if(substr($name, 0,2) =='db'){
            $config = strtolower(substr($name, 2));
            if(empty($this->objDb)){
                $this->objDb = new LibDatabase($config);
            }else{
                $this->objDb->config($config);
            }
            return $this->objDb; 
        }

        // 自动加载redis
        if(substr($name, 0,5) =='redis'){
            $config = strtolower(substr($name, 5));
            if(empty($this->objRedis)){
                $this->objRedis = new LibRedis($config);
            }else{
                $this->objRedis->config($config);
            }
            return $this->objRedis;
        }

        //自动加载excel插件
        if(substr($name, 0,5) =='excel'){
            if(empty($this->objExcel)){
                $this->objExcel = new LibExcel();
            }
            return $this->objExcel;
        }
        throw new LinkException("变量{$name}不被支持,请预先Model中定义",EXCEPT_CORE);
    }
}

?>


```

首先，该Model类有只有一个`__get()` 方法，没有定义其他属性，所有只要是访问这个Model类的属性，都会来调用这个`__get()` 方法。而传入的参数$name就是想要调用Model类的属性。

其次，`__get()` 方法里有3个判断，分别用于返回一个LibDatabase数据库类和一个LibRedis缓存类和一个LibExcel 的Excel插件类的实例。

假设$mod 是Model的一个对象。我们来分析以下三中情况：

1、$mod->dbconfsys->getAll($sql);

2、$mod->redisconfsys->get($key);

3、$mod->excel->export($data);

第一个：访问Model类的dbconfsys属性。但是Model类中并没有该属性。所以 `__get()` 的第一个判读成立，那么会返回LibDatabase('confsys')的实例。这里confsys其实是一个数据库的配置。如果有多个数据库的配置比如confadmin、conftest等，都可以使用dbconfadmin和dbconftest来实例化该数据库的一个连接对象。当然连接的处理以及数据处理是在LibDatabase里实现的。最后调用该实例的getAll方法执行sql语句。

第二个：访问Model类的redisconfsys属性，同理，此时 `__get()` 的第二个判断成立。返回的是LibRedis('confsy')的实例。这里的confsys则是一个redis的配置。像数据库实例一样，如果有多个配置，也是同样的处理方式。最后调用该实例的get方法获取某个键的值。

第三个：访问Model类的excel 属性，此时`__get()` 的第三个判断成立。返回的是LibExcel()的实例。最后调用export方法导出数据。

这个Model类可以作为基础的底层模型。然后系统中所有数据表的模型或者每个模块功能的模型都继承Model，同样可以使用该Model的功能，这里需要注意的是每种对象的调用都需要先早Model中做好判断处理。



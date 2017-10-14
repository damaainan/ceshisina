## [PHP 函数重载](http://blog.csdn.net/smartyidiot/article/details/6126761)

[另一篇文章](http://xuwenzhi.com/2016/03/03/php%E7%8B%AC%E7%89%B9%E7%9A%84%E9%87%8D%E8%BD%BD%E6%9C%BA%E5%88%B6/)

对于弱类型语言来说，PHP函数重载，并不像一般的OOP那样。

因为函数重载必须满足两个条件：

1、函数参数的个数不一样。

2、参数的类型不一样。

这两点，PHP都没有办法满足，您可以对函数多添加参数，只是相当于多传了个临时变量。而弱类型本来就不区分，所以无法通过这些来实现。

但是，可以通过下面的方法来实现简单的伪重载吧。

### 1、默认参数

从这个上面就可以看到，如果一个函数里面，我对不是必须参数填写添加相应的默认值，就可以完成相应的功能。

```php
<?php
function overloadFun($param1, $param2 = '1',$param3 = true)  
{  
    // do something   
}  
```

### 2、利用函数func_get_args()和call_user_func_array()，详细的帮助参照PHP手册。

利用一个有规则的函数进行调用，以方便统一管理。

```php
<?php
function overloadFun()  
{  
    // overloadFun可以随便定义，但为了命名规范，建议宝贝为与此函数名一样，  
    // 后面的尾随数值为参数个数，以方便管理  
$name="overloadFun".func_num_args();   
  return call_user_func_array(array($this,$name), func_get_args());       
}  
  
function overloadFun0()  
{  
    // do something  
}  
  
function overloadFun1()  
{  
    // do something  
}  
  
function overloadFun2()  
{  
    // do something  
}  
```

### 3、利用__call($name, $arg) 函数进行处理。

```php
<?php
function __call($name, $args)  
{  
    if($name=='overloadFun')  
    {  
        switch(count($args))  
        {  
            case 0: $this->overloadFun0();break;  
            case 1: $this->overloadFun1($args[0]); break;  
            case 2: $this->overloadFun2($args[0], $args[1]); break;  
            default: //do something  
              break;  
        }  
    }  
}  
  
function overloadFun0()  
{  
    // do something  
}  
  
function overloadFun1()  
{  
    // do something  
}  
  
function overloadFun2()  
{  
    // do something  
}   
```
总结，这几种方法，都可以实现伪重载，基本第2种和第3种，内容可以相互处理判断。

文中只是给出了方法，可能还有许多细节地方需要处理，比如，判断整型、类别等。

不过，根据上面的内容，php可能永远不会出现真正的重载，那样就会失去语言本身的意义了。

[0]: #
# PHP CPP：一个开发PHP扩展的C++库

 [PHP][0] , [PHP CPP][1] , [扩展][2]

本资源由 [伯乐在线][3] - [邢敏][4] 整理

![php-cpp](http://jbcdn1.b0.upaiyun.com/2016/09/df56ea18732519679447dc01b00fe4b4.png)

PHP-CPP是一个开源的C++库，通过它可以快速方便地写出的C++函数，然后给php调用。不同于常规的php扩展——那些实现太复杂太难用，并且要对Zend引擎以及指针操作有足够深入的学习 ，而PHP-CPP写就的扩展非常简单易懂。

**注意：PHP 7专属!** 这个库已经升级为PHP7.0及以上专用。如果你在用更低版本的php，使用 [PHP-CPP-LEGACY][5]库代替。PHP-CPP和PHP-CPP-LEGACY库拥有几乎一样的API接口，所以你可以很轻松地把PHP 5.*的扩展搬到PHP 7 。

## PHP-CPP是啥子?

一个用于开发PHP扩展的C++库。提供一系列文档完善，易于使用和扩展的类，让你可以创建PHP的原生扩展。[更多][6]

## 为什么是PHP-CPP?

这个C++库让开发PHP扩展变得有趣。基于PHP-CPP之上的扩展，易于理解、维护轻松并且代码优美——并且用C++来实现函数大大加速了程序效率！[更多][7]

## 实战战绩

你想看看现实中C++扩展有多快? 检出这个PHP脚本，将之和C++中的同个算法对比。[更多][8]

剧透：


```
 C ++ :  0.79793095588684  seconds

 php :  8.9202060699463  seconds
```
## 基础入门

### 函数

PHP-CPP库充分发挥C++11的独到力量， 转换你的函数和PHP间的参数和返回。
```

 Php:: Value hello_world ( )

 {

  return  "hello world!" ;

 }
```
上面是一个原生C++函数。 利用PHP-CPP 你可以导出函数到PHP ，只要一行代码。


```
 extension . add ( "hello_world" ,  hello_world ) ;
```
### 参数和返回

在PHP-CPP里这很容易的
```
 Php:: Value my_plus ( Php:: Parameters  & parameters )

 {

  return  ( int ) parameters [ 0 ]  +  ( int ) parameters [ 1 ] ;

 }
```
PHP-CPP库保证了来自PHP的参数 (内部是复杂的C结构体)，自动被转换为int传到你的函数，然后你的函数 “my_plus” 的返回同样也转回PHP变量。

### 相关资源

* [官方文档][9]：提供入门介绍、错误输出、函数注册调用等说明
* [下载链接][10]

官方网站：[http://www.php-cpp.com][11]  
开源地址：[https://github.com/CopernicaMarketingSoftware/PHP-CPP][12]

[0]: http://hao.jobbole.com/tag/php/
[1]: http://hao.jobbole.com/tag/php-cpp/
[2]: http://hao.jobbole.com/tag/%e6%89%a9%e5%b1%95/
[3]: http://www.jobbole.com
[4]: http://www.jobbole.com/members/dfghj44444
[5]: https://github.com/CopernicaMarketingSoftware/PHP-CPP-LEGACY
[6]: http://www.php-cpp.com/documentation
[7]: http://www.php-cpp.com/documentation/ten-reasons-for-using-php-cpp
[8]: http://www.php-cpp.com/documentation/bubblesort
[9]: http://www.php-cpp.com/documentation/
[10]: http://www.php-cpp.com/download
[11]: http://www.php-cpp.com
[12]: https://github.com/CopernicaMarketingSoftware/PHP-CPP
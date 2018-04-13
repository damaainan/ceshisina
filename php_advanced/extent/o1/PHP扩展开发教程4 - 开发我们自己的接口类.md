## PHP扩展开发教程4 - 开发我们自己的接口类

来源：[https://segmentfault.com/a/1190000014293086](https://segmentfault.com/a/1190000014293086)

PHP扩展是高级PHP程序员必须了解的技能之一，对于一个初入门的PHP扩展开发者，怎么才能开发一个成熟的扩展，进入PHP开发的高级领域呢？本系列开发教程将手把手带您从入门进入高级阶段。
本教程系列在linux下面开发（推荐使用centos），php版本用的是5.6，并假设您有一定的linux操作经验和c/c++基础。
有问题需要沟通的朋友请加QQ技术交流群32550793和我沟通。
上一章演示了如何在PHP扩展中导出普通函数，本章介绍怎么样在扩展中导出类。使得PHP能够在脚本中直接访问扩展中的C++类。
## 一、如何在PHP扩展中导出C++类

下面是使用PHP-CPP开发的一个扩展骨架代码，编译可以导出一个演示C++类。

工程的源码见github，可以用git客户端下或者打开网址打包下载。

``` 
# git clone https://github.com/elvisszhang/phpcpp_counter.git
```

现在我们的类名是 Counter, 扩展里面注册类的语法是这样子的

``` 
Php::Class<Counter> counter("Counter");
```

Counter类里面有个函数叫 increment，通过下面语法告诉扩展让php脚本能访问这个函数。

``` 
counter.method<&Counter::increment> ("increment");
```

main.cpp 的C++源码如下。

```
#include#include <time.h>

//扩展的导出类 Counter
class Counter : public Php::Base
{
private:
    int _value = 0;
public:
    Counter() = default;
    virtual ~Counter() = default;
    //类的普通成员函数
    Php::Value increment() { return ++_value; }
    Php::Value decrement() { return --_value; }
    Php::Value value() const { return _value; }
    //类的静态成员函数
    static Php::Value gettime() {return time();}
};

//告诉编译器get_module是个纯C函数
extern "C" {
    //get_module是扩展的入口函数
    PHPCPP_EXPORT void *get_module() {
        static Php::Extension myExtension("counter", "1.0.0");
        
        //初始化导出类
        Php::Class<Counter> counter("Counter");
        
        //注册导出类的可访问普通函数
        counter.method<&Counter::increment> ("increment");
        counter.method<&Counter::decrement> ("decrement");
        counter.method<&Counter::value> ("value");
        
        //注册导出类的可访问静态函数
        counter.method<&Counter::gettime>("gettime");

        //注册导出类，使用右值引用方式，优化资源使用
        myExtension.add(std::move(counter));
        
        //返回扩展对象指针
        return myExtension;
    }
}
```

对应上述例子的php测试代码如下。

```php
<?php
$counter = new Counter;
echo 'result of increment() = '. $counter->increment() . PHP_EOL;
echo 'result of increment() = '. $counter->increment() . PHP_EOL;
echo 'result of decrement() = '. $counter->decrement() . PHP_EOL;
echo 'result of value() = '. $counter->value() . PHP_EOL;
echo 'result of gettime() = '. Counter::gettime() . PHP_EOL;
?>
```

上述php代码运行后的输出信息如下。

``` 
result of increment() = 1
result of increment() = 2
result of decrement() = 1
result of value() = 1
result of gettime() = 1523363778
```
## 二、扩展类的普通函数支持的样式

扩展类的函数，必须按照一定的规范来写，返回值和参数的名称、类型都是有规定。否则就不能被PHP脚本认识。

最常见的是下面4种函数样式，跟上一章的普通函数的样式其实差不多，返回值和参数的用法也完全一样，所以就不再多说。

```c
// signatures of supported regular methods
void        YourClass::example1();
void        YourClass::example2(Php::Parameters &params);
Php::Value  YourClass::example3();
Php::Value  YourClass::example4(Php::Parameters &params);
```

另外函数如果带上修饰符 const。还有下面4种变化的样式。

```c
void        YourClass::example5() const;
void        YourClass::example6(Php::Parameters &params) const;
Php::Value  YourClass::example7() const;
Php::Value  YourClass::example8(Php::Parameters &params) const;
```
## 三、参考文献

[PHP-CPP帮助：classes-and-objects][0]

[0]: http://www.php-cpp.com/documentation/classes-and-objects
## PHP中的重载

来源：[https://tlanyan.me/overload-in-php/](https://tlanyan.me/overload-in-php/)

时间 2018-05-22 00:13:48

 
整理思路时想到一个问题：PHP为什么不允许同名函数存在？即不允许常见于其他语言的重载机制？
 
## 重载和重写
 
先区分一下重载（overload）和重写(override)：重载指多个名字相同，但 **`参数不同`**  的函数在 **`同一作用域`**  并存的现象;重写出现在继承中，指子类重定义父类功能的现象，也被称为覆盖。重载中说的参数不同有三种情况：参数个数不同，参数类型不同，参数顺序不同。重写一般指函数的覆盖，即相同签名的成员函数在子类中重新定义（实现抽象函数或接口不是重写），是实现多态（polymorphism）的一种关键技术。成员变量也可以重载/覆盖，但一般不会这么做。
 
用简单的C代码来说明重载：
 
```c
int add(int a, int b) { return a + b; }
double add(double a, double b) { return a + b; }
double add(int a, int b, double c) { return a + b + c; }
double add(double a, int b, int c) { return a + b + c; }
```
 
第一个函数为参考基准，其他三个对应重载的三种情形。函数重载多见于强类型语言，编译后函数在函数符号表的名称一般是函数名加参数类型。上面的四个函数，g++编译后，nm命令查看符号表中的名字，输出如下：
 
``` 
[tlanyan@server ~]# nm test | grep add
0000000000400730 t _GLOBAL__sub_I__Z3addii
0000000000400851 T _Z3adddd
00000000004008b1 T _Z3adddii
000000000040083d T _Z3addii
000000000040087d T _Z3addiid
```
 
最后四行的第三列对应编译后四个函数的符号信息，_Z3为前缀，add是函数名，剩下的字母d代表double，i代表int，与生命一一对应。
 
再回到PHP的重载。PHP的函数声明中参数无需声明类型，直接排除参数类型不同、参数顺序不同两种重载，只剩下参数个数不同一条路可走。定义一个参数个数不同名字相同的函数，这么一个小小的重载要求，在PHP中也是不合法的！原因是PHP中不允许同名函数存在，想定义重名函数，死心吧！虽然大多数情况下以默认参数方式实现重载基本上够用，但不时还会觉得憋屈，忍不住想问一句：PHP为什么不允许（同名函数）重载啊？！
 
## PHP的苦衷
 
PHP不支持同名函数的重载是有原因的。上面已经提到，PHP函数声明时不需要指定参数类型，重载中的三种情况立马废掉两种。幸存的参数个数不同这一条路也走不通，为什么呢？因为PHP中调用函数时，少传参数，不行；多传参数，没问题！来个简单的例子：
 
```php
function foo($arg1, $arg2) {
    echo "$arg1,  $arg2\n";
}
 
// 函数调用
// 参数过少，提示：
//PHP Warning:  Missing argument 2 for foo()
// PHP Notice:  Undefined variable: arg2 in php shell code on line 2
foo("tlanyan");
 
// 参数个数正好，运行正常
foo("hello", "tlanyan");
 
// 多传参数，运行正常
foo("hello", "tlanyan", "nice day");
 
// 传更多参数，也一切正常
foo("hello", "tlanyan", "morning", "noon", "afternoon", "evening", "night");
```
 
只要个数不小于声明的，传多少参数PHP不管。所以参数个数不同，在PHP中不足以区分函数。
 
个人认为另一个不允许名函数存在的重要原因是`function_exists`、`method_exists`、`is_callable`这些API的存在。作为简单易用的语言，PHP为开发人员提供了查询函数名是否存在/可用的便利API，这在编程语言中很少见（尤其是`get_defined_functions`这类API）。可以看到，这些API都不需要参数信息。如果能定义参数不同的重载函数，这些API都要跟着改，势必引入额外的复杂性。正所谓鱼与熊掌不可兼得，方便你用时没想到参数不同，不方便你定义就抱怨，好像不好吧？
 
PHP5引入了反射API，这是非常强大的类型信息查询工具。就函数声明而言，ReflectionMethod/ReflectionFunction类的getParameters/getNumberOfParameters/getNumberOfRequiredParameters等API，功能上甩`function_exists`等好几条街。有了反射机制，按理说`function_exists`这些API可以安心的退休。虽然反射这一套东西功能强大，但远没有旧式API简单好用。再加上看看市面上的代码，有多少类库和框架依赖旧式API。从兼容性和实用性考虑，个人认为短时间内能以同名函数方式重载的概率非常小。
 
## PHP中的重载
 
只看完上面的内容就说PHP不支持重载，我想随便一个资深的PHP开发都会不由自主的取下拖鞋，然后教你什么是PHP中的重载，并保证至少有好几种实现方法；官方人员对这种认知估计也无力吐槽：能不能好好看官方文档？！官网中可是有一节专门讲 [重载][1] ！
 
![][0]
 
因为种种原因，PHP不支持传统的重载，也就是同名函数的重载，但PHP是支持重载的，而且姿势还不少。简单来说，PHP中主要有以下几种重载方式：
 
 
* 默认参数，定义一个全面的函数版本，不是必须的值在声明时赋予默认值； 
* 定义一个不声明参数的入口函数，函数内使用`func_num_args/func_get_args`获取参数个数/数组，然后根据参数个数转发到具体实现的函数；  
* 自PHP5.6起，可以用变长参数实现重载，`func_get_args`的另一种形式；  
* 对于类中的成员函数，可以通过`__call`和`__callStatic`实现重载。  
 
 
如果你还知道其他方式，欢迎评论给出方案。
 
## 总结
 
PHP的特性决定了其不支持同名函数方式的重载，但并不意味着PHP不支持重载。实际上PHP可以多种方式实现重载，并保持其一贯的简单易用性。
 
感谢阅读！
 
## 参考
 
 
* [http://php.net/manual/it/language.oop5.overloading.php][2]  
 
 


[1]: http://php.net/manual/it/language.oop5.overloading.php
[2]: http://php.net/manual/it/language.oop5.overloading.php
[0]: https://img2.tuicool.com/Z3aIVfI.jpg 
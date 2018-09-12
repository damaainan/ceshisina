## PHP扩展开发教程3 - 开发一个我们自己的数学函数库

来源：[https://segmentfault.com/a/1190000014265010](https://segmentfault.com/a/1190000014265010)

PHP扩展是高级PHP程序员必须了解的技能之一，对于一个初入门的PHP扩展开发者，怎么才能开发一个成熟的扩展，进入PHP开发的高级领域呢？本系列开发教程将手把手带您从入门进入高级阶段。
本教程系列在linux下面开发（推荐使用centos），php版本用的是5.6，并假设您有一定的linux操作经验和c/c++基础。
有问题需要沟通的朋友请加QQ技术交流群32550793和我沟通。
上一章演示了一个hello world扩展，大家基本了解了用PHP-CPP开发的扩展的C++源码的基本样式。下面一起开发一个简单的数学运算库(mymath)来熟悉如何导出各种接口函数。
mymath数学库的代码已放在github上，可以直接git下载或者浏览器打开网页下载源码。

git下载命令行

```
git clone https://github.com/elvisszhang/phpcpp_mymath.git
```

浏览器下载网址和仓库网址一样：[https://github.com/elvisszhan...][0]
## 一、不带参数，没有返回值的扩展函数写法

函数功能：打印100以内的素数

函数名称：mm_print_pn_100

如何注册扩展函数
必须在get_module函数体中，注册函数 mm_print_pn_100，以便能在php中能直接调用。

```c
PHPCPP_EXPORT void *get_module() 
{
        // 必须是static类型，因为扩展对象需要在PHP进程内常驻内存
        static Php::Extension extension("mymath", "1.0.0");
        
        //这里可以添加你要暴露给PHP调用的函数
        extension.add<mm_print_pn_100>("mm_print_pn_100");
        
        // 返回扩展对象指针
        return extension;
}
```

函数声明及代码如下。
函数不需要参数，函数的参数列表里面什么也不用放，空着就行。函数不需要返回值，返回值类型设置为void。

```c
//打印100以内的素数
void mm_print_pn_100()
{
    int x = 2;
    int y = 1;
    int line = 0;
    while (x <= 100){
        int z = x - y; //z随y递减1
        int a = x%z; //取余数
        if (a == 0) { //如果x被z整除
            if (z == 1) {//如果z为1（x是质数）
                Php::out << x << " ";//输出x
                line ++;//每行输出的数的数量加1
             }
            x ++; //x加1
            y = 1;//y还原
        }
        else {//如果没有被整除
            y ++;//y加1，下一次循环中z减1
        }
        if (line == 10) {//每输出10个数 
            Php::out << std::endl;//输出一个换行        
            line = 0;//还原line
        }
    }
    if (line != 0) //最后一行输出换行
        Php::out << std::endl;
    Php::out.flush();
}
```

PHP测试代码

```php
<?php
//打印100以内的素数
mm_print_pn_100();
```

运行以上PHP代码，输出结果是

```
2 3 5 7 11 13 17 19 23 29
31 37 41 43 47 53 59 61 67 71
73 79 83 89 97
```
## 二、不带参数，有返回值的扩展函数写法

函数功能：计算1、2、3、...、100的和
函数名称：mm_sum_1_100

注册函数 mm_sum_1_100，注册方式同上一节

```
extension.add<mm_sum_1_100>("mm_sum_1_100");
```

函数声明及代码如下。
函数不需要参数，函数参数列表设置为空就可以。
函数有返回值，返回值类型设置为 Php::Value。由于Php::value 重载了构造函数和operator = 运算符，常见数据类型（整形，字符串，浮点数，数组等）可以直接返回。

```
//获取1-100的和
Php::Value mm_sum_1_100()
{
    int sum = 0;
    int i;
    for(i=1;i<=100;i++){
        sum += i;
    }
    return sum; //可以直接返回sum值，自动生成 Php::value 类型
}
```

PHP测试代码：

```php
<?php
$sum = mm_sum_1_100();
echo 'sum (1~100) = ' . $sum . PHP_EOL;
?>
```

运行以上PHP代码，输出结果是

```
sum (1~100) = 5050
```
## 三、带有参数，没有返回值的扩展函数写法

函数功能：计算任意给定整数，打印该整数以内的所有素数

函数名称：mm_print_pn_any

注册函数 mm_print_pn_any，注册方式同上一节

```
extension.add<mm_print_pn_any>("mm_print_pn_any");
```

函数声明及代码如下。由于需要参数，函数参数需要写成Php::Parameters &params，由于没有返回值，返回值类型设置void。
另外需要检测参数是否输入，参数的类型也需要检测是不是整形。不检测直接用的话，代码容易出异常。

```c
//任意给定一个整数，打印出小于等于该整数的所有素数
void mm_print_pn_any(Php::Parameters &params)
{
    //检查必须输入一个参数
    if(params.size() == 0){
        Php::out << "error: need a parameter " << std::endl;
        return;
    }
    //检查参数必须是整形
    if( params[0].type() != Php::Type::Numeric){
        Php::out << "error: parameter must be numeric" << std::endl;
        return;
    }
    //检查数字必须大于1
    int number = params[0];
    if(number <= 1){
        Php::out << "error: parameter must be larger than 1" << std::endl;
        return;
    }
    //检查参数必须大于0
    int x = 2;
    int y = 1;
    int line = 0;
    while (x <= number){
        int z = x - y; //z随y递减1
        int a = x%z; //取余数
        if (a == 0) { //如果x被z整除
            if (z == 1) {//如果z为1（x是质数）
                Php::out << x << " ";//输出x
                line ++;//每行输出的数的数量加1
             }
            x ++; //x加1
            y = 1;//y还原
        }
        else {//如果没有被整除
            y ++;//y加1，下一次循环中z减1
        }
        if (line == 10) {//每输出10个数
            Php::out << std::endl;//输出一个换行        
            line = 0;//还原line
        }
    }
    if (line != 0) //最后一行输出换行
        Php::out << std::endl;
    Php::out.flush();    
}
```

PHP测试代码

```php
<?php
echo '---runing mm_print_pn_any()---' . PHP_EOL;
mm_print_pn_any();

echo PHP_EOL . '---runing mm_print_pn_any(\'xyz\')---' . PHP_EOL;
mm_print_pn_any('xyz');

echo PHP_EOL . '---runing mm_print_pn_any(200)---' . PHP_EOL;
mm_print_pn_any(200);
?>
```

运行以上PHP代码，输出结果是

``` 
---runing mm_print_pn_any()---
error: need a parameter

---runing mm_print_pn_any('xyz')---
error: parameter must be numeric

---runing mm_print_pn_any(200)---
2 3 5 7 11 13 17 19 23 29
31 37 41 43 47 53 59 61 67 71
73 79 83 89 97 101 103 107 109 113
127 131 137 139 149 151 157 163 167 173
179 181 191 193 197 199

```
## 四、标量型参数，有返回值的扩展函数写法

函数功能：给定一系列参数，计算其总和

函数名称：mm_sum_all

注册扩展函数 mm_sum_all，注册方式同上一节

``` 
extension.add<mm_sum_all>("mm_sum_all");
```

函数声明及代码如下。

``` 
//获取所有参数的和
Php::Value mm_sum_all(Php::Parameters &params)
{
    int sum = 0;
    for (auto &param : params){
        //字符串类型可以自动转换成整形
        sum += param;
    }
    return sum;
}
```

PHP测试代码

```php
<?php

$sum = mm_sum_all(1,2,'3','5'); //字符串类型可以自动转换成整形
echo 'sum (1,2,\'3\',\'5\') = ' . $sum . PHP_EOL;
?>
```

测试输出结果：

``` 
sum (1,2,'3','5') = 11
```
## 五、数组型参数，有返回值的扩展函数写法

函数功能：给定一个数组类型的参数，计算数组全部元素的总和

函数名称：mm_sum_array

注册函数 mm_sum_array ，注册方式同第一节

函数声明及代码如下。

``` 
//获取所有数组各元素的和
Php::Value mm_sum_array(Php::Parameters &params)
{
    //没有给定参数，返回0
    if(params.size() == 0){
        return 0;
    }
    //参数类型不是数组，转成整形返回
    if( params[0].type() != Php::Type::Array){
        return (int)params[0];
    }
    //数组中的元素逐个相加
    int sum = 0;
    Php::Value array = params[0];
    int size = array.size();
    int i;
    for(i=0;i<size;i++){
        sum += array.get(i);
    }
    return sum;
}
```

PHP测试代码

```php
<?php
$nums = array(1,3,5,7);
$sum = mm_sum_array($nums);
echo 'sum (array(1,3,5,7)) = ' . $sum . PHP_EOL;
?>
```

测试输出结果：

``` 
sum (array(1,3,5,7)) = 16
```
## 六、返回值类型是数组的扩展函数写法

上面函数的返回值都是标量类型，数组是PHP特别常用的类型，如果想返回一个数组类型，可以使用c++的std::vector，PHP-CPP会贴心的把它自动转换成PHP认识的数组类型。

我们现在的演示函数功能是“返回30以内的所有素数的数组”。扩展里面注册函数的方式同第一节。

函数声明及代码如下。

``` 
//获取30以内的所有素数
Php::Value mm_get_pn_30()
{
    std::vector<int> pn;
    int x = 2;
    int y = 1;
    while (x <= 30){
        int z = x - y; //z随y递减1
        int a = x%z; //取余数
        if (a == 0) { //如果x被z整除
            if (z == 1) {//如果z为1（x是质数）
                pn.push_back(x); //放数组中去
            }
            x ++; //x加1
            y = 1;//y还原
        }
        else {//如果没有被整除
            y ++;//y加1，下一次循环中z减1
        }
    }    
    return pn;
}
```

PHP测试代码

```
<?php
$pn = mm_get_pn_30();
var_dump($pn);
?>
```

测试输出结果：

```
array(10) {
  [0]=>
  int(2)
  [1]=>
  int(3)
  [2]=>
  int(5)
  [3]=>
  int(7)
  [4]=>
  int(11)
  [5]=>
  int(13)
  [6]=>
  int(17)
  [7]=>
  int(19)
  [8]=>
  int(23)
  [9]=>
  int(29)
}
```
## 七、参考文献

[c++质数判定及输出质数表][1]
[PHP-CPP函数开发帮助][2]

[0]: https://github.com/elvisszhang/phpcpp_mymath.git
[1]: https://www.cnblogs.com/X-star/p/5667370.html
[2]: http://www.php-cpp.com/documentation/functions
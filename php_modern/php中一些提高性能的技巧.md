## php中一些提高性能的技巧

来源：[http://www.cnblogs.com/vinter/p/8890705.html](http://www.cnblogs.com/vinter/p/8890705.html)

时间 2018-04-20 14:18:00

## php中一些提高性能的技巧

tags:php性能 提高性能 php中的@ php的静态

引言：php作为一种脚本语言，本身的性能上肯定是不如c++或者java的。拥有简单易学的特性的同时，性能提升的空间也并不是没有。养成一些好的编程习惯，也许可以让你的php代码性能得到可见的提升。

### 一、消除不必要的错误提示

有很多朋友编程的时候遇到notice和warning这类的错误，如果不影响正常的逻辑就不去处理了，类似下面这种

```php
<?php

    //想在循环中拼接字符串，却不初始化字符串直接使用 .=
    $list = array(
        1=>'hello',
        2=>'world'
            //...
    );
    foreach($list as $key=>$val){
        $str .= $val;
    }
    // Notice: Undefined variable: str in D:\11\index.php
    
    /*********************************************************/
    
    //不注意的数组下标越界或key不存在
    $List_1 = array('1','2');
    echo $List_1[3];
    //Notice: Undefined offset: 3 in D:\11\index.php on line 13
    
    /*********************************************************/
    
    //使用已经过时的函数  比如使用函数mysql_escape_string()会有如下提示
    
//Deprecated: mysql_escape_string(): This function is deprecated; use mysql_real_escape_string() instead. in D:\readCode\xhprofshoujikanbingcom\cgi\xhprof.php on line 51
    
  /*********************************************************/
  
    //静态的调用非静态的方法 报E_STRICT
class Cl_a{
    function a(){
        echo 'A类的a方法';
    }
}
class Cl_B{
    static function b(){
        echo 'B类的b方法';
    }
}

function test_1(){
    $a = new Cl_a();
    $a::a();
}
function test_2(){
    $b = new Cl_b();
    $b::b();
}

test_1();
test_2();

    //Strict standards: Non-static method Cl_a::a() should not be called statically in D:\11\index.php on line 15
?>
```

这种情况会导致性能的下降，凡是能引起警告（warning or E_STRICT ）或者提示（notice）的代码都会走php的异常流程，记录异常日志（涉及到文件I/O）。性能可能会降低50%左右。


### 二、使用静态变量和方法

测试结果如下

```php
<?php
    
class Cl_a{
    public $a=1;
    function a(){
        $this->a++;
    }
}
class Cl_b{
    public static $a = 1;
    static function b(){
        self::$a++;
    }
}

function test_1(){
    $a = new Cl_a();
    for($i=0;$i<1000;$i++){
        $c=$a->a+1;//外部调用
        $a->a();//内部调用
    }
    echo $a->a;
}
function test_2(){
    $b = new Cl_b();
    for($i=0;$i<1000;$i++){
        $c=$b::$a+1;//外部调用
        $b::b();//内部调用
    }
    echo $b::$a;
}
test_1(); //51012微秒
test_2(); //49039微秒

?>
```
### 三、[尽量不适用@符号][0]

[说实话我没见到过必须使用@符号的地方][1]

```php
<?php
    
class Cl_a{
    public $a=1;
    function a(){
        $b =1;
    }
}

function test_1(){
    @$a = new Cl_a();
    for($i=0;$i<1000;$i++){
        @$a->a();//内部调用
    }
    echo $a->a;
}
function test_2(){
    $a = new Cl_a();
    for($i=0;$i<1000;$i++){
        $a->a();//内部调用
    }
    echo $a->a;
}
@test_1();  //51,133
test_2();   //48,381

?>
```

### 四、使用php内置的变量来代替一些函数。

某些时候也可以用`$_SERVER['REQUEST_TIME']`替换time()。、

这个性能的提示我测试出的结果让我都有些不能相信

```php
<?php

function test_1(){
    
    for($i=0;$i<1000;$i++){
        $a = php_uname('s');
        $b = phpversion();
        $c = php_sapi_name();
        
    }
    echo $a,$b,$c;
}
function test_2(){
    for($i=0;$i<1000;$i++){
        $a = PHP_OS;
        $b = PHP_VERSION;
        $c = PHP_SAPI;
    }
    echo $a,$b,$c;
}
test_1();   //132,015  
test_2();   //340  惊不惊喜意不意外

$is_win = DIRECTORY_SEPARATOR == '//'; //可以用来判断是不是windows系统 速度很快

?>
```
### 五、注意不要把一些不必要的耗时代码写到循环中

例如，cuont函数不要写在for循环的条件中，不要在循环中声明不必要的变量等。

```php
<?php
function test_1(){
    $a = array(
    1,2,3,4,5,6,7,8,9,0,
    1,2,3,4,5,6,7,8,9,0,
    1,2,3,4,5,6,7,8,9,0,
    1,2,3,4,5,6,7,8,9,0,
    1,2,3,4,5,6,7,8,9,0,
    1,2,3,4,5,6,7,8,9,0,
    );
    for($i=0;$i<count($a);$i++){
        
    }
    
}
function test_2(){
    $a = array(
    1,2,3,4,5,6,7,8,9,0,
    1,2,3,4,5,6,7,8,9,0,
    1,2,3,4,5,6,7,8,9,0,
    1,2,3,4,5,6,7,8,9,0,
    1,2,3,4,5,6,7,8,9,0,
    1,2,3,4,5,6,7,8,9,0,
    );
    $count = count($a);
    for($i=0;$i<$count;$i++){
        
    }
}
test_1();//3,602
test_2();//223
?>
```
### 六、尽量用php内置的函数替换正则

这个网上有很多对比的，我就不再重新写一遍了；

直接给一下常用的三个函数性能对比：str_replace > strtr > preg_replace

### 七、包含文件有技巧

包含文件的时候，如果能确定不会重复包含，尽量使用include,require不要用include_once和require_once，而且包含错误(一般you 函数和变量被覆盖)通常是可以被测试出来的。

测试结果如下：

```php
<?php
function test_1(){
    $a ='11';
    for($i=0;$i<1000;$i++){
        include_once 'a.php';
    }
}
function test_2(){
    $a ='11';
    for($i=0;$i<1000;$i++){
        include 'a.php';
    }
}

test_1();//1,477
test_2();//152,704

?>
```
### 八、可以用全等号代替双等

这个相信有很多人都知道，因为双等号是会有类型转换的。

测试结果如下：

```php
<?php
function test_1(){
    $a ='11';
    for($i=0;$i<1000;$i++){
        if($a=='11'){
            echo 1;
        }
    }
}
function test_2(){
    $a ='11';
    for($i=0;$i<1000;$i++){
        if($a==='11'){
            echo 1;
        }
    }
}

test_1();//耗时501微秒
test_2();//耗时434微秒

?>
```


### 九、多维数组赋值可以使用引用来提高性能

多维数组越复杂，引用带来的性能提高越大，引用可以减少数组元素的哈希查找次数。

```php
<?php

function test_1(){
    $a['b']['c'] = array();
    $a['b']['d'] = array();
    for($j=0;$j<1000;$j++){
        for ($i = 0; $i < 5; ++$i){
            $a['b']['c'][$i] = $i;
        }
    }
}
function test_2(){
    $a['b']['c'] = array();
    $a['b']['d'] = array();
    $ref =& $a['b']['c'];
    for($j=0;$j<1000;$j++){
        for ($i = 0; $i < 5; ++$i){
            $ref[$i] = $i;  
        }
    }
}
test_1();//1270
test_2();//1015
//多维数组越复杂，引用带来的性能提高越大，引用可以减少数组元素的哈希查找
?>
```


### 十、大的数组或数据如果使用完毕，及时unset掉

这个就不用过多解释了，节省内存。

### 十一、不做无意义的封装

如果不能实现特别好的设计，解耦，复用效果可以不封装简单方法

因为每次调用方法都会开辟一个新的内存区域，传值的时候对数据的引用也会增加。

  
### 十二、为代码和数据做缓存

这个数据缓存就不用说了，可能有部分朋友不知道代码缓存，代码缓存节省代码运行时间的远离：php是解释型语言，和编译型语言的区别如下：

编译型语言：编程完成后，通过预处理->编译->汇编->链接后变成可执行程序（计算机直接运行的二进制文件），以后每次运行都直接运行这个可执行性文件。


解释型语言：具体的过程这里也说不清楚，可以理解为，解释型语言每次运行的时候都相当于进行了上面编译型语言编译的过程（其实还不太一样）生成可执行的文件。

opcache就是把解释后的文件存入了内存，每次运行的时候就不用经过解释的过程，可以提高20%-100%的性能（数据来自网络，但是原理上肯定是能提升性能的，如果代码经常迭代的话慎用）。

  
### 十三、用单引号替换双引号

因为双引号的时候内部可以放变量，php会判断内部是否有变量。

```php
<?php
function test_1(){
    $a ="1111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111";
    for($i=0;$i<1000;$i++){
        echo $a;
    }
    
}
function test_2(){
    $a ='1111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111';
    for($i=0;$i<1000;$i++){
        echo $a;
    }
}
test_1();//2953
test_2();//2025
?>
```

### 关于foreach和for循环效率

这篇文章已经太长了，关于foreach和for循环效率和文件读取的效率下次再单独拿出来介绍吧



[0]: mailto:%E5%B0%BD%E9%87%8F%E4%B8%8D%E9%80%82%E7%94%A8@%E7%AC%A6%E5%8F%B7
[1]: mailto:%E8%AF%B4%E5%AE%9E%E8%AF%9D%E6%88%91%E6%B2%A1%E8%A7%81%E5%88%B0%E8%BF%87%E5%BF%85%E9%A1%BB%E4%BD%BF%E7%94%A8@%E7%AC%A6%E5%8F%B7%E7%9A%84%E5%9C%B0%E6%96%B9
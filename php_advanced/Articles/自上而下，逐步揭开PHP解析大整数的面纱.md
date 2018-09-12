# [自上而下，逐步揭开PHP解析大整数的面纱][0]

## 遇到的问题

最近遇到一个PHP大整数的问题，问题代码是这样的

```php
$shopId = 17978812896666957068;
var_dump($shopId);
```
上面的代码输出，会把$shopId转换成float类型，且使用了科学计数法来表示，输出如下：

> float(1.7978812896667E+19)

但在程序里需要的是完整的数字作为查找数据的参数，所以需要用的是完整的数字，当时以为只是因为数据被转换成科学计数法了，于是想到的解决方案是强制让它不使用科学计数法表示：

```php
$shopId= number_format(17978812896666957068);
var_dump($shopId);
```
这时候奇怪的事情出现了，输出的是：

> 17978812896666957824

当时没有仔细看，对比了前十位就没有继续往下看，所以认为问题解决了，等到真正根据ID去找数据的时候才发现数据查不出来，这时候才发现是数据转换错误了。

这里使用number_format失败的原因在后面会讲到，当时就想到将原来的数据转成字符串的，但是使用了以下方法仍然不行

```php
$shopId= strval(17978812896666957068);
var_dump($shopId);

$shopId = 17978812896666957068 . ‘’;
var_dump($shopId);
```
输出的结果都是

> float(1.7978812896667E+19)

最后只有下面这种方案是可行的：

```php
$shopId = ‘17978812896666957068’;
var_dump($shopId);

// 输出
//string(20) "17978812896666957068"
```
众所周知，PHP是一门解释型语言，所以当时就大胆地猜测PHP是在编译期间就将数字的字面量常量转换成float类型，并用科学计数法表示。但仅仅猜测不能满足自己的好奇心，想要看到真正实现代码才愿意相信。于是就逐步分析、探索，直到找到背后的实现。

刚开始根据这个问题直接上网搜“PHP大整数解析过程”，并没有搜到答案，因此只能自己去追查。一开始对PHP的执行过程不熟悉，出发点就只能是一步一步地调试，然后

示例代码：

```php
// test.php
$var = 17978812896666957068;
var_dump($var);
```
## 追查过程

1、查看opcode  
通过vld查看PHP执行代码的opcode，可以看到，赋值的是一个ASSIGN的opcode操作

![][1]

接下来就想看看ASSIGN是在哪里执行的。

2、gdb调试  
2-1、用list查看有什么地方可以进行断点

![][2]

2-2、暂时没有头绪，在1186断点试试

![][3]

结果程序走到sapi/cli/php_cli.c文件的1200行了，按n不断下一步执行，一直到这里就走到了程序输出结果了：

![][4]

2-4、于是猜测，ASSIGN操作是在do_cli函数里面进行的，因此对do_cli函数做断点：break do_cli。  
输入n，不断回车，在sapi/cli/php_cli.c文件的993行之后就走到程序输出结果了：

![][5]

2-5、再对php_execute_script函数做断点：break php_execute_script，不断逐步执行，发现在main/main.c文件的2537行就走到程序输出结果了：

![][6]

2-6、继续断点的步骤：break zend_execute_scripts，重复之前的步骤，发现在zend/Zend.c文件的1476行走到了程序输出结果的步骤：

![][7]

看到这里的时候，第1475行里有一个op_array，就猜测会不会是在op_array的时候就已经有值了，于是开始打印op_array的值：

![][8]

打印之后并没有看到有用的信息，但是其实这里包含有很大的信息量，比如opcode的handler: **ZEND_ASSIGN_SPEC_CV_RETVAL_CV_CONST_RETVAL_UNUSED_HANDLER**，但是当时没注意到，因此就想着看看op_array是怎么被赋值的，相关步骤做了什么。

2-7、重新从2-5的断点开始，让程序逐步执行，看到op_array的赋值如下：

![][9]

看到第1470行将zend_compile_file函数运行的结果赋值给op_array了，于是break zend_compile_file，被告知zend_compile_file未定义，通过源码工具追踪到zend_compile_file指向的是compile_file，于是break zend_compile

发现是在Zend/zend_language_scanner.l 文件断点了，逐步执行，看到这行pass_two(op_array)，猜测可能会在这里就有值，所以打印看看：

![][10]

结果发现还是跟之前的一样，但是此时看到有一个opcodes的值，再打印看看

![][11]

![][12]

看到opcode = 38，网上查到38代表赋值

![][13]

2-8、于是可以知道，在这一步之前就已得到了ASSIGN的opcode，因此，不断地往前找，从op_array开始初始化时就开始，逐步打印op_array->opcodes的值，一直都是null，

![][14]

直到执行了CG(zend_lineno) = last_lineno;才得到opcode = 38 的值：

![][15]

因为这一句：CG(zend_lineno) = last_lineno;是一个宏，所以也没头绪，接近放弃状态。。。

于是先去了解opcode的数据结构，在[深入理解PHP内核书][16]里找到opcode处理函数查找这一章，给了我一些继续下去的思路。

引用里面的内容：  
在PHP内部有一个函数用来快速的返回特定opcode对应的opcode处理函数指针：zend_vm_get_opcode_handler()函数：

![][17]

知道其实opcode处理函数的命名是有以下规律的

```
    ZEND_[opcode]_SPEC_(变量类型1)_(变量类型2)_HANDLER
```
根据之前调试打印出来的内容，在2-6的时候就看到了一个handler的值：

![][18]

是  
**ZEND_ASSIGN_SPEC_CV_CONST_RETVAL_UNUSED_HANDLER**，

找出函数的定义如下：

![][19]

可以看到，opcode操作的时候，值是从EX_CONSTANT获取的，根据定义展开这个宏，那就是

```
    opline->op2->execute_data->literals
```
这里可以得到两个信息：  
1、参数的转换在opcode执行前就做好了  
2、赋值过程取值时是在op2->execute_data->literals，如果猜想没错的话，op2->execute_data->literals此时保存的就是格式转换后的值，可以打印出来验证一下

打印结果如下：

![][20]

猜想验证正确，但是没有看到真正做转换的地方，还是不死心，继续找PHP的Zend底层做编译的逻辑代码。

参考开源的[GitHub项目][21]，PHP编译阶段如下图：

![][22]

猜测最有可能的是在zendparse、zend_compile_top_stmt这两个阶段完成转换，因为这个两个阶段做的事情就是将PHP代码转换成opcode数组。

上网搜索了PHP语法分析相关的文章，有一篇里面讲到了解析整数的过程，因此找到了PHP真正将大整数做转换的地方：

```c
<ST_IN_SCRIPTING>{LNUM} {
char *end;
if (yyleng < MAX_LENGTH_OF_LONG - 1) { /* Won't overflow */
    errno = 0;
    ZVAL_LONG(zendlval, ZEND_STRTOL(yytext, &end, 0));
    /* This isn't an assert, we need to ensure 019 isn't valid octal
    * Because the lexing itself doesn't do that for us
    */
    if (end != yytext + yyleng) {
        zend_throw_exception(zend_ce_parse_error, "Invalid numeric literal", 0);
        ZVAL_UNDEF(zendlval);
        RETURN_TOKEN(T_LNUMBER);
    }
} else {
    errno = 0;
    ZVAL_LONG(zendlval, ZEND_STRTOL(yytext, &end, 0));
    if (errno == ERANGE) { /* Overflow */
        errno = 0;
        if (yytext[0] == '0') { /* octal overflow */
            ZVAL_DOUBLE(zendlval, zend_oct_strtod(yytext, (const char **)&end));
        } else {
            ZVAL_DOUBLE(zendlval, zend_strtod(yytext, (const char **)&end));
        }
        /* Also not an assert for the same reason */
        if (end != yytext + yyleng) {
            zend_throw_exception(zend_ce_parse_error,
            "Invalid numeric literal", 0);
            ZVAL_UNDEF(zendlval);
            RETURN_TOKEN(T_DNUMBER);
        }
        RETURN_TOKEN(T_DNUMBER);
    }    
    /* Also not an assert for the same reason */
    if (end != yytext + yyleng) {
        zend_throw_exception(zend_ce_parse_error, "Invalid numeric literal", 0);
        ZVAL_UNDEF(zendlval);
        RETURN_TOKEN(T_DNUMBER);
    }
}
ZEND_ASSERT(!errno);
RETURN_TOKEN(T_LNUMBER);
}
```
可以看到，zend引擎在对PHP代码在对纯数字的表达式做词法分析的时候，先判断数字是否有可能会溢出，如果有可能溢出，先尝试将其用LONG类型保存，如果溢出，先用zend_strtod将其转换为double类型，然后用double类型的zval结构体保存之。

number_format失败的原因  
通过gdb调试，追查到number_format函数，在PHP底层最终会调用php_conv_fp函数对数字进行转换：

![][23]

函数原型如下：

```
    PHPAPI char * php_conv_fp(register char format, register double num, boolean_e add_dp, int precision, char dec_point, bool_int * is_negative, char *buf, size_t *len);
```
这里接收的参数num是一个double类型，因此，如果传入的是字符串类型数字的话，number_format函数也会将其转成double类型传入到php_conf_fp函数里。而这个double类型的num最终之所以输出为17978812896666957824，是因为进行科学计数法之后的精度丢失了，重新转成double时就恢复不了原来的值。在C语言下验证：

```c
double local_dval = 1.7978812896666958E+19;
printf("%f\n", local_dval);
```
输出的结果就是

> 17978812896666957824.000000

所以，这不是PHP的bug，它就是这样的。

此类问题解决方案  
对于存储，超过PHP最大表示范围的纯整数，在MySQL中可以使用bigint/varchar保存，MySQL在查询出来的时候会将其使用string类型保存的。  
对于赋值，在PHP里，如果遇到有大整数需要赋值的话，不要尝试用整型类型去赋值，比如，不要用以下这种：

```php
$var = 17978812896666957068;
```
而用这种：

```php
$var = '17978812896666957068';
```
而对于number_format，在64位操作系统下，它能解析的精度不会丢失的数，建议的最大值是这个：9007199254740991。参考鸟哥博客：[http://www.laruence.com/2011/12/19/2399.html][24]

## 总结

这个问题的原因看起来不太重要，虽然学这个对于实际上的业务开发也没什么用，不会让你的开发能力“duang"地一下上去几个level，但是了解了PHP对于大整数的处理，也是自己知识框架的一个小小积累，知道了为什么之后，在日常开发中就会多加注意，比如从存储以及使用赋值的角度。了解这个细节还是很有好处的。

回想整个解决问题的过程，个人感觉有点长，总共大约花了4个小时去定位这个问题。因为对PHP的内核只是一知半解，没有系统的把整个流程梳理下来，所以一开始也不知道从哪里开始下手，就开始根据自己的猜测来调试。现在回想起来，应该先学习PHP的编译、执行流程，然后再去猜测具体的步骤。

[0]: http://www.cnblogs.com/hoohack/p/7519782.html
[1]: ../img/411456445.png
[2]: ../img/1916296156.png
[3]: ../img/141614450.png
[4]: ../img/1672320073.png
[5]: ../img/28072102.png
[6]: ../img/1443777130.png
[7]: ../img/1439640686.png
[8]: ../img/452215817.png
[9]: ../img/1908290426.png
[10]: ../img/826486261.png
[11]: ../img/611039569.png
[12]: ../img/407817230.png
[13]: ../img/1449998198.png
[14]: ../img/872216035.png
[15]: ../img/1564475996.png
[16]: http://www.php-internals.com/book/
[17]: ../img/150888133.png
[18]: ../img/1310301449.png
[19]: ../img/1989077960.png
[20]: ../img/1461899066.png
[21]: https://github.com/pangudashu/php7-internal
[22]: ../img/1212169477.png
[23]: ../img/1925745864.png
[24]: http://www.laruence.com/2011/12/19/2399.html
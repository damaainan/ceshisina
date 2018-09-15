# [［PHP源码阅读］number_format函数][0]


上次讲到[PHP是如何解析大整数][1]的，一笔带过了number_format的处理，再详细阅读该函数的源码，以下是小分析。

## 函数原型
```
    string number_format ( float $number [, int $decimals = 0 ] )
    
    string number_format ( float $number , int $decimals = 0 , string $dec_point = "." , string $thousands_sep = "," )
```
函数可以接受1、2、4个参数（具体可以看代码的实现）。

如果只提供第一个参数，number的小数部分会被去掉，并且每个千位分隔符都是英文小写逗号"," ；  
如果提供两个参数，number将保留小数点后的位数到你设定的值，其余同楼上；  
如果提供了四个参数，number 将保留decimals个长度的小数部分, 小数点被替换为dec_point，千位分隔符替换为thousands_sep

## PHP_FUNCTION(number_format)
```c
// number
// 你要格式化的数字
// num_decimal_places
// 要保留的小数位数
// dec_separator
// 指定小数点显示的字符
// thousands_separator
// 指定千位分隔符显示的字符
/* {{{ proto string number_format(float number [, int num_decimal_places [, string dec_separator, string thousands_separator]])
   Formats a number with grouped thousands */
PHP_FUNCTION(number_format)
{
    // 期望number_format的第一个参数num是double类型的，在词法阶段已经对字面量常量做了转换
    double num;
    zend_long dec = 0;
    char *thousand_sep = NULL, *dec_point = NULL;
    char thousand_sep_chr = ',', dec_point_chr = '.';
    size_t thousand_sep_len = 0, dec_point_len = 0;
    // 解析参数
    ZEND_PARSE_PARAMETERS_START(1, 4)
        Z_PARAM_DOUBLE(num)// 拿到double类型的num
        Z_PARAM_OPTIONAL
        Z_PARAM_LONG(dec)
        Z_PARAM_STRING_EX(dec_point, dec_point_len, 1, 0)
        Z_PARAM_STRING_EX(thousand_sep, thousand_sep_len, 1, 0)
    ZEND_PARSE_PARAMETERS_END();
    switch(ZEND_NUM_ARGS()) {
    case 1:
        RETURN_STR(_php_math_number_format(num, 0, dec_point_chr, thousand_sep_chr));
        break;
    case 2:
        RETURN_STR(_php_math_number_format(num, (int)dec, dec_point_chr, thousand_sep_chr));
        break;
    case 4:
        if (dec_point == NULL) {
            dec_point = &dec_point_chr;
            dec_point_len = 1;
        }
        if (thousand_sep == NULL) {
            thousand_sep = &thousand_sep_chr;
            thousand_sep_len = 1;
        }
        // _php_math_number_format_ex
        // 真正处理的函数，在本文件第1107行
        RETVAL_STR(_php_math_number_format_ex(num, (int)dec,
                dec_point, dec_point_len, thousand_sep, thousand_sep_len));
        break;
    default:
        WRONG_PARAM_COUNT;
    }
}
/* }}} */
```
## 代码执行流程图

![][2]

## `_php_math_number_format_ex`函数实现的各种参数数量，最终都会调用_p`hp_math_number_format_ex`函数。函数主要做的是：

> 处理负数；  
> 根据要保留的小数点对浮点数进行四舍五入；  
> 调用strpprintf函数将浮点数表达式转成字符串表示；  
> 计算需要分配给结果变量的字符串长度；  
> 将结果拷贝到返回值中（如果有千位符，则进行千位符分割）

## strpprintf

这个函数是实现浮点数与字符串的转换，如上文所说，最终是调用了php_conv_fp函数做的转换（这里是通过gdb调试做的定位），而php_conv_fp函数，往下追踪，调用的是zend_dtoa函数，

更多细节注解，见[github项目提交记录][3]。

## 总结

阅读完这个函数的源码，学习到的是浮动数与字符串的互相转换的实现细节，字符串与浮点数之间的关系较复杂，之后还要继续学习。

[0]: http://www.cnblogs.com/hoohack/p/7570136.html
[1]: http://www.hoohack.me/2017/09/14/learning-php-big-number-detail
[2]: ./img/1550596176.png
[3]: https://github.com/hoohack/read-php-src/commit/2bac1ac45911d42884b0fe7bda2ecce65dd59235
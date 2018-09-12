# [PHP output buffer](https://www.jianshu.com/p/1a020dfe9e39)

2017.09.13 13:33  字数 1764 

# PHP output buffer

## LNMP架构中的输出缓冲区

首先来看一下LNMP架构中涉及输出缓冲区的部分，后面会依次验证：

![image.png][1]

从图中，可以看出 Nginx层，SAPI层(PHP-FPM)和PHP内核中均有输出缓冲区，由此可见缓冲区是多么重要的一部分。

## PHP-FPM 输出缓冲区

探讨下PHP-FPM 中的输出缓冲区，先来设置一个ini配置: output_buffering，把他设置为 0：

```ini
; Development Value: 4096
; Production Value: 4096
; http://php.net/output-buffering
output_buffering = 0
```

设置完这个参数后，便关闭了PHP内核中的默认缓冲区。  
来看一段代码，因为nginx中设置的fastcgi buffer的大小是4k，而每次输出的数据都是大于4k的，因此输出不会受到nginx输出缓冲区的影响。

### 未使用任何flush函数
```php
<?php
/**
 * 默认页面
 */
class IndexController extends AbstractController {

    public function process() {

        for($i = 0; $i < 10; ++$i) {
            echo $i;
            echo str_repeat(' ', 4096) . "<br />";
            sleep(1);
        }   
        exit;
    }   
}   
```

运行这段代码，预期是每次输出1个数字，可是结果却是每次输出2个数字，即0，1一起输出，2，3一起输出，这是为什么呢？  
由于关闭了PHP内核中的输出缓冲，也没有使用ob_start系列的函数，而且数据量也大于了nginx中的输出缓冲，所以我猜测PHP-FPM中还有一个输出缓冲区,通过查阅fpm的源代码(php-5.6.10/sapi/fpm/fpm/fastcgi.h文件)，我发现在`fcgi_request`结构中有一个`out_buf`参数，大小是`8k`：
```c
typedef struct _fcgi_request {
    int            listen_socket;
#ifdef _WIN32
    int            tcp;
#endif
    int            fd;
    int            id;
    int            keep;
    int            closed;
 
    int            in_len;
    int            in_pad;
 
    fcgi_header   *out_hdr;
    unsigned char *out_pos;
    unsigned char  out_buf[1024*8];
    unsigned char  reserved[sizeof(fcgi_end_request_rec)];
 
    HashTable     *env;
} fcgi_request;
```

于是我觉得这个参数应该就是PHP-FPM中的输出缓冲区大小。  
OK，来验证一下，将上面代码中空格的数目改为8192试试，即：
```php
public function process() {
    for($i = 0; $i < 10; ++$i) {
        echo $i;
        echo str_repeat(' ', 8192) . "<br />";
        sleep(1);
    }
    exit;
}
```

重新请求该页面，果然，现在是每次输出1个数字了，和我预料的一样。

### 使用ob_flush函数:
```php
public function process() {
    for($i = 0; $i < 10; ++$i) {
        echo $i;
        echo str_repeat(' ', 4096) . "<br />";
        ob_flush();
        sleep(1);
    }
    exit;
}
```

重新请求上面的页面，发现还是每次输出2个数字，说明ob_flush不能刷新PHP-FPM缓冲区。

### 使用flush函数:
```php
public function process() {
    for($i = 0; $i < 10; ++$i) {
        echo $i;
        echo str_repeat(' ', 4096) . "<br />";
        flush();
        sleep(1);
    }
    exit;
}
```

重新请求上面的页面，每次输出1个数字，说明flush函数可以刷新PHP-FPM缓冲区。

## PHP内核默认缓冲区

了解了PHP-FPM中的缓冲区后，来看看PHP内核中的输出缓冲区。  
将`ini`配置中`output_buffering`设置为`16384`，即`16k`，这主要是为了避开PHP-FPM中缓冲区的影响，毕竟PHP-FPM中的缓冲区大小是硬编码的，没法修改配置，只能规避。

### 未使用任何flush函数

重新请求二(1)中的代码, 页面中每次输出4个数字，说明output_buffering的设置起了作用，4个数字的输出结果就是16k+。

### 使用ob_flush函数

重新请求二(2)中的代码，页面中每2个数字输出一次，可见output_buffering已经不起作用了，现在只有PHP-FPM中的缓冲区起作用,由此说明ob_flush函数可以刷新PHP内核中的默认输出缓冲区。

### 使用flush函数

重新请求二(3)中的代码，页面中每4个数字输出一次，这说明flush函数不能刷新PHP内核中默认的输出缓冲区。

### 同时使用flush和ob_flush函数，即:
```php
public function process() {

    for($i = 0; $i < 10; ++$i) {
        echo $i;
        echo str_repeat(' ', 4096) . "<br />";
        ob_flush();
        flush();
        sleep(1);
    }
    exit;
}
```

正如前面已经得到的结论，`ob_flush`可以刷新PHP内核中的默认缓冲区，flush可以刷新PHP-FPM中的缓冲区，那么这2个函数一起使用，就可以得到预期的效果了:每次输出1个数字。

至此，已经了解了LNMP输出缓冲区架构图中default Ob到PHP-FPM的部分了。

## PHP内核用户自定义缓冲区

PHP内核中，除了默认的输出缓冲区之外，用户也可以自己定义输出缓冲区，也就是ob_系列函数了。  
(1)、可以通过`ob_start`函数激活一个缓冲区，激活的缓存区被压入栈中(OG(handlers))，多次调用ob_start函数会生成一个缓冲区栈。  
开启`output_buffering`配置后，PHP内核默认输出缓冲区处于OG(handlers)的栈底。  
(2)、通过ob_get_contents函数可以获取OG(handlers)栈顶部缓冲区(称之为active)的内容。  
(3)、通过`ob_flush`函数可以将active缓冲区的内容冲刷到active缓冲区的上一级缓冲区(栈顶的下一位)，如果active已经处于栈底，那么冲刷active缓冲区会将其内容输出到SAPI(正如在三中所看到的)。  
(4)、通过`ob_end_flush`函数除了将active缓冲区的内容冲刷出去外，还会将当前缓冲区从栈顶弹出。

从(3)(4)中可以看出，如果在代码中使用了嵌套的ob_start，如果还希望，调用完ob_flush和flush的组合之后，就能将PHP中缓冲区的内容输出给Nginx，那么代码中的ob_start函数的数目和ob_end_相关函数的数目应该是一致的,如下简单示例:
```php
public function process() {
    for($i = 0; $i < 10; ++$i) {
        ob_start();
        echo $i;
        ob_start();
        echo str_repeat(' ', 4096) . "<br />";
        ob_end_flush();
        ob_end_flush();
        ob_flush();
        flush();
        sleep(1);
    }   
    exit;
}
```

函数仅用于示例，没有实际意义….  
至此，已经了解了LNMP输出缓冲区架构图中User Ob的部分。

## Nginx fastcgi 缓冲区

之前都是关闭了gzip，因为gzip后，数据小于fastcgi的buffer大小，这样的话就没法达到想要的效果，但是关闭gzip就起不到压缩数据的目的了，而且在整个数据的生成过程中，已经有多个buffer了，因此可以关闭nginx的fastcgi buffer了，如下:
```nginx
    fastcgi_buffering off;
```

运行如下的一段代码:
```php
public function process() {
    for($i = 0; $i < 10; ++$i) {
        echo $i;
        echo str_repeat(' ', 4096) . "<br />";
        ob_flush();
        flush();
        sleep(1);
    }
    exit;
}
```

这段代码是可以达到的效果的: 每s输出一个数字!  
当然，直接关闭`fastcgi_buffer`是很不灵活的，因为毕竟有很多请求是不使用bigpipe模式的，还好nginx为提供了一个header头: `X-Accel-Buffering`,使用这个header头可以不使用buffer，如下:
```php
public function process() {
    header('X-Accel-Buffering: no');
    for($i = 0; $i < 10; ++$i) {
        echo $i;
        echo str_repeat(' ', 4096) . "<br />";
        ob_flush();
        flush();
        sleep(1);
    }   
    exit;
}
```

这样在开启`fastcgi_buffer`和gzip的情况下面也可以每s输出一个数字。  
在代码中始终有补全4k个空格的操作，这是因为有些浏览器接收到这么多数据的时候才会渲染，我在mac中的chrome中测试，当使用了`X-Accel-Buffering`头的时候，即使不补全空格，也是可以实现的效果的，但是在windows中的IE浏览器中不行。

## implicit_flush配置和ob_implicit_flush函数

其中，如果将`php.ini`配置中的`implicit_flush`设置为1, 或者调用`ob_implicit_flush(true)`，那么就不需要调用flush函数了，他们都会刷新PHP-FPM缓冲区中的内容。

本文中主要讨论了SAPI使用PHP-FPM时的输出缓冲区情况，对于别的SAPI，需要区别对待，比如CLI，他的`implicit_flush`硬编码为1，他的`output_buffering`也是设置为0。


[1]: ../img/1270516-3aa4c34352ab4f68.png
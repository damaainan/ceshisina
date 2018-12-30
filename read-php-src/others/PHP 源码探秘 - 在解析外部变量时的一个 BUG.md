## PHP 源码探秘 - 在解析外部变量时的一个 BUG

来源：[https://segmentfault.com/a/1190000017051950](https://segmentfault.com/a/1190000017051950)


## 安利

原文：我的个人博客 [https://mengkang.net/1301.html][0]
工作了两三年，技术停滞不前，迷茫没有方向，安利一波我的直播 [PHP 进阶之路][1]
## bug 复现

有个朋友跟我描述了一个bug，要我帮看看是什么情况。原本他有一个表单，如下。

```html
<form method="post">
    <input type="text" name="id[]" value="1">
    <input type="text" name="id[]" value="2">
    <input type="submit">
</form>
```

但是有一个前端插件会动态插入两个`input`，最后`ajax`提交的时候是

```html
<form method="post">
    <input type="text" name="id[]" value="1">
    <input type="text" name="id[]_text" value="a">
    <input type="text" name="id[]" value="2">
    <input type="text" name="id[]_text" value="b">
    <input type="submit">
</form>
```
## 后端

当我们用 php 来接收的时候

```php
echo file_get_contents('php://input');
echo "\n";
var_export($_POST);
echo "\n";
echo PHP_VERSION;
```

结果是

```
id%5B%5D=1&id%5B%5D_text=a&id%5B%5D=2&id%5B%5D_text=b
array (
  'id' => 
  array (
    0 => '1',
    1 => 'a',
    2 => '2',
    3 => 'b',
  ),
)
7.0.10
```
## 使用 nodejs 尝试

```js
var http = require('http');
var querystring = require('querystring');

var postHTML = '<form method="post">' +
    '<input type="text" name="id[]" value="1"><input type="text" name="id[]_text" value="a">' +
    '<input type="text" name="id[]" value="2"><input type="text" name="id[]_text" value="b">' +
    '<input type="submit"></form>';

http.createServer(function (req, res) {
    var body = "";
    req.on('data', function (chunk) {
        body += chunk;
        console.log(body);
        body = querystring.parse(body);
        console.log(body);
    });
    req.on('end', function () {
        res.writeHead(200, {'Content-Type': 'text/html; charset=utf8'});
        res.write(postHTML);
        res.end();
    });
}).listen(3000);
```

控制台输出的是

```
id%5B%5D=1&id%5B%5D_text=a&id%5B%5D=2&id%5B%5D_text=b
{ 'id[]': [ '1', '2' ], 'id[]_text': [ 'a', 'b' ] }
```
## 小结

在接收外部变量时，多个相同的外部变量，在`nodejs`中会被放在一个数组里面，而`php`中则是后者覆盖前者，如果需要传递数组变量，则在变量名后面添加上`[]`。 **`这个不兼容，ok，是语言的特性能接受`** 。

但是在`php`中在解析`id[]_text`的数据的时候都转换成`id[]`了，这点就有点坑了。rfc 在这方面也没看到有规定否则不会出现两种语言解析不一致的情况了。
## 源码分析

也就是说 php 后端在解析的时候的问题。那只能从源码里一探究竟看php是如何解析post数据的了。
我把子进程数修改为1，然后根据`pid`来调试

```
gdb -p 22892
...
(gdb) b /data/soft/php-7.1.10/main/php_variables.c:php_register_variable_ex
Breakpoint 1 at 0x812877: file /data/soft/php-7.1.10/main/php_variables.c, line 70.
(gdb) i b
Num     Type           Disp Enb Address            What
1       breakpoint     keep y   0x0000000000812877 in php_register_variable_ex at /data/soft/php-7.1.10/main/php_variables.c:70
(gdb)
(gdb) c
Continuing.

Breakpoint 1, php_register_variable_ex (var_name=0x7fb5b9056218 "id[]", val=0x7ffff23dacd0, track_vars_array=0xf114a0) at /data/soft/php-7.1.10/main/php_variables.c:70
70        if (track_vars_array && Z_TYPE_P(track_vars_array) == IS_ARRAY) {
(gdb) bt
#0  php_register_variable_ex (var_name=0x7fb5b9056218 "id[]", val=0x7ffff23dacd0, track_vars_array=0xf114a0) at /data/soft/php-7.1.10/main/php_variables.c:70
#1  0x00000000005af0d1 in php_sapi_filter (arg=<value optimized out>, var=0x7fb5b9056218 "id[]", val=0x7ffff23dad48, val_len=1, new_val_len=0x7ffff23dad40)
    at /data/soft/php-7.1.10/ext/filter/filter.c:465
#2  0x00000000008135d0 in add_post_var (arr=0x7ffff23dce50, var=0x7ffff23dcda0, eof=<value optimized out>) at /data/soft/php-7.1.10/main/php_variables.c:308
#3  0x0000000000813ce6 in add_post_vars (content_type_dup=<value optimized out>, arg=0x7ffff23dce50) at /data/soft/php-7.1.10/main/php_variables.c:324
#4  php_std_post_handler (content_type_dup=<value optimized out>, arg=0x7ffff23dce50) at /data/soft/php-7.1.10/main/php_variables.c:361
#5  0x000000000080cfe0 in sapi_handle_post (arg=<value optimized out>) at /data/soft/php-7.1.10/main/SAPI.c:174
#6  0x00000000008133cf in php_default_treat_data (arg=0, str=0x0, destArray=<value optimized out>) at /data/soft/php-7.1.10/main/php_variables.c:423
#7  0x000000000066c581 in mbstr_treat_data (arg=0, str=0x0, destArray=0x0) at /data/soft/php-7.1.10/ext/mbstring/mb_gpc.c:69
#8  0x0000000000812463 in php_auto_globals_create_post (name=0x7fb5b1ddf768) at /data/soft/php-7.1.10/main/php_variables.c:720
#9  0x000000000084125f in zend_activate_auto_globals () at /data/soft/php-7.1.10/Zend/zend_compile.c:1681
#10 0x000000000081282e in php_hash_environment () at /data/soft/php-7.1.10/main/php_variables.c:690
#11 0x0000000000804c11 in php_request_startup () at /data/soft/php-7.1.10/main/main.c:1672
#12 0x0000000000918282 in main (argc=<value optimized out>, argv=<value optimized out>) at /data/soft/php-7.1.10/sapi/fpm/fpm/fpm_main.c:1904
(gdb)
```

那么我们看`php_register_variable_ex`怎么写的，源码精简了下，如下

```c
#include <stdio.h>
#include <assert.h>
#include <memory.h>
#include <stdlib.h>

void php_register_variable_ex(char *var_name);

typedef unsigned char zend_bool;

int main() {
    char *var_name = "id 1.2[]_3";
    php_register_variable_ex(var_name);
    return 0;
}

void php_register_variable_ex(char *var_name)
{
    char *p = NULL;
    char *ip = NULL;        /* index pointer */
    char *index;
    char *var, *var_orig;
    size_t var_len, index_len;
    zend_bool is_array = 0;

    assert(var_name != NULL);

    /* ignore leading spaces in the variable name */
    while (*var_name==' ') {
        var_name++;
    }

    /*
     * Prepare variable name
     */
    var_len = strlen(var_name);
    var = var_orig = malloc(var_len + 1);
    memcpy(var_orig, var_name, var_len + 1);

    /* ensure that we don't have spaces or dots in the variable name (not binary safe) */
    for (p = var; *p; p++) {
        if (*p == ' ' || *p == '.') {
            *p='_';
        } else if (*p == '[') {
            is_array = 1;
            ip = p;
            *p = 0;
            break;
        }
    }
    var_len = p - var;
    
    printf("var\t%s\n",var);
    printf("var_len\t%zu\n",var_len);

}
```

根据`php_register_variable_ex`里面的规则:


* `name`里面的` `和`.`都被替换成`_`
* `name`里遇到`[`则认为是数组，数组的key为`[`前面的字符串，后面的都被舍去。


上面我模拟了表单提交一个`name`为`id 1.2[]_3`时，输出结果就是

```
var    id_1_2
var_len    6
```
## 思考为什么

上面的替换规则在官方手册中有说明

[http://php.net/manual/zh/lang...][2]
Dots and spaces in variable names are converted to underscores.但是没有看到命名中关于不使用`[]`后连接字符串的说明。
### extract

难道是因为`extract`原因，如果数组`key`里面有`[]`，则没办法正常执行了。

```php
$foo["id"] = 1;
$foo["id[]_text"] = 2;

var_export($foo);

extract($foo);

var_export(get_defined_vars());
```

试了以上代码，也印证了我的想法`id[]_text`的值直接丢失了。
### 所以


* `php`在接受这样命名的（`foo[]boo`）外部变量名是不符合规范的， **`手册文档需要补全`** ；
* `php`在接受这样不符合命名规范的（`foo[]boo`）外部变量的时候是强制转换成数组，还是直接丢弃呢？


## 后续处理方案


* 我提交了 bug [https://bugs.php.net/bug.php?...][3]

* 官方修复：在文档上补全说明 [http://php.net/manual/zh/lang...][2]

* php 8 里面可能设置开关来控制是否对外部变量进行转换 [https://bugs.php.net/bug.php?...][5]


[0]: https://mengkang.net/1301.html
[1]: https://segmentfault.com/ls/1650000011318558
[2]: http://php.net/manual/zh/language.variables.external.php
[3]: https://bugs.php.net/bug.php?id=77172
[4]: http://php.net/manual/zh/language.variables.external.php
[5]: https://bugs.php.net/bug.php?id=34882
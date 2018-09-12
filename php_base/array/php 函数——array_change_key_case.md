## [每日一个 php 函数——array_change_key_case](https://segmentfault.com/a/1190000010476969)

> 因为已经有文档了，可能有些人觉得我写这个有些多余了。可是并不是每一个 PHPer 都会好好地去阅读文档，自然有一些函数可能都没有听说过（很不幸我也是这其中的一员）。我也希望能通过写这些文章，能够促使我完整地读完文档，同时，能够给其它的 PHPer 一个参考，“啊，原来还有这个函数” 的感觉。同时，我也希望我能通过写这些文章，去阅读各个函数的 C 语言实现。也实现自我驱动地学习。

## 函数原型

    array array_change_key_case ( array $array [, int $case = CASE_LOWER ] )

该函数的具体作用是，将一个数组中的所有的英文字母转换为大写或小写。

我们可以看到，这个函数接收两个参数，返回一个数组。第一个参数数组没有使用引用的方式，那么说明该函数并不会改变原数组，它会生成新的数组作为返回值。而第二个参数是可选的，它控制着该函数是转换成大写还是小写。默认是转化为小写。

## 函数使用

### 第二个参数

函数的第二个参数传入的是一个预定义常量，分别是 `CASE_LOWER` 和 `CASE_UPPER`，前者是将 key 转换成小写，也是函数的默认值；后者是将 key 转换成大写。

### 使用

```php
$arr = [
    'loWer' => 1,
];
 
$toLower = array_change_key_case($arr, CASE_LOWER);
// 我认为，不管它的默认值是什么，我们都要写上这第二个参数。我们的代码写出来，是给人看的，不是给机器看的。
// 所以我们的代码应当尽量多的包含语义。

$toUpper = array_change_key_case($arr, CASE_UPPER);

var_dump($toLower);

/**
[
    'lower' => 1
]
*/

var_dump($toUpper);

/**
[
    'LOWER' => 1
]
*/
```

不过，这个函数不是递归的。我们看一下下面这个例子。

```php
$arr = [
    'loWer' => [
        'Lower' => 1,
    ],
];
 
$toLower = array_change_key_case($arr, CASE_LOWER);

var_dump($toLower);

/**
[
    'lower' => [
        'Lower' => 1,
    ],
]
    */
```
### 坑

这个函数的使用，是有个坑的，这个坑就是，当转换之后，如果结果中有两个相同的 key，那么就会保留最后的那个。举个例子。

```php
$arr = [
    'key' => 1,
    'kEy' => 2,
    'keY' => 3,
];

$toLower = array_change_key_case($arr, CASE_UPPER);

var_dump($toLower); // ['key' => 3]
```
在这个例子中，我们发现，当执行转换之后，三个 key 变成相同的了，那么在这种情况下，只会保留最后一个元素作为 key。这里得到的数组是 ['key' => 3]。

## 内核实现

该函数的源代码在 php-src/ext/standard/array.c 中。

### 源码

我们先来看一下源代码。

```c
PHP_FUNCTION(array_change_key_case)
{
    zval *array, *entry;
    zend_string *string_key;
    zend_string *new_key;
    zend_ulong num_key;
    zend_long change_to_upper=0;

    ZEND_PARSE_PARAMETERS_START(1, 2)
        Z_PARAM_ARRAY(array)
        Z_PARAM_OPTIONAL
        Z_PARAM_LONG(change_to_upper)
    ZEND_PARSE_PARAMETERS_END();

    array_init_size(return_value, zend_hash_num_elements(Z_ARRVAL_P(array)));

    ZEND_HASH_FOREACH_KEY_VAL(Z_ARRVAL_P(array), num_key, string_key, entry) {
        if (!string_key) {
            entry = zend_hash_index_update(Z_ARRVAL_P(return_value), num_key, entry);
        } else {
            if (change_to_upper) {
                new_key = php_string_toupper(string_key);
            } else {
                new_key = php_string_tolower(string_key);
            }
            entry = zend_hash_update(Z_ARRVAL_P(return_value), new_key, entry);
            zend_string_release(new_key);
        }

        zval_add_ref(entry);
    } ZEND_HASH_FOREACH_END();
}
```
### 关于 PHP_FUNCTION 宏

熟悉 PHP 扩展开发的同学应该都知道，PHP_FUNCTION 这个宏，是定义一个 PHP 函数用的，参数就是 PHP 函数的函数名。关于这个宏，有兴趣的可以去看看源码，它其实是将 PHP_FUNCTION(array_change_key_case) 替换成了 void zif_array_change_key_case(zend_execute_data *execute_data, zval *return_value)，这样的一个函数定义。注意里面的 return_value 变量，后面会用到这个变量。

### 逻辑代码

其实，真正的逻辑代码，是在 ZEND_HASH_FOREACH_KEY_VAL 宏和 ZEND_HASH_FOREACH_END 之间的。上面的几个宏，是为了检查并获取传参进 PHP 函数的变量。我们可以看到 zend_long change_to_upper=0; 这个是用来判断，是大写还是小写的。这里定义的默认值是 0，所以这个函数的默认是小写。而整个函数的最核心的代码是 php_string_toupper 和 php_string_tolower 这两个函数。

这是其中之一的代码。

```c
PHPAPI zend_string *php_string_toupper(zend_string *s)
{
    unsigned char *c, *e;

    c = (unsigned char *)ZSTR_VAL(s);
    e = c + ZSTR_LEN(s);

    while (c < e) {
        if (islower(*c)) {
            register unsigned char *r;
            zend_string *res = zend_string_alloc(ZSTR_LEN(s), 0);

            if (c != (unsigned char*)ZSTR_VAL(s)) {
                memcpy(ZSTR_VAL(res), ZSTR_VAL(s), c - (unsigned char*)ZSTR_VAL(s));
            }
            r = c + (ZSTR_VAL(res) - ZSTR_VAL(s));
            while (c < e) {
                *r = toupper(*c);
                r++;
                c++;
            }
            *r = '\0';
            return res;
        }
        c++;
    }
    return zend_string_copy(s);
}
```
用 C 写过转换字符串大小写的同学都知道，其实和我们自己实现的思路基本都差不多。只是用了几个宏。c 就是字符串的首地址，e 是字符串 '\0' 的地址。从 c 到 e 循环，然后来对每一个地址的字符转换大小写。

这里用到的 islower、isupper、tolower、toupper 都是 ANSI C 中提供的函数。

## 结语

PHP 的文档其实是很好的学习资料，但是很多 PHPer 都没有真正好好看过文档（包括我）。前面也说了，我写这个的目的，就是希望能够通过这种方式，来对文档进行全面的扫描。同时，也从每一个函数切入，逐步地去看 PHP 内核的实现。对于后面 C 的文字，我只能尽我自己所能来解释了，有不到的地方大家多包涵，毕竟我也是一个学习者。


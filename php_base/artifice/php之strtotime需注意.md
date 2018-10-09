## php之strtotime需注意

来源：[http://fbd.intelleeegooo.cc/php-strtotime/](http://fbd.intelleeegooo.cc/php-strtotime/)

时间 2018-10-09 17:05:24


php之strtotime需注意

```php
var_dump(date('Y-m-d'));
var_dump(date('Y-m-d', strtotime('- 1 day')));
var_dump(date('Y-m-d', strtotime('+ 2 day')));
var_dump(date('Y-m-d', strtotime('- 1 week')));
var_dump(date('Y-m-d', strtotime('+ 2 week')));
```

打印出来的结果是：

```php
string(10) "2018-10-09"
string(10) "2018-10-08"
string(10) "2018-10-11"
string(10) "2018-10-02"
string(10) "2018-10-23"
```

上面的这些都没有问题，毕竟day和week的时间是固定的，但是month就不一样了，有大月和小月

```php
var_dump(date("Y-m-d", strtotime("-1 month", strtotime("2018-05-31"))));
```

打印出来的结果是：

```php
string(10) "2018-05-01"
```

！！！怎么回事儿，我希望得到的是`2018-04-30`。看了[鸟哥的这篇博客][0]知道了其中原委。

那上面的`2018-05-31`举例子，`-1 month`应该是`2018-04-31`，但是4月没有31号， 所以结果就是`2018-05-01`如果要获取`上一个月最后一天`，可以使用`last day of -1 month`来获取，如下图所示

```php
var_dump(date("Y-m-d", strtotime("last day of -1 month", strtotime("2018-05-31"))));
```

打印结果是：

```php
string(10) "2017-04-30"
```

为了避免`strtotime`引起的问题，还可以使用`mktime`来解决一些问题，比如说：

每月最后一天最后一秒需要汇总一下当月的某些业务指标，发送给老板。

那么开始时间和结束时间，就可以这样：

mktime的语法是这样的：`mktime(hour,minute,second,month,day,year);`

```php
$startTime = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d'), date('Y')));
$endTime = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'), date('d'), date('Y')));
```


[0]: http://www.laruence.com/2018/07/31/3207.html
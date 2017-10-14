# php里的随机数

 时间 2017-06-12 11:25:59  

原文[http://5alt.me/2017/06/php里的随机数/][1]


这次来填一个关于php随机数预测的坑。php5和php7中的随机数产生机制已经大有不同，本文将详细讲一讲这些区别和爆破种子的时候会产生的问题。

php中常用的随机数产生函数是 `rand()` 和 `mt_rand()` 。下面将针对这两个函数展开。 

## php5 中的随机数 

### rand 

php5 中的 `rand` 函数调用的是 `glibc` 中的 `random()` 。其实现算法可以简化为如下代码。 

```c
    #include <stdio.h>
    
    #define MAX 1000
    #define seed 1
    
    main() {
      int r[MAX];
      int i;
    
      r[0] = seed;
      for (i=1; i<31; i++) {
        r[i] = (16807LL * r[i-1]) % 2147483647;
        if (r[i] < 0) {
          r[i] += 2147483647;
        }
      }
      for (i=31; i<34; i++) {
        r[i] = r[i-31];
      }
      for (i=34; i<344; i++) {
        r[i] = r[i-31] + r[i-3];
      }
      for (i=344; i<MAX; i++) {
        r[i] = r[i-31] + r[i-3];
        printf("%d\n", ((unsigned int)r[i]) >> 1);
      }
    }
```

我们可以看到，当前的随机数与之前第31个和之前第3个有关。因此只要获取了连续31个随机数，就有极大概率（输出的时候左移导致部分信息丢失）预测后面的随机数。

### mt_rand 

php5的 `mt_rand` 函数实现的是一个错误版本的 MT19937 随机数生成算法。 

在 twist 函数中有个地方把变量弄错了 =。 = 

对php5的 `mt_rand` 的攻击就是根据几个随机数序列来爆破种子，然后推测出整个随机数的序列。工具是openwall的 `php_mt_seed` 。 

值得一提的是， `mt_rand` 可以指定随机数产生的范围 `int mt_rand ( int $min , int $max )` 。此时产生随机数的方式是用一个宏来处理。 

```c
    #define RAND_RANGE(__n, __min, __max, __tmax) \
        (__n) = (__min) + (long) ((double) ( (double) (__max) - (__min) + 1.0) * ((__n) / ((__tmax) + 1.0)))
    ...
    number = (long) (php_mt_rand(TSRMLS_C) >> 1);
    if (argc == 2) {
        RAND_RANGE(number, min, max, PHP_MT_RAND_MAX);
    }
    RETURN_LONG(number);
```

即用内置函数 `php_mt_rand` 产生的随机数进行乘除操作，得到位于合适范围的值。需要注意的是，无论 `mt_rand` 函数的调用带不带范围，其输出的结果与 php_mt_rand(TSRMLS_C) >> 1 有关。通过 `mt_rand()` 可以直接计算出 `mt_rand(min, max)` 来。 

## php7 中的随机数 

### rand 

php7中的 rand 函数同 `mt_rand` ， srand 同 `mt_srand` 。 

### mt_rand 

从 php 7.1.0 开始， mt_rand 函数修复了之前对 MT19937 实现上的错误，但是仍然保留了错误版本的随机数生成方式。详情见 `mt_srand` 的文档。 

* MT_RAND_MT19937 Uses the fixed, correct, Mersenne Twister implementation, available as of PHP 7.1.0.
* MT_RAND_PHP Uses an incorrect Mersenne Twister implementation which was used as the default up till PHP 7.1.0. This mode is available for backward compatibility.

除了修复 `twist` 函数的问题之外，在产生一个范围的随机数的时候，php7和php5的行为也不一致。 

```c
    if (argc == 0) {
        // genrand_int31 in mt19937ar.c performs a right shift
        RETURN_LONG(php_mt_rand() >> 1);
    }
    ...
    RETURN_LONG(php_mt_rand_common(min, max));
    ...
    if (BG(mt_rand_mode) == MT_RAND_MT19937) {
        return php_mt_rand_range(min, max);
    }
    ...
    umax++;
    ...
    result = php_mt_rand();
    ...
    return (zend_long)((result % umax) + min);
```

根据代码我们可以看到， `mt_rand` 函数不带范围的时候，输出的结果为 php_mt_rand() >> 1 ；而带范围的时候，输出的结果为 `(php_mt_rand() % (max-min+1)) + min` 。通过 `mt_rand()` 不可以直接计算出 `mt_rand(min, max)` ，存在一些误差。 

由于上述原因，之前用来爆破随机数种子的工具已经无法直接使用。我自己实现了一个多线程爆破种子的c程序，在4核2G内存的虚拟机上跑了大概4个小时才将种子遍历完毕。

### random_int / random_bytes 

php7 提供了更加安全的随机数产生函数， `random_int` 和 `random_bytes` 调用了系统的一些安全的随机数产生函数来输出。 

* On Windows, » CryptGenRandom() will always be used.
* On Linux, the » getrandom(2) syscall will be used if available.
* On other platforms, /dev/urandom will be used.
* If none of the aforementioned sources are available, then an Exception will be thrown.

## php的运行模式对随机数产生的影响 

### Apache2handler 

在 `/sapi/apache2handler/sapi_apache2.c` 中 static int php_handler(request_rec *r) 函数可以看到， 

```c
    ctx = SG(server_context);
    parent_req = ctx->r;
    ...
    if (!parent_req) {
            php_apache_request_dtor(r);
            ...
          }
```

只有在 `parent_req` 为 NULL 的情况下，才会运行到 `php_apache_request_dtor` ，调用 `php_request_shutdown` ，这个函数会调用注册的 `PHP_RSHUTDOWN_FUNCTION` ，导致随机数的种子被标记为未初始化。 

在Apache下，一个 `Connection` 中的所有 `request` 都交给一个 Apache 的进程处理。很可能没有调用到 `php_apache_request_dtor` 导致在一个 `Connection` 中的请求共用一个种子。（未测试） 

### php-fpm 

在 `/sapi/fpm/fpm/fpm_main.c` 中 int main(int argc, char *argv[]) 函数可以看到， php-fpm 的进程会循环处理请求，请求结束后调用 php_request_shutdown 函数进行清理。因此，在 php-fpm 的环境下，每个请求用的都是一个新的种子。


[1]: http://5alt.me/2017/06/php里的随机数/

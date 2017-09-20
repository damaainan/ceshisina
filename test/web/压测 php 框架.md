# 压测 php 框架

date: 2017-8-20 21:28:57

最近一个项目因为性能原因(原项目使用 nodejs, 单项目中同时提供 http tcp websocket 服务)需要重构, 短连接(http api) 准备用 php(开发速度快, 所以 xxx 是世界上最好的语言) 重写.

下面对 ci / lumen / hyper-api 3 个框架进行压测, 来选择这次重写需要使用的框架.

关于 **压测**, 参考 rango 的博客: [http://rango.swoole.com/archives/254][1]

## 压测结果

> qps: query per second, 查看服务器性能最直观的指标  
> 关于 qps 低: 服务器基础服务是最低配的 **阿里云 ecs + docker**

php\qps | ab/hello | ab/db | ab/redis  
-|-|-|-
hyper-api | 133.29 | 25.06 | 89.13  
ci | 115.47 | 23.66 | 45.03  
lumen | 67.52 | 18.15 | 36.39 

不得不说, [hyper-api][2] 的表现非常亮眼, 欢迎大家尝试

3 个测试项目的代码:

* hayper-api: [https://coding.net/u/daydaygo/p/hyper-api/git][2]
* ci: [https://coding.net/u/daydaygo/p/ci/git][3]
* lumen: [https://coding.net/u/daydaygo/p/lumen/git][4]

## 压测 case

* ab/hello: 3 个框架均返回 json 数据: {"code":"0000","msg":"success"}
* ab/db: 3 个框架均使用 model 并返回 10 条 users 表数据

```
    1   牟萍  dolorem_qui@example.net $2y$10$MLYoIDH0DHwzpnLlVkIV0u/NoWcio1cta2Q4xtdAXFf8HR1fvAMwe    hLSviARL7f  2017-08-20 18:14:22 2017-08-20 18:14:22
    2   丘智敏 vcum@example.com    $2y$10$MLYoIDH0DHwzpnLlVkIV0u/NoWcio1cta2Q4xtdAXFf8HR1fvAMwe    JTg0zvrG5S  2017-08-20 18:14:22 2017-08-20 18:14:22
    3   胥斌  in.nostrum@example.org  $2y$10$MLYoIDH0DHwzpnLlVkIV0u/NoWcio1cta2Q4xtdAXFf8HR1fvAMwe    FRekYNw2NA  2017-08-20 18:14:22 2017-08-20 18:14:22
    4   唐秀云 vnatus@example.com  $2y$10$MLYoIDH0DHwzpnLlVkIV0u/NoWcio1cta2Q4xtdAXFf8HR1fvAMwe    oOxO2oRneR  2017-08-20 18:14:22 2017-08-20 18:14:22
    5   罗海燕 tqui@example.net    $2y$10$MLYoIDH0DHwzpnLlVkIV0u/NoWcio1cta2Q4xtdAXFf8HR1fvAMwe    0FPqnEbWlt  2017-08-20 18:14:22 2017-08-20 18:14:22
    6   卜欢  aut.labore@example.net  $2y$10$MLYoIDH0DHwzpnLlVkIV0u/NoWcio1cta2Q4xtdAXFf8HR1fvAMwe    ZaqOYvfq7q  2017-08-20 18:14:22 2017-08-20 18:14:22
    7   白欣  optio_nesciunt@example.net  $2y$10$MLYoIDH0DHwzpnLlVkIV0u/NoWcio1cta2Q4xtdAXFf8HR1fvAMwe    aIU2QRm6kJ  2017-08-20 18:14:22 2017-08-20 18:14:22
    8   甘鹰  natus07@example.com $2y$10$MLYoIDH0DHwzpnLlVkIV0u/NoWcio1cta2Q4xtdAXFf8HR1fvAMwe    azP1OW5QKW  2017-08-20 18:14:22 2017-08-20 18:14:22
    9   欧东  excepturi.nulla@example.com $2y$10$MLYoIDH0DHwzpnLlVkIV0u/NoWcio1cta2Q4xtdAXFf8HR1fvAMwe    Yg8tJhNvDN  2017-08-20 18:14:22 2017-08-20 18:14:22
    10  原晨  ipsa.quis@example.net   $2y$10$MLYoIDH0DHwzpnLlVkIV0u/NoWcio1cta2Q4xtdAXFf8HR1fvAMwe    Xa91IMhTfu  2017-08-20 18:14:22 2017-08-20 18:14:22
```

* db/redis: 3 个框架均访问 redis 获取 1 条存储的 user 数据

```
    127.0.0.1:6379> get ab-test
    
    "{\"id\":1,\"name\":\"\\u725f\\u840d\",\"email\":\"dolorem_qui@example.net\",\"password\":\"$2y$10$MLYoIDH0DHwzpnLlVkIV0u\\/NoWcio1cta2Q4xtdAXFf8HR1fvAMwe\",\"remember_token\":\"hLSviARL7f\",\"created_at\":\"2017-08-20 18:14:22\",\"updated_at\":\"2017-08-20 18:14:22\"}"
```

## 压测数据

> 在压测 db 的时候, 一直报 > apr_pollset_poll: The timeout specified has expired (70007)> , 所以只发起了 100 的访问

* ci - ab/hello

```
    ab -c 100 -n 10000 http://ci.daydaygo.top/ab/hello
    
    Document Path:          /ab/hello
    Document Length:        31 bytes
    
    Concurrency Level:      100
    Time taken for tests:   86.604 seconds
    Complete requests:      10000
    Failed requests:        0
    Total transferred:      1860000 bytes
    HTML transferred:       310000 bytes
    Requests per second:    115.47 [#/sec] (mean)
    Time per request:       866.044 [ms] (mean)
    Time per request:       8.660 [ms] (mean, across all concurrent requests)
    Transfer rate:          20.97 [Kbytes/sec] received

```

* lumen ab/hello

```
    ab -c 100 -n 10000 http://lumen.daydaygo.top/ab/hello
    
    Document Path:          /ab/hello
    Document Length:        31 bytes
    
    Concurrency Level:      100
    Time taken for tests:   148.115 seconds
    Complete requests:      10000
    Failed requests:        0
    Total transferred:      2120000 bytes
    HTML transferred:       310000 bytes
    Requests per second:    67.52 [#/sec] (mean)
    Time per request:       1481.150 [ms] (mean)
    Time per request:       14.811 [ms] (mean, across all concurrent requests)
    Transfer rate:          13.98 [Kbytes/sec] received
```

* hyper-api ab/hello

```
    ab -c 100 -n 10000 http://hyper.daydaygo.top/ab/hello
    
    Document Path:          /ab/hello
    Document Length:        31 bytes
    
    Concurrency Level:      100
    Time taken for tests:   75.025 seconds
    Complete requests:      10000
    Failed requests:        0
    Total transferred:      2520000 bytes
    HTML transferred:       310000 bytes
    Requests per second:    133.29 [#/sec] (mean)
    Time per request:       750.254 [ms] (mean)
    Time per request:       7.503 [ms] (mean, across all concurrent requests)
    Transfer rate:          32.80 [Kbytes/sec] received
```

* ci ab/db

```
    ab -c 100 -n 400 -k http://ci.daydaygo.top/ab/db
    
    Document Path:          /ab/db
    Document Length:        2351 bytes
    
    Concurrency Level:      100
    Time taken for tests:   4.226 seconds
    Complete requests:      100
    Failed requests:        0
    Total transferred:      250600 bytes
    HTML transferred:       235100 bytes
    Requests per second:    23.66 [#/sec] (mean)
    Time per request:       4226.170 [ms] (mean)
    Time per request:       42.262 [ms] (mean, across all concurrent requests)
    Transfer rate:          57.91 [Kbytes/sec] received
```

* lumen ab/db

```
    ab -c 100 -n 100 -k http://lumen.daydaygo.top/ab/db
    
    Document Path:          /ab/db
    Document Length:        2400 bytes
    
    Concurrency Level:      100
    Time taken for tests:   5.510 seconds
    Complete requests:      100
    Failed requests:        0
    Total transferred:      258100 bytes
    HTML transferred:       240000 bytes
    Requests per second:    18.15 [#/sec] (mean)
    Time per request:       5509.861 [ms] (mean)
    Time per request:       55.099 [ms] (mean, across all concurrent requests)
    Transfer rate:          45.75 [Kbytes/sec] received
```

* hyper ab/db

```
    ab -c 100 -n 100 -k http://hyper.daydaygo.top/ab/db
    
    Document Path:          /ab/db
    Document Length:        2321 bytes
    
    Concurrency Level:      100
    Time taken for tests:   3.991 seconds
    Complete requests:      100
    Failed requests:        0
    Total transferred:      254200 bytes
    HTML transferred:       232100 bytes
    Requests per second:    25.06 [#/sec] (mean)
    Time per request:       3991.113 [ms] (mean)
    Time per request:       39.911 [ms] (mean, across all concurrent requests)
    Transfer rate:          62.20 [Kbytes/sec] received
```

* ci ab/redis

```
    ab -c 100 -n 1000 -k http://ci.daydaygo.top/ab/redis
    
    Document Path:          /ab/redis
    Document Length:        239 bytes
    
    Concurrency Level:      100
    Time taken for tests:   22.205 seconds
    Complete requests:      1000
    Failed requests:        0
    Keep-Alive requests:    0
    Total transferred:      394000 bytes
    HTML transferred:       239000 bytes
    Requests per second:    45.03 [#/sec] (mean)
    Time per request:       2220.522 [ms] (mean)
    Time per request:       22.205 [ms] (mean, across all concurrent requests)
    Transfer rate:          17.33 [Kbytes/sec] received
```

* hyper ab/redis

```
    ab -c 100 -n 1000 -k http://hyper.daydaygo.top/ab/redis
    
    Document Path:          /ab/redis
    Document Length:        239 bytes
    
    Concurrency Level:      100
    Time taken for tests:   11.220 seconds
    Complete requests:      1000
    Failed requests:        0
    Keep-Alive requests:    0
    Total transferred:      468000 bytes
    HTML transferred:       239000 bytes
    Requests per second:    89.13 [#/sec] (mean)
    Time per request:       1121.954 [ms] (mean)
    Time per request:       11.220 [ms] (mean, across all concurrent requests)
    Transfer rate:          40.74 [Kbytes/sec] received
```

* lumen ab/redis

```
    ab -c 100 -n 1000 -k http://lumen.daydaygo.top/ab/redis
    
    Document Path:          /ab/redis
    Document Length:        239 bytes
    
    Concurrency Level:      100
    Time taken for tests:   27.479 seconds
    Complete requests:      1000
    Failed requests:        0
    Keep-Alive requests:    0
    Total transferred:      420000 bytes
    HTML transferred:       239000 bytes
    Requests per second:    36.39 [#/sec] (mean)
    Time per request:       2747.931 [ms] (mean)
    Time per request:       27.479 [ms] (mean, across all concurrent requests)
    Transfer rate:          14.93 [Kbytes/sec] received
```

## 后记

因为 3 个框架都不算特别熟悉, 在写压测 case 时, 基本是照着文档来实现的, 所以, 尽管 **还有很大的优化的空间**, 但是这个时候我的选择倾向于 **hyper-api**.

关于性能, 也有几点认识:

* 性能优化是没有止境的
* 要多好的性能才够, 1 个亿够不够
* 什么时候该考虑性能, 至少要业务能有量才行

[0]: /u/281dc8d93b25
[1]: http://rango.swoole.com/archives/254
[2]: https://coding.net/u/daydaygo/p/hyper-api/git
[3]: https://coding.net/u/daydaygo/p/ci/git
[4]: https://coding.net/u/daydaygo/p/lumen/git
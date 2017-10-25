# 简易性能测试工具wrk

 Posted by zhida on October 22, 2016

## 介绍

wrk 是一个很简单的 http 性能测试工具. 也可以叫做 http benchmark 工具. 只有一个命令行, 就能做很多基本的 http 性能测试.

## 简单步骤

#### 安装

    git clone https://github.com/wg/wrk.git  
    cd wrk  
    make  
    

#### 使用

    wrk -t12 -c100 -d30s -T30 http://www.baidu.com 
    wrk -t2 -c100 -d30s http://localhost:8081/ticket/tyj/profitList/uegjVvnL-Watj-iFBA-X4lu-OHINAkqC7Cwy
    
    

* t: 线程数
* c: 请求连接数
* d: 测试时间，
* T: 默认超时时间是1秒. 这个有点短. 我一般设置为30秒

一般线程数不宜过多. 核数的2到4倍足够了. 多了反而因为线程切换过多造成效率降低. 因为 wrk 不是使用每个连接一个线程的模型, 而是通过异步网络 io 提升并发量. 所以网络通信不会阻塞线程执行. 这也是 wrk 可以用很少的线程模拟大量网路连接的原因. 而现在很多性能工具并没有采用这种方式, 而是采用提高线程数来实现高并发. 所以并发量一旦设的很高, 测试机自身压力就很大. 测试效果反而下降.

#### 结果说明

      Thread Stats   Avg      Stdev     Max   +/- Stdev
        Latency   354.59ms  377.09ms   1.99s    83.74%
        Req/Sec    21.07     12.79    90.00     80.37%
      6957 requests in 30.10s, 102.55MB read
      Socket errors: connect 0, read 19, write 0, timeout 117
    Requests/sec:    231.11
    Transfer/sec:      3.41MB
    

* Latency: 可以理解为响应时间, 有平均值, 标准偏差, 最大值, 正负一个标准差占比.
* Req/Sec: 每个线程每秒钟的完成的请求数, 同样有平均值, 标准偏差, 最大值, 正负一个标准差占比.
* 一般我们来说我们主要关注平均值和最大值. 标准差如果太大说明样本本身离散程度比较高. 有可能**系统性能波动很大**.

#### 进阶-脚本调用

##### post.lua

    wrk.method = "POST"  
    wrk.body   = "foo=bar&baz=quux"  
    wrk.headers["Content-Type"] = "application/x-www-form-urlencoded"  
    

##### 调用

    wrk -t12 -c100 -d30s -T30s --script=post.lua --latency http://www.baidu.com  
    

## 参考网站

[wrk – 小巧轻盈的 http 性能测试工具.][0]

[0]: http://zjumty.iteye.com/blog/2221040
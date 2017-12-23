# 压测工具wrk和Artillery的比较

 时间 2017-12-22 16:11:36  

原文[http://www.deanwangpro.com/2017/12/09/wrk-and-artillery/][1]


#### wrk 

wrk自身性能就非常惊人，使用epoll这种多路复用技术，所以可以用少量的线程来跟被测服务创建大量连接，进行压测，同时不占用过多的CPU和内存。

命令非常简单

    wrk -t8 -c200 -d30s --latency  "http://www.baidu.com"
    

这样就可以进行最简单的压测。但是真实使用起来肯定会有复杂的场景，比如先要登录取到token再进行下一步。好在wrk支持lua脚本，提供了几个阶段的hook来让用户自定义逻辑，具体可以看github上的官方提供的script sample。

我这里举一个获取token的例子

    -- @Author: wangding
    -- @Date: 2017-12-06 15:13:19
    -- @Last Modified by: wangding
    -- @Last Modified time: 2017-12-06 23:57:49
    local cjson = require "cjson"
    local cjson2 = cjson.new()
    local cjson_safe = require "cjson.safe"
    
    token = nil
    path  = "/api/auth/login"
    method = "POST"
    
    wrk.headers["Content-Type"] = "application/json"
    
    request = function()
       return wrk.format(method, path, nil, '{"username":"demo@demo.com","password":"demo"}')
    end
    
    response = function(status, headers, body)
       if not token and status == 200 then
          value = cjson.decode(body)
          token = value["token"]
          method = "GET"
          path  = "/api/contact?size=20&page=0"
          wrk.headers["Authorization"] = token
       end
    end
    

`request` 和 `response` 分别是两个hook，每次请求都会调用，那么这里request的逻辑就是一开始就使用 POST 请求 `/api/auth/login` 并且带有body，请求完成进入response，第一次token肯定是nil，所以把repose的token解析出来付给全局变量 `token` ，之后改写全局变量为 GET 请求地址 /api/contact 并且设置了header包含 Authorization 。 

这样实际是变通的实现了一个简单scenario的测试，那么问题来了，如果场景更复杂怎么办？写肯定是可以写的，但是并不直观，所以wrk不太适合一个包含有序场景的压力测试。

再来看一下wrk的report，这一点是我最喜欢的

    wrk -t8 -c200 -d30s -H "Authorization: token" --latency "http://10.0.20.2:8080/api/contact?size=20&page=0"
    Running 30s test @ http://10.0.20.2:8080/api/contact?size=20&page=0
      8 threads and 200 connections
      Thread Stats   Avg      Stdev     Max   +/- Stdev
        Latency   769.49ms  324.43ms   1.99s    72.08%
        Req/Sec    33.37     21.58   131.00     62.31%
      Latency Distribution
         50%  728.97ms
         75%  958.69ms
         90%    1.21s
         99%    1.74s
      7606 requests in 30.03s, 176.69MB read
      Socket errors: connect 0, read 0, write 0, timeout 38
    Requests/sec:    253.31
    Transfer/sec:      5.88MB
    

开启8线程，每个线程200个连接，持续30s的调用，可以看到报告中直接给出了最关键的指标QPS，这里的值是253.31。平均响应时间是33.37ms。简单直接，非常易懂。

但是这里面有个坑就是cjson这个lua module的使用，不可以使用lua5.2，必须使用lua5.1而且需要特定的wrk和cjson。我直接使用docker来封装这个运行环境，坏处是docker使用host模式本身性能可能就有影响。

#### Artillery 

一开始看到Artillery主要是因为它支持带场景的测试，也就是带有步骤，看一眼获取token再进行下一步的脚本。

    config:
    target: "http://10.0.20.2:8080"
    phases:
    - duration: 30
    arrivalRate: 100
    scenarios:
    - flow:
    - post:
    url: "/api/auth/login"
    json:
    username: "demo@demo.com"
    password: "demo"
    capture:
    json: "$.token"
    as: "token"
    - log: "Login token:{{ token }}"
    - get:
    url: "/api/contact?size=20&page=0"
    headers:
    Authorization: "{{ token }}"
    

`flow` 就是表示步骤， `duration` 表示持续30s，跟wrk不同的是没有thread的概念，Artillery是nodejs写的， `arrivalRate` 表示每秒模拟100个请求，所以两个参数乘起来就是3000个请求。看一下报告什么样： 

    All virtual users finished
    Summary report @ 12:45:41(+0800) 2017-12-08
      Scenarios launched:  3000
      Scenarios completed: 3000
      Requests completed:  3000
      RPS sent: 98.33
      Request latency:
        min: 15.7
        max: 179.1
        median: 19
        p95: 25.8
        p99: 37.5
      Scenario duration:
        min: 16.4
        max: 191.4
        median: 19.8
        p95: 27
        p99: 44.6
      Scenario counts:
        0: 3000 (100%)
      Codes:
        200: 3000
    

这里的 RPS sent 是指前10s平均发送请求数，所以这个和我们常说的QPS还是不一样的。如果想提高request的总数就要增加 `arrivalRate` ，比如上文wrk一共发了7606请求，那么这里 `arrivalRate` 提高到200一共可以在30s发6000次，但是改完就悲剧了， 

    Warning: High CPU usage warning.
    See https://artillery.io/docs/faq/#high-cpu-warnings for details.
    

Artillery一直在不断的告警，说明这个工具自身的局限性导致想要并发发送大量请求的时候，自己就很占CPU。

#### 小结 

wrk小巧而且性能非常好，报告直观。但是对于带多个步骤的压测场景无力。

Artillery太耗资源，而且报告不直观。 **不建议采用** 。 

除此之外唯一带场景的测试工具就是Jmeter了，但是Jmeter本身使用JVM是否可以短时间模拟大量并发，还是需要测试，建议与wrk做对比实验。

### 附录：简单的性能调优 

在用wrk测试GET请求的时候，发现无论如何提高连接数，QPS都是在250左右，此时CPU和内存都没有占满。怀疑是有其他瓶颈。最后发现Spring Boot内嵌的tomcat线程无法突破200，所以看了一下文档，发现默认最大线程数就是200，对 application.yml 进行了调整（同时调整了多个服务，包括gateway） 

    server:
    tomcat:
    max-threads: 1000
    max-connections: 2000
    

调整之后开启8线程，每个100个连接测试

    Running 30s test @ http://10.0.10.4:8769/api/contact?size=20&page=0
      8 threads and 100 connections
      Thread Stats   Avg      Stdev     Max   +/- Stdev
        Latency   235.56ms  267.57ms   1.98s    91.07%
        Req/Sec    72.12     30.19   190.00     68.17%
      Latency Distribution
         50%  166.46ms
         75%  281.10ms
         90%  472.03ms
         99%    1.45s
      15714 requests in 30.03s, 4.77MB read
    Requests/sec:    523.29
    Transfer/sec:    162.56KB
    

可以看到QPS达到了500以上直接翻倍了，再尝试提高连接数发现瓶颈就在内存了。

此外之前用公网做了一次压测，QPS只有10左右，看了一下阿里云的监控原来是出口带宽造成的，只有1MB的出口带宽，连接数调多大也没用。

未来还需要进行场景的细化，再决定是否使用不同的工具进行测试。


[1]: http://www.deanwangpro.com/2017/12/09/wrk-and-artillery/

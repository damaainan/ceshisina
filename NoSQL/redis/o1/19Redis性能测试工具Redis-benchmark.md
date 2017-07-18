# 【redis专题(19)】Redis性能测试工具Redis-benchmark



## 指令说明

    Usage: redis-benchmark [-h <host>] [-p <port>] [-c <clients>] [-n <requests]> [-k <boolean>]  
    
    -h <hostname>      Server hostname (default 127.0.0.1)  
    -p <port>          Server port (default 6379)  
    -s <socket>        Server socket (overrides host and port)  
    -c <clients>       Number of parallel connections (default 50)  
    -n <requests>      Total number of requests (default 10000)  
    -d <size>          Data size of SET/GET value in bytes (default 2)  
    -k <boolean>       1=keep alive 0=reconnect (default 1)  
    
    -r <keyspacelen>   Use random keys for SET/GET/INCR, random values for SADD  
    Using this option the benchmark will get/set keys  
    in the form mykey_rand:000000012456 instead of constant keys, the <keyspacelen> argument determines the max  
    number of values for the random number. For instance  
    if set to 10 only rand:000000000000 - rand:000000000009  
    range will be allowed.  
    
    -P <numreq>        Pipeline <numreq> requests. Default 1 (no pipeline).  
    -q                 Quiet. Just show query/sec values 只显示每秒钟能处理多少请求数结果  
    --csv              Output in CSV format  
    -l                 Loop. Run the tests forever 永久测试  
    -t <tests>         Only run the comma separated list of tests. The test names are the same as the ones produced as output.  
    -I                 Idle mode. Just open N idle connections and wait.  
    

## 示例

    redis-benchmark -h 127.0.0.1 -p 6379 -q -d 100  #SET/GET 100 bytes 检测host为127.0.0.1 端口为6379的redis服务器性能
    
    redis-benchmark -h 127.0.0.1 -p 6379 -c 5000 -n 100000 #5000个并发连接，100000个请求，检测host为127.0.0.1 端口为6379的redis服务器性能 
    

## 测试信息

    redis-benchmark -n 100000 -c 60 # 向redis服务器发送100000个请求，每个请求附带60个并发客户端
    

结果（部分）：

====== SET ======   
对集合写入测试

    100000 requests completed in 2.38 seconds # 100000个请求在2.38秒内完成
    60 parallel clients # 每次请求有60个并发客户端
    3 bytes payload # 每次写入3个字节的数据
    keep alive: 1 # 保持一个连接，一台服务器来处理这些请求
    
    93.06% <= 15 milliseconds
    99.96% <= 31 milliseconds
    99.98% <= 46 milliseconds
    99.99% <= 62 milliseconds
    100.00% <= 62 milliseconds # 所有请求在62毫秒内完成
    42105.26 requests per second #每秒处理42105.26次请求
    

其它测试

    [root@localhost ~]# redis-benchmark -h 127.0.0.1 -p 6379 -c 5000 -n 100000  -d 100 -q  
    PING_INLINE: 34506.55 requests per second  
    PING_BULK: 34059.95 requests per second  
    SET: 31959.09 requests per second  
    GET: 31466.33 requests per second  
    INCR: 33311.12 requests per second  
    LPUSH: 29265.44 requests per second  
    LPOP: 36968.58 requests per second  
    SADD: 32030.75 requests per second  
    SPOP: 33344.45 requests per second  
    LPUSH (needed to benchmark LRANGE): 29735.36 requests per second  
    LRANGE_100 (first 100 elements): 16116.04 requests per second  
    LRANGE_300 (first 300 elements): 6659.56 requests per second  
    LRANGE_500 (first 450 elements): 4108.29 requests per second  


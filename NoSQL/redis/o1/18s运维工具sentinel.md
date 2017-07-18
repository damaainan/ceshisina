# 【redis专题(18)】Redis运维工具sentinel


## 介绍

1. Sentinel不断与master通信,获取master的slave信息.
1. 监听master与slave的状态
1. 如果某slave失效,直接通知master去除该slave.
1. 如果master失效,,是按照slave优先级(可配置), 选取1个slave做 new master,把其他slave-->new master


疑问: sentinel与master通信,如果某次因为master IO操作频繁,导致超时,此时,认为master失效,很武断.   
解决: sentnel允许多个实例看守1个master, 当N台(N可设置)sentinel都认为master失效,才正式失效.

## Sentinel选项配置

    port 26379 # 端口
    
    sentinel monitor mymaster 127.0.0.1 6379 2 # 监视主服务器的ip和端口,当2个sentinel实例都认为master失效时,正式失效
    
    sentinel auth-pass mymaster 012_345^678-90 # master是否要密码  
    
    sentinel down-after-milliseconds mymaster 30000 # (mastername millseconds #默认为30秒) master被当前sentinel实例认定为"失效"的间隔时间,多少毫秒后连接不到master认为断开,注意：如果当前sentinel与master直接的通讯中，在指定时间内没有响应或者响应错误代码，那么当前sentinel就认为master失效(SDOWN，"主观"失效)  
    
    sentinel can-failover mymaster yes #当前sentinel实例是否允许实施"failover"(故障转移是否允许sentinel修改slave->master. 如为no,则只能监控,无权修改)  no表示当前sentinel为"观察者"(只参与"投票".不参与实施failover)，全局中至少有一个为yes  
    
    sentinel parallel-syncs mymaster 1 #一次性修改几个slave指向新的new master.
    
    sentinel client-reconfig-script mymaster /var/redis/reconfig.sh ,# 在重新配置new master,new slave过程,可以触发的脚本
    

## 启动

设置slave的优先级 slave-priority #从服务器的优先级,当主服挂了,会自动挑slave priority最小的为主服

    ./redis-server ./sentinel.conf --sentinel


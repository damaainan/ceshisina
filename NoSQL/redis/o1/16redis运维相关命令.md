# 【redis专题(16)】redis运维相关命令


## 常用运维命令

**显示服务器时间 time**

    redis 127.0.0.1:6380> time 
    1) "1375270361"  # 时间戳(秒)
    2) "504511"      # 微秒数
    

**查看当前数据库的key的数量 dbsize**

    redis 127.0.0.1:6380> dbsize 
    (integer) 2
    redis 127.0.0.1:6380> select 2
    OK
    

**后台进程重写aof bgrewriteaof**

    127.0.0.1:6379> bgrewriteaof
    Background append only file rewriting started
    

**保存rdb快照 bgsave(后台保存) save**

    127.0.0.1:6379> bgsave #内存不阻塞,当前进程dump
    Background saving started
    

**上次保存的时间** lastsave

**清空数据**

      flushdb #清空当前db
      flushall #清空全部db
    

**服务器关闭** Showdown [save/nosave]

**查看redis服务器的信息,性能调优** Info [Replication/CPU/Memory..] 

**配置项管理**

动态获取或设置config,config get/set 类似php中的ini_set/get  
Config get 配置项   
Config set 配置项 值 (特殊的选项,不允许用此命令设置,如slave-of, 需要用单独的slaveof命令来设置)

    127.0.0.1:6379> config get dbfilename
    1) "dbfilename"
    2) "dump6379.rdb"
    
    127.0.0.1:6379> config get slowlog-log-slower-than
    1) "slowlog-log-slower-than"
    2) "10000" #响应速度大于10000微妙的就会给记录下来;
    
    127.0.0.1:6379> config get slowlog-max-len
    1) "slowlog-max-len"
    2) "128"  #最多能记录128条慢查询记录;
    

**slowlog get N** 获取慢N条慢日志

## Info需要注意的参数

**内存Memory**

    used_memory:859192  #数据结构的空间
    used_memory_rss:7634944 #实占空间
    mem_fragmentation_ratio:8.89 #前2者的比例,1.N为佳 如果此值过大,说明redis的内存的碎片化严重,可以导出再导入一次.
    

**主从复制Replication**

    role:slave #当前服务器所占的角色slave还是master;
    master_host:192.168.1.128 #主服务器ip;
    master_port:6379
    master_link_status:up
    

**持久化Persistence**

    rdb_changes_since_last_save:0  #上次是什么时候改变的
    rdb_last_save_time:1375224063  #上次是什么时候保存的
    

**fork耗时**

    #Status
    latest_fork_usec:936  #上次导出rdb快照,持久化花费微秒
    注意: 如果某实例有10G内容,导出需要2分钟,
    每分钟写入10000次,导致不断的rdb导出,磁盘始处于高IO状态.


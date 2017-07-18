# 【redis专题(3)】命令语法介绍之list


通过链表结构可以模仿队列结构与堆栈结构；关于队列结构和堆栈结构可以查看[https://www.zybuluo.com/a5635268/note/290475][0]

## 增

    lpush key value1 value2 value3...
    

作用: 把值插入到链表头部

    rpush key value1 value2 value3...
    

    

    127.0.0.1:6379> rpush zimu a b c d e f
    (integer) 6

作用: 把值插入到链接尾部

## 删

    rpop key
    

作用: 返回并删除链表尾元素

    lpop key
    

作用: 返回并删除链表头元素

    lrem key count value
    

作用: 从key链表中删除 value值   
注: 删除count的绝对值个value后结束   
Count > 0 从表头删除   
Count < 0 从表尾删除

lrem key 2 b 从表头开始找b,找到就给删除,删除2个;   
lrem key -2 b 从表尾开始找b,找到就给删除,删除2个;

## 改

    ltrim key start stop
    

作用: 剪切key对应的链表,切[start,stop]一段,并把该段重新赋给key

    lindex key index
    

作用: 返回index索引上的值,   
如 lindex key 2

    llen key
    

作用:计算链接表的元素个数

    linsert key after|before search value
    

作用: 在key链表中寻找'search',并在search值之前|之后,插入value   
注: 一旦找到一个search后,命令就结束了,因此不会插入多个value

    

    127.0.0.1:6379> linsert lb1 before c aa #在链表lb1的元素c前面插入aa

    rpoplpush source dest
    

作用: 从链表source的尾部拿出,放在链表dest的头部,并返回该单元值

场景: 双链表完成安全队列

业务逻辑:   
1:Rpoplpush task bak   
2:接收返回值,并做业务处理   
3:如果成功,rpop bak 清除任务. 如不成功,下次从bak表里取任务

    brpop/blpop key timeout
    

作用:等待弹出key的尾/头元素,   
Timeout为等待超时时间   
如果timeout为0,则一直等待

场景: 长轮询Ajax,在线聊天时,能够用到

    

    127.0.0.1:6379> brpop lb2 30 #30秒内监听lb2队列,一旦有插入新的队列元素就马上弹出,并返回相应信息;
    1) "lb2"
    2) "222"
    (8.55s)

## 查

    lrange key start stop
    

作用: 返回链表中[start ,stop]中的元素   
规律: 左数从0开始,右数从-1开始   
lrange key 0 -1 查出全部链表结构

[0]: https://www.zybuluo.com/a5635268/note/290475
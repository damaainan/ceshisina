# 【redis专题(2)】命令语法介绍之string


REDIS有5大数据结构：string，list，sortedset，sets，hash。 这5个结构我将用5篇文章来记录各自是怎么用的，然后再用一篇文章来说一下各自的应用场景；

更多语法请参考：   
[http://doc.redisfans.com/][0]

## string

### **增**

    set key value [ex 秒数] / [px 毫秒数]  [nx] /[xx]  
    set a 1 ex 10 , 10秒有效
    Set a 1 px 9000  , 9秒有效
    

将key和value对应。如果key已经存在了，它会被覆盖，而不管它是什么类型。

注: 如果ex,px同时写,以后面的有效期为准   
如 `set a 1 ex 100 px 9000`, 实际有效期是9000毫秒(9秒)

nx: 表示key不存在时,执行操作   
xx: 表示key存在时,执行操作

    mset multi set
    mset key1 v1 key2 v2 ....
    

一次性设置多个键值

### **删**

删除一个key并返回key的数量

    del key
    

    

    # Example
    redis> SET key2 "World"
    OK
    redis> DEL key1 key2 key3
    (integer) 2
    redis>

### **改**

    rename oldkey newskey
    

将key重命名为newkey，如果key与newkey相同，将返回一个错误。如果newkey已经存在，则值将被覆盖。

    renamenx oldkey newskey
    

nx -> not exits 当且仅当 newkey 不存在时，将 key 改名为 newkey 。当 key 不存在时，返回一个错误。

    setrange key offset value
    

    

    redis 127.0.0.1:6379> set greet hello
    OK
    redis 127.0.0.1:6379> setrange greet 2 x
    (integer) 5
    redis 127.0.0.1:6379> get greet
    "hexlo"
    # 如果偏移量>字符长度, 该字符自动补0\x00
    redis 127.0.0.1:6379> setrange greet 6 !
    (integer) 7
    redis 127.0.0.1:6379> get greet
    "heyyo\x00!"

作用: 把字符串的offset偏移字节,改成value

    append key value
    

作用: 把value追加到key的原值上

    getset key newvalue
    

    

    redis 127.0.0.1:6379> set cnt 0
    OK
    redis 127.0.0.1:6379> getset cnt 1
    "0"
    redis 127.0.0.1:6379> getset cnt 2
    "1"

作用:设置新值并返回旧值

    incr key
    

作用: 指定的key的值加1,并返回加1后的值

1. 不存在的key当成0,再incr操作
1. 范围为64有符号


- - -

    incrby key number
    

    

    redis 127.0.0.1:6379> incrby age 90
    (integer) 92

作用: key每次递增number,但仅限于整数

    incrbyfloat key floatnumber
    

    

    redis 127.0.0.1:6379> incrbyfloat age 3.5
    "95.5"

作用: key每次递增floatnumber,但仅限于整数

    decr key
    

    

    redis 127.0.0.1:6379> set age 20
    OK
    redis 127.0.0.1:6379> decr age
    (integer) 19

    decrby key number
    

    

    redis 127.0.0.1:6379> decrby age 3
    (integer) 16

    setbit key offset value
    

    

    redis 127.0.0.1:6379> set char A
    OK
    redis 127.0.0.1:6379> setbit char 2 1
    #大写字母转换成小写字母;
    #大写字母和小写字母在ascii表中的区别:
    A:0100 0001 a:0110 0001
    B:0100 0010 b:0110 0010
    差异:在第二位0和1的差别;

作用: 设置offset对应二进制位上的值   
返回: 该位上的旧值

注意:   
1:如果offset过大,则会在中间填充0,   
2:offset最大大到多少   
3:offset最大2^32-1,可推出最大的的字符串为512M

    bitop operation destkey key1 [key2 ...]
    

对key1,key2..keyN进行operation位元操作,并将结果保存到 destkey 上。

operation 可以是 AND 、 OR 、 NOT 、 XOR的任意一种:

1. BITOP AND destkey key [key ...] ，对一个或多个 key 求逻辑并，并将结果保存到 destkey 。
1. BITOP OR destkey key [key ...] ，对一个或多个 key 求逻辑或，并将结果保存到 destkey 。
1. BITOP XOR destkey key [key ...] ，对一个或多个 key 求逻辑异或，并将结果保存到 destkey 。
1. BITOP NOT destkey key ，对给定 key 求逻辑非，并将结果保存到 destkey 。
    
```
    redis 127.0.0.1:6379> setbit lower 7 0 # 00000000 空字符
    (integer) 0
    redis 127.0.0.1:6379> setbit lower 2 1 #00100000 空格
    (integer) 0
    redis 127.0.0.1:6379> get lower
    " "
    redis 127.0.0.1:6379> set char Q
    OK
    redis 127.0.0.1:6379> get char  #01010001
    "Q"
    redis 127.0.0.1:6379> bitop or char char lower #求逻辑或 #01010001 #00100000 #01110001
    (integer) 1
    redis 127.0.0.1:6379> get char  #01110001
    "q"
    # 注意: 对于NOT操作, key不能多个
```
### **查**

    GET key
    

返回key的value。如果key不存在，返回特殊值nil。如果key的value不是string，就返回错误，因为GET只处理string类型的values。

    KEYS pattern
    

查找所有符合给定模式 pattern 的 key 。

* `KEYS *` 匹配数据库中所有 key 。
* `KEYS h?llo` 通配单个字符 如 hello ， hallo 和 hxllo 等。
* `KEYS h*llo` 通配任意多个字符(包括没有) 如hllo 和 heeeeello 等。
* `KEYS h[ae]llo` 通配括号内的某1个字符 如hello 和 hallo ，但不匹配 hillo 。


**特殊符号用 \ 隔开。**

    randomkey
    

从当前数据库返回一个随机的key。

    type key
    

返回 key 所储存的值的类型。

    exists key
    

返回key是否存在。如果存在返回1,不存在就返回0

    mget key1 key2 ..keyn
    

一次性获取多个建

    getrange key start stop
    

    

    redis 127.0.0.1:6379> set title 'chinese'
    OK
    redis 127.0.0.1:6379> getrange title 0 3
    "chin"
    redis 127.0.0.1:6379> getrange title 1 -2
    "hines"

作用: 是获取字符串中 [start, stop]范围的值   
注意: 对于字符串的下标,左数从0开始,右数从-1开始

1. start>=length, 则返回空字符串
1. stop>=length,则截取至字符结尾
1. 如果start 所处位置在stop右边, 返回空字符串


- - -

    getbit key offset
    

    

    redis 127.0.0.1:6379> set char A
    OK
    redis 127.0.0.1:6379> getbit char 1
    (integer) 1
    redis 127.0.0.1:6379> getbit char 2
    (integer) 0
    redis 127.0.0.1:6379> getbit char 7
    (integer) 1

作用: 获取值的二进制表示,对应位上的值(从左开始,从0编号)

关于二进制运算：

    AND: 与运算,逻辑乘  0x0=0    0x1=1x0=0  1x1=1
    
    OR:  或运算,逻辑加 0+0=0   0+1=1+0=1   1+1=1
    
    NOT: 逻辑非        非1=0  非0=1
    
    XOR: 0异或０＝０　0异或１=１ 1异或0＝1　1异或1=0    

[0]: http://doc.redisfans.com/
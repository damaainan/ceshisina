# Redis Lua 脚本使用

作者  [三产][0] 已关注 2017.06.16 17:15  字数 5895  阅读 178 评论 0 喜欢 0

### Lua 简介

Lua语言提供了如下几种数据类型：booleans（布尔）、numbers（数值）、strings（字符串）、tables（表格）。

下面是一些 Lua 的示例，里面注释部分会讲解相关的作用：

    --
    --
    -- 拿客 
    -- 网站：www.coderknock.com 
    -- QQ群：213732117
    -- 三产 创建于 2017年06月15日 12:04:54。
    -- 描述：
    --
    --
    local strings website = "coderknock.com"
    print(website)
    local tables testArray = { website, "sanchan", true, 2, 3.1415926 }
    
    -- 遍历 testArray
    print("========  testArray =======")
    for i = 1, #testArray
    do
        print(testArray[i])
    end
    -- 另一种遍历方式
    print("======== in  testArray =======")
    for index, value in ipairs(testArray)
    do
        -- 这种方式拼接 boolean 是会报错
        print("index ---->"..index)
        -- 这种组合大量数据时效率高
        print(value)
    end
    
    --while 循环
    print("======== while =======")
    local int sum = 0
    local int i = 0
    while i <= 100
    do
        sum = sum +i
        i = i + 1
    end
    --输出结果为5050
    print(sum)
    
    --if else
    print("======== if else =======")
    for i = 1, #testArray
    do
        if testArray[i] == "sanchan"
        then
            print("true")
            break
        else
            print(testArray[i])
        end
    end
    
    -- 哈希
    local tables user_1 = { age = 28, name = "tome" }
    --user_1 age is 28
    print("======== hash =======")
    print(user_1["name"].." age is " .. user_1["age"])
    print("======== in hash =======")
    for key, value in pairs(user_1)
    do
        print(key .. ":".. value)
    end
    
    print("======== function =======")
    function funcName(str)
        -- 代码逻辑
        print(str)
        return "new"..str
    end
    
    print(funcName("123"))

### Redis 中执行 Lua 脚本

Lua脚本功能为Redis开发和运维人员带来如下三个好处：

* Lua脚本在Redis中是原子执行的，执行过程中间不会插入其他命令。
* Lua脚本可以帮助开发和运维人员创造出自己定制的命令，并可以将这些命令常驻在Redis内存中，实现复用的效果。

* Lua脚本可以将多条命令一次性打包，有效地减少网络开销。

#### EVAL

>** 自2.6.0可用。**

>** 时间复杂度：**[> EVAL][1]>  和 EVALSHA 可以在 O(1) 复杂度内找到要被执行的脚本，其余的复杂度取决于执行的脚本本身。

##### 语法：EVAL script numkeys key [key ...] arg [arg ...]

##### 说明：

从 Redis 2.6.0 版本开始，通过内置的 Lua 解释器，可以使用 [EVAL][1] 命令对 Lua 脚本进行求值。

script 参数是一段 Lua 5.1 脚本程序，它会被运行在 Redis 服务器上下文中，这段脚本不必(也不应该)定义为一个 Lua 函数。

numkeys 参数用于指定键名参数的个数。

键名参数 key [key ...] 从 [EVAL][1] 的第三个参数开始算起，表示在脚本中所用到的那些 Redis 键(key)，这些键名参数可以在 Lua 中通过全局变量 KEYS 数组，用 1 为起始所有的形式访问( KEYS[1] ， KEYS[2] ，以此类推)。

在命令的最后是那些不是键名参数的附加参数 arg [arg ...] ，可以在 Lua 中通过全局变量 ARGV 数组访问，访问的形式和 KEYS 变量类似( ARGV[1] 、 ARGV[2] ，诸如此类)。

上面这几段长长的说明可以用一个简单的例子来概括：

    coderknock>EVAL 'return "return String KEYS1: "..KEYS[1].." KEYS2: ".." "..KEYS[2].." ARGV1: "..ARGV[1].." ARGV2: "..ARGV[2]' 3 KEYS1Str KEYS2Str KEYS3Str ARGV1Str ARGV2Str ARGV3Str ARGV4Str
    "return String KEYS1: KEYS1Str KEYS2:  KEYS2Str ARGV1: ARGV1Str ARGV2: ARGV2Str"

在 Lua 脚本中，可以使用两个不同函数来执行 Redis 命令，它们分别是：

* redis.call()
* redis.pcall()

这两个函数的唯一区别在于它们使用不同的方式处理执行命令所产生的错误，在后面的『错误处理』部分会讲到这一点。

redis.call() 和 redis.pcall() 两个函数的参数可以是任何格式良好(well formed)的 Redis 命令：

    # 最后的 0 代表的是没有 keys 是必须的 
    127.0.0.1:6379> EVAL "return redis.call('SET','testLuaSet','luaSetValue')" 0
    OK
    127.0.0.1:6379> GET testLuaSet
    "luaSetValue"
    127.0.0.1:6379> EVAL "return redis.call('GET','testLuaSet')" 0
    "luaSetValue"

上面的脚本虽然完成了功能，但是 key 部分应该由 Redis 传入而不是在 Lua 脚本中直接写入，我们改进一下：

    127.0.0.1:6379>  EVAL "return redis.call('SET',KEYS[1],ARGV[1])" 1 evalShell shellTest
    OK
    127.0.0.1:6379> GET evalShell
    "shellTest"

下面我们再次改进运行多 key 插入，这里使用 Python ：

    import redis
    
    r = redis.StrictRedis(host='127.0.0.1', password='admin123', port=6379, db=0)
    luaScript = """
    for i = 1, #KEYS
    do
        redis.call('SET', KEYS[i], ARGV[i])
    end
    return #KEYS
    """
    luaSet = r.register_script(luaScript)
    luaSet(keys=["pyLuaKey1", "pyLuaKey2", "pyLuaKey3"], args=["pyLuaKeyArg1", "pyLuaKeyArg2", "pyLuaKeyArg3"])
    # r.eval(luaScript,)
    # 下面会报错 因为 ARGV 会数组越界
    # luaSet(keys=["key1", "key2", "key3"], args=["arg1"])

我们在终端中验证一下是否插入成功：

    127.0.0.1:6379> GET pyLuaKey1
    "pyLuaKeyArg1"
    127.0.0.1:6379> GET pyLuaKey2
    "pyLuaKeyArg2"
    127.0.0.1:6379> GET pyLuaKey3
    "pyLuaKeyArg3"

要求使用正确的形式来传递键(key)是有原因的，因为不仅仅是 [EVAL][1] 这个命令，所有的 Redis 命令，在执行之前都会被分析，籍此来确定命令会对哪些键进行操作。

因此，对于 [EVAL][1] 命令来说，必须使用正确的形式来传递键，才能确保分析工作正确地执行。除此之外，使用正确的形式来传递键还有很多其他好处，它的一个特别重要的用途就是确保 Redis 集群可以将你的请求发送到正确的集群节点。(对 Redis 集群的工作还在进行当中，但是脚本功能被设计成可以与集群功能保持兼容。)不过，这条规矩并不是强制性的，从而使得用户有机会滥用(abuse) Redis 单实例配置(single instance configuration)，代价是这样写出的脚本不能被 Redis 集群所兼容。

##### 在 Lua 数据类型和 Redis 数据类型之间转换

当 Lua 通过 call() 或 pcall() 函数执行 Redis 命令的时候，命令的返回值会被转换成 Lua 数据结构。同样地，当 Lua 脚本在 Redis 内置的解释器里运行时，Lua 脚本的返回值也会被转换成 Redis 协议(protocol)，然后由 [EVAL][1] 将值返回给客户端。

数据类型之间的转换遵循这样一个设计原则：如果将一个 Redis 值转换成 Lua 值，之后再将转换所得的 Lua 值转换回 Redis 值，那么这个转换所得的 Redis 值应该和最初时的 Redis 值一样。

换句话说， Lua 类型和 Redis 类型之间存在着一一对应的转换关系。

以下列出的是详细的转换规则：

从 Redis 转换到 Lua ：

* Redis 整数转换成 Lua numbers
* Redis bulk 回复转换成 Lua strings
* Redis 多条 bulk 回复转换成 Lua tables，tables 内可能有其他别的 Redis 数据类型
* Redis 状态回复转换成 Lua tables， tables 内的 ok 域包含了状态信息
* Redis 错误回复转换成 Lua tables ，tables 内的 err 域包含了错误信息
* Redis 的 Nil 回复和 Nil 多条回复转换成 Lua 的 booleans false

从 Lua 转换到 Redis：

* Lua numbers 转换成 Redis 整数
* Lua strings 换成 Redis bulk 回复
* Lua tables (array) 转换成 Redis 多条 bulk 回复
* 一个带单个 ok 域的 Lua tables，转换成 Redis 状态回复
* 一个带单个 err 域的 Lua tables ，转换成 Redis 错误回复
* Lua 的 booleans false 转换成 Redis 的 Nil bulk 回复

从 Lua 转换到 Redis 有一条额外的规则，这条规则没有和它对应的从 Redis 转换到 Lua 的规则：

* Lua booleans true 转换成 Redis 整数回复中的 1

以下是几个类型转换的例子：

    # Lua strings  换成 Redis bulk 回复
    127.0.0.1:6379> EVAL "return redis.call('GET','evalShell')" 0
    "shellTest"
    # 错误的情况
    127.0.0.1:6379>  EVAL "return redis.call('SADD','evalShell','a')" 0
    
    (error) ERR Error running script (call to f_e17faafbc130014cebb229b71e0148b1f8f52389): @user_script:1: WRONGTYPE Operation against a key holding the wrong kind of value
    # redis 中与 lua 各种类型转换
    127.0.0.1:6379> EVAL "return {1,3.1415,'luaStrings',true,false}" 0
    1) (integer) 1
    2) (integer) 3
    3) "luaStrings"
    4) (integer) 1
    5) (nil)

##### 脚本的原子性

Redis 使用单个 Lua 解释器去运行所有脚本，并且， Redis 也保证脚本会以原子性(atomic)的方式执行：**当某个脚本正在运行的时候，不会有其他脚本或 Redis 命令被执行**。这和使用 [MULTI][2] / [EXEC][3] 包围的事务很类似。在其他别的客户端看来，脚本的效果(effect)要么是不可见的(not visible)，要么就是已完成的(already completed)。

另一方面，这也意味着，执行一个运行缓慢的脚本并不是一个好主意。写一个跑得很快很顺溜的脚本并不难，因为脚本的运行开销(overhead)非常少，但是当你不得不使用一些跑得比较慢的脚本时，请小心，因为当这些蜗牛脚本在慢吞吞地运行的时候，其他客户端会因为服务器正忙而无法执行命令。

##### 错误处理

前面的命令介绍部分说过， redis.call() 和 redis.pcall() 的唯一区别在于它们对错误处理的不同。

当 redis.call() 在执行命令的过程中发生错误时，脚本会停止执行，并返回一个脚本错误，错误的输出信息会说明错误造成的原因：

    127.0.0.1:6379>  EVAL "return redis.call('SADD','evalShell','a')" 0
    
    (error) ERR Error running script (call to f_e17faafbc130014cebb229b71e0148b1f8f52389): @user_script:1: WRONGTYPE Operation against a key holding the wrong kind of value

和 redis.call() 不同， redis.pcall() 出错时并不引发(raise)错误，而是返回一个带 err 域的 Lua 表(table)，用于表示错误（这样与命令行客户端直接操作返回相同）：

    127.0.0.1:6379>  EVAL "return redis.pcall('SADD','evalShell','a')" 0
    (error) WRONGTYPE Operation against a key holding the wrong kind of value

##### Helper 函数返回Redis类型

从Lua返回Redis类型有两个 Helper 函数。

* redis.error_reply(error_string)返回错误回复。此函数只返回一个字段表，其中err字段设置为指定的字符串。
* redis.status_reply(status_string)返回状态回复。此函数只返回一个字段表，其中ok字段设置为指定的字符串。

使用 Helper 函数或直接以指定的格式返回表之间没有区别，因此以下两种形式是等效的：

    return {err="My Error"}
    return redis.error_reply("My Error")

### 脚本缓存

Redis 保证所有被运行过的脚本都会被永久保存在脚本缓存当中，这意味着，当 [EVAL][1] 命令在一个 Redis 实例上成功执行某个脚本之后，随后针对这个脚本的所有 EVALSHA 命令都会成功执行。

刷新脚本缓存的唯一办法是显式地调用 SCRIPT FLUSH 命令，这个命令会清空运行过的所有脚本的缓存。通常只有在云计算环境中，Redis 实例被改作其他客户或者别的应用程序的实例时，才会执行这个命令。

缓存可以长时间储存而不产生内存问题的原因是，它们的体积非常小，而且数量也非常少，即使脚本在概念上类似于实现一个新命令，即使在一个大规模的程序里有成百上千的脚本，即使这些脚本会经常修改，即便如此，储存这些脚本的内存仍然是微不足道的。

事实上，用户会发现 Redis 不移除缓存中的脚本实际上是一个好主意。比如说，对于一个和 Redis 保持持久化链接(persistent connection)的程序来说，它可以确信，执行过一次的脚本会一直保留在内存当中，因此它可以在 pipline 中使用 EVALSHA 命令而不必担心因为找不到所需的脚本而产生错误(稍候我们会看到在 pipline 中执行脚本的相关问题)。

#### SCRIPT 命令

Redis 提供了以下几个 SCRIPT 命令，用于对脚本子系统(scripting subsystem)进行控制：

#### SCRIPT LOAD

>** 自2.6.0可用。**

>** 时间复杂度：** O(N) ,  N  为脚本的长度(以字节为单位)。

##### 语法：**SCRIPT LOAD script**

##### 说明：

清除所有 Lua 脚本缓存。

##### 返回值：

给定 script 的 SHA1 校验和

#### SCRIPT DEBUG

>** 自3.2.0可用。**

>** 时间复杂度：O(1)。**

##### 语法：SCRIPT DEBUG YES|SYNC|NO

##### 说明：

Redis包括一个完整的 Lua 调试器，代号 LDB，可用于使编写复杂脚本的任务更简单。在调试模式下，Redis 充当远程调试服务器，客户端 redis-cli 可以逐步执行脚本，设置断点，检查变量等 。

**应避免施工生产机器进行调试！**

LDB可以以两种模式之一启用：异步或同步。在异步模式下，服务器创建一个不阻塞的分支调试会话，并且在会话完成后，数据的所有更改都将**回滚**，因此可以使用相同的初始状态重新启动调试。同步调试模式在调试会话处于活动状态时阻塞服务器，并且数据集在结束后会保留所有更改。

* YES。启用Lua脚本的非阻塞异步调试（更改将被丢弃）。
* [SYNC][4]。启用阻止Lua脚本的同步调试（保存对数据的更改）。
* NO。禁用脚本调试模式。

##### 返回值：

总是返回 OK

##### 示例：

该功能是新出功能，使用频率不是很高，在之后我会单独录个视频来进行演示（请关注我的博客 www.coderknock.com，或关注本文后续更新）。

#### SCRIPT FLUSH

>** 自2.6.0可用。**

>** 时间复杂度：O(N) ，  N  为缓存中脚本的数量。**

##### 语法：**SCRIPT FLUSH**

##### 说明：

清除所有 Lua 脚本缓存。

##### 返回值：

总是返回 OK

#### **SCRIPT EXISTS**

>** 自2.6.0可用。**

>** 时间复杂度：** O(N) ,  N  为给定的 SHA1 校验和的数量。

##### 语法：**SCRIPT EXISTS sha1 [sha1 ...]**

##### 说明：

给定一个或多个脚本的 SHA1 校验和，返回一个包含 0 和 1 的列表，表示校验和所指定的脚本是否已经被保存在缓存当中。

##### 返回值：

一个列表，包含 0 和 1 ，前者表示脚本不存在于缓存，后者表示脚本已经在缓存里面了。

列表中的元素和给定的 SHA1 校验和保持对应关系，比如列表的第三个元素的值就表示第三个 SHA1 校验和所指定的脚本在缓存中的状态。

#### **SCRIPT KILL**

>** 自2.6.0可用。**

>** 时间复杂度：** O(1)。

##### 语法：**SCRIPT KILL**

##### 说明：

杀死当前正在运行的 Lua 脚本，当且仅当这个脚本没有执行过任何写操作时，这个命令才生效。

这个命令主要用于终止运行时间过长的脚本，比如一个因为 BUG 而发生无限 loop 的脚本，诸如此类。

[SCRIPT KILL][5] 执行之后，当前正在运行的脚本会被杀死，执行这个脚本的客户端会从 [EVAL][1] 命令的阻塞当中退出，并收到一个错误作为返回值。

另一方面，假如当前正在运行的脚本已经执行过写操作，那么即使执行 [SCRIPT KILL][5] ，也无法将它杀死，因为这是违反 Lua 脚本的原子性执行原则的。在这种情况下，唯一可行的办法是使用 SHUTDOWN NOSAVE 命令，通过停止整个 Redis 进程来停止脚本的运行，并防止不完整(half-written)的信息被写入数据库中。

##### 返回值：

执行成功返回 OK ，否则返回一个错误。

#### SCRIPT 相关示例：

    # 加载一个脚本到缓存
    127.0.0.1:6379> SCRIPT LOAD "return redis.call('SET',KEYS[1],ARGV[1])"
    "cf63a54c34e159e75e5a3fe4794bb2ea636ee005"
    # EVALSHA 在后面会讲解，这里就是调用一个脚本缓冲
    127.0.0.1:6379> EVALSHA cf63a54c34e159e75e5a3fe4794bb2ea636ee005 1 ttestScript evalSHATest
    OK
    127.0.0.1:6379> GET ttestScript
    "evalSHATest"
    127.0.0.1:6379> SCRIPT EXISTS cf63a54c34e159e75e5a3fe4794bb2ea636ee005
    1) (integer) 1
    # 这里有三个 SHA 第一第三是随便输入的，检测是否存在脚本缓存
    127.0.0.1:6379> SCRIPT EXISTS  nonsha cf63a54c34e159e75e5a3fe4794bb2ea636ee005 abc
    1) (integer) 0
    2) (integer) 1
    3) (integer) 0
    # 清空脚本缓存
    127.0.0.1:6379> SCRIPT FLUSH
    OK
    # 清除脚本缓存后再次执行就找不到该脚本了
    127.0.0.1:6379>  SCRIPT EXISTS cf63a54c34e159e75e5a3fe4794bb2ea636ee005
    1) (integer) 0
    
    # 没有脚本在执行时
    127.0.0.1:6379> SCRIPT KILL
    (error) ERR No scripts in execution right now.

我们创建一个 lua 脚本，该脚本存在 E:/LuaProject/src/Sleep.lua ：

    --
    --
    -- 拿客 
    -- 网站：www.coderknock.com 
    -- QQ群：213732117
    -- 三产 创建于 2017年06月16日 15:47:30。
    -- 描述：
    --
    --
    
    for i = 1, 1000000
    do
        print(i)--就是循环打印这样可以模拟长时间的脚本执行
    end
    return "ok"

使用 redis-cli --eval 调用：

    C:\Users\zylia>redis-cli -a admin123 --eval E:/LuaProject/src/Sleep.lua

此时服务端开始输出，当前客户端被阻塞：

    1
    2
    3
    ...
    23456

我们再启动一个客户端：

    C:\Users\zylia>redis-cli -a admin123
    # 杀掉还在执行的那个脚本
    127.0.0.1:6379> SCRIPT KILL
    OK
    (0.84s)

此时刚才我们执行脚本的客户端(就是被阻塞的那个)会抛出异常：

    (error) ERR Error running script (call to f_d5ee0fe7467b0e19fe3fb0a0388d522bf26d95d8): @user_script:13: Script killed by user with SCRIPT KILL...

服务端也会停止打印：

    524991
    524992
    524993
    524994
    524995
    524996
    524997
    524998
    [1904] 16 Jun 15:55:24.178 # Lua script killed by user with SCRIPT KILL.

### 带宽和 EVALSHA

[EVAL][1] 命令要求你在每次执行脚本的时候都发送一次脚本主体(script body)。Redis 有一个内部的缓存机制，因此它不会每次都重新编译脚本，不过在很多场合，付出无谓的带宽来传送脚本主体并不是最佳选择。

为了减少带宽的消耗， Redis 实现了 EVALSHA 命令，它的作用和 [EVAL][1] 一样，都用于对脚本求值，但它接受的第一个参数不是脚本，而是脚本的 SHA1 校验和(sum)。

EVALSHA 命令的表现如下：

* 如果服务器还记得给定的 SHA1 校验和所指定的脚本，那么执行这个脚本
* 如果服务器不记得给定的 SHA1 校验和所指定的脚本，那么它返回一个特殊的错误，提醒用户使用 [EVAL][1] 代替 EVALSHA

我将之前的脚本存储到缓存中使用 EVALSHA 调用：

    127.0.0.1:6379> SCRIPT LOAD "return redis.call('GET','evalShell')"
    "c870035beb27b1c404c19624c50b5e451ecf1623"
    127.0.0.1:6379> EVALSHA c870035beb27b1c404c19624c50b5e451ecf1623 0
    "shellTest"
    127.0.0.1:6379> evalsha 6b1bf486c81ceb7edf3c093f4c48582e38c0e791 0
    (nil)
    127.0.0.1:6379> evalsha ffffffffffffffffffffffffffffffffffffffff 0
    (error) NOSCRIPT No matching script. Please use EVAL.
    # 在 EVAL 错误的情况中 call to f_e17faafbc130014cebb229b71e0148b1f8f52389
    # e17faafbc130014cebb229b71e0148b1f8f52389 就是该命令的 SHA1 值
    127.0.0.1:6379>  EVAL "return redis.call('SADD','evalShell','a')" 0
    
    (error) ERR Error running script (call to f_e17faafbc130014cebb229b71e0148b1f8f52389): @user_script:1: WRONGTYPE Operation against a key holding the wrong kind of value

### 纯函数脚本

在编写脚本方面，一个重要的要求就是，脚本应该被写成纯函数(pure function)。

也就是说，脚本应该具有以下属性：

对于同样的数据集输入，给定相同的参数，脚本执行的 Redis 写命令总是相同的。脚本执行的操作不能依赖于任何隐藏(非显式)数据，不能依赖于脚本在执行过程中、或脚本在不同执行时期之间可能变更的状态，并且它也不能依赖于任何来自 I/O 设备的外部输入。  
使用系统时间(system time)，调用像 RANDOMKEY 那样的随机命令，或者使用 Lua 的随机数生成器，类似以上的这些操作，都会造成脚本的求值无法每次都得出同样的结果。

为了确保脚本符合上面所说的属性， Redis 做了以下工作：

Lua 没有访问系统时间或者其他内部状态的命令

* Redis 会返回一个错误，阻止这样的脚本运行： 这些脚本在执行随机命令之后(比如 RANDOMKEY 、 SRANDMEMBER 或 TIME 等)，还会执行可以修改数据集的 Redis 命令。如果脚本只是执行只读操作，那么就没有这一限制。注意，随机命令并不一定就指那些带 RAND 字眼的命令，任何带有非确定性的命令都会被认为是随机命令，比如 TIME 命令就是这方面的一个很好的例子。

* 每当从 Lua 脚本中调用那些返回无序元素的命令时，执行命令所得的数据在返回给 Lua 之前会先执行一个静默(slient)的字典序排序(lexicographical sorting)。举个例子，因为 Redis 的 Set 保存的是无序的元素，所以在 Redis 命令行客户端中直接执行 SMEMBERS ，返回的元素是无序的，但是，假如在脚本中执行 redis.call("smembers", KEYS[1]) ，那么返回的总是排过序的元素。

* 对 Lua 的伪随机数生成函数 math.random 和 math.randomseed 进行修改，使得每次在运行新脚本的时候，总是拥有同样的 seed 值。这意味着，每次运行脚本时，只要不使用 math.randomseed，那么 math.random 产生的随机数序列总是相同的。

当 Redis 执行 Lua 脚本时会对脚本进行检查，要执行的 lua 脚本：

    function fun()
      -- 业务逻辑
    end

执行是报错，因为 Redis 不允许脚本中存在 function：

    C:\Users\zylia>redis-cli -a admin123 --eval E:/LuaProject/src/Sleep.lua
    (error) ERR Error running script (call to f_36ebb6a8391764938e347056b2de7a33626c029b): @enable_strict_lua:8: user_script:11: Script attempted to create global variable 'fun'

要执行的 lua 脚本：

    for i = 1, 100
    do
        os.execute("ping -n " .. tonumber(2) .. " localhost > NUL")
        print(i)
    end
    return "ok"

执行是报错，因为 Redis 不允许脚本使用 os 等一部分全局变量：

    C:\Users\zylia>redis-cli -a admin123 --eval E:/LuaProject/src/Sleep.lua
    (error) ERR Error running script (call to f_bb4268eafae9d9bcd8a2571f067abf5ab46be3d0): @enable_strict_lua:15: user_script:13: Script attempted to access unexisting global variable 'os'

### 全局变量保护

为了防止不必要的数据泄漏进 Lua 环境， Redis 脚本不允许创建全局变量。如果一个脚本需要在多次执行之间维持某种状态，它应该使用 Redis key 来进行状态保存。

企图在脚本中访问一个全局变量(不论这个变量是否存在)将引起脚本停止， [EVAL][1] 命令会返回一个错误：

    127.0.0.1:6379> EVAL "website='coderknock.com'" 0
    (error) ERR Error running script (call to f_ad03e14e835e9880720cd43db8062256c089cd79): @enable_strict_lua:8: user_script:1: Script attempted to create global variable 'website'

Lua 的 debug 工具，或者其他设施，比如打印（alter）用于实现全局保护的 meta table ，都可以用于实现全局变量保护。

实现全局变量保护并不难，不过有时候还是会不小心而为之。一旦用户在脚本中混入了 Lua 全局状态，那么 AOF 持久化和复制（replication）都会无法保证，所以，请不要使用全局变量。

避免引入全局变量的一个诀窍是：将脚本中用到的所有变量都使用 local 关键字定义为局部变量。

### 库

Redis 内置的 Lua 解释器加载了以下 Lua 库：

* base
* table
* string
* math
* debug
* cjson
* cmsgpack

其中 cjson 库可以让 Lua 以非常快的速度处理 JSON 数据，除此之外，其他别的都是 Lua 的标准库。

每个 Redis 实例都保证会加载上面列举的库，从而确保每个 Redis 脚本的运行环境都是相同的。

下面我们演示一下 cjson 的使用，Lua 脚本如下

    --
    --
    -- 拿客 
    -- 网站：www.coderknock.com 
    -- QQ群：213732117
    -- 三产 创建于 2017年06月16日 15:47:30。
    -- 描述：
    --
    --
    
    local json = cjson
    local str = '["testWebsit", "testQQ", "sanchan"]'   -- json格式的字符串
    local j = json.decode(str)      -- 解码为表
    for i = 1, #j
    do
        print(i.." --> "..j[i])
    end
    str = '{"WebSite": "coderknock.com", "QQGroup": 213732117}'
    j = json.decode(str)
    
    j['Auth'] = 'sachan'
    local new_str = json.encode(j)
    return new_str

执行过程如下，上面的命令窗口是我们的客户端，下面是 Redis：

![cjson%20%u6267%u884C%u7ED3%u679C][6]



cjson 执行结果

可以看到，客户端输出了一个序列号 json ，服务端打印出来我们解码的 json。

### 使用脚本散发 Redis 日志

在 Lua 脚本中，可以通过调用 redis.log 函数来写 Redis 日志(log)：

redis.log(loglevel, message)

其中， message 参数是一个字符串，而 loglevel 参数可以是以下任意一个值：

redis.LOG_DEBUG  
redis.LOG_VERBOSE  
redis.LOG_NOTICE  
redis.LOG_WARNING  
上面的这些等级(level)和标准 Redis 日志的等级相对应。

对于脚本散发(emit)的日志，只有那些和当前 Redis 实例所设置的日志等级相同或更高级的日志才会被散发。

以下是一个日志示例：

redis.log(redis.LOG_WARNING, "Something is wrong with this script.")执行上面的函数会在服务器端产生这样的信息：

[32343] 22 Mar 15:21:39 # Something is wrong with this script.### 沙箱(sandbox)和最大执行时间

脚本应该仅仅用于传递参数和对 Redis 数据进行处理，它不应该尝试去访问外部系统(比如文件系统)，或者执行任何系统调用。

除此之外，脚本还有一个最大执行时间限制，它的默认值是 5 秒钟，一般正常运作的脚本通常可以在几分之几毫秒之内完成，花不了那么多时间，这个限制主要是为了防止因编程错误而造成的无限循环而设置的。

最大执行时间的长短由 lua-time-limit 选项来控制(以毫秒为单位)，可以通过编辑 redis.conf 文件或者使用 CONFIG GET 和 CONFIG SET 命令来修改它。

当一个脚本达到最大执行时间的时候，它并不会自动被 Redis 结束，因为 Redis 必须保证脚本执行的原子性，而中途停止脚本的运行意味着可能会留下未处理完的数据在数据集(data set)里面。

因此，当脚本运行的时间超过最大执行时间后，以下动作会被执行：

* Redis 记录一个脚本正在超时运行
* Redis 开始重新接受其他客户端的命令请求，但是只有 SCRIPT KILL 和 SHUTDOWN NOSAVE 两个命令会被处理，对于其他命令请求， Redis 服务器只是简单地返回 BUSY 错误。
* 可以使用 SCRIPT KILL 命令将一个仅执行只读命令的脚本杀死，因为只读命令并不修改数据，因此杀死这个脚本并不破坏数据的完整性
* 如果脚本已经执行过写命令，那么唯一允许执行的操作就是 SHUTDOWN NOSAVE ，它通过停止服务器来阻止当前数据集写入磁盘

### pipeline上下文(context)中的 EVALSHA

在 pipeline 请求的上下文中使用 EVALSHA 命令时，要特别小心，因为在 pipeline 中，必须保证命令的执行顺序。

一旦在 pipeline 中因为 EVALSHA 命令而发生 NOSCRIPT 错误，那么这个 pipeline 就再也没有办法重新执行了，否则的话，命令的执行顺序就会被打乱。

为了防止出现以上所说的问题，客户端库实现应该实施以下的其中一项措施：

* 总是在 pipeline 中使用 EVAL 命令
* 检查 pipeline 中要用到的所有命令，找到其中的 EVAL 命令，并使用 SCRIPT EXISTS 命令检查要用到的脚本是不是全都已经保存在缓存里面了。如果所需的全部脚本都可以在缓存里找到，那么就可以放心地将所有 EVAL 命令改成 EVALSHA 命令，否则的话，就要在pipeline 的顶端(top)将缺少的脚本用 SCRIPT LOAD 命令加上去。

[0]: http://www.jianshu.com/u/2de721a368d3
[1]: http://redisdoc.com/script/eval.html#eval
[2]: http://redisdoc.com/transaction/multi.html#multi
[3]: http://redisdoc.com/transaction/exec.html#exec
[4]: https://redis.io/commands/sync
[5]: http://redisdoc.com/script/script_kill.html#id1
[6]: https://upload-images.jianshu.io/upload_images/1284956-0afc6f92c2836bad.png
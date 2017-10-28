# MySQL运维之神奇的参数(终结篇)

 时间 2017-06-30 22:26:48  

原文[http://keithlan.github.io/2017/06/30/sql_safe_updates_practise/][1]


## 一、主要内容

* 1. 生产前的测试方案
* 1. 生产环境如何平滑实施
* 1. 生产坏境中遇到哪些困难
* 1. 我们的解决方案
* 1. 价值与意义

## 二、背景

这个项目的起源，来源于生产环境中的N次误删数据，所以才有他的姊妹篇文章，一个神奇的参数前传 

## 三、生产前的测试方案

### 3.1 why

* 为什么要做测试方案

    1. 大家都知道设置sql_safe_update=1 会拒绝掉很多你想不到的SQL，这样会导致业务出问题，服务中断，影响非常严重  
    2. 我们需要测试出哪些SQL语句会被拒绝？  
    3. 我们需要知道已经上线的SQL语句中，哪些会被拒绝？  
    
    总之，我们需要无缝升级，怎么样才能既加强安全防范，又不影响业务呢？  
    这就是我们的SQL防火墙系统的升级改造之路
    

### 3.2 如何测试

非常感谢DBA团队袁俊敏同学的细心测试

    1. 根据官方文档的提示，以及之前碰壁的经验，我们详细的设计了各种SQL方案
        a. 单键索引
            a.1 update语句
            a.2 delete语句
            a.3 replace into系列
            a.4 有limit
            a.5 无limit
            a.6 有where条件
            a.7 无where条件
            a.8 隐式类型字符转换
            a.9 SQL带有函数方法
        b. 组合索引
            b.1 update语句
            b.2 delete语句
            b.3 replace into系列
            b.4 有limit
            b.5 无limit  
            b.6 有where条件
            b.7 无where条件
            b.8 隐式类型字符转换
            b.9 SQL带有函数方法
        等等
    

### 3.3 哪些语句会触发sql_safe_update报错

    1. 有where 条件且没有使用索引,且没有limit语句  --触发
    2. 没有where 条件 ， 有limit，delete语句--触发
    3. 没有where 条件 ， 没有limit， delete+update语句  --触发
    
    总结： 没有使用索引的DML语句，都会被触发
    

## 四、生产环境如何平滑实施

log_queries_not_using_indexes=on

long_query_time = 10000

log_queries_not_using_indexes 无长连接的概念,立即对所有链接生效

    通过log_queries_not_using_indexes=on + long_query_time = 10000 可以抓出所有我们需要的dml，解决掉这些sql，我们的目的就达到了
    

## 五、生产坏境中遇到哪些困难

这边说一个典型的坑

* 你们真的以为设置了log_queries_not_using_indexes就一定能够抓出我们需要的DML吗？

    1. 首先：log_queries_not_using_indexes=on，的确是可以抓出所有没有使用索引的DML  
    2. 但是：再设置log_queries_not_using_indexes之前，这个connection已经存在了，那么log_queries_not_using_indexes对早已经存在的connection是不起作用的
    

* 故障一

    1. master 由于对于长连接不生效，所以全表更新dml在master执行了，但是在slave却不能执行，导致主从同步失败
    

* 故障二

    1. master 由于对于长连接不生效，所以全表更新dml在master执行了，那么意味着，你以为可以保障MySQL安全，其实只是自欺欺人而已
    

## 六、我们的解决方案

解决长连接问题

* 删掉所有链接

    1. 有人说，那简单，我们直接全部删掉所有链接就好了。  
     的确，全部删除，的确可以做到，但是是不是有点粗暴呢？
    
    2. 那就让业务方将所有长连接应用重启
     这。。。业务方会很崩溃，也不可能停掉所有的长连接服务
    

* 只kill具有dml权限的长连接

    * 如何找到长连接
    
    1. 长连接的特点：长
    2. 那么MySQL里面的show processlist有两个非常重要的属性
        Id: session id
        Time: command status time
    3. 误区
        这里有大部分人会根据Time来识别这个链接是不是长连接，那么他一定不理解time的含义  
        它并不是链接的时间长短，而是command某个状态的时间而已  
    
    4. 大家已经猜到，最终的方案就是通过session id来判断识别长连接
    
        4.0 先在master上设置sql_safe_update=on
        4.1 然后假设10:00 show processlist，记录下所有的id
        4.2 那么明天10:00 show processlist，与上一次的id进行匹配，如果匹配中了，那么说明这个connection已经存在一天，那么可以认为他是长连接了  
        4.3 判断这些id对应的用户权限，只读账号忽略  
        4.4 kill掉这些长连接即可（注意：repl，system user 这些系统进程不要被误删掉了，否则哭都来不及）
        4.5 可以根据host:port告知业务方，一起配合重启和kill之后的观察
    

## 价值和意义

目前我们已经完成了N组DB集群的设置

这里有很多人有疑问：

1. 花这么大的代价，只是为了设置这样的一个参数，值得吗？
1. 万一搞不好，弄出问题来，岂不是没事找事，给自己找罪受？
1. 这样操作，开发会支持你吗？你们老大支持你吗？

我是这么理解的：

1. 刚开始的时候，的确难度非常大，后来我们经过无数次测试和技术方案演练，还是决定冒着枪林弹雨，只为以后的数据安全
1. 一切以用户为中心，我们必须用我们专业的判断对用户负责

* final：我将这件事看做： ‘功在当代，利在千秋’ 的一件事


[1]: http://keithlan.github.io/2017/06/30/sql_safe_updates_practise/

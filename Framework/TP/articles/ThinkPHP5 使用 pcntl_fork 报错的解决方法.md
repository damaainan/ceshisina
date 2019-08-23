## ThinkPHP5 使用 pcntl_fork 报错的解决方法

来源：[https://blog.csdn.net/chenzhuyu/article/details/80197525](https://blog.csdn.net/chenzhuyu/article/details/80197525)

时间：

版权声明：本文为博主原创文章，未经博主允许不得转载。	https://blog.csdn.net/chenzhuyu/article/details/80197525				

## 错误现象


* Error while sending STMT_CLOSE packet.
* Packets out of order. Expected 1 received 9. Packet size=90
* MySQL server has gone away.(ThinkPHP5新版已经解决断线问题)
* PDO::prepare(): Premature end of data


## 原因


数据库连接以后,新的线程找不到对应的数据库连接.



## 解决方法


* 在pcntl_fork之前不要对数据库进行连接.
* 断线重连字符串break_match_str添加对应错误并打开break_reconnect
* 在pcntl_fork之前关闭数据库连接:Db::getConnection()->close()->free(); 

 注意:db()->getConnection()->close()->free();是不行的


mysqlslap 是 Mysql 自带的压力测试工具，可以模拟出大量客户端同时操作数据库的情况，通过结果信息来了解数据库的性能状况  
mysqlslap 的一个主要工作场景就是对数据库服务器做基准测试  
例如我们拿到了一台服务器，准备做为数据库服务器，那么这台服务器的硬件资源能够支持多大的访问压力呢？优化了操作系统的内核参数后，是否提升了性能？调整了Mysql配置参数后，对性能有多少影响？……  
通过一系列的调优工作，配合基准测试，就可以把这台服务器调整到最佳状态，也掌握了健康状态下的性能指标  
以后在实际运行过程中，当监控的数据接近了基准指标时，说明数据库服务器快要满负荷了，需要分析是数据库结构设计、SQL语句这类用法问题，还是硬件资源的确不够了，然后进行相应的处理  
数据库服务器也可能需要硬件升级，升级之后也需要进行基准测试，和之前的测试结果对比，确保升级后的性能是提升的，防止不恰当的升级或者错误的配置引起性能下降  
了解了 mysqlslap 的用处，下面看一下如何使用 mysqlslap  
  
  
**mysqlslap 示例**  
**01 简单用法**  
对数据库做一个简单的自动测试  
mysqlslap --user=root --password=111111 --auto-generate-sql   
--auto-generate-sql 作用是自动生成测试SQL  
  
  
结果中各项含义：  
Average number of ...   
运行所有语句的平均秒数  
  
  
Minimum number of ...   
运行所有语句的最小秒数  
  
  
Maximum number of ...   
运行所有语句的最大秒数   
  
  
Number of clients ...   
客户端数量  
  
  
Average number of queries per client   
每个客户端运行查询的平均数  
  
  
**02添加并发**  
mysqlslap --user=root --password=111111 --concurrency=100 --number-of-queries=1000 --auto-generate-sql  
--concurrency=100 指定同时有100个客户端连接  
--number-of-queries=1000 指定总的测试查询次数（并发客户端数 * 每个客户端的查询次数）  
  
  
**03自动生成复杂表**  
自动测试时，创建的表结构非常简单，只有两列，实际的产品环境肯定会更复杂，可以使用参数指定列的数量和类型，例如  
mysqlslap --user=root --password=111111 --concurrency=50 --number-int-cols=5 --number-char-cols=20 --auto-generate-sql  
--number-int-cols=5 指定生成5个 int 类型的列  
--number-char-cols=20 指定生成20个 char 类型的列  
  
  
**04使用自己的测试库和测试语句**  
自动测试可以帮助我们了解硬件层面的状况，对于我们产品特定的情况，还是使用自己的库来测试比较好，可以复制一份产品库过来，然后对此库测试，例如  
mysqlslap --user=root --password=111111 --concurrency=50 --create-schema=employees --query="SELECT * FROM dept_emp;"  
--create-schema 用来指定测试库名称  
--query 是自定义的测试语句  
实际使用时，一般是测试多个复杂的语句，可以定义一个脚本文件，例如  
echo "SELECT * FROM employees;SELECT * FROM titles;SELECT * FROM dept_emp;SELECT * FROM dept_manager;SELECT * FROM departments;" > ~/select_query.sql  
把多个查询语句写入了一个 sql 文件，然后使用此文件执行测试  
mysqlslap --user=root --password=111111 --concurrency=20 --number-of-queries=1000 --create-schema=employees --query="select_query.sql" --delimiter=";"  
--query 中指定了sql文件  
--delimiter 说明sql文件中语句间的分隔符是什么  
  
  
上面用到的 employees 测试库的创建脚本我放到了网盘（[https://pan.baidu.com/s/1c1EozoW][0]），有兴趣体验 mysqlslap 的话可以下载下来试试  
  
  
**参考资料**  
http://dev.mysql.com/doc/refman/5.7/en/mysqlslap.html  
https://www.digitalocean.com/community/tutorials/how-to-measure-mysql-query-performance-with-mysqlslap

[0]: https://pan.baidu.com/s/1c1EozoW
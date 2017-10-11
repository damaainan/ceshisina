# 浅析 awk 文本分析

[胖虎小李][0] 关注 2017.10.09 13:56  字数 956  

#### 作用

awk 逐行读取文件，以空格为分隔符将每行切片，切开的部分再进行各种分析处理

#### 格式

`awk {option} 'pattern + {action}' {filenames}`

###### option:

* **-F** 自定义每行切片时的分隔符，默认空格，也可以使用 fs 定义空格符
* **-v** 变量赋值（例：`awk -v path=$PATH`）
* **-f** 从脚本文件中读取awk命令

###### pattern（不能被{ }包裹）:

* 正则表达式 例：`cat 111 | awk ' /^li/ {print $0}'` 匹配每行以li开头的记录
* 关系表达式 例：`cat 111 | awk 'NR==2 {print $0}'` 只输出第二条记录
* 模式匹配表达式 例：`cat 111 | awk ':' '$1~/zarafa/ {print $0}'` 匹配第一列包含zarafa的记录，与关系表达式相比，只是模糊比较，不是精确比较（~匹配 ~!不匹配）
* BEGIN END 模块，`awk 'BEGIN{ commands } pattern{ commands } END{ commands }'`，先执行BEGIN后面的 commands 语句，一般用作自定义变量，输入表头等，只执行一次，再逐行扫描文件，从第一行到最后一行重复执行 pattern{ commands } 语句，最后执行END后面的 commands 语句，只执行一次，一般用作分析结果、数据汇总的输出
* awk也是一种编程语言，pattern中完美支持do while、while、for、continue、break以及数组等语法

###### action:

* print 一般的行为就是打印结果，真正的分析处理数据过程在pattern中
* 存在多个action时结尾必须用;分隔

#### 常用内置变量

* $n 当前读取行的第n个字段
* $0 当前行的所有内容
* FILENAME 当前输入文件名
* NF 当前行的字段数
* NR 当前行的行号
* FS 字段分隔符（和-F参数作用相同，可自定义字段分隔符）
* OFS 自定义输出字段分隔符（加O表示outfile）
* RS 行间分隔符（可自定义行与行输出时的分隔符）
* ORS 自定义输出行间分隔符（加O表示outfile）

#### 基础实例（基础案例引用自 [linux _awk_命令详解 - ggjucheng][1]）

* `last -n 5 | awk '{print $1}'` 显示最近登录的5个账户
* `cat /etc/passwd | awk -F ':' '{print $1}'` 显示 /etc/passwd 的账户
* `cat /etc/passwd | awk -F ':' '{print $1"\t"$7}'` 显示 /etc/passwd 的账户和shell，以Tab键分割
* `cat /etc/passwd | awk -F ':' 'BEGIN{print "start"} {print $1","$7} END {print "over"}'` 显示 /etc/passwd 的账户和shell，以逗号分割，并且在数据的首尾增加start、over标识
* `cat /etc/passwd | awk '/root/ {print $0}'` 输出 /etc/passwd 有 root 关键字的行
* `cat /etc/passwd | awk -F ":" ' {count++;} END {print count;}'`  
`cat /etc/passwd | awk -F ':' 'END {print NR}'`  
统计 /etc/passwd 账户人数
* `ls -l | awk '{sum=sum+$5} END {print sum}'` 统计某个文件夹下文件占用字节数
* `ls -l | awk '$5>5120 {print $0}'` 输出某个文件夹下文件字节数大于5K的文件
* `cat /etc/passwd | awk -F ":" '{print $1,$3}' OFS="---"` 将输出的分隔符改为---

#### 运维案例（运维案例引用自 [Awk使用案例总结（运维必会）][2]）

##### 处理文件

Nginx日志 `/usr/local/nginx1.2.2/logs/access.log`  

##### 文件格式

    127.0.0.1  [31/Aug/2017:00:01:01 +0800] "POST /index.php HTTP/1.1" 200 1540 "-" "-" "xxxxxx" xxxxxx"
    

##### 具体案例

* **统计日志中访问最多的10个IP**  
`awk '{a[$1]++;} END {for(i in a) print a[i] "\t" i | "sort -k1 -nr | head -n10 "}' access.log`  
利用`a[$1]++;`，实现IP去重，并且统计IP出现的次数，之后再利用`sort`命令，按照第一列的数值倒叙排序，输出前10行
* **统计日志中访问大于100次的IP**  
`awk '{a[$1]++;} END {for(i in a) if(a[i]>100) print a[i]"\t"i | "sort -k1 -nr | head -n 10" }' access.log`
* **倒叙列打印文件**

![倒叙列转化][3]


  
`cat 222.txt | awk '{for(i=NF; i>=1; i--) {printf "%s ",$i}print s}'`（print s 打印一个换行符） 

* **从第2列打印到最后**

![从第2列打印到最后][4]


`cat 222 | awk '{for(i=2;i<=NF;i++) {printf"%s ",$i}print s}'`

[0]: /u/f2c2d3d12d98
[1]: http://www.baidu.com/link?url=QJGPjGm2ATGwHSYPshQfUWFsQmt2rKVznWvV4i42GmoBKvVIhvjSpm07pJbJUqCuK5lk5PmA9FxEpcJs_o_jaSbHIYNz-7QVzIpJecg99Y7
[2]: http://lizhenliang.blog.51cto.com/7876557/1764025
[3]: ./img/5704547-c439f6a08f7e9dbf.png
[4]: ./img/5704547-5c44467b1c41ebe3.png
## Linux Shell: awk利用索引合并文件

来源：[http://www.jianshu.com/p/63122e7fb79e](http://www.jianshu.com/p/63122e7fb79e)

时间 2018-11-21 15:38:11


找到两个文件含有共同索引的行, 并合并行

  
### 示例文件

  
#### a.txt

```
1 a
2 b
3 c
4 d
```

  
#### b.txt

```
2 x
3 y
1 z
```

文件特征: 第一列为共同索引, 期望根据`a.txt`的第一列的值找到`b.txt`相同的值并拼接成一行

  
### 上代码

```
$ awk 'NR==FNR{a[$1]=$2;next} NR>FNR{if($1 in a)print $0, a[$1]; else print $0}' b.txt a.txt
1 a z
2 b x
3 c y
4 d
```

  
### 原理解析

先看一下文档`man awk` 
```
NR    The total number of input records seen so far. # 从最开始运行到现在处理的记录总行数
FNR   The input record number in the current input file. # 当前文件的当前处理行数
```

第一段代码:`NR==FNR`代表当前读取的是第一个文件. 然后把第一个文件的需要的列内容记录在数组里面, 并定义好数据索引

```
NR==FNR{a[$1]=$2;next}
```

第二段代码:`NR>FNR`代表当前读取的是第二个文件. 通过第一列索引从数组`a[]`里面拉取之前已缓存的记录. 如果数组里面找不到就直接输出当前内容

```
NR>FNR{if($1 in a)print $0, a[$1]; else print $0}
```

这条命令类似于mysql的left join查询

```
SELECT a.id, a.v, b.v FROM a LEFT JOIN b
       ON (a.id = b.id);
```

文章来源:[https://www.derror.com/log/linux-shell-awk-combine-files][0]


[0]: https://www.derror.com/log/linux-shell-awk-combine-files
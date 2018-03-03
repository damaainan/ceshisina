## 从业务模型学习shell 之 awk

来源：[http://www.jianshu.com/p/a7e3590d4f1d](http://www.jianshu.com/p/a7e3590d4f1d)

时间 2018-03-02 10:38:53

 
《从业务模型学习shell》系列不是一本工具书，内容为笔者在生产实践中的一些经验总结，希望以更容易理解的语言和例子让读者得到收获，快速有效应用所学习的内容提高开发工作中的效率的同时少踩到坑。若对相关操作接触较少，建议在bash中完成相关练习操作。
 
简书暂不支持TOC，本文大纲如下，有需要的可以直接跳到特定章节查看
 

* 一、使用awk的目的 
* 二、正确使用awk 
* 三、业务场景案例 
 

### 一、使用awk的目的
 
在日常生产工作中，可能经常会需要操作线上文件（如日志文件），进行一系列的统计、排序等操作，信息一般是按照指定的格式打印的，而需要进行统计加工的信息往往是希望从全局来观察了解某项指标的变化趋势。
 
假设你已经有了基本linux操作的经历，会知道：在linux环境下的文本处理，我们通常会使用到 **`tail / grep`**  等命令  **（其他章节补充介绍）**  来进行文本的筛查，但这两个操作很难宏观的来判断文本中某类特征的分布情况，难以帮助我们做出正确的决策。 
 
#### 1.1 认识awk
 
先来看一个简单的例子：

```
# 1>思考如何打印出接口的单次请求总耗时数据
# 2>思考如何统计EGetUserInfo.getUserInfo接口的平均耗时
# 文件名相对路径：info.log
# 日期时间 | 请求ID | 请求耗时 | 接口名 | 方法名 | 行号 | 日志内容
2018-02-28 19:23:03.126|CA083JDK68hB|0|wang.shikun.blog.EGetUserInfo|getUserInfo|24|in params:{"uid":103020}
2018-02-28 19:23:03.129|CA083JDK68hB|3|wang.shikun.blog.EGetUserInfo|getUserInfo|28|查询DB获取用户信息
2018-02-28 19:23:03.132|CA083JDK68hB|6|wang.shikun.blog.EGetUserInfo|getUserInfo|32|out params:{"uid":103020}
2018-02-28 19:23:04.126|CA08FCDK68hB|0|wang.shikun.blog.EGetUserInfo|getUserInfo|24|in params:{"uid":303620}
2018-02-28 19:23:04.129|CA08FCDK68hB|3|wang.shikun.blog.EGetUserInfo|getUserInfo|28|查询DB获取用户信息
2018-02-28 19:23:04.134|CA08FCDK68hB|8|wang.shikun.blog.EGetUserInfo|getUserInfo|32|out params:{"uid":303620}
```
 
想好：分析一个 **`已知`**  的文件，首先要搞清楚文件的结构，能够剥离出特征值，有 **`较完整`**  的处理思路  **（一般来说复杂的shell都是一步步观察输出、调试完成的，能确定正确的方向才算是已知）**  
 
在这个需求中，我们知道：以“|”分割的话， **`耗时`**  这个特征值可以从日志文件的第3列拿到，这里用“out params”对应的请求耗时字段即是整个请求的执行耗时，且每次请求只打一次。（这里简单举例，具体应看实际场景） 
 
既然是 **` 跟“第x列”相关的问题，使用  **awk**  再合适不过了 `**  。这里先给出第一问的shell，第2问我们在后面篇幅解答。 

```
#例1：通过grep筛选出指定特征行后，使用awk命令打印对应列信息
grep -F 'EGetUserInfo|getUserInfo|32|out par' info.log | awk -F '|' '{print $3}'

#输出结果为：
6
8
```
 
这里， 我们指定以 “|” 作为分隔符（使用 -F 参数），再把分割后的  **第3列($3)**  打印出来。如果你手打了这条shell，但是  如果把  **awk**  的单引号打成了双引号，会发现并没有得到你想要的结果，输出了  **grep**  的结果，但  **awk**  没有生效  。 

```
#例2：awk 的错误使用
grep -F 'EGetUserInfo|getUserInfo|32|out par' info.log | awk -F '|' "{print $3}"

#输出结果为：
2018-02-28 19:23:03.132|CA083JDK68hB|6|wang.shikun.blog.EGetUserInfo|getUserInfo|32|out params:{"uid":103020}
2018-02-28 19:23:04.134|CA08FCDK68hB|8|wang.shikun.blog.EGetUserInfo|getUserInfo|32|out params:{"uid":303620}
```
 
### 二、正确使用awk
 
用好：从上面的两个例子中我们可以看到， 仅仅是一个引号的用法，就可能让我们误入歧途。所以在文本分析中，除了要有正确的处理思路，还应正确掌握工具的使用方法。
 
#### 2.1 awk 的写法
 
通常来说，一个awk语句是这样的格式：

```
1)
$ awk '{ 操作语句 }' 文件名
# 注意是单引号
# 如 awk '{print $1}'  info.log      会按空格（默认）分割每一行，并输出第一列数据

2)
# 处理标准输出时
$ 标准输出 | awk '{ 操作语句}'
# 如：cat info.log | awk '{print $0}'     会将info.log文件的所有行都打印出来，这里$0是匹配整行
# 要想匹配最后一列，可以使用$NF参数
```
 
实际上， awk同其他linux命令一样，具有诸多参数，配合使用各个参数，我们可以更加灵活的获取想要的信息。在linux环境键入awk，得到的用法提示是这样的：

```
awk [-F fs] [-v var=value] [-f progfile | 'prog'] [file ...]
```
 
接下来，我们来看看这些参数的使用。（本文不会有较多篇幅来介绍各个参数，有需要可以查看相关手册）
 
#### 2.1.1 awk -F
 
其中，-F 参数在前文已经提到了，指定分割符，方便我们按需要获取字符串，同时这也是我们 **`在生产工作中使用到的最频繁的参数`**  ，因为生产实践中的文件，若使用空格来进行文本分割的话，非常容易和有效信息混淆，如这样一段日志文件，使用空格来分割每一列就显得很吃力了，想读懂都不太容易： 

```
2018-02-28 19:23:03.126 CA083JDK68hB 0 wang.shikun.blog.EGetUserInfo getUserInfo 24 in params: {"uid": 103020}
2018-02-28 19:23:03.132 CA083JDK68hB 6 wang.shikun.blog.EGetUserInfo getUserInfo 32 out params:{"uid":103020}
```
 
当然，针对这一组还算规范的数据，我们还是可以用F参数比较轻松的获取指定信息。

```
# 1> 找出都有哪几行打印了日志
awk -F 'getUserInfo ' '{print $2}' info.log | awk '{print $1}'

# 输出
24
32
```
 
在本段样本数据中，我们从中选取了一段通用的可作为标准分割的字符串作为分隔符来获取数据，虽然看起来并不是很友好，但的确能帮助我们更快速的拿到目标信息又不失准确。
 
#### 2.1.2 awk -v
 
从用法提示中我们大致能知道， -v 参数是用于定义变量。 实际上， 在处理已知文本时，需要处理的数据都是清楚的，处理逻辑也基本是基于这些文本信息，很少需要用到自定义变量来辅助我们完成样本文件的分析。

```
# 2> 针对2.1.1中的问题，在每一行输出前标记当前为第几行输出结果
awk -F 'getUserInfo ' '{print $2}' info.log | awk -v a=1 '{print a++":"$1}'

# 输出结果
1:24
2:32
```
 
#### 2.2 进行统计
 
在掌握了基本的语法后，我们已经可以完成部分样本数据处理了。但是光把特征数据找出来是还不具有较大的统计意义，而工作中通常会需要我们基于特征数据完成部分统计需求。
 
#### 2.2.1 特征行数据的累加
 
我们来回顾1.1中的第2问：统计接口的平均耗时。通过第一问的解答，我们已经能够拿到各次请求的总耗时，那么平均耗时就是对各次请求的耗时数据累加求平均值：

```
# 计算平均耗时
grep -F 'EGetUserInfo|getUserInfo|32|out par' info.log | awk -F '|' '{print $3}'  | awk '{sum += $1;a++}END{print sum/a}'

# 输出
7
```
 
这次我们在获取每次耗时的结果后新增了一段 **`awk '{sum += $1;a++}END{print sum/a}'`**  ，这里我们使用了一个新的表达式，这是awk的流程控制表达式，在awk功能完成后（END）执行的操作，与其对应的还有BEGIN，在awk功能开始前执行的操作，我们来看具体用法： 

```
# 计算平均耗时
# awk 'BEGIN{awk处理前逻辑}{awk逻辑}END{awk处理结束后逻辑}'
grep -F 'EGetUserInfo|getUserInfo|32|out par' info.log | awk -F '|' '{print $3}'  | awk 'BEGIN{print "平均耗时为："} {sum += $1;a++}END{print sum/a}'

# 输出
平均耗时为：
7
```
 
在本例中，BEGIN/END 都是为了打印文本， 真正计算平均值的逻辑 **`{sum += $1;a++}`**  中定义了两个局部变量`sum` ：将每一行的第一列累加起来，`a` ：记录“累加”这个动作执行的次数。 
 
#### 2.2.2 高级功能
 
尽管本文已经写了较长的篇幅，但相信大家读过来也发现了实际的知识点并不多，只是awk的冰山一角，甚至在其他工具书中可能只是简单的几行描述。本文更多的是阐述如何去用好awk来帮助我们解决实际问题，告诉你该怎么去思考问题的解决方案，让你知道如何使用工具书。
 
所谓高级功能，如其他的程序设计语言，awk也提供各种语法、函数、变量、内建变量等特性，实际上，在前文中也有所使用。通过这些特性，可以更加方便我们写出高效的shell，每一个特性都有一系列的值和定义，在这里不再粘贴，有兴趣的可以查阅awk手册 中第12至16点附录部分。  **若链接失效/无法打开可留言**  
 
### 三、业务场景案例
 
实际上，本文所提的内容已经涵盖了笔者工作中70%的awk操作，工作中往往不会需要特别复杂的shell脚本。但对于复杂需求，希望你通过本文的阅读，能够清楚解决方向。
 
#### 3.1 大日志排查
 
下面我们来看一个相对复杂的需求，也是笔者最近碰到的实际工作问题，可以先思考下要怎么处理：

```
# 问题描述：
# 某应用在线上打印日志量巨大，打印速度惊人， 单台机器一小时打印日质量1G以上，为同类应用的5倍以上。
# 每当日志压缩、日志删除等操作都导致极大的I/O压力，期间服务性能极差，迫切需要定位出是服务代码中哪些日志在疯狂打log。

# 这里我们简化下日志样本，思路还是一样的。样本文件如下：
2018-02-28 19:23:03.126|CA083JDK68hB|0|wang.shikun.blog.EGetUserInfo|getUserInfo|24|in params:{"uid":103020}
2018-02-28 19:23:03.129|CA083JDK68hB|3|wang.shikun.blog.EGetUserInfo|getUserInfo|28|查询DB获取用户信息
2018-02-28 19:23:03.132|CA083JDK68hB|6|wang.shikun.blog.EGetUserInfo|getUserInfo|32|out params:{"uid":103020}
2018-02-28 19:23:03.134|CA553JDK68hB|6|wang.shikun.blog.EGetTotalLike|getLikeNum|98| Id:98239281sd 的文章获得198个赞赏
2018-02-28 19:23:03.134|CA553JDK68hB|6|wang.shikun.blog.EGetTotalLike|getLikeNum|98| Id:98239283sd 的文章获得98个赞赏
2018-02-28 19:23:03.134|CA553JDK68hB|6|wang.shikun.blog.EGetTotalLike|getLikeNum|98| Id:98239284dd 的文章获得18个赞赏
2018-02-28 19:23:04.126|CA08FCDK68hB|0|wang.shikun.blog.EGetUserInfo|getUserInfo|24|in params:{"uid":303620}
2018-02-28 19:23:04.129|CA08FCDK68hB|3|wang.shikun.blog.EGetUserInfo|getUserInfo|28|查询DB获取用户信息
2018-02-28 19:23:04.134|CA08FCDK68hB|8|wang.shikun.blog.EGetUserInfo|getUserInfo|32|out params:{"uid":303620}
2018-02-28 19:23:05.126|CA083JDK68hB|0|wang.shikun.blog.EGetUserInfo|getUserInfo|24|in params:{"uid":103020}
2018-02-28 19:23:05.129|CA083JDK68hB|3|wang.shikun.blog.EGetUserInfo|getUserInfo|28|查询DB获取用户信息
2018-02-28 19:23:05.132|CA083JDK68hB|6|wang.shikun.blog.EGetUserInfo|getUserInfo|32|out params:{"uid":103020}
2018-02-28 19:23:06.126|CA08FCDK68hB|0|wang.shikun.blog.EGetUserInfo|getUserInfo|24|in params:{"uid":303620}
2018-02-28 19:23:06.129|CA08FCDK68hB|3|wang.shikun.blog.EGetUserInfo|getUserInfo|28|查询DB获取用户信息
2018-02-28 19:23:06.134|CA08FCDK68hB|8|wang.shikun.blog.EGetUserInfo|getUserInfo|32|out params:{"uid":303620}
2018-02-28 19:23:07.126|CA083JDK68hB|0|wang.shikun.blog.EGetUserInfo|getUserInfo|24|in params:{"uid":103020}
2018-02-28 19:23:07.129|CA083JDK68hB|3|wang.shikun.blog.EGetUserInfo|getUserInfo|28|查询DB获取用户信息
2018-02-28 19:23:07.132|CA083JDK68hB|6|wang.shikun.blog.EGetUserInfo|getUserInfo|32|out params:{"uid":103020}
2018-02-28 19:23:08.126|CA08FCDK68hB|0|wang.shikun.blog.EGetUserInfo|getUserInfo|24|in params:{"uid":303620}
2018-02-28 19:23:08.129|CA08FCDK68hB|3|wang.shikun.blog.EGetUserInfo|getUserInfo|28|查询DB获取用户信息
2018-02-28 19:23:08.134|CA08FCDK68hB|8|wang.shikun.blog.EGetUserInfo|getUserInfo|32|out params:{"uid":303620}
2018-02-28 19:24:04.126|CA08FCDK68hB|0|wang.shikun.blog.EGetUserInfo|getUserInfo|24|in params:{"uid":303620}
2018-02-28 19:24:04.129|CA08FCDK68hB|3|wang.shikun.blog.EGetUserInfo|getUserInfo|28|查询DB获取用户信息
2018-02-28 19:24:04.134|CA08FCDK68hB|8|wang.shikun.blog.EGetUserInfo|getUserInfo|32|out params:{"uid":303620}
2018-02-28 19:24:05.126|CA083JDK68hB|0|wang.shikun.blog.EGetUserInfo|getUserInfo|24|in params:{"uid":103020}
2018-02-28 19:24:05.129|CA083JDK68hB|3|wang.shikun.blog.EGetUserInfo|getUserInfo|28|查询DB获取用户信息
2018-02-28 19:24:05.132|CA083JDK68hB|6|wang.shikun.blog.EGetUserInfo|getUserInfo|32|out params:{"uid":103020}
2018-02-28 19:24:06.126|CA08FCDK68hB|0|wang.shikun.blog.EGetUserInfo|getUserInfo|24|in params:{"uid":303620}
2018-02-28 19:24:06.129|CA08FCDK68hB|3|wang.shikun.blog.EGetUserInfo|getUserInfo|28|查询DB获取用户信息
2018-02-28 19:24:06.134|CA08FCDK68hB|8|wang.shikun.blog.EGetUserInfo|getUserInfo|32|out params:{"uid":303620}
2018-02-28 19:24:07.126|CA083JDK68hB|0|wang.shikun.blog.EGetUserInfo|getUserInfo|24|in params:{"uid":103020}
2018-02-28 19:24:07.129|CA083JDK68hB|3|wang.shikun.blog.EGetUserInfo|getUserInfo|28|查询DB获取用户信息
2018-02-28 19:24:07.132|CA083JDK68hB|6|wang.shikun.blog.EGetUserInfo|getUserInfo|32|out params:{"uid":103020}
2018-02-28 19:24:08.126|CA08FCDK68hB|0|wang.shikun.blog.EGetUserInfo|getUserInfo|24|in params:{"uid":303620}
2018-02-28 19:24:08.129|CA08FCDK68hB|3|wang.shikun.blog.EGetUserInfo|getUserInfo|28|查询DB获取用户信息
2018-02-28 19:24:08.134|CA08FCDK68hB|8|wang.shikun.blog.EGetUserInfo|getUserInfo|32|out params:{"uid":303620}
2018-02-28 19:24:03.134|CA553JDK68hB|6|wang.shikun.blog.EGetTotalRead|getReadNum|1198| Id:98239281sd 的文章获得11198个阅读量
2018-02-28 19:24:03.134|CA553JDK68hB|6|wang.shikun.blog.EGetTotalRead|getReadNum|1198| Id:98239283sd 的文章获得1098个阅读量
2018-02-28 19:24:03.134|CA553JDK68hB|6|wang.shikun.blog.EGetTotalRead|getReadNum|1198| Id:98239284dd 的文章获得1800个阅读量
```
 
这里模拟了一个日志片段， 主要问题就是：如何从中找到打印量比例最大的那些日志，改成debug或者删除。
 
对于这个问题， 我们首先要清楚的是，日志文件中的“接口名”、“方法名”、“行号”是我们确定代码文件的关键信息（这里称之为日志路径），而找出占比最大的日志信息就可以转变成所有日志按`日志路径` 归类后总字符长度的占比。 
 
#### 3.1.1 统计每行日志长度并对应出日志路径

```sh
awk -F '|'  '{print length($0),$4"#"$5"#"$6}' info.log 
#使用到了内建函数length来统计长度
```
 
  
  

![][0]

 
#### 3.1.2 按“日志路径”累加字符数， 按字符数长度倒序输出

```sh
awk -F '|'  '{print length($0),$4"#"$5"#"$6}' info.log | awk '{sum[$2] += $1}END{for(row in sum ) {print sum[row], row}}' | sort -nr
# 新增部分：| awk '{sum[$2] += $1}END{for(row in sum ) {print sum[row], row}}' | sort -nr
# 使用到了数组sum、END表达式来记录每一个日志路径的累加值
```
 
  
  

![][1]

 
#### 3.1.3 按百分比统计并排序

```sh
awk -F '|'  '{print length($0),$4"#"$5"#"$6}' info.log | awk '{sum[$2] += $1}END{for(row in sum ) {print sum[row], row}}' | sort -nr  | awk '{total += $1; list[$1] = $2}END{for(i in list){print 1.0*i/total*100"", i, list[i]}}' |sort -nr
# 新增部分：| awk '{total += $1; list[$1] = $2}END{for(i in list){print 1.0*i/total*100"", i, list[i]}}' |sort -nr
# 使用到了数组、累加、END表达式来计算每一个日志路径的占比情况
```
 
  
  

![][2]

 
#### 3.1.4 查看大于10%占比的日志量总共占的比例

```sh
awk -F '|'  '{print length($0),$4"#"$5"#"$6}' info.log | awk '{sum[$2] += $1}END{for(row in sum ) {print sum[row], row}}' | sort -nr  | awk '{total += $1; list[$1] = $2}END{for(i in list){print 1.0*i/total*100"", i, list[i]}}' |sort -nr  | awk 'BEGIN{print "大于10%日志总占比的日志量总共占有日志文件比例："}{if($1 > 10.0) total+=$1}END{print total"%"}'
# 新增部分：  | awk 'BEGIN{print "大于10%日志总占比的日志量总共占有日志文件比例："}{if($1 > 10.0) total+=$1}END{print total"%"}'
# 使用到了条件语句来过滤不满足条件的值
```
 
  
  

![][3]

 
通过以上几步的分析，我们精准的找出了日志量最大的几条日志，我们可以把重心放在优化前3条日志上了，把这3条日志打印干掉的话预估可以减少80%的日志打印量，提升服务性能。
 
### 结语
 
以上内容就是本次分享的awk实际应用的相关内容了，后续将对更多开发者一般不常用但是好用的linux命令进行分享，感谢关注。若文中有写错的地方，还请留言指正，防止对他人产生误导！
 


[0]: ./img/FV36nuZ.png
[1]: ./img/6Zfaea2.png
[2]: ./img/7B3ueqf.png
[3]: ./img/VjiqIrR.png


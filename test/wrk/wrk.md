# wrk — 小巧轻盈的 http 性能测试工具.

[发表回复][0]

via: http://zjumty.iteye.com/blog/2221040

测试先行是软件系统质量保证的有效手段. 在单元测试方面, 我们有非常成熟的 xUnit 方案. 在集成测试方面, 我们 selenium 等自动化方案. 在性能测试方面也有很多成熟的工具, 比如 LoadRunner, Jmeter 等. 但是很多工具都是给专门的性能测试人员使用的, 功能虽然强大, 但是安装和操作不太方便. 作为开发人员, 我们有些时候想快速验证我们的解决方案是不是存在性能问题, 或者在并发情况下是否有意想不到的问题. 安装 LoadRunner 这样工具, 录制脚本很麻烦, 用起来就像在用大炮打蚊子.

wrk 是一个很简单的 http 性能测试工具. 也可以叫做 http benchmark 工具. 只有一个命令行, 就能做很多基本的 http 性能测试.

wrk 的开源的, 代码在 github 上. https://github.com/wg/wrk

首先要说的一点是: wrk 只能运行在 Unix 类的系统上. 比如 linux, mac, solaris 等. 也只能在这些系统上编译.

这里不得不说一下, 为什么很多人说 mac 是最好的开发环境. 不是因为使用 mac 逼格有多高. 而是你可以同时得到 windows 和 linux 的好处. 多数 linux 下的开发工具都可以在 mac 上使用. 很多都是预编译好的, 有些只需要编译一下就能用.

wrk 的一个很好的特性就是能用很少的线程压出很大的并发量. 原因是它使用了一些操作系统特定的高性能 io 机制, 比如 select, epoll, kqueue 等. 其实它是复用了 redis 的 ae 异步事件驱动框架. 确切的说 ae 事件驱动框架并不是 redis 发明的, 它来至于 Tcl的解释器 jim, 这个小巧高效的框架, 因为被 redis 采用而更多的被大家所熟知.

要用 wrk, 首先要编译 wrk.  
你的机器上需要已经安装了 git 和基本的c编译环境. wrk 本身是用 c 写的. 代码很少. 并且没有使用很多第三方库. 所以编译基本不会遇到什么问题.
```
 git clone  https : //github.com/wg/wrk.git

 cd wrk

 make
```
就 ok了.  
make 成功以后在目录下有一个 wrk 文件. 就是它了. 你可以把这个文件复制到其他目录, 比如 bin 目录. 或者就这个目录下执行.

如果编译过程中出现:
```
src / wrk . h : 11 : 25 :  fatal error :  openssl / ssl . h :  No such file or  directory

  #include <openssl/ssl.h>
```
是因为系统中没有安装openssl的库.


    sudo apt-get install libssl-dev  
或  

    sudo yum install openssl-devel

我们先来做一个简单的性能测试:

    wrk  - t12  - c100  - d30s http : //www.baidu.com

30秒钟结束以后可以看到如下输出:
```
 Running  30s  test  @  http : //www.baidu.com

  12  threads and  100  connections

  Thread Stats Avg Stdev Max  + / -  Stdev

  Latency  538.64ms  368.66ms  1.99s  77.33 %

  Req / Sec  15.62  10.28  80.00  75.35 %

  5073  requests in  30.09s ,  75.28MB  read

  Socket errors :  connect  0 ,  read  5 ,  write  0 ,  timeout  64

 Requests / sec :  168.59

 Transfer / sec :  2.50MB
```
先解释一下输出:  
12 threads and 100 connections  
这个能看懂英文的都知道啥意思: 用12个线程模拟100个连接.  
对应的参数 -t 和 -c 可以控制这两个参数.

一般线程数不宜过多. 核数的2到4倍足够了. 多了反而因为线程切换过多造成效率降低. 因为 wrk 不是使用每个连接一个线程的模型, 而是通过异步网络 io 提升并发量. 所以网络通信不会阻塞线程执行. 这也是 wrk 可以用很少的线程模拟大量网路连接的原因. 而现在很多性能工具并没有采用这种方式, 而是采用提高线程数来实现高并发. 所以并发量一旦设的很高, 测试机自身压力就很大. 测试效果反而下降.

下面是线程统计:
```
 Thread Stats Avg Stdev Max  + / -  Stdev

  Latency  538.64ms  368.66ms  1.99s  77.33 %

  Req / Sec  15.62  10.28  80.00  75.35 %
```
Latency: 可以理解为响应时间, 有平均值, 标准偏差, 最大值, 正负一个标准差占比.  
Req/Sec: 每个线程每秒钟的完成的请求数, 同样有平均值, 标准偏差, 最大值, 正负一个标准差占比.

一般我们来说我们主要关注平均值和最大值. 标准差如果太大说明样本本身离散程度比较高. 有可能系统性能波动很大.

接下来:
```
 5073  requests in  30.09s ,  75.28MB  read

  Socket errors :  connect  0 ,  read  5 ,  write  0 ,  timeout  64

 Requests / sec :  168.59

 Transfer / sec :  2.50MB
```
30秒钟总共完成请求数和读取数据量.  
然后是错误统计, 上面的统计可以看到, 5个读错误, 64个超时.  
然后是所以线程总共平均每秒钟完成168个请求. 每秒钟读取2.5兆数据量.

可以看到, 相对于专业性能测试工具. wrk 的统计信息是非常简单的. 但是这些信息基本上足够我们判断系统是否有问题了.

wrk 默认超时时间是1秒. 这个有点短. 我一般设置为30秒. 这个看上去合理一点.  
如果这样执行命令:

    / wrk  - t12  - c100  - d30s  - T30s  http : //www.baidu.com

可以看到超时数就大大降低了, Socket errors 那行没有了:
```
 Running  30s  test  @  http : //www.baidu.com

  12  threads and  100  connections

  Thread Stats Avg Stdev Max  + / -  Stdev

  Latency  1.16s  1.61s  14.42s  86.52 %

  Req / Sec  22.59  19.31  108.00  70.98 %

  4534  requests in  30.10s ,  67.25MB  read

 Requests / sec :  150.61

 Transfer / sec :  2.23MB
```
通过 -d 可以设置测试的持续时间. 一般只要不是太短都是可以的. 看你自己的忍耐程度了.

![][1]

  
时间越长样本越准确. 如果想测试系统的持续抗压能力, 采用 loadrunner 这样的专业测试工具会更好一点.

想看看响应时间的分布情况可以加上–latency参数:


    wrk  - t12  - c100  - d30s  - T30s  -- latency http : //www.baidu.com

```
 Running  30s  test  @  http : //www.baidu.com

  12  threads and  100  connections

  Thread Stats Avg Stdev Max  + / -  Stdev

  Latency  1.22s  1.88s  17.59s  89.70 %

  Req / Sec  14.47  9.92  98.00  77.06 %

  Latency Distribution

  50 %  522.18ms

  75 %  1.17s

  90 %  3.22s

  99 %  8.87s

  3887  requests in  30.09s ,  57.82MB  read

  Socket errors :  connect  0 ,  read  2 ,  write  0 ,  timeout  0

 Requests / sec :  129.19

 Transfer / sec :  1.92MB
```
可以看到50%在0.5秒以内, %75在1.2s 以内. 看上去还不错.

看到这里可能有人会说了, HTTP 请求不会总是这么简单的, 通常我们会有 POST,GET 等多个 method, 会有 Header, 会有 body 等.

在我第一次知道有 wrk 这个工具的时候他确实还不太完善, 要想测试一些复杂的请求还有点难度. 现在 wrk 支持 lua 脚本. 在这个脚本里你可以修改 method, header, body, 可以对 response 做一下自定义的分析. 因为是 lua 脚本, 其实这给了你无限的可能. 但是这样一个强大的功能如果不谨慎使用, 会降低测试端的性能, 测试结果也受到影响.

一般修改method, header, body不会影响测试端性能, 但是操作 request, response 就要格外谨慎了.

我们通过一些测试场景在看看怎么使用 lua 脚本.

POST + header + body.

首先创建一个 post.lua 的文件:
```
 wrk . method  =  "POST"

 wrk . body  =  "foo=bar&baz=quux"

 wrk . headers [ "Content-Type" ]  =  "application/x-www-form-urlencoded"
```
就这三行就可以了, 当然 headers 可以加入任意多的内容.  
然后执行:


    wrk  - t12  - c100  - d30s  - T30s  -- script = post . lua  -- latency http : //www.baidu.com

当然百度可能不接受这个 post 请求.

对 wrk 对象的修改全局只会执行一次.  
通过 wrk 的源代码可以看到 wrk 对象的源代码有如下属性:
```
 local wrk  =  {

  scheme  =  "http" ,

  host  =  "localhost" ,

  port  =  nil ,

  method  =  "GET" ,

  path  =  "/" ,

  headers  =  { } ,

  body  =  nil ,

  thread  =  nil ,

 }
```
schema, host, port, path 这些, 我们一般都是通过 wrk 命令行参数来指定.

wrk 提供的几个 lua 的 hook 函数:

setup 函数  
这个函数在目标 IP 地址已经解析完, 并且所有 thread 已经生成, 但是还没有开始时被调用. 每个线程执行一次这个函数.  
可以通过thread:get(name), thread:set(name, value)设置线程级别的变量.

init 函数  
每次请求发送之前被调用.  
可以接受 wrk 命令行的额外参数. 通过 — 指定.

delay函数  
这个函数返回一个数值, 在这次请求执行完以后延迟多长时间执行下一个请求. 可以对应 thinking time 的场景.

request函数  
通过这个函数可以每次请求之前修改本次请求的属性. 返回一个字符串. 这个函数要慎用, 会影响测试端性能.

response函数  
每次请求返回以后被调用. 可以根据响应内容做特殊处理, 比如遇到特殊响应停止执行测试, 或输出到控制台等等.

```
 function  response ( status ,  headers ,  body )

  if  status  ~ =  200  then

  print ( body )

  wrk . thread : stop ( )

  end

 end
```
done函数  
在所有请求执行完以后调用, 一般用于自定义统计结果.
```
 done  =  function ( summary ,  latency ,  requests )

  io . write ( "------------------------------\n" )

  for  _ ,  p  in  pairs ( {  50 ,  90 ,  99 ,  99.999  } )  do

  n  =  latency : percentile ( p )

  io . write ( string . format ( "%g%%,%d\n" ,  p ,  n ) )

  end

 end
```
下面是 wrk 源代码中给出的完整例子:
```
 local counter  =  1

 local threads  =  { }

 function  setup ( thread )

  thread : set ( "id" ,  counter )

  table . insert ( threads ,  thread )

  counter  =  counter  +  1

 end

 function  init ( args )

  requests  =  0

  responses  =  0

  local msg  =  "thread %d created"

  print ( msg : format ( id ) )

 end

 function  request ( )

  requests  =  requests  +  1

  return  wrk . request ( )

 end

 function  response ( status ,  headers ,  body )

  responses  =  responses  +  1

 end

 function  done ( summary ,  latency ,  requests )

  for  index ,  thread in  ipairs ( threads )  do

  local id  =  thread : get ( "id" )

  local requests  =  thread : get ( "requests" )

  local responses  =  thread : get ( "responses" )

  local msg  =  "thread %d made %d requests and got %d responses"

  print ( msg : format ( id ,  requests ,  responses ) )

  end

 end
```
测试复合场景时, 也可以通过 lua 实现访问多个 url.  
例如这个复杂的 lua 脚本, 随机读取 paths.txt 文件中的 url 列表, 然后访问.:
```
 counter  =  1

 math . randomseed ( os . time ( ) )

 math . random ( ) ;  math . random ( ) ;  math . random ( )

 function  file_exists ( file )

  local  f  =  io . open ( file ,  "rb" )

  if  f  then  f : close ( )  end

  return  f  ~ =  nil

 end

 function  shuffle ( paths )

  local  j ,  k

  local  n  =  #paths

  for  i  =  1 ,  n  do

  j ,  k  =  math . random ( n ) ,  math . random ( n )

  paths [ j ] ,  paths [ k ]  =  paths [ k ] ,  paths [ j ]

  end

  return  paths

 end

 function  non_empty_lines_from ( file )

  if  not  file_exists ( file )  then  return  { }  end

  lines  =  { }

  for  line in  io . lines ( file )  do

  if  not  ( line  ==  '' )  then

  lines [ #lines + 1] = line

  end

  end

  return  shuffle ( lines )

 end

 paths  =  non_empty_lines_from ( "paths.txt" )

 if  #paths <= 0 then

  print ( "multiplepaths: No paths found. You have to create a file paths.txt with one path per line" )

  os . exit ( )

 end

 print ( "multiplepaths: Found "  . .  #paths .. " paths")

 request  =  function ( )

  path  =  paths [ counter ]

  counter  =  counter  +  1

  if  counter  >  #paths then

  counter  =  1

  end

  return  wrk . format ( nil ,  path )

 end
```
关于 cookie  
有些时候我们需要模拟一些通过 cookie 传递数据的场景. wrk 并没有特殊支持, 可以通过 wrk.headers[“Cookie”]=”xxxxx”实现.  
下面是在网上找的一个离职, 取 Response的cookie作为后续请求的cookie

```
 function  getCookie ( cookies ,  name )

  local start  =  string . find ( cookies ,  name  . .  "=" )

  if  start  ==  nil then

  return  nil

  end

  return  string . sub ( cookies ,  start  +  #name + 1, string.find(cookies, ";", start) - 1)

 end

 response  =  function ( status ,  headers ,  body )

  local token  =  getCookie ( headers [ "Set-Cookie" ] ,  "token" )

  if  token  ~ =  nil then

  wrk . headers [ "Cookie" ]  =  "token="  . .  token

  end

 end
```
wrk 本身的定位不是用来替换 loadrunner 这样的专业性能测试工具的. 其实有这些功能已经完全能应付平时开发过程中的一些性能验证了.

[0]: https://blog.satikey.com/p/5768.html#respond
[1]: http://zjumty.iteye.com/images/smiles/icon_biggrin.gif
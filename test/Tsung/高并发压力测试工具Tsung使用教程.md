# [高并发压力测试工具Tsung使用教程][0]

 [服务器][1] 1年前 (2016-03-05) 9679浏览  [5评论][2]

目录

* [下载安装][3]
* [运行][4]
* [配置文件][5]
    
    * [clients][6]
    * [servers][7]
    * [monitoring][8]
    * [load][9]
    * [options][10]

* [sessions][11]

tsung是erlang开发的一个开源的多协议分布式负载测试工具，它能用来压力测试HTTP, WebDAV, SOAP, PostgreSQL, MySQL, LDAP 和 Jabber/XMPP的服务器。它可以分布在多个客户机，并能够模拟成千上万的虚拟用户数并发。

## 下载安装 

Tsung 已经在Linux、FreeBSD和Solaris系统上测试过，当然也可以在所有支持[Erlang语言][12]的平台上工作（Linux、Solaris、*BSD、Win32 和 Mac OS X）。

Mac OS X通过 Homebrew 即可安装 Tsung，命令： brew install tsung。

Ubuntu 安装也很简单，命令：apt- get install tsung。

其他系统需要先下载源码包再安装，源码包下载地址：[http://tsung.erlang-projects.org/dist/][13]，方法：

    wget http://tsung.erlang-projects.org/dist/tsung-1.6.0.tar.gz  # 以1.6.0版本为例
    tar -zxf tsung-1.6.0.tar.gz   # 解压
    cd tsung-1.6.0                # 进入目录
    ./configure                   # 配置，生成 Makefile 文件
    make                          # 编译
    make install                  # 安装

configure 的时候，如果没有安装 Tsung 依赖的 Erlang 语言库，会提示下面的错误：

    ...
    checking for Erlang/OTP root directory... configure: error: in `/root/tsung-1.6.0':
    configure: error: test Erlang program execution failed
    ...

可以用如下的命令安装，然后再 configure。

    brew install erlang        # OS X Homebrew
    port install erlang        # OS X MacPorts
    apt-get install erlang     # Ubuntu 和 Debian
    yum install erlang         # Fedora
    pkg install erlang         # FreeBSD

其他系统需要从源码安装 Erlang，稍微复杂一些，可参考：[https://github.com/erlang/otp/blob/maint/HOWTO/INSTALL.md][14] 。

## 运行 

安装完成后，会生成两个命令文件： tsung 和 tsung-recorder，默认生成在 /usr/local/bin 目录下，使用 -h参数可以查到它们所有的参数：

    tsung -h

在启动 tsung 之前，我们需要一个 XML 格式的测试配置文件，tsung会根据该配置文件进行测试。 /usr/share/doc/tsung/examples 目录下有一些 XML 配置文件范例，将其中HTTP测试配置文件 http_simple.xml 拷贝到当前目录，然后再执行，命令如下：

    cp /usr/local/share/doc/tsung/examples/http_simple.xml ./   # 拷贝范例配置文件
    tsung -f http_simple.xml start                              # 指定xml文件并开始。如果不指定，则默认使用 ~/.tsung/tsung.xml

这个命令会打印出测试的日志目录，直到测试结束。Log 日志文件保存在目录 ~/.tsung/log/ 下，当启动一个新的测试时，会在这个目录下面创建一个新的子目录，用以保存测试的数据，格式为当前日期和时间的组合，例如： ~/.tsung/log/20160217-0940 。默认情况下，控制节点会启动一个嵌入的网站服务器，侦听8091端口（可以用 -n 选项禁用）。

然后再等待测试就可以了。也可以用tail命令查看实时记录：

    tail -f ~/.tsung/log/20140430-1126/tsung.log

结束之后，再用 tsung_stats .pl生成报表。

    yum install gnuplot       # tsung_stats.pl 需要用到的 gnuplot
    mkdir http_simple         # 创建用以保存报表的目录
    cd http_simple            # 进入目录
    /usr/local/lib/tsung/bin/tsung_stats.pl --stats ~/.tsung/log/20160305-0933/tsung.log  # 生成报表

之后，会在 http_simple 目录下生成3个目录和1个 log 文件，其中 images 目录下就是报表图片，类似如下。

![graphes-HTTP_CODE-rate_tn][15]

![graphes-Perfs-mean_tn][16]

以上仅是测试，没有实际用处，因为没有配置要测试的网站，所以所得到的结果没有实际意义，需要修改 XML 配置文件后再测试，才会得到有意义的结果。

## 配置文件 

打开 http_simple.xml ，下面来讲几个关键的配置。

### clients 

用户产生的方式

    <clients>
      <client host="localhost" use_controller_vm="true" maxusers="30000"></client>
    </clients>

tsung运行时可以由很多的虚拟机组成，client配置指明这个client机器上最多生成的用户数，如果use_controller_vm为true的话，那么当用户数达到maxusers，tsung会自动生成新的VM。

### servers 

    <servers>
      <server host="garden.blue.jude.poppen.lab" port="80" type="tcp"></server>
    </servers>

server段可以配置**被测服务器**的相关信息，也可以配置成集群，如下

    <servers>
      <server host="server1" port="80" type="tcp" weight="4"></server>
      <server host="server2" port="80" type="tcp" weight="1"></server>
    </servers>

tsung会根据weight值来选择发起请求的server

### monitoring 

系统监控服务，配置完后可获取被测server的cpu，内存，负载，数据库的相关信息。可以配置成erlang的监控服务和snmp的监控服务。

    <monitoring>
      <monitor host="garden" type="erlang">
        <mysqladmin port="3306" username="root" ></mysqladmin>
      </monitor>
    </monitoring>

### load 

    <load>
      <arrivalphase phase="1" duration="3" unit="minute">
        <users maxnumber="100" interarrival="0.02" unit="second" ></users>
      </arrivalphase>
    </load>

load段可配置访问的负载，访问可以配成多个阶段，由phase值指定。duration是测试持续时间，unit是单位。

users段的maxnumber限制了生成的最大用户数，interarrival=”0.02”表示0.02秒产生一个新用户，用户按照session的配置顺序执行session中的request。

### options 

    <options>
     <option type="ts_http" name="user_agent">
     <user_agent probability="80">Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.7.8) Gecko/20050513 Galeon/1.3.21</user_agent>
     <user_agent probability="20">Mozilla/5.0 (Windows; U; Windows NT 5.2; fr-FR; rv:1.7.8) Gecko/20050511 Firefox/1.0.4</user_agent>
     </option>
     </options>

options段可配置一些请求的信息，如agent信息。

## sessions 

    <sessions>
      <session name="http-example" probability="70" type="ts_http">
        <setdynvars sourcetype="random_number" start="1" end ="100">
          <var name="itemid" ></var>
        </setdynvars>
        <transaction name='getlist'>
          <request subst="true">
            <http url="/comment/getList" method="POST" contents = "item_type=image&item_id=%%_itemid%%"></http>
          </request>
        </transaction>
      </session>
      <session name="http-example" probability="30" type="ts_http">
        <setdynvars sourcetype="random_number" start="1" end="100">
          <var name="itemid" ></var>
        </setdynvars>
        <setdynvars sourcetype="random_number" start="20" end="5000000">
          <var name="content" ></var>
        </setdynvars>
        <transaction name='getlist'>
          <request subst="true">
             <http url="/comment/addComment" method="POST" contents = "item_type=image&item_id=%%_itemid%%&content=%%_content%%"></http>
          </request>
        </transaction>
      </session>
    </sessions>

可配置多个子session，进而可测试多个api，可以设置请求概率，在probability里被定义，要求每个session的probability之和是100。类型是http。

sessions里可用for来设定请求次数，如下

    <for from="1" to="@loop" incr="1" var="counter">

在里面可以设置请求的具体信息。在请求参数里可以带上随机数。随机数和随机字符串的定义如下：

    <setdynvars sourcetype="random_number" start="20" end="5000000">
      <var name="xxx" ></var>
    </setdynvars>
    <setdynvars sourcetype="random_string" length="10">
      <var name="xxx" ></var>
    </setdynvars>

以%%_xxx%%的形式来调用，这里必须注意的是，要使用随机数，request必须加上subst=”true”参数，不然随机数无法被引用成功。随机数也可从文件读取，如csv。

http内部可定义header参数：

    <http_header name="Authorization" value="111"></http_header>
    <http_header name="Cookie" value="authToken=%%_auth_token%%; Path=/"></http_header>
    <!-- content-Type：POST请求参数的格式，如果是json格式可以这样写 -->
    <http_header name="Content-Type" value="application/json"></http_header>

thinktime可用于定义两个请求的间隔时间

    <thinktime value="1"></thinktime>

另外可定义不同的transaction ，这样子结果里就会显示不同transaction的具体信息。

参考文档：

1. [Tsung 1.6.0 documentation][17]
1. [Load Testing using Tsung][18]
1. [Test the Performance and Scalability of Your Web Applications With Tsung][19]
1. [压力测试工具tsung用法简介][20]

[0]: http://www.awaimai.com/628.html
[1]: http://www.awaimai.com/category/server
[2]: http://www.awaimai.com/628.html#comments
[3]: #i
[4]: #i-2
[5]: #i-3
[6]: #clients
[7]: #servers
[8]: #monitoring
[9]: #load
[10]: #options
[11]: #sessions
[12]: http://www.erlang.org/
[13]: http://tsung.erlang-projects.org/dist/
[14]: https://github.com/erlang/otp/blob/maint/HOWTO/INSTALL.md
[15]: ./img/graphes-HTTP_CODE-rate_tn.png
[16]: ./img/graphes-Perfs-mean_tn.png
[17]: http://tsung.erlang-projects.org/user_manual/index.html
[18]: https://engineering.helpshift.com/2014/tsung/
[19]: https://beebole.com/blog/erlang/test-performance-and-scalability-of-your-web-applications-with-tsung/
[20]: http://codezye.com/2015/12/28/%E5%8E%8B%E5%8A%9B%E6%B5%8B%E8%AF%95%E5%B7%A5%E5%85%B7tsung%E7%94%A8%E6%B3%95%E7%AE%80%E4%BB%8B/
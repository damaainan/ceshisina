## [Tsung笔记之插件编写篇][0]

### 前言

Tsung对具体协议、通道的支持，一般以插件形式提供接口，接口不是很复杂，插件也很容易编写，支持协议多，也就不足为怪了。

下面首先梳理一下当前Tsung 1.6.0所有内置插件，然后为一个名称为Qmsg的私有二进制协议编写插件, 运行Qmsg服务器端程序，执行压力测试，最后查看测试报告。

### 已支持插件梳理

Tsung 1.6.0支持的协议很多，简单梳理一下：

![Tsung Controller  Support Plugins V2-1][1]

￼

* 压测的协议首先需要支持xml形式配置，配置内容需要 tsung_config_protocolname 模块解析 
  * 存放在tsung_controller目录下
* 其次是tsung client端也要插件 ts_protocolname 模块支持数据操作 
  * 存放在tsung目录下
* 同时在tsung项目examples目录下也给出了已支持协议配置简单示范xml文件

已经支持协议简单说明：

1. amqp，Advanced Message Queuing Protocol缩写，只要支持高级消息队列协议的应用，都可以用来做压测，比如RabbitMQ，ActiveMQ等
1. http，基本协议，构建于HTTP协议之上的，还有类似于BOSH，WebDav等上层业务协议
1. jabber，也称之为XMPP，支持的相当丰富，除了TCP/SSl，还可以通过Websocekt进行传递
1. raw，针对原始类型消息，不做编解码处理，直接在TCP / UDP / SSL等传输层中传递，这个对部分私有协议，比较友好，不用写单独的编解码处理，直接透传好了
1. shell，针对LInux/Unix终端命令调用进行压测，这种场景比较小众
1. fs，filesystem缩写，针对文件系统的读写性能进行压测
1. job，针对任务调度程序进行的压测，比如PBS/torqueLSF、OAR等

### Tsung插件工作机制

粗一点来看Tsung插件的工作流程（点击可以看大图）：

[![tsung_qmsg_flo](./img/tsung_qmsg_flow-1.png) ￼](http://images.blogjava.net/blogjava_net/yongboy/tsung_qmsg_flow-1.png)

放大一些（引用 [hncscwc][2] 博客图片，相当赞！）：

![][3]

### 为什么要编写插件

Tsung针对通用协议有支持，若是私有或不那么通用的协议，就不会有专门的插件支持了，那么可选的有两条路子：

* 使用raw模式发送原始消息，需要自行组装
* 自己编写插件，灵活处理编解码

既然谈到了插件，我们也编写一个插件也体验一下编写插件的过程。

### Qmsg协议定义

假设一个虚拟场景，打造一个新的协议Qmsg，二进制格式组成：

![qmsg_protoco][4]

￼

这种随意假象出来的格式，不妨称作为**qmsg**（Q可爱形式的message）协议，仅作为Demo演示而存在。简单场景：

* 用户发言，包含用户id和发言内容 
  
    * User ID，32位自然数类型
    * 发言为文字内容，字符串形式，长度不固定
    * 组装后的请求体为二进制协议格式
    * PocketLen:**##UserId + UserComment##**
* 服务器端返回用户ID和一个幸运数字(32位表示) 
  
    * PocketLen:**##UserId + RandomCode##**

为了卡哇伊一些，多了一些点缀的“**####**”符号。

### 编写一个完整插件

这里基于Tsung 1.6.0版本构建一个Qmsg插件，假定你懂一些Erlang代码，以及熟悉Tsung一些基本概念。

#### 0. 创建一个项目

要创建Tsung的一个Qmsg插件项目，虽没有固定规范，但按照已有格式组织好代码层级也是有必要的。

    ├── include
    │   └── ts_qmsg.hrl
    ├── src
    │   ├── tsung
    │   │   └── ts_qmsg.erl
    │   └── tsung_controller
    │       └── ts_config_qmsg.erl
    └── tsung-1.0.dtd
    

#### 1. 创建配置文件

Tsung的压测以xml文件驱动，因此需要界定一个Qmsg插件形式的完整会话的XML呈现，比如：

    <session probability="100" name="qmsg-demo" type="ts_qmsg">
        <request>
          <qmsg uid="1001">Hello Tsung Plugin</qmsg>
        </request>
    
        <request>
          <qmsg uid="1002">This is a Tsung Plugin</qmsg>
        </request>
    </session>
    

* ts_qmsg，会话类型所依赖协议模拟客户端实现
* `<qmsg uid="Number">Text</qmsg>` 定义了qmsg会话可配置形式，内嵌在request元素内
* uid为属性

> 此时，你若直接在xml文件中编辑，会遇到校验错误。

#### 2. 更新DTD文件

Tsung的xml文件依赖tsung-1.0.dtd文件进行校验配置是否有误，需要做对DTD文件做修改，以支持所添加新的协议。

在tsung-1.0.dtd项目中，最小支持：

1. session元素type属性中添加上 ts_qmsg
1. request元素处添加 qmsg :``` <!ELEMENT request ( match*, dyn_variable*, ( http | jabber | raw | pgsql | ldap | mysql |fs | shell | job | websocket | amqp | mqtt | qmsg) )>```
1. 添加qmsg元素定义：

```
    <!ELEMENT qmsg (#PCDATA) >
    <!ATTLIST qmsg
        uid         CDATA   "0"
        ack         (local | no_ack | parse) #REQUIRED
        >
    
```

> 完整内容，可参考 tsung_plugin_demo/tsung-1.0.dtd 文件。

#### 3. 头文件 include/ts_qmsg.hrl头文件include/ts_qmsg.hrl定义数据保存的结构（也称之为记录/record）：

    -record(qmsg_request, {
              uid,
              data
             }).
    
    -record(qmsg_dyndata, {
              none
             }
           ).
    

1. qmsg_request: 存储从xml文件解析的qmsg请求数据，用于生成压力请求
1. qmsg_dyndata: 存储动态参数（当前暂未使用到）

#### 4. XML文件解析

ts_config_qmsg.erl文件，用于解析和协议Qmsg关联的配置：  
- 只需要实现parse_config/2唯一方法  
- 解析xml文件中所配置Qmsg协议请求相关配置  
- 被ts_config:parse/1在遇到Qmsg协议配置时调用

备注：

1. 若要支持动态替换，需要的字段以字符串形式读和存储

#### 5. ts_qmsg.erlts_qmsg.erl模块主要提供Qmsg协议的编解码的完整动作, 以及当前协议界定下的用户会话属性设定。

首先需要实现接口ts_plugin规范定义的所有需要函数，定义了参数值和返回值。

    -behavior(ts_plugin).
    
    ...
    
    -export([add_dynparams/4,
             get_message/2,
             session_defaults/0,
             subst/2,
             parse/2,
             parse_bidi/2,
             dump/2,
             parse_config/2,
             decode_buffer/2,
             new_session/0]).
    

相对来说，核心为协议的编解码功能：

* get_message/2，构造请求数据，编码成二进制，上层ts_client模块通过Socket连接发送给目标服务器
* parse/2，(当对响应作出校验时)从原始Socket上返回的数据进行解码，取出协议定义业务内容

这部分代码可以参考 tsung_plugin_demo/src/tsung/ts_client.erl 文件。

#### 6. 如何编译

虽然理论上可以单独编，生成的beam文件直接拷贝到已经安装的tsung对应目录下面，但实际上插件编写过程中要依赖多个tsung的hrl文件，这造成了依赖路径问题。采用直接和tsung打包一起部署，实际操作上有些麻烦，

为了节省体力，使用一个shell脚本 - build_plugin.sh，方便快速编译、部署：

    # !/bin/bash
    
    cp tsung-1.0.dtd $1/
    cp include/ts_qmsg.hrl $1/include/
    cp src/tsung_controller/ts_config_qmsg.erl $1/src/tsung_controller/
    cp src/tsung/ts_qmsg.erl $1/src/tsung/
    
    cd $1/
    make uninstall
    ./configure --prefix=/usr/local
    make install
    

> 这里指定安装Tsung的指定目录为> /usr/local> ，可以根据需要修改

需要提前准备好tsung-1.6.0目录：

    wget http://tsung.erlang-projects.org/dist/tsung-1.6.0.tar.gz
    tar xf tsung-1.6.0.tar.gz
    

在编译Qmsg插件脚本时, 指定一下tsung-1.6.0解压后的路径即可：

    sh build_plugin.sh /your_path/tsung-1.6.0
    

后面嘛，就等着自动编译和安装呗。

### 启动Qmsg协议的压测

#### 1. 首先启动Qmsg服务器端程序

既然有压测端，就需要一个Qmsg协议处理的后端程序qmsg_server.erl，用于接收客户端请求，获得用户ID值之后，生成一个随机数字，组装成二进制协议，然后发给客户端，这就是全部功能。

这个程序，简单一个文件，在 tsung_plugin_demo目录下面，编译运行, 默认监听5678端口：

    erlc qmsg_server.erl && erl -s qmsg_server start
    

另外，还提供了一个手动调用接口，方便在Erlang Shell端调试：

    %% 下面为
    qmsg_server:sendmsg(1001, "这里是用户发言").
    
    

> 启动之后，监听地址 *: 5678 

源码见：tsung_plugin_demo/qmsg_server.erl#### 2. 编写Qmsg压测XML配置文件

因为是演示示范，一台Linxu主机上就可以进行了：

* 连接本机的 127.0.0.1:5678
* 最多产生10个用户，每秒产生1个，压力负载设置的很低
* 两个不同类型会话，比重10% + 90% = 100%
* qmsg-subst-example会话使用了用户ID个和用户发言内容自动生成机制
```
    <tsung loglevel="debug" dumptraffic="false" version="1.0">
      <clients>
        <client host="localhost" use_controller_vm="true"></client>
      </clients>
    
      <servers>
        <server host="127.0.0.1" port="5678" type="tcp"></server>
      </servers>
    
      <load>
        <arrivalphase phase="1" duration="1" unit="minute">
          <users maxnumber="10" interarrival="1" unit="second"></users>
        </arrivalphase>
      </load>
    
      <sessions>
        <session probability="10" name="qmsg-example" type="ts_qmsg">
          <request>
            <qmsg uid="1001" ack="parse">Hello Tsung Plugin Qmsg!</qmsg>
          </request>
        </session>
        <session probability="90" name="qmsg-subst-example" type="ts_qmsg">
          <setdynvars sourcetype="random_number" start="3" end="32">
            <var name="random_uid"></var>
          </setdynvars>
          <setdynvars sourcetype="random_string" length="13">
            <var name="random_txt"></var>
          </setdynvars>
          <request subst="true">
            <qmsg uid="%%_random_uid%%" ack="parse">Haha : %%_random_txt%%</qmsg>
          </request>
          <thinktime value="6"></thinktime>
          <request subst="true">
            <qmsg uid="%%_random_uid%%" ack="parse">This is a Tsung Plugin</qmsg>
          </request>
        </session>
      </sessions>
    </tsung>
```

这部分内容，请参考 tsung_plugin_demo/tsung_qmsg.xml 文件。

#### 3. 执行压力测试

当Qmsg的压力测试配置文件写好之后，可以开始执行压力测试了：

    tsung -f tsung_qmsg.xml start
    

其输出：

    tarting Tsung
    Log directory is: /root/.tsung/log/20160621-1334
    [os_mon] memory supervisor port (memsup): Erlang has closed
    [os_mon] cpu supervisor port (cpu_sup): Erlang has closed
    

其中, 其日志为：/root/.tsung/log/20160621-1334。

#### 4. 查看压测报告

进入其生成压测日志目录，然后生成报表，查看压测结果哈：

    cd /root/.tsung/log/20160621-1334
    
    /usr/local/lib/tsung/bin/tsung_stats.pl
    
    echo "open your browser (URL: http://IP:8000/report.html) and vist the report now :))"
    /usr/bin/python -m SimpleHTTPServer
    

嗯，打开你的浏览器，输出所在服务器的IP地址，就可以看到压测结果了。

### 小结

以上代码已经放入github仓库：[https://github.com/weibomobile/tsung_plugin_demo][5]。

实际业务的私有协议内容要比上面Demo出来的Qmsg复杂的多，但其私有协议插件编写，如上面所述几个步骤，按照规范编写，单机测试，然后延伸到分布式集群，完整流程都是一致的。

嗯，搞定了插件，就可以对系统愉快地进行压测了 :))

[0]: http://www.blogjava.net/yongboy/archive/2016/07/30/431396.html
[1]: ./img/Tsung-Controller-Support-Plugins-V2-1-1.png
[2]: http://my.oschina.net/hncscwc/home
[3]: ./img/193300_43Vz_184909.jpg
[4]: ./img/qmsg_protocol-1.png
[5]: https://github.com/weibomobile/tsung_plugin_demo
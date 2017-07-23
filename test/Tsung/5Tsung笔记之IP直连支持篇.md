## [Tsung笔记之IP直连支持篇][0]

### 前言

前面说到设计一个小型的C/S类型远程终端套件以替换SSH，并且已经应用到线上。这个问题，其实不是Tsung自身的问题，是外部连接依赖问题。

Tsung在启动分布式压测时，主节点tsung_controller要连接的从机必须要填写主机名，主机名没有内网DNS服务器支持解析的情况下(我所经历互联网公司很少有提供支持的)，只好费劲在/etc/hosts文件中填写主机名称和IP地址的映射关系，颇为麻烦，尤其是要添加一批新的压测从机或从机变动频率较大时。

那么如何解决这些问题呢，让tsung在复杂的机房内网环境下，完全基于IP进行直连，这将是本文所讨论的内容。

### 预备知识

#### 完全限定域名

完全限定域名，缩写为FQDN (fully qualified domain name)，[赛门铁克给出的中文定义][1]：

> 一种用于指定计算机在域层次结构中确切位置的明确域名。  
> 一台特定计算机或主机的完整 Internet 域名。FQDN 包括两部分：主机名和域名。例如 mycomputer.mydomain.com。  
> 一种包含主机名和域名（包括顶级域）的 URL。例如，www.symantec.com 是完全限定域名。其中 www 是主机，symantec 是二级域，.com 是顶级域。FQDN 总是以主机名开始且以顶级域名结束，因此 [www.sesa.symantec.com][2] 也是一个 FQDN。

若机器主机名为内网域名形式，并且支持DNS解析，方便其它服务器可通过该主机名直接找到对应IP地址，能够 ping -c 3 机器域名 通，那么机器之间能够容易找到对方。

服务器hostname的命名，若不是域名形式，简短名称形式，比如“yk_mobile_dianxin_001”，一般内网的DNS服务器不支持解析，机器之间需要互相在/etc/hosts文件建立彼此IP地址映射关系才能够互相感知对方。

#### Erlang节点名称的规则

因为Tsung使用Erlang编写，Erlang关于节点启动名称规定，也是Tsung需要面对的问题。

Erlang节点名称一般需要遵循两种格式：

1. 一般名称（也称之为短名称）形式，不包含“.”字符，比如 erl -name tsun_node
1. 完全限定域名形式 
  
    * 域名形式，比如erl -name tsun_node.youdomain.com
    * IP形式，比如erl -name 10.10.10.103

Tsung处理方式：

* 若非特别指定，一般默认为短名称形式
* 启动时可以通过-F参数指定使用完全限定域名形式

#### 获得IP地址

主机名称无论是完全限定域名形式，还是简单的短名称形式，当别的主机需要通过主机名访问时，系统层面需要通过DNS系统解析成IP地址才能够进行网络连接。当内网DNS能够解析出来IP来，没有什么担心的；（短名称）解析不出来时，多半会通过写入到系统的 /etc/hosts 文件中，这样也能够解析成功。

一般机房内网环境，主机名称大都是短名称形式，若需分布式，每一个主机之间都要能够互相联通，最经济做法就是直接使用IP地址，可避免写入大量映射到 hosts 文件中，也会避免一些隐患。

### 主节点启动增加IP支持

默认情况下，Tsung Master主节点名称类似于tsung_controller@主机名：

* 节点名称前缀默认为：tsung_controller （除非在tsung启动时通过-i指定前缀）
* 一般主机名都是字符串形式（hostname命令可设置主机名）
* 可将主机名称设置为本机IP，但不符合人类认知惯性

既然Tsung主节点默认对IP节点名称支持不够，改造一下tsung/tsung.sh.in脚本。

Tsung启动时-F参数为指定使用**完全限定域名(FQDN)**形式，不支持携带参数。若要直接传递IP地址，类似于：

> -F Your_IP

修改tsung.sh.in，可以传递IP地址，手动组装节点名称：

    F) NAMETYPE="-name"
        SERVER_IP=$OPTARG
        if [ "$SERVER_IP" != "" ]; then
            CONTROLLER_EXTENDS="@$SERVER_IP"
        fi
        ;;
    

修改不复杂，更多细节请参考：[https://github.com/weibomobile/tsung/blob/master/tsung.sh.in][3]

启动Tsung时，指定本地IP：

    tsung -F 10.10.10.10 -f tsung.xml start
    

tsung_controller目前节点名称已经变为：

> -name tsung_controller@10.10.10.10

嗯，目标达成。

### 从节点主机增加IP配置

给出一个节点client50配置：

    <client host="client50"  maxusers="100000" cpu="7" weight="4">
        <ip value="10.10.10.50"></ip>
        <ip value="10.10.10.51"></ip>
    </client>
    

Tsung Master想访问client50，需要提前建立client50与IP地址的映射关系：

    echo "10.10.10.50 client50" >> /etc/hosts
    

host属性默认情况下只能填写长短名称，无法填写IP地址，为了兼容已有规则，修改tsung-1.0.dtd文件为client元素新增一个hostip属性：

    <!ATTLIST client
         cpu      NMTOKEN "1"
         type     (machine | batch)  "machine"
         host     NMTOKEN #IMPLIED
         hostip   CDATA ""
         batch    (torque | pbs | lsf | oar) #IMPLIED
         scan_intf NMTOKEN #IMPLIED
         maxusers NMTOKEN "800"
         use_controller_vm (true | false) "false"
         weight   NMTOKEN "1">
    

修改src/tsung_controller/ts_config.erl文件，增加处理逻辑，只有当主节点主机名为IP时才会取hostip作为主机名：

    {ok, MasterHostname} = ts_utils:node_to_hostname(node()),
    case {ts_utils:is_ip(MasterHostname), ts_utils:is_ip(Host), ts_utils:is_ip(HostIP)} of
       %% must be hostname and not ip:
        {false, true, _} ->
            io:format(standard_error,"ERROR: client config: 'host' attribute must be a hostname, "++ "not an IP ! (was ~p)~n",[Host]),
            exit({error, badhostname});
        {true, true, _} ->
            %% add a new client for each CPU
            lists:duplicate(CPU,#client{host     = Host,
                                        weight   = Weight/CPU,
                                        maxusers = MaxUsers});
        {true, _, true} ->
            %% add a new client for each CPU
            lists:duplicate(CPU,#client{host     = HostIP,
                                        weight   = Weight/CPU,
                                        maxusers = MaxUsers});
        {_, _, _} ->
            %% add a new client for each CPU
            lists:duplicate(CPU,#client{host     = Host,
                                        weight   = Weight/CPU,
                                        maxusers = MaxUsers})
    end
    

嗯，现在可以这样配置从节点了，不用担心Tsung启动时是否附加-F参数了：

    <client host="client50" hostip="10.10.10.50" maxusers="100000" cpu="7" weight="4">
        <ip value="10.10.10.50"></ip>
        <ip value="10.10.10.51"></ip>
    </client>
    

其实，只要你确定只使用主节点主机名为IP地址，可以直接设置host属性值为IP值，可忽略hostip属性，但这以牺牲兼容性为代价的。

    <client host="10.10.10.50" maxusers="100000" cpu="7" weight="4">
        <ip value="10.10.10.50"></ip>
        <ip value="10.10.10.51"></ip>
    </client>
    

为了减少/etc/hosts大量映射写入，还是推荐全部IP形式，这种形式适合Tsung分布式集群所依赖服务器的快速租赁模型。

### 源码地址

针对Tsung最新代码增加的IP直连特性所有修改，已经放在github上：

[https://github.com/weibomobile/tsung][4] 。

并且已经递交pull request： [https://github.com/processone/tsung/pull/189][5] 。

比较有意思的是，有这样一条评论：

![][6]

￼

#### 针对Tsung 1.6.0修改版

最近一次发行版是tsung 1.6.0，这个版本比较稳定，我实际压测所使用的就是在此版本上增加IP直连支持（如上所述），已经被单独放入到github上：

[https://github.com/weibomobile/tsung-1.6.0][7]

至于如何安装，git clone到本地，后面就是如何编译tsung的步骤了，不再累述。

### 小结

若要让IP直连特性生效，再次说明启用步骤一下：

1. tsung.xml文件配置从机hostip属性，或host属性，填写正确IP
1. tsung启动时，指定本机可用IP地址：tsung -F Your_Available_IP -f tsung.xml ... start

IP直连，再配合前面所写SSH替换方案，可以让Tsung分布式集群在复杂网络机房内网环境下适应性向前迈了一大步。

[0]: http://www.blogjava.net/yongboy/archive/2016/07/28/431354.html
[1]: https://www.symantec.com/zh/cn/security_response/glossary/define.jsp?letter=f&word=fqdn-fully-qualified-domain-name
[2]: http://www.sesa.symantec.com
[3]: https://github.com/weibomobile/tsung/blob/master/tsung.sh.in
[4]: https://github.com/weibomobile/tsung
[5]: https://github.com/processone/tsung/pull/189
[6]: ./img/14696293372281.jpg
[7]: https://github.com/weibomobile/tsung-1.6.0
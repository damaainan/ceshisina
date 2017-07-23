## [Tsung笔记之分布式增强跳出SSH羁绊篇][0]

### 前言

Erlang天生支持分布式环境，Tsung框架的分布式压测受益于此，简单轻松操控子节点生死存亡、派发任务等不费吹灰之力。

Tsung启动分布式压测时，主节点tsung_controller默认情况下需要通过SSH通道连接到远程机器上启动从节点，那么问题便来了，一般互联网公司基于跳板/堡垒机/网关授权方式访问机房服务器，那么SSH机制失效，并且被明令禁止。SSH不通，Tsung主机启动不了从机，分布式更无从谈起。

那么如何解决这个问题呢，让tsung在复杂的机房网络环境设定下更加如鱼得水，将是本文所讨论的内容。 

### RSH：Remote Shell

RSH，remote shell缩写，维基百科上英文解释：[https://en.wikipedia.org/wiki/Remote_Shell][1]。作为一个终端工具，Linux界鸟哥曾经写过 [RSH客户端和服务器端搭建教程][2]。

在CentOS下安装也简单：

    yum install rsh
    

Erlang借助于rsh命令行工具通过SSH通道连接到从节点启动Tsung应用，下面可以看到rsh工具本身失去了原本的含义，类似于exec命令功效。

比如Erlang主节点（假设这个服务器名称为node_master，并且已经在/etc/hosts文件建立了IP地址映射）在启动时指定rsh的可选方式为SSH：

    erl -rsh ssh -sname foo -setcookie mycookie
    

启动之后，要启动远程主机节点名称为node_slave的子节点：

    slave:start(node_slave, bar, "-setcookie mycookie").
    

上面Erlang启动从节点函数，最终被翻译为可执行的shell命令：

    ssh node_slave erl -detached -noinput -master foo@node_master -sname bar@node_slave -s slave slave_start foo@node_master slave_waiter_0 -setcookie mycookie
    

> erl命令Erlang的启动命令，要求主机node_slave自身也要安装了Erlang的运行时环境才行。

从节点的启动命令最终依赖于SSH连接并远程执行，其通用一般格式为：

    ssh HOSTNAME/IP Command
    

这就是基于Erlang构建的Tsung操控从节点启动的最终实现机制。

> 其它语言中，Master启动Slave也是如此机制

### SSH为通用方案，但不是最好的方案

业界选用[SSH][3]机制连接远程Unix/Linux服务器主机，分布式环境下要能够自由免除密码方式启动远程主机上（这里指的是内部Lan环境）应用，一般需要设置公钥，需要传递公钥，需要保存到各自机器上，还有经常遇到权限问题，很是麻烦，这是其一。若要取消某台服务器登陆授权，则需要被动修改公钥，也是不够灵活。

另外一般互联网公司处于安全考虑都会禁止公司内部人员直接通过SSH方式登录到远程主机进行操作，这样导致SSH通道失效，Tsung主机通过SSH连接到从机并执行命令，也就不可能了。

其实，在基于分布式压测环境下，快速租赁、快速借用/归还的模型就很适合。一般公司很少会存在专门用于压测的大量空闲机器，但是线上会运行着当前负载不高的服务器，可以拿来用作压测客户端使用，用完就归还。因为压测不会是长时间运行的服务，其为短时间行为。这种模式下就不适合复杂的SSH公钥满天飞，后期忘记删除的情况，在压测端超多的情况下，无疑也将造成运维成本激增，安全性降低等问题。

### SSH替换方案：一种快速租赁模式远程终端方案

现在需要寻找一种新的代替方案，一种适应快速租赁的远程终端实现机制。

#### 替换方案要求点

1. 类似于SSH Server，监听某个端口，能够执行传递过来的命令
1. 能够根据IP地址授权，这样只有Tsung Master才能够访问从节点，从节点之间无法直接对连
1. 需要接受一些操控指令，可以判断是否存活
1. 一到两个脚本/程序搞定，尽量避免安装，开箱即用
1. 总之配置、操作一定要简单，实际运维成本一定要低

没找到很轻量的实现，可以设计并实现这样一种方案。

#### 服务器端守护进程

轻量级服务端守护进程 = 一个监控端口的进程（rsh_daemon.sh） + 执行命令过滤功能(rsh_filter)

rsh_daemon.sh 负责守护进程的管理：

* 基于CentOS 6/7默认安装的ncat程序
* 主要用于管理19999端口监听
* start/stop/restart 负责监控进程启动、关闭
* status 查看进程状态
* kill 提供手动方式关闭并删除掉自身
* rsh_filter用于检测远程传入命令并进行处理 
  
    * 接收ping指令，返回pong
    * 执行Erlang从节点命令，并返回 done 字符串
    * 对不合法命令，直接关闭

rsh_daemon.sh代码很简单：

```shell
    #!/bin/bash
    # the script using for start/stop remote shell daemon server to replace the ssh server
    PORT=19999
    FILTER=~/tmp/_tmp_rsh_filter.sh
    # the tsung master's hostname or ip
    tsung_controller=tsung_controller
    SPECIAL_PATH=""
    PROG=`basename $0`
    
    prepare() {
        cat << EOF > $FILTER
    #!/bin/bash
    
    ERL_PREFIX="erl"
    
    while true
    do
        read CMD
        case \$CMD in
            ping)
                echo "pong"
                exit 0
                ;;
            *)
                if [[ \$CMD == *"\${ERL_PREFIX}"* ]]; then
                    exec $SPECIAL_PATH\${CMD}
                fi
                exit 0
                ;;
        esac
    done
    EOF
        chmod a+x $FILTER
    }
    
    start() {
        NUM=$(ps -ef|grep ncat | grep ${PORT} | grep -v grep | wc -l)
    
        if [ $NUM -gt 0 ];then
            echo "$PROG already running ..."
            exit 1
        fi
    
        if [ -x "$(command -v ncat)" ]; then
            echo "$PROG starting now ..."
            ncat -4 -k -l $PORT -e $FILTER --allow $tsung_controller &
        else
            echo "no exists ncat command, please install it ..."
        fi
    }
    
    stop() {
        NUM=$(ps -ef|grep ncat | grep rsh | grep -v grep | wc -l)
    
        if [ $NUM -eq 0 ]; then
            echo "$PROG had already stoped ..."
        else
            echo "$PROG is stopping now ..."
            ps -ef|grep ncat | grep rsh | grep -v grep | awk '{print $2}' | xargs kill
        fi
    }
    
    status() {
        NUM=$(ps -ef|grep ncat | grep rsh | grep -v grep | wc -l)
    
        if [ $NUM -eq 0 ]; then
            echo "$PROG had already stoped ..."
        else
            echo "$PROG is running ..."
        fi
    }
    
    usage() {
        echo "Usage: $PROG <options> start|stop|status|restart"
        echo "Options:"
        echo "    -a <hostname/ip>  allow only given hosts to connect to the server (default is tsung_controller)"
        echo "    -p <port>         use the special port for listen (default is 19999)"
        echo "    -s <the_erl_path> use the special erlang's erts bin path for running erlang (default is blank)"
        echo "    -h                display this help and exit"
        exit
    }
    
    while getopts "a:p:s:h" Option
    do
        case $Option in
            a) tsung_controller=$OPTARG;;
            p) PORT=$OPTARG;;
            s) TMP_ERL=$OPTARG
                if [ "$OPTARG" != "" ]; then
                    if [[ "$OPTARG" == *"/" ]]; then
                        SPECIAL_PATH=$OPTARG
                    else
                        SPECIAL_PATH=$OPTARG"/"
                    fi
                fi
                ;;
            h) usage;;
            *) usage;;
        esac
    done
    shift $(($OPTIND - 1))
    
    case $1 in
            start)
                prepare
                start
                ;;
            stop)
                stop
                ;;
            status)
                status
                ;;
            restart)
                stop
                start
                ;;
            *)
                usage
                ;;
    esac
```

总结一下：

* 基于ncat监听19999端口提供bind shell机制，但限制有限IP可访问
* 动态生成命令过滤脚本rsh_filter.sh，执行Erlang从节点命令

请参考：[https://github.com/weibomobile/tsung_rsh/blob/master/rsh_daemon.sh][4]

### 客户端连接方案

服务器端已经提供了端口接入并准备好了接收指令，客户端（rsh_client.sh）可以进行连接和交互了：

* 类似SSH客户端接收方式：rsh_client.sh Host/IP Command
* 完全基于nc命令，连接远程主机
* 连接成功，发送命令
* 得到相应，流程完成

一样非常少的代码呈现。

```shell
    #!/bin/sh
    
    PORT=19999
    
    if [ $# -lt 2  ]; then
        echo "Invalid number of parameters"
        exit 1
    fi
    
    REMOTEHOST="$1"
    COMMAND="$2"
    
    if [ "${COMMAND}" != "erl"  ]; then
        echo "Invalid command ${COMMAND}"
        exit 1
    fi
    
    shift 2
    
    echo "${COMMAND} $*" | /usr/bin/nc ${REMOTEHOST} ${PORT}
    
```


### Erlang主节点如何启动

有了SSH替换方案，那主节点就可以这样启动了：

    erl -rsh ~/.tsung/rsh_client.sh -sname foo -setcookie mycookie
    

比如当Tsung需要连接到另外一台服务器上启动从节点时，它最终会翻译成下面命令：

    /bin/sh /root/.tsung/rsh_client.sh node_slave erl -detached -noinput -master foo@node_master -sname bar@node_slave -s slave slave_start foo@node_master slave_waiter_0 -setcookie mycookie
    

客户端脚本rsh_client.sh则最终需要执行连接到服务器、并发送命的命令：

    echo "erl -detached -noinput -master foo@node_master -sname bar@node_slave -s slave slave_start foo@node_master slave_waiter_0 -setcookie mycookie" | /usr/bin/nc node_slave 19999
    

这样就实现了和SSH一样的功能了，很简单吧。

### Tsung如何切换切换？

为tsung启动添加-r参数指定即可：

    tsung -r ~/.tsung/rsh_client.sh -f tsung.xml start
    

### 进阶：可指定运行命令路径

rsh_client.sh脚本最后一行修改一下，指定目标服务器erl运行命令：

```shell
    #!/bin/sh
    
    PORT=19999
    
    if [ $# -lt 2  ]; then
        echo "Invalid number of parameters"
        exit 1
    fi
    
    REMOTEHOST="$1"
    COMMAND="$2"
    
    if [ "${COMMAND}" != "erl"  ]; then
        echo "Invalid command ${COMMAND}"
        exit 1
    fi
    
    shift 2
    exec echo "/root/.tsung/otp_18/bin/erl $*" | /usr/bin/nc ${REMOTEHOST} 19999
    
```

上面脚本所依赖的上下文环境可以是这样的，机房服务器操作系统和版本一致，我们把Erlang 18.1整个运行时环境在一台机器上已经安装的目录（比如目录名为otp_18），拷贝到远程主机/root/.tsung/目录，相比于安装而言，可以让Tsung运行依赖的Eralng环境完全可以移植化（Portable），一次安装，多次复制。

### 代码托管地址

本文所谈及代码，都已经托管在github：  
[https://github.com/weibomobile/tsung_rsh][5]

后续代码更新、BUG修复等，请直接参考该仓库。

### 小结

简单一套新的替换SSH通道无密钥登陆远程主机C/S模型，虽然完整性上无法与SSH相比，但胜在简单够用，完全满足了当前业务需要，并且其运维成本低，无疑让Tsung在复杂服务器内网环境下适应性又朝前多走了半里路。

下一篇将介绍为Tsung增加IP直连特性支持，使其分布式网络环境下适应性更广泛一些。

[0]: http://www.blogjava.net/yongboy/archive/2016/07/27/431340.html
[1]: https://en.wikipedia.org/wiki/Remote_Shell
[2]: http://linux.vbird.org/linux_server/0310telnetssh/0310telnetssh-centos4.php#rsh
[3]: https://zh.wikipedia.org/wiki/Secure_Shell
[4]: https://github.com/weibomobile/tsung_rsh/blob/master/rsh_daemon.sh
[5]: https://github.com/weibomobile/tsung_rsh
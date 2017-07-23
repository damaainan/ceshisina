## [Tsung笔记之压测端资源限制篇][0]

### 前言

这里汇集一下影响tsung client创建用户数的各项因素。因为Tsung是IO密集型的应用，CPU占用一般不大，为了尽可能的生成更多的用户，需要考虑内存相关事宜。

### IP & 端口的影响

#### 1. 系统端口限制

Linux系统端口为short类型表示，数值上限为65535。假设分配压测业务可用端口范围为1024 - 65535，不考虑可能还运行着其它对外连接的服务，真正可用端口也就是64000左右（实际上，一般为了方便计算，一般直接设定为50000）。换言之，即在一台机器上一个IP，可用同时对外建立64000网络连接。

若是N个可用IP，理论上 64000*N，实际上还需要满足：

* 充足内存支持 
    
    * tcp接收/发送缓冲区不要设置太大，tsung默认分配32K（可以修改成16K，一般够用了）
    * 一个粗略估算一个连接占用80K内存，那么10万用户，将占用约8G内存
* 为多IP的压测端分配适合的权重，以便承担更多的终端连接

另外还需要考虑端口的快速回收等，可以这样做：

    sysctl -w net.ipv4.tcp_syncookies=1
    sysctl -w net.ipv4.tcp_tw_reuse=1
    sysctl -w net.ipv4.tcp_tw_recycle=1
    sysctl -w net.ipv4.tcp_fin_timeout=30
    sysctl -w net.ipv4.ip_local_port_range="1024 65535"
    
    sysctl -p
    

> 若已经在 /etc/sysctl.conf 文件中有记录，则需要手动修改

作为附加，可设置端口重用：

    
    

注意，不要设置下面的可用端口范围：

    
    

因为操作系统会自动跳过已经被占用本地端口，而Tsung只能够被动通过错误进行可用端口+1继续下一个连接，有些多余。

#### 2. IP和端口组合

每一个client支持多个可用IP地址列表

    
        
        
    
    

tsung client从节点开始准备建立网络连接会话时，需要从tsung_controller主节点获取具体的会话信息，其中就包含了客户端连接需要使用到来源{LocalIP， LocalPort}二元组。由tsung_controller主节点完成。

    get_user_param(Client,Config)->
        {ok, IP} = choose_client_ip(Client),
        {ok, Server} = choose_server(Config#config.servers, Config#config.total_server_weights),
        CPort = choose_port(IP, Config#config.ports_range),
        {{IP, CPort}, Server}.
    
    choose_client_ip(#client{ip = IPList, host=Host}) ->
        choose_rr(IPList, Host, {0,0,0,0}).
    
    ......
    
    choose_client_ip(#client{ip = IPList, host=Host}) ->
        choose_rr(IPList, Host, {0,0,0,0}).
    
    choose_rr(List, Key, _) ->
        I = case get({rr,Key}) of
              undefined -> 1 ; % first use of this key, init index to 1
              Val when is_integer(Val) ->
                (Val rem length(List))+1 % round robin
        end,
        put({rr, Key},I),
        {ok, lists:nth(I, List)}.
    
    %% 默认不设置 ports_range 会直接返回0
    %% 不建议设置 
    %% 因为这样存在端口冲突问题，除非确实不存被占用情况
    choose_port(_,_, undefined) ->
        {[],0};
    choose_port(Client,undefined, Range) ->
        choose_port(Client,dict:new(), Range);
    choose_port(ClientIp,Ports, {Min, Max}) ->
        case dict:find(ClientIp,Ports) of
            {ok, Val} when Val =< Max ->
                NewPorts=dict:update_counter(ClientIp,1,Ports),
                {NewPorts,Val};
            _ -> % Max Reached or new entry
                NewPorts=dict:store(ClientIp,Min+1,Ports),
                {NewPorts,Min}
        end.
    

从节点建立到压测服务器连接时，就需要指定从主节点获取到的本机IP地址和端口两元组：

    Opts = protocol_options(Protocol, Proto_opts)  ++ [{ip, IP},{port,CPort}],
    ......
    gen_tcp:connect(Server, Port, Opts, ConnectTimeout).
    

#### 3. IP自动扫描特性

若从机单个网卡绑定了多个IP，又懒于输入，可以配置扫描特性:

    
    

本质上使用shell方式获取IP地址，并且支持CentOS 6/7。

        /sbin/ip -o -f inet addr show dev eth0
    

> 因为扫描比较慢，Tsung 1.6.1推出了ip_range特性支持。

### Linux系统打开文件句柄限制

系统打开文件句柄，直接决定了可以同时打开的网络连接数量，这个需要设置大一些，否则，你可能会在[tsung_controller@IP.log][1]文件中看到error_connect_emfile类似文件句柄不够使用的警告，建议此值要大于 > N * 64000。

    echo "* soft nofile 300000" >> /etc/security/limits.conf
    echo "* hard nofile 300000" >> /etc/security/limits.conf
    

或者，在Tsung会话启动脚本文件中明确添加上ulimit -n 300000。

### 内存的影响

一个网络Socket连接占用不多，但上万个或数十万等就不容小觑了，设置不当会导致内存直接成为屏障。

#### 1. TCP接收、发送缓存

Tsung默认设置的网络Socket发送接收缓冲区为16KB，一般够用了。

以TCP为例，某次我手误为Tcp接收缓存赋值过大(599967字节)，这样每一个网络了解至少占用了0.6M内存，直接导致在16G内存服务上网络连接数到2万多时，内存告急。

    
    
    

此值会覆盖Linux系统设置接收、发送缓冲大小。

粗略的默认值计算，一个网络连接发送缓冲区 + 接收缓冲区，再加上进程处理连接堆栈占用，约40多K内存，为即计算方便，设定建立一个网络连接消费50K内存。

先不考虑其它因素，若我们想要从机模拟10W个用户，那么当前可用内存至少要剩余：50K * 100000 / 1000K = 5000M = 5G内存。针对一般服务器来讲，完全可满足要求（剩下事情就是要有两个可用IP了）。

#### 2. Erlang函数堆栈内存占用

使用Erlang程序写的应用服务器，进程要存储堆栈调用信息，进程一多久会占用大量内存，想要服务更多网络连接/任务，需要将不活动的进程设置为休眠状态，以便节省内存，Tsung的压测会话信息若包含thinktime时间，也要考虑启用hibernate休眠机制。

    
    

值单位秒，默认thinktime超过10秒后自动启动，这里修改为5秒。

### XML文件设置需要注意部分

#### 1. 日志等级要调高一些

tsung使用error_logger记录日志，其只适用于真正的异常情况，若当一般业务调试类型日志量过多时，不但耗费了大量内存，网络/磁盘写入速度跟不上生产速度时，会导致进程堵塞，严重会拖累整个应用僵死，因此需要在tsung.xml文件中设置日志等级要高一些，至少默认的notice就很合适。

#### 2. 不要启用dump

dump是一个耗时的行为，因此默认为false，除非很少的压测用户用于调试。

#### 3. 动态属性太多，会导致请求超时

```
<option name="file_server" id="userdb" value="/your_path/100w_users.csv"/>

...

<setdynvars sourcetype="file" fileid="userdb" delimiter=";" order="iter">
    <var name="userid" />
    <var name="nickname" />
</setdynvars>

...

<request subst="true">
    <yourprotocol type="hello" uid="%%_userid%%" ack="local">
        Hello, I'm %%_nickname%%
    </yourprotocol>
</request>
```

设定一个有状态的场景，用户ID储存在文件中，每一次会话请求都要从获取到用户ID，压测用户一旦达到百万级别并且用户每秒产生速率过大（比如每秒1000个用户），会经常遇到超时错误：

    =ERROR REPORT==== 25-Jul-2016::15:14:11 ===
    ** Reason for termination =
    ** {timeout,{gen_server,call,
                            [{global,ts_file_server},{get_next_line,userdb}]}}
    

这是因为，当tsung client遇到setdynvars指令时，会直接请求主机ts_file_server模块，当一时间请求量巨大，可能会造成单一模块处理缓慢，出现超时问题。

怎么办：

1. 降低用户每秒产生速率，比如300秒用户生成
1. 不用从文件中存储用户id等信息，采用别的方式

### 如何限流/限速

某些时候，要避免tsung client压测端影响所在服务器网络带宽IO太拥挤，需要限制流量，其采用令牌桶算法。

    
    

* 值为KB单位每秒
* 目前仅对传入流量生效

阀值计算方式：

    {RateConf,SizeThresh} = case RateLimit of
                                Token=#token_bucket{} ->
                                    Thresh=lists:min([?size_mon_thresh,Token#token_bucket.burst]),
                                    {Token#token_bucket{last_packet_date=StartTime}, Thresh};
                                undefined ->
                                    {undefined, ?size_mon_thresh}
               end,
    

接收传入流量数据，需要计算：

    handle_info2({gen_ts_transport, _Socket, Data}, wait_ack, State=#state_rcv{rate_limit=TokenParam}) when is_binary(Data)->
        ?DebugF("data received: size=~p ~n",[size(Data)]),
        NewTokenParam = case TokenParam of
                            undefined ->
                                undefined;
                            #token_bucket{rate=R,burst=Burst,current_size=S0, last_packet_date=T0} ->
                                {S1,_Wait}=token_bucket(R,Burst,S0,T0,size(Data),?NOW,true),
                                TokenParam#token_bucket{current_size=S1, last_packet_date=?NOW}
                        end,
        {NewState, Opts} = handle_data_msg(Data, State),
        NewSocket = (NewState#state_rcv.protocol):set_opts(NewState#state_rcv.socket,
                                                           [{active, once} | Opts]),
        case NewState#state_rcv.ack_done of
            true ->
                handle_next_action(NewState#state_rcv{socket=NewSocket,rate_limit=NewTokenParam,
                                                      ack_done=false});
            false ->
                TimeOut = case (NewState#state_rcv.request)#ts_request.ack of
                    global ->
                        (NewState#state_rcv.proto_opts)#proto_opts.global_ack_timeout;
                    _ ->
                        (NewState#state_rcv.proto_opts)#proto_opts.idle_timeout
                end,
                {next_state, wait_ack, NewState#state_rcv{socket=NewSocket,rate_limit=NewTokenParam}, TimeOut}
        end;
    

下面则是具体的令牌桶算法：

    %% @spec token_bucket(R::integer(),Burst::integer(),S0::integer(),T0::tuple(),P1::integer(),
    %%                    Now::tuple(),Sleep::boolean()) -> {S1::integer(),Wait::integer()}
    
    %% @doc Implement a token bucket to rate limit the traffic: If the
    %%      bucket is full, we wait (if asked) until we can fill the
    %%      bucket with the incoming data
    %%      R = limit rate in Bytes/millisec, Burst = max burst size in Bytes
    %%      T0 arrival date of last packet,
    %%      P1 size in bytes of the packet just received
    %%      S1: new size of the bucket
    %%      Wait: Time to wait
    %% @end
    token_bucket(R,Burst,S0,T0,P1,Now,Sleep) ->
        S1 = lists:min([S0+R*round(ts_utils:elapsed(T0, Now)),Burst]),
        case P1 < S1 of
            true -> % no need to wait
                {S1-P1,0};
            false -> % the bucket is full, must wait
                Wait=(P1-S1) div R,
                case Sleep of
                    true ->
                        timer:sleep(Wait),
                        {0,Wait};
                    false->
                        {0,Wait}
                end
        end.
    

### 小结

以上简单梳理一下影响tsung从机创建用户的各项因素，实际环境其实相当复杂，需要一一对症下药才行。

[0]: http://www.blogjava.net/yongboy/archive/2016/07/26/431322.html
[1]: mailto:tsung_controller@IP.log
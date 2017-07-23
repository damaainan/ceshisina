## [Tsung笔记之主从资源协调篇][0]

### 前言

接着上文，tsung一旦启动，主从节点之间需要协调分配资源，完成分布式压测任务。

### 如何启动Tsung压测从机

Erlang SDK提供了从机启动方式：

    slave:start(Host, Node, Opts)
    

启动从机需要借助于免登陆形式远程终端，比如SSH（后续会讨论SSH存在不足，以及全新的替代品），需要自行配置。

    <client host="client_100" maxusers="60000" weight="1">
        <ip value="10.10.10.100"></ip>
    </client>
    

* host属性对应value为从机主机名称：**client_100**
* Node节点名称由`tsung_controller`组装，类似于 tsung10@client_100
* Opts表示相关参数
* 一个物理机器，可以存在多个tsung从机实例
* 一个tsung从机实例对应一个tsung client

简单翻译一下：slave:start(client_100, 'tsung10@client_100', Opts)从机需要关闭时，就很简单了：

    slave:stop(Node)
    

当然若主机中途挂掉，从机也会自动自杀掉自身。

#### 启动tsung client方式

Tsung主机启动从机成功，从机和主机就可以Erlang节点进程之间进行方法调用和消息传递。潜在要求是，tsung编译后beam文件能够在Erlang运行时环境中能够访问到，这个和Java Classpath一致原理。

    rpc:multicall(RemoteNodes,tsung,start,[],?RPC_TIMEOUT)
    

到此为止，一个tsung client实例成功运行。

* tsung client实例生命周期结束，不会导致从机实例主动关闭
* tsung slave提供了运行时环境，tsung client是业务
* tsung slave和tsung client关系是1 : 1关系，很多时候为了理解方便，不会进行严格区分

### 压测目标

明白了主从启动方式，下面讨论压测目标，比如50万用户的量，根据给出的压测从机列表，进行任务分配。

#### 压测目标配置

tsung压测xml配置文件，load元素可以配置总体任务生成的信息。

    <load>
        <arrivalphase phase="1" duration="60" unit="minute">
            <!--users maxnumber="500000" interarrival="0.004" unit="second"></users-->
            <users maxnumber="500000" arrivalrate="250" unit="second"></users>
        </arrivalphase>
    </load>
    

* 定义一个最终压力产生可以持续60分钟压测场景， 上限用户量为50万
* `arrivalphase duration`属性持续时长表示生成压测用户可消费总体时间60分钟，即为T1
* users元素其属性表示单位时间内（这里单位时间为秒）产生用户数为250个
* 50万用户，将在2000秒(约34分钟)内生成，耗时时长即为T2
* T2小于`arrivalphase`定义的用户生成阶段持续时间T1
* 若T2时间后（34分钟)后因为产生用户数已经达到了上限，将不再产生新的用户，知道整个压测结束
* 若 T1 小于 T2，则50万用户很难达到，因此T1时间要设置长一些

#### 从节点信息配置

所说从节点也是压测客户端，需要配置clients元素：

    <clients>
        <client host="client_100" maxusers="60000" weight="1">
            <ip value="10.10.10.100"></ip>
        </client>
    
        ......
    
        <client host="client_109" maxusers="120000" weight="2">
            <ip value="10.10.10.109"></ip>
            <ip value="10.10.10.119"></ip>
        </client>
    </clients>
    

1. 单个client支持多个IP，用于突破单个IP对外建立连接数的限制(后续会讲到）
1. xml所定义的一个cliet元素，可能被分裂出若干从机实例(即tsung client)，1 : N

#### 根据CPU数量分裂tsung client实例情况

在《Tsung Documentation》给出了建议，一个CPU一个tsung client实例：

> Note: Even if an Erlang VM is now able to handle several CPUs (erlang SMP), benchmarks shows that it’s more efficient to use one VM per CPU (with SMP disabled) for tsung clients. Only the controller node is using SMP erlang.  
> Therefore, cpu should be equal to the number of cores of your nodes. If you prefer to use erlang SMP, add the -s option when starting tsung (and don’t set cpu in the config file).

* 默认策略, 一个tsung client对应一个CPU，若不设置CPU属性，默认值就是1
* 一个cpu对应一个tsung client，N个CPU，N个tsung client
* 共同分担权重，每一个分裂的tsung client权重 Weight/N
* 一旦设置cpu属性，无论Tsung启动时是否携带-s参数设置共享CPU，都会 

    * 自动分裂CPU个tsung client实例
    * 每一个实例权重为Weight/CPU
```
    %% add a new client for each CPU
    lists:duplicate(CPU,#client{host     = Host,
                                weight   = Weight/CPU,
                                maxusers = MaxUsers})
    
```

若要设置单个tsung client实例共享多个CPU（此时不要设置cpu属性啦），需要在tsung启动时添加-s参数，tsung client被启动时，smp属性被设置成auto：

    -smp auto +A 8
    

这样从机就只有一个tsung client实例了，不会让人产生困扰。若是临时租借从机，建议启动时使用-s参数，并且要去除cpu属性设置，这样才能够自动共享所有CPU核心。

#### 从机分配用户过多，一样会分裂新的tsung client实例

假设client元素配置maxusers数量为1K，那么实际上被分配数量为10K(压测人数多，压测从机少)时，那么tsung_controller会继续分裂新的tsung client实例，直到10K用户数量完成。

    <client host="client_98" maxusers="1000" weight="1">
        <ip value="10.10.10.98"></ip>
    </client>
    

tsung client分配的数量超过自身可服务上限用户时（这里设置的是1K）时，关闭自身。

    launcher(_Event, State=#launcher{nusers = 0, phases = [] }) ->
        ?LOG("no more clients to start, stop  ~n",?INFO),
        {stop, normal, State};
    
    launcher(timeout, State=#launcher{nusers        = Users,
                                      phase_nusers  = PhaseUsers,
                                      phases        = Phases,
                                      phase_id      = Id,
                                      started_users = Started,
                                      intensity     = Intensity}) ->
        BeforeLaunch = ?NOW,
        case do_launch({Intensity,State#launcher.myhostname,Id}) of
            {ok, Wait} ->
                case check_max_raised(State) of
                    true ->
                        %% let the other beam starts and warns ts_mon
                        timer:sleep(?DIE_DELAY),
                        {stop, normal, State};
                    false->
                        ......
                end;
            error ->
                % retry with the next user, wait randomly a few msec
                RndWait = random:uniform(?NEXT_AFTER_FAILED_TIMEOUT),
                {next_state,launcher,State#launcher{nusers = Users-1} , RndWait}
        end.
    

`tsung_controller`接收从节点退出通知，但分配总数没有完成，会启动新的tsung client实例（一样先启动从节点，然后再启动tsung client实例）。整个过程串行方式循环，直到10K用户数量完成：

    %% start a launcher on a new beam with slave module
    handle_cast({newbeam, Host, Arrivals}, State=#state{last_beam_id = NodeId, config=Config, logdir = LogDir}) ->
        Args = set_remote_args(LogDir,Config#config.ports_range),
        Seed = Config#config.seed,
        Node = remote_launcher(Host, NodeId, Args),
        case rpc:call(Node,tsung,start,[],?RPC_TIMEOUT) of
            {badrpc, Reason} ->
                ?LOGF("Fail to start tsung on beam ~p, reason: ~p",[Node,Reason], ?ERR),
                slave:stop(Node),
                {noreply, State};
            _ ->
                ts_launcher_static:stop(Node), % no need for static launcher in this case (already have one)
                ts_launcher:launch({Node, Arrivals, Seed}),
                {noreply, State#state{last_beam_id = NodeId+1}}
        end;
    

### tsung client分配用户数

一个tsung client分配的用户数，可以理解为会话任务数。Tsung以终端可以模拟的用户为维度进行定义压测。

所有配置tsung client元素（设置M1）权重相加之和为总权重TotalWeight，用户总数为MaxMember，一个tsung client实例（总数设为M2）分配的模拟用户数可能为：

    MaxMember*(Weight/TotalWeight)
    

需要注意：  
- **M2 >= M1**  
- 若压测阶段 < `arrivalphase`元素配置duration值过小，小于最终用户50万用户按照每秒250速率耗时时间，最终分配用户数将小于期望值

### 只有一台物理机的tsung master启动方式

    <clients>
      <client host="localhost" use_controller_vm="true"></client>
    </clients>
    

没有物理从机，主从节点都在一台机器上，需要设置`use_controller_vm="true"`。相比tsung集群，单一节点tsung启动就很简单，主从之间不需要SSH通信，直接内部调用。

    local_launcher([Host],LogDir,Config) ->
        ?LOGF("Start a launcher on the controller beam ~p~n", [Host], ?NOTICE),
        LogDirEnc = encode_filename(LogDir),
        %% set the application spec (read the app file and update some env. var.)
        {ok, {_,_,AppSpec}} = load_app(tsung),
        {value, {env, OldEnv}} = lists:keysearch(env, 1, AppSpec),
        NewEnv = [ {debug_level,?config(debug_level)}, {log_file,LogDirEnc}],
        RepKeyFun = fun(Tuple, List) ->  lists:keyreplace(element(1, Tuple), 1, List, Tuple) end,
        Env = lists:foldl(RepKeyFun, OldEnv, NewEnv),
        NewAppSpec = lists:keyreplace(env, 1, AppSpec, {env, Env}),
    
        ok = application:load({application, tsung, NewAppSpec}),
        case application:start(tsung) of
            ok ->
                ?LOG("Application started, activate launcher, ~n", ?INFO),
                application:set_env(tsung, debug_level, Config#config.loglevel),
                case Config#config.ports_range of
                    {Min, Max} ->
                        application:set_env(tsung, cport_min, Min),
                        application:set_env(tsung, cport_max, Max);
                    undefined ->
                        ""
                end,
                ts_launcher_static:launch({node(), Host, []}),
                ts_launcher:launch({node(), Host, [], Config#config.seed}),
                1 ;
            {error, Reason} ->
                ?LOGF("Can't start launcher application (reason: ~p) ! Aborting!~n",[Reason],?EMERG),
                {error, Reason}
        end.
    

### 用户生成控制

#### 用户和会话控制

每一个tsung client运行着一个`ts_launch/ts_launch_static`本地注册模块，掌控终端模拟用户生成和会话控制。

* 向主节点`ts_config_server`请求隶属于当前从机节点的会话信息
* 启动模拟终端用户`ts_client`
* 控制下一个模拟终端用户`ts_client`需要等待时间，也是控制从机用户生成速度
* 执行是否需要切换到新的阶段会话
* 控制模拟终端用户是否已经达到了设置的maxusers上限 
    
    * 到上限，自身使命完成，关闭自身

* 源码位于 tsung-1.6.0/src/tsung 目录下

主机按照xml配置生成全局用户产生速率，从机按照自身权重分配的速率进行单独控制，这也是任务分解的具体呈现。

#### 用户生成速度控制

在Tsung中用户生成速度称之为强度，根据所配置的load属性进行配置

    <load>
        <arrivalphase phase="1" duration="60" unit="minute">
            <users maxnumber="500000" arrivalrate="250" unit="second"></users>
        </arrivalphase>
    </load>
    

关键属性：

* `interarrival`，生成压测用户的时间间隔
* `arrivalrate`：单位时间内生成用户数量
* 两者最终都会被转换为生成用户强度系数值是0.25
* 这个是总的强度值，但需要被各个tsung client分解
```
    parse(Element = #xmlElement{name=users, attributes=Attrs},
          Conf = #config{arrivalphases=[CurA | AList]}) ->
    
        Max = getAttr(integer,Attrs, maxnumber, infinity),
        ?LOGF("Maximum number of users ~p~n",[Max],?INFO),
    
        Unit  = getAttr(string,Attrs, unit, "second"),
        Intensity = case {getAttr(float_or_integer,Attrs, interarrival),
                          getAttr(float_or_integer,Attrs, arrivalrate)  } of
                        {[],[]} ->
                            exit({invalid_xml,"arrival or interarrival must be specified"});
                        {[], Rate}  when Rate > 0 ->
                            Rate / to_milliseconds(Unit,1);
                        {InterArrival,[]} when InterArrival > 0 ->
                            1/to_milliseconds(Unit,InterArrival);
                        {_Value, _Value2} ->
                            exit({invalid_xml,"arrivalrate and interarrival can't be defined simultaneously"})
                    end,
        lists:foldl(fun parse/2,
            Conf#config{arrivalphases = [CurA#arrivalphase{maxnumber = Max,
                                                            intensity=Intensity}
                                   |AList]},
                    Element#xmlElement.content);
    
```
`tsung_controller`对每一个tsung client生成用户强度分解为 `ClientIntensity = PhaseIntensity * Weight / TotalWeight`，而1000 * ClientIntensity就是易读的每秒生成用户速率值。
```
    get_client_cfg(Arrival=#arrivalphase{duration = Duration,
                                         intensity= PhaseIntensity,
                                         curnumber= CurNumber,
                                         maxnumber= MaxNumber },
                   {TotalWeight,Client,IsLast} ) ->
        Weight = Client#client.weight,
        ClientIntensity = PhaseIntensity * Weight / TotalWeight,
        NUsers = round(case MaxNumber of
                           infinity -> %% only use the duration to set the number of users
                               Duration * ClientIntensity;
                           _ ->
                               TmpMax = case {IsLast,CurNumber == MaxNumber} of
                                            {true,_} ->
                                                MaxNumber-CurNumber;
                                            {false,true} ->
                                                0;
                                            {false,false} ->
                                                lists:max([1,trunc(MaxNumber * Weight / TotalWeight)])
                                        end,
                               lists:min([TmpMax, Duration*ClientIntensity])
                       end),
        ?LOGF("New arrival phase ~p for client ~p (last ? ~p): will start ~p users~n",
              [Arrival#arrivalphase.phase,Client#client.host, IsLast,NUsers],?NOTICE),
        {Arrival#arrivalphase{curnumber=CurNumber+NUsers}, {ClientIntensity, NUsers, Duration}}.
```

前面讲到每一个tsung client被分配用户数公式为：`min(Duration * ClientIntensity, MaxNumber * Weight / TotalWeight)`：

* 避免总人数超出限制
* 阶段Phase持续时长所产生用户数和tsung client分配用户数不至于产生冲突，一种协调策略

再看一下launch加载一个终端用户时，会自动根据当前分配用户生成压力系数获得`ts_stats:exponential(Intensity)`下一个模拟用户产生等待生成的最长时间，单位为毫秒。

    do_launch({Intensity, MyHostName, PhaseId})->
        %%Get one client
        %%set the profile of the client
        case catch ts_config_server:get_next_session({MyHostName, PhaseId} ) of
            {'EXIT', {timeout, _ }} ->
                ?LOG("get_next_session failed (timeout), skip this session !~n", ?ERR),
                ts_mon:add({ count, error_next_session }),
                error;
            {ok, Session} ->
                ts_client_sup:start_child(Session),
                X = ts_stats:exponential(Intensity),
                ?DebugF("client launched, wait ~p ms before launching next client~n",[X]),
                {ok, X};
            Error ->
                ?LOGF("get_next_session failed for unexpected reason [~p], abort !~n", [Error],?ERR),
                ts_mon:add({ count, error_next_session }),
                exit(shutdown)
        end.
    

ts_stats:exponential逻辑引入了指数计算：

    exponential(Param) ->
        -math:log(random:uniform())/Param.
    

继续往下看吧，隐藏了部分无关代码：

    launcher(timeout, State=#launcher{nusers        = Users,
                                      phase_nusers  = PhaseUsers,
                                      phases        = Phases,
                                      phase_id      = Id,
                                      started_users = Started,
                                      intensity     = Intensity}) ->
        BeforeLaunch = ?NOW,
        case do_launch({Intensity,State#launcher.myhostname,Id}) of
            {ok, Wait} ->
                                ...
                            {continue} ->
                                Now=?NOW,
                                LaunchDuration = ts_utils:elapsed(BeforeLaunch, Now),
                                %% to keep the rate of new users as expected,
                                %% remove the time to launch a client to the next
                                %% wait.
                                NewWait = case Wait > LaunchDuration of
                                              true -> trunc(Wait - LaunchDuration);
                                              false -> 0
                                          end,
                                ?DebugF("Real Wait = ~p (was ~p)~n", [NewWait,Wait]),
                                {next_state,launcher,State#launcher{nusers = Users-1, started_users=Started+1} , NewWait}
                                ...
            error ->
                % retry with the next user, wait randomly a few msec
                RndWait = random:uniform(?NEXT_AFTER_FAILED_TIMEOUT),
                {next_state,launcher,State#launcher{nusers = Users-1} , RndWait}
        end.
    

下一个用户生成需要等待`Wait - LaunchDuration`毫秒时间。

给出一个采样数据，只有一个从机，并且用户产生速度1秒一个，共产生10个用户：

    <load>
        <arrivalphase phase="1" duration="50" unit="minute">
            <users maxnumber="10" interarrival="1" unit="second"></users>
        </arrivalphase>
    </load>
    

采集日志部分，记录了Wait时间值，其实总体时间还需要加上LaunchDuration（虽然这个值很小）：

    ts_launcher:(7:<0.63.0>) client launched, wait 678.5670934164623 ms before launching next client
    ts_launcher:(7:<0.63.0>) client launched, wait 810.2982455546687 ms before launching next client
    ts_launcher:(7:<0.63.0>) client launched, wait 1469.2208436232288 ms before launching next client
    ts_launcher:(7:<0.63.0>) client launched, wait 986.7202548184069 ms before launching next client
    ts_launcher:(7:<0.63.0>) client launched, wait 180.7484423006169 ms before launching next client
    ts_launcher:(7:<0.63.0>) client launched, wait 1018.9190235965457 ms before launching next client
    ts_launcher:(7:<0.63.0>) client launched, wait 1685.0156394273606 ms before launching next client
    ts_launcher:(7:<0.63.0>) client launched, wait 408.53992361334065 ms before launching next client
    ts_launcher:(7:<0.63.0>) client launched, wait 204.40900996137086 ms before launching next client
    ts_launcher:(7:<0.63.0>) client launched, wait 804.6040921461512 ms before launching next client
    

总体来说，每一个用户生成间隔间不是固定值，是一个大约值，有偏差，但接近于目标设定（1000毫秒生成一个用户标准间隔）。

### 执行模拟终端用户会话流程

关于会话的说明：

* 一个session元素中的定义一系列请求-响应等交互行为称之为一次完整会话
* 一个模拟用户需要执行一次完整会话，然后生命周期完成，然后结束

模拟终端用户模块是`ts_client`（状态机），挂载在`ts_client_sup`下，由`ts_launcher/ts_launcher_static`调用`ts_client_sup:start_child(Session)`启动，是压测任务的最终执行者，承包了所有脏累差的活：

* 所有下一步需要执行的会话指令都需要向主机的`ts_config_server`请求
* 执行会话指令
* 具体协议调用相应协议插件，比如ts_mqtt组装会话消息
* 建立网络Socket连接，封装众多网络通道
* 发送请求数据，处理响应
* 记录并发送监控数据和日志


![ts_client][1]

￼

### 小结

简单梳理主从之间启动方式，从机数量分配策略，以具体压测任务如何在从机上分配和运行等内容。

[0]: http://www.blogjava.net/yongboy/archive/2016/07/25/431310.html
[1]: ./img/ts_client.png
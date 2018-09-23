## tmux的一些操作技巧

来源：[http://brucedone.com/archives/1176](http://brucedone.com/archives/1176)

时间 2018-06-29 10:45:24



## tmux简介

tmux是一个优秀的终端复用软件，类似GNU Screen，但来自于OpenBSD，采用BSD授权。使用它最直观的好处就是，通过一个终端登录远程主机并运行tmux后，在其中可以开启多个控制台而无需再“浪费”多余的终端来连接这台远程主机；当然其功能远不止于此。

```
直接使用yum安装: yum install tmux
ubuntu: apt-get install tmux 
 


```

tmux使用C/S模型构建，主要包括以下单元模块:



* server 服务器。输入tmux命令时就开启了一个服务器。
* session 会话。一个服务器可以包含多个会话。
* window 窗口。一个会话可以包含多个窗口。
* pane 面板。一个窗口可以包含多个面板。 操作
  

类似各种平铺式窗口管理器，tmux使用键盘操作，常用快捷键包括：

Ctrl+b (或者你绑定的前缀键)  激活控制台；此时以下按键生效


### 系统操作

```
?   列出所有快捷键；按q返回
d   脱离当前会话；这样可以暂时返回Shell界面，输入tmux attach能够重新进入之前的会话
D   选择要脱离的会话；在同时开启了多个会话时使用
Ctrl+z  挂起当前会话
r   强制重绘未脱离的会话
s   选择并切换会话；在同时开启了多个会话时使用
:   进入命令行模式；此时可以输入支持的命令，例如kill-server可以关闭服务器
[   进入复制模式；此时的操作与vi/emacs相同，按q/Esc退出
~   列出提示信息缓存；其中包含了之前tmux返回的各种提示信息
 


```


### 窗口操作

```
c   创建新窗口
&   关闭当前窗口
数字键 切换至指定窗口
p   切换至上一窗口
n   切换至下一窗口
l   在前后两个窗口间互相切换
w   通过窗口列表切换窗口
,   重命名当前窗口；这样便于识别
.   修改当前窗口编号；相当于窗口重新排序
f   在所有窗口中查找指定文本
 


```


### 面板操作

```
”   将当前面板平分为上下两块
%   将当前面板平分为左右两块
x   关闭当前面板
!   将当前面板置于新窗口；即新建一个窗口，其中仅包含当前面板
Ctrl+方向键    以1个单元格为单位移动边缘以调整当前面板大小
Alt+方向键 以5个单元格为单位移动边缘以调整当前面板大小
Space   在预置的面板布局中循环切换；依次包括even-horizontal、even-vertical、main-horizontal、main-vertical、tiled
q   显示面板编号
o   在当前窗口中选择下一面板
方向键 移动光标以选择面板
{   向前置换当前面板
}   向后置换当前面板
Alt+o   逆时针旋转当前窗口的面板
Ctrl+o  顺时针旋转当前窗口的面板 tmux配置
 


```

tmux的系统级配置文件为**`/etc/tmux.conf`**，用户级配置文件为~/.tmux.conf。配置文件实际上就是tmux的命令集合，也就是说每行配置均可在进入命令行模式后输入生效。

## 配置

下面是我的~/.tmux.conf配置：

```
# base
set -g display-time 3000    # 提示信息的持续时间；设置足够的时间以避免看不清提示，单位为毫秒
set -g history-limit 65535  # 每个窗口中可展示的历史行数
 
# mouse
set -g mode-mouse on         # 开启鼠标控制
set -g mouse-resize-pane on  # 开启鼠标可调整pane大小
set -g mouse-select-pane on  # 开启鼠标可选择pane
set -g mouse-select-window on # 开启鼠标可选择窗口
 
# bind key
unbind '"'           # 取消 '"' 的绑定, 原用于上下分割窗口
bind _ splitw -v     # 绑定 '_' 上下分割窗口 
unbind %             # 取消 '%' 的绑定, 原用于左右分割窗口
bind | splitw -h     # 绑定 '|' 左右分割窗口
bind r source-file ~/.tmux.conf \; display "Reloaded!" # 绑定 'r' 用于重载配置文件,重载后显示 "Reloaded!"
bind s setw synchronize-panes on  # 开启 pane 命令同步
bind a setw synchronize-panes off # 关闭 pane 命令同步
 
# window
set -w -g utf8 on    # 窗口显示内容使用utf8字符集显示
set -w -g window-status-current-bg red  # 当前选中窗口背景色为红色
 
# title
set -g set-titles on           # 开启终端程序的标题显示
set -g set-titles-string "#T"  # 标题显示内容为 ~/.bashrc 中 $PROMPT_COMMAND 变量的内容
 
# status bar
set -g status-utf8 on          # 状态栏使用utf8字符集
set -g status-bg black         # 状态栏背景色为 黑色
set -g status-fg white         # 状态栏前景色为 白色
set -g status-interval 2       # 状态栏刷新频率 2秒
set -g status-justify "left"   # 窗口列表的位置 靠左
set -g status-left "#[fg=yellow]#S "  # 状态栏最左端: Session的名称(颜色为yellow)
set -g status-right "#[fg=black]#T #[fg=yellow]%H:%M"  # 状态栏最右端: $PROMPT_COMMAND及时间
set -g status-right-length 50  # 状态栏右端的长度
 


```

tmux的session管理

```
Seesion 可以有效地分离工作环境。如我有三个网站, 可以分别设置'siteA','siteB','siteC'三个Session, 可以针对不同网站的需求和服务器的分布情况进行特定管理.
tmux new -s session_name # 创建一个叫做 session_name 的 tmux session
tmux attach -t session_name  # 重新开启叫做 session_name 的 tmux session
tmux switch -t session_name # 转换到叫做 session_name 的 tmux session
tmux list-sessions  # 列出现有的所有 session
tmux ls  # 列出现有的所有 session
tmux detach (prefix + d) # 离开当前开启的 session
tmux rename-session -t [current-name] [new-name] # 重命名session
 


```


## 脚本

Session的批量重建和管理脚本

```
#!/bin/bash
 
 
Session=$1
session_arr=(siteA siteB siteC)
 
function tmux_siteA()
{
    # mg
    tmux new-session -d -s 'siteA' -n 'mg' # 创建session siteA, 并给默认窗口命名为 mg
    tmux select-window -t 'mg'
    tmux split-window -h -p 50
    tmux send-keys -t 0 'ssh 192.168.1.101' C-m        # 其中 C-m 表示回车键 
 
    # ngx.web
    tmux new-window -n 'ngx.web'           # 新建 ngx.web 的窗口
    tmux split-window -h -p 50             # 左右分割 ngx.web 窗口为两个pane, 新建的pane的百分比为50%
    tmux send-keys -t 0 'ssh 1.1.1.0' C-m  # 引号内的内容是该pane中需要预先执行的命令
    tmux send-keys -t 1 'ssh 1.1.1.1' C-m  # 我这里是分别登录到两台机器中去
}
 
function tmux_siteB()
{
    # mg.zbx
    tmux new-session -d -s 'siteB' -n 'mg.zbx'
    tmux select-window -t 'mg.zbx'
    tmux split-window -h -p 50
    tmux select-pane -t 0
    tmux split-window -v -p 50
    tmux send-keys -t 1 'pwd' C-m
    tmux send-keys -t 2 'ssh 192.168.1.100' C-m
 
    # ngx.web
    tmux new-window -n 'ngx.web'
    tmux split-window -h -p 50
    tmux select-pane -t 1
    tmux split-window -v -p 50
    tmux select-pane -t 0
    tmux split-window -v -p 50
    tmux send-keys -t 0 'ssh 192.168.1.100' C-m
    tmux send-keys -t 1 'ssh 192.168.1.101' C-m
    tmux send-keys -t 2 'ssh 192.168.1.102' C-m
    tmux send-keys -t 3 'ssh 192.168.1.103' C-m
 
}
 
function tmux_siteC()
{
    # mg
    tmux new-session -d -s 'siteC' -n 'mg'
    tmux select-window -t 'mg'
    tmux split-window -h -p 50
    tmux send-keys -t 2 'g 80' C-m
 
    # push
    tmux new-window -n 'push'
    tmux split-window -h -p 50
    tmux select-pane -t 1
    tmux split-window -v -p 50
    tmux select-pane -t 0
    tmux split-window -v -p 50
    tmux send-keys -t 0 'ssh 192.168.1.100' C-m
    tmux send-keys -t 1 'ssh 192.168.1.101' C-m
    tmux send-keys -t 2 'ssh 192.168.1.102' C-m
    tmux send-keys -t 3 'ssh 192.168.1.103' C-m
 
}
 
for i in ${session_arr[@]}
do
     if [ "X$i" == "X$Session" ]
     then
        tmux start-server
        tmux has-session -t $Session 2>/dev/null
        Res=$?
          if [ $Res != 0 ]
          then
               tmux_$Session
          fi
 
        tmux attach-session -d -t $Session
     fi
done
 


```



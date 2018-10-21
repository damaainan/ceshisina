### 安装 Vim

### 安装 git
```
apt-get install git 
```

**全局设置**
```
# 显示中文
git config --global core.quotepath false
# 设置 ssh 

```


### 安装 tmux
```
apt-get install tmux
```
简单配置

`/etc/tmux.conf`

```
# 全局设置
# 修改 ctrl+b 前缀为 ctrl+a
set -g prefix C-a
unbind C-b
bind C-a send-prefix
set-option -g prefix2 `
# 绑定重载 settings 的热键
bind r source-file /etc/tmux.conf \; display-message "Config reloaded.."

# 设置window的起始下标为1
set -g base-index 1
# 设置pane的起始下标为1
set -g pane-base-index 1

#-- base --#
set -g default-terminal "screen-256color"
set -g display-time 3000
set -g history-limit 65535

# 鼠标支持
set-option -g mouse on
# 关闭默认窗口标题
set -g set-titles off

#-- bindkeys --#
unbind '"'
bind - splitw -v -c '#{pane_current_path}'
unbind %
bind | splitw -h -c '#{pane_current_path}'

bind c new-window -c "#{pane_current_path}"

# 定义上下左右键为hjkl键
bind -r k select-pane -U
bind -r j select-pane -D
bind -r h select-pane -L
bind -r l select-pane -R
```

### 安装 ccat
```
aria2c https://github.com/jingweno/ccat/releases/download/v1.1.0/linux-amd64-1.1.0.tar.gz  #https://github.com/jingweno/ccat/releases

tar zxvf ccat.tar.gz

cp ccat /bin/ccat 

alias cat="ccat"
```

### 安装 [neofetch](https://github.com/dylanaraps/neofetch)
系统信息查看
```
apt-get install neofetch
```


### 安装 curl aria2c htop dstat sysstat
```
apt-get install curl aria2 htop dstat sysstat
```

### 安装 [lnav](https://github.com/tstack/lnav)

lnav工具是在终端界面看日志的神器
```
dpkg -i lnav.deb
```


### 安装 [bmon](https://github.com/tgraf/bmon) 
命令行监控网速

### 安装 ssh 

#### 配置 github ssh 免密码登录



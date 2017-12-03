# 简介
**[utils](https://github.com/lfckop/utils)**: shell工具集，通常用于**Linux**环境中，包含一些常用的alias、脚本、git命令自动补全、定制化的命令提示符(显示git分支名)等等，意图提供直观、简化的命令行操作。用户也可根据自己的需要对其进行修改和定制。

这个项目会一直进行更新和维护。欢迎大家使用、提意见和merge request！

# 用法
1. 将*utils*下载至用户home目录：`$ cd $HOME && git clone https://github.com/lfckop/utils.git`
2. 有两种使用方式可供选择：
  * 只影响当前shell会话：`$ source ${HOME}/utils/.source.sh`
  * 影响所有的shell会话：在文件`/etc/profile`最后添加一行`source ${HOME}/utils/.source.sh`，或者：`$ sudo echo 'source ${HOME}/utils/.source.sh' >> /etc/profile` (需要root权限)。

# 功能介绍
## 常用`alias`
```bash
alias ll="ls -alF"
alias grep="grep --color=auto"
alias pg="ps aux | head -1; ps aux | grep -v grep | grep"
alias ng="netstat -antup | awk 'NR==2'; netstat -antup | grep"
alias lg="lsof -i -n | head -1; lsof -i -n | grep"
alias extip="curl -s http://whatismyip.akamai.com/"
alias e="exit"
alias yi="yum -y install"
alias mcp="mvn clean package"
alias mcpnt="mvn clean package -DskipTests -Dmaven.test.skip=true"
alias sost="netstat -n | awk '/^tcp/ {s[\$NF]++} END{for(i in s) print i, s[i]}' OFS='\t'"
```
这些`alias`都定义在`.source.sh`文件中，一般比较直观，意图是提供简化的命令行操作，部分alias介绍如下。

* `pg`: 查找某个进程：`$ pg java`，可根据进程PID或进程名进行查找。
* `ng`: 检查端口使用情况：`$ ng 8080`，可根据端口号、进程PID或进程名进行查找。
* `extip`: 获取本机外网ip：`$ extip`.
* `mcp`: Java项目maven打包：`$ mcp`.
* `sost`: 按socket状态给出统计信息：`$ sost`.

## 命令提示符`$PS1`
```bash
export PS1='\[\033[1;32m\][\[\033[0;32m\]\u@\h:\[\033[1;35m\]\w\[\033[1;36m\]$(__git_ps1 " (%s)")\[\033[1;32m\] ]\[\033[1;31m\] \$\[\033[0m\] '
```
命令提示符`$PS1`同`alias`一样定义在`.source.sh`文件中，此`$PS1`会显示当前路径和当前文件夹的git分支名(如果它是git目录)，并用颜色区分展示，同时提供了git命令自动补全功能。

在对系统提供的默认命令提示符不满意时，可尝试使用这个或在此基础上根据个人喜好对其进行定制。

## 常用脚本
这些脚本实际上只是对一些基础命令的一层薄包装，意图将一些常用的功能以**简洁方便**的方式提供出来。

通常这些命令都会需要一个或多个参数，在不提供参数直接使用这些命令的时候，会打印出`Usage`提示信息并立即退出；同样，这些`Usage`信息也会作为注释写在各个脚本的头部，建议使用者详细阅读。

现分别简单介绍如下，如果感兴趣或有疑惑的话可以直接阅读代码。

### `catmf`
对`unzip -p some-jar-file.jar META-INF/MANIFEST.MF`命令的封装，
打印输出jar包的`META-INF/MANIFEST.MF`文件。

```bash
$ catmf
Usage: catmf jarfile.jar
```

### `cl`
对`bc`命令的封装，在命令行中进行数学计算**C**alcu**L**ate - `cl`。

```bash
$ cl
Usage: cl "99.1*(88.6+77.7)"
   or: cl 99.9/k (equals: 'cl 99.9/1024')
   or: cl 99.9/m (equals: 'cl 99.9/1024/1024')
```

### `ff`
对`find`和`grep`命令的封装，用于在当前目录的文件中查找某个字符串。

```bash
$ ff
Usage: ff key [filename...]
Example: ff ConcurrentHashMap
Example: ff ConcurrentHashMap "*.java"
Example: ff ConcurrentHashMap Test1.java Test2.java
```

### `first60s`和`jdt`
`first60s` and `jdt (Java Dump Tool)`，用于流程化保存现场。

当线上某个Java服务出现严重性能问题，有时候为了临时快速恢复服务，重启应用或许是个不错的选项。但是，在重启应用前需要保存现场，以便提供足够的日志信息来帮助后续排查并尝试解决问题。其中，保存现场这一步是可以大概流程化的，一是节省时间快速处理，二是期望保留足够的当前运行时信息并写到日志文件中。

1. `first60s`：保存当前系统信息，如内存、CPU、网络、磁盘等主要部件的负载、IO、性能、容量等相关信息。脚本基本上是参考netflix的一篇博文["Linux Performance Analysis in 60,000 Milliseconds"](http://techblog.netflix.com/2015/11/linux-performance-analysis-in-60s.html)，作者[Brendan Gregg](http://www.brendangregg.com/)；网上也有其译文["Linux性能分析的前60000毫秒"](https://segmentfault.com/a/1190000004104493)。

    ```bash
    Usage: first60s
    ```

2. `jdt`：**J**ava **D**ump **T**ool，保存当前Java应用的运行时信息，主要是用jstack, jmap, jinfo和jstat保存相关信息，再结合GC日志，应该可以对后续排查提供更多帮助。

    ```bash
    Usage: jdt [pid]
    ```

### `ipfrom`
通过请求[ip.cn](http://ip.cn)查询ip。

```bash
$ ipfrom
Usage: ipfrom ip
```

### `jarfind, jargrep`
这两个脚本来自于新浪微博平台研发技术专家[qdaxb](https://github.com/qdaxb)的开源工具[wtool_java](https://github.com/qdaxb/wtool_java/tree/master/tools)。

`jarfind`用于在jar包中查找文件；`jargrep`用于在jar包中检索字符串。

```bash
$ jarfind
Usage: jarfind file_name jar_path

$ jargrep
Usage: jargrep text <path or filename>
```

### `tarc, tart, tarx`
这三个工具是对`tar`命令的封装，分别用于打包、列出包文件、解包。因考虑到文件存储与传输速度都不是限制，为提高打包与解包速度，故没有对文件进行压缩。

```bash
$ tarc
Usage: tarc filename_prefix files...

$ tart
Usage: tart filename.tar

$ tarx
Usage: tarx filename.tar
```
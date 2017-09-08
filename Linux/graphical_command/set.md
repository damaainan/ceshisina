 **set命令** **-->用来依照不同的需求来设置所使用shell的执行方式**

 ****

 **set设置中常用设置含义:**

 allexport -a 从设置开始标记所有新的和修改过的用于输出的变量   
braceexpand -B 允许符号扩展,默认选项   
emacs 在进行命令编辑的时候,使用内建的emacs编辑器, 默认选项  
errexit  -e 如果一个命令返回一个非0退出状态值(失败),就退出.  
histexpand -H 在做临时替换的时候允许使用!和!! 默认选项  
history 允许命令行历史,默认选项  
ignoreeof 禁止coontrol-D的方式退出shell，必须输入exit。  
interactive-comments 在交互式模式下， #用来表示注解  
keyword -k 为命令把关键字参数放在环境中  
monitor -m 允许作业控制  
noclobber -C 保护文件在使用重新动向的时候不被覆盖  
noexec -n 在脚本状态下读取命令但是不执行，主要为了检查语法结构。  
noglob  -d 禁止路径名扩展，即关闭通配符   
notify -b 在后台作业以后通知客户  
nounset  -u 在扩展一个没有的设置的变量的时候， 显示错误的信息   
onecmd -t 在读取并执行一个新的命令后退出   
physical  -P 如被设置，则在使用pwd和cd命令时不使用符号连接的路径而是物理路径  
posix 改变shell行为以便符合POSIX要求  
privileged 一旦被设置，shell不再读取.profile文件和env文件 shell函数也不继承任何环境  
verbose -v 为调试打开verbose模式  
vi 在命令行编辑的时候使用内置的vi编辑器  
xtrace -x 打开调试回响模式

 

![][0]

 【**set,env和export**】  
 set,env和export这三个命令都可以用来显示shell变量,其区别?

   
set 用来显示本地变量  
env 用来显示环境变量  
export 用来显示和设置环境变量  
  
set 显示当前shell的变量，包括当前用户的变量  
env 显示当前用户的变量  
export 显示当前导出成用户变量的shell变量

[0]: ./img/20170115092015500.png
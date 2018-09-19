## 浅析php.ini设置与Web安全

来源：[https://www.se7ensec.cn/2018/09/19/浅析php.ini设置与Web安全/](https://www.se7ensec.cn/2018/09/19/浅析php.ini设置与Web安全/)

时间 2018-09-19 13:24:40

 
 
php的安全模式是个非常重要的内嵌的安全机制，能够控制一些php中的函数，比如 **`system()`**  ，同时把很多文件操作函数进行了权限控制，也不允许对某些关键文件的文件，比如 **`/etc/passwd`** 
 
但是默认的php.ini是没有打开安全模式的，我们把它打开：
 `safe_mode = on`## 0x02 用户组安全 
 
当 **`safe_mode`**  打开时， **`safe_mode_gid`**  被关闭，那么php脚本能够对文件进行访问，而且相同组的用户也能够对文件进行访问。
 
建议设置为：
 `safe_mode_gid = off`如果不进行设置，可能我们无法对我们服务器网站目录下的文件进行操作了，比如我们需要对文件进行操作的时候。
 
## 0x03 安全模式下执行程序主目录 
 
如果安全模式打开了，但是却是要执行某些程序的时候，可以指定要执行程序的主目录：
 `safe_mode_exec_dir = D:/usr/bin`一般情况下是不需要执行什么程序的，所以推荐不要执行系统程序目录，可以指向一个目录， 然后把需要执行的程序拷贝过去，比如：
 `safe_mode_exec_dir = D:/tmp/cmd`但是，我更推荐不要执行任何程序，那么就可以指向我们网页目录：
 `safe_mode_exec_dir = D:/usr/www`## 0x04 安全模式下包含文件 
 
如果要在安全模式下包含某些公共文件，那么就修改一下选项：
 `safe_mode_include_dir = D:/usr/www/include/`其实一般php脚本中包含文件都是在程序自己已经写好了，这个可以根据具体需要设置。
 
## 0x05 控制php脚本能访问的目录 
 
使用 **`open_basedir`** 
 
选项能够控制PHP脚本只能访问指定的目录，这样能够避免PHP脚本访问 不应该访问的文件，一定程度上限制了 **`phpshell`**  的危害，我们一般可以设置为只能访问网站目录：
 `open_basedir = D:/usr/www`## 0x06 关闭危险函数 
 
如果打开了安全模式，那么函数禁止是可以不需要的，但是我们为了安全还是考虑进去。比如， 我们觉得不希望执行包括 **`system()`** 
 
等在那的能够执行命令的php函数，或者能够查看php信息的 **`phpinfo()`**  等函数。
 
那么我们就可以禁止它们：
 `disable_functions=system,passthru,exec,shell_exec,popen,phpinfo`如果你要禁止任何文件和目录的操作，那么可以关闭很多文件操作：
 `disable_functions=chdir,chroot,dir,getcwd,opendir,readdir,scandir,fopen,unlink,delete,copy,mkdir,rmdir,rename,file,file_get_contents,fputs,fwrite,chgrp,chmod,chown`以上只是列了部分不叫常用的文件处理函数，你也可以把上面执行命令函数和这个函数结合， 就能够抵制大部分的 **`phpshell`**  了。
 
## 0x07 关闭PHP版本信息在http头中的泄漏 
 
我们为了防止黑客获取服务器中php版本的信息，可以关闭该信息泄露在http中：
 `expose_php = Off`比如黑客在
 `telnet www.phpddt.com 80`的时候，那么将无法看到PHP的信息。
 
## 0x08 关闭注册全局变量 
 
在PHP中提交的变量，包括使用POST或者GET提交的变量，都将自动注册为全局变量，能够直接访问，这是对服务器非常不安全的。
 
所以我们不能让它注册为全局变量，就把注册全局变量选项关闭：
 `register_globals = Off`当然，如果这样设置了，那么获取对应变量的时候就要采用合理方式，比如获取GET提交的变量var，那么就要用 **`$_GET['var']`**  来进行获取。
 
## 0x09 打开magic_quotes_gpc来防止SQL注入 
 
SQL注入是非常危险的问题，小则网站后台被入侵，重则整个服务器沦陷，所以一定要小心。
 
php.ini中有一个设置：
 `magic_quotes_gpc = Off`这个默认是关闭的，如果它打开后将自动把用户提交对sql的查询进行转换，比如把`'`转为`\'`等，这对防止sql注射有重大作用。所以我们推荐设置为：
 `magic_quotes_gpc = On`## 0x10 错误信息控制 
 
一般php在没有连接到数据库或者其他情况下会有提示错误，一般错误信息中会包含php脚本当前的路径信息或者查询的SQL语句等信息，这类信息提供给黑客后，是不安全的。
 
所以一般服务器建议禁止错误提示：
 `display_errors = Off`如果你却是是要显示错误信息，一定要设置显示错误的级别，比如只显示警告以上的信息：
 `error_reporting = E_WARNING & E_ERROR`当然，还是建议关闭错误提示。
 
## 0x11 错误日志 
 
建议在关闭 **`display_errors`**  后能够把错误信息记录下来，便于查找服务器运行的原因：
 `log_errors = On`同时也要设置错误日志存放的目录，建议根apache的日志存在一起：
 `error_log = D:/usr/local/apache2/logs/php_error.log`注意：给文件必须允许apache用户的和组具有写的权限。



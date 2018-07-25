## PHP环境安全加固方案

来源：[https://www.helloweba.net/server/560.html](https://www.helloweba.net/server/560.html)

时间 2018-06-02 18:52:53



#### 1.启用 PHP 的安全模式

PHP 环境提供的安全模式是一个非常重要的内嵌安全机制，PHP 安全模式能有效控制一些 PHP 环境中的函数（例如system()函数），对大部分的文件操作函数进行权限控制，同时不允许对某些关键文件进行修改（例如 /etc/passwd）。但是，默认的 php.ini 配置文件并没有启用安全模式。

您可以通过修改 php.ini 配置文件启用 PHP 安全模式：

``` 
safe_mode = on
```


#### 2.用户组安全

当您启用安全模式后，如果safe_mode_gid选项被关闭，PHP 脚本能够对文件进行访问，且相同用户组的用户也能够对该文件进行访问。

因此，建议您将该选项设置为关闭状态：

``` 
safe_mode_gid = off
```

注意： 该选项参数仅适用于 Linux 操作系统。

如果不进行该设置，您可能无法对服务器网站目录下的文件进行操作。


#### 3.安全模式下执行程序主目录

如果启用了安全模式后，想要执行某些程序的时候，可以指定需要执行程序的主目录，例如：

``` 
safe_mode_exec_dir = /usr/bin
```

一般情况下，如果不需要执行什么程序，建议您不要指定执行系统程序的目录。您可以指定一个目录，然后把需要执行的程序拷贝到这个目录即可，例如：

``` 
safe_mode_exec_dir = /temp/cmd
```

但是，更推荐您不要执行任何程序。这种情况下，只需要将执行目录指向网页目录即可：

``` 
safe_mode_exec_dir = /usr/www
```

注意：执行目录的路径以您实际操作系统目录路径为准。


#### 4.安全模式下包含文件

如果您需要在安全模式下包含某些公共文件，您只需要修改以下选项即可：

``` 
safe_mode_include_dir = /usr/www/include/
```

一般情况下，PHP 脚本中包含的文件都是在程序已经写好的，可以根据您的具体需要进行设置。


#### 5.控制 PHP 脚本能访问的目录

使用open_basedir选项能够控制 PHP 脚本只能访问指定的目录，这样能够避免 PHP 脚本访问不应该访问的文件，一定程度下降低了 phpshell 的危害。一般情况下，可以设置为只能访问网站目录：

``` 
open_basedir = /usr/www
```


#### 6.关闭危险函数

如果您启用了安全模式，那么可以不需要设置函数禁止，但为了安全考虑，还是建议您进行相关设置。例如，您不希望执行包括system()等在内的执行命令的 PHP 函数，以及能够查看 PHP 信息的phpinfo()等函数，那么您可以通过以下设置禁止这些函数：

``` 
disable_functions = system, passthru, exec, shell_exec, popen, phpinfo, escapeshellarg, escapeshellcmd, proc_close, proc_open, dl
```

如果您想要禁止对于任何文件和目录的操作，那么您可以关闭以下文件相关操作。

``` 
disable_functions = chdir, chroot, dir, getcwd, opendir, readdir, scandir, fopen, unlink, delete, copy, mkdir, rmdir, rename, file, file_get_contents, fputs, fwrite, chgrp,chmod, chown
```

注意： 以上设置中只列举了部分比较常用的文件处理函数，您也可以将上面的执行命令函数和这些文件处理函数相结合，就能给抵制大部分的 phpshell 威胁。


#### 7.关闭 PHP 版本信息在 HTTP 头中的泄露

为了防止黑客获取服务器中 PHP 版本的信息，您可以禁止该信息在 HTTP 头部内容中泄露：

``` 
expose_php = off
```

这样设置之后，黑客在执行`telnet <domain> 80`尝试连接您的服务器的时候，将无法看到 PHP 的版本信息。


#### 8.关闭注册全局变量

在 PHP 环境中提交的变量，包括使用 POST 或者 GET 命令提交的变量，都将自动注册为全局变量，能够被直接访问。这对您的服务器是非常不安全的，因此建议您将注册全局变量的选项关闭，禁止将所提交的变量注册为全局变量。

``` 
register_globals = off
```

注意： 该选项参数在 PHP 5.3 以后的版本中已被移除。

当然，如果这样设置之后，获取对应变量的时候就需要采取合理方式。例如，获取 GET 命令提交的变量 var，就需要使用`$_GET['var']`命令来进行获取，在进行 PHP 程序设计时需要注意。


#### 9.SQL 注入防护

SQL 注入是一个非常危险的问题，小则造成网站后台被入侵，重则导致整个服务器沦陷。
`magic_quotes_gpc`选项默认是关闭的。如果打开该选项，PHP 将自动把用户提交对 SQL 查询的请求进行转换（例如，把 ’ 转换为 \’ 等），这对于防止 SQL 注入攻击有很大作用，因此建议您将该选项设置为：

``` 
magic_quotes_gpc = on
```

注意： 该选项参数在 PHP 5.4.0 以后的版本中已被移除。

所以最好使用PDO预处理方式处理SQL查询。


#### 10.错误信息控制

一般 PHP 环境在没有连接到数据库或者其他情况下会有错误提示信息，错误信息中可能包含 PHP 脚本当前的路径信息或者查询的 SQL 语句等信息，这类信息如果暴露给黑客是不安全的，因此建议您禁止该错误提示：

``` 
display_errors = Off
```

如果您确实要显示错误信息，一定要设置显示错误信息的级别。例如，只显示警告以上的错误信息：

``` 
error_reporting = E_WARNING & E_ERROR
```

注意： 强烈建议您关闭错误提示信息。


#### 11.错误日志

建议您在关闭错误提示信息后，对于错误信息进行记录，便于排查服务器运行异常的原因：

``` 
log_errors = On
```

同时，需要设置错误日志存放的目录，建议您将 PHP 错误日志与 Apache 的日志存放在同一目录下：

``` 
error_log = /usr/local/apache2/logs/php_error.log
```

注意： 该文件必须设置允许 Apache 用户或用户组具有写的权限。

还有最重要的是升级系统补丁，升级PHP版本。

本文摘自阿里云，本站编辑对原文稍作删减。



## [译] 写好 shell 脚本的 13 个技巧

来源：[https://mp.weixin.qq.com/s/f3xDHZ7dCQr7sHJ9KDvuyQ](https://mp.weixin.qq.com/s/f3xDHZ7dCQr7sHJ9KDvuyQ)

时间 2018-06-16 08:53:36

 
![][0]
 
作者 | Joseph Matthias Goh
 
译者 | 无明
 
编辑 | 张婵
 
产品的最终用户通常不懂技术，所以不管你怎么折腾产品代码都无所谓。但脚本代码不一样，它们是开发人员写给开发人员的。
 
有多少次，你运行./script.sh，然后输出一些东西，但却不知道它刚刚都做了些什么。这是一种很糟糕的脚本用户体验。我将在这篇文章中介绍如何写出具有良好开发者体验的 shell 脚本。
 
产品的最终用户通常不懂技术，所以不管你怎么折腾产品代码都无所谓。但脚本代码不一样，它们是开发人员写给开发人员的。
 
这样会导致一些问题：
 
 
* 混乱的脚本——我知道，我们都是工程师，读得懂代码，但即使这样，也请为我们这些对 Shell 脚本不是很熟练的人考虑一下（我们在写代码时也会为你们考虑的）。
  
* 满屏的日志和错误输出——就算我们也是工程师，并不代表我们了解你所做的一切。
  
* 弄得一团糟却没有做好清理工作——是的，我们可以顺着你的脚本手动撤销变更，但你真的会让那些信任你的脚本的人这么做吗？
  
 
 
所以，我们可以通过一些方法来为自己和别人写出更好的 shell 脚本。这里给出的所有示例都可以使用与 POSIX 标准兼容的 shell 运行（#!/bin/sh），因为它是最常用的。嫌文章太长了可以只看以下总结部分：
 
 
* 提供`--help`标记
  
* 检查所有命令的可用性
  
* 独立于当前工作目录
  
* 如何读取输入：环境变量 vs. 标记
  
* 打印对系统执行的所有操作
  
* 如果有必要，提供`--silent`选项
  
* 重新开启显示
  
* 用动画的方式显示进度
  
* 用颜色编码输出
  
* 出现错误立即退出脚本
  
* 自己执行清理工作
  
* 在退出时使用不同的错误码
  
* 在结束时打印一个新行
  
 
 
有时间的话可以接着往下看具体内容：
 
### 提供`--help`标记
 
安装在系统上的二进制文件通常带有`man`帮助文档，但对于脚本来说就不一定了。因此我们通常需要为脚本提供`-h`或`--help`标记来打印有关如何使用脚本的信息。如果其他工程师需要修改脚本，这也可以作为脚本的内联文档：
 
``` 
#!/bin/sh
if [ ${#@} -ne 0 ] && [ "${@#"--help"}" = "" ]; then
  printf -- '...help...\n';
  exit 0;
fi;
```
 
这段脚本先计算参数长度（`${#@} -ne 0`），只有当参数长度不为零时才会检查`--help`标记。下一个条件会检查参数中是否存在字符串`“--help”`。第一个条件是必需的，如果参数长度为零则不需要打印帮助信息。
 
### 检查所有命令的可用性
 
脚本通常会调用其他脚本或二进制文件。在调用可能不存在的命令时，请先检查它们是否可用。可以使用“command -v 二进制文件名称”来执行此操作，看看它的退出代码是否为零。如果命令不可用，可以告诉用户应该如何获得这个二进制文件：
 
``` 
#!/bin/sh
_=$(command -v docker);
if [ "$?" != "0" ]; then
  printf -- 'You don\'t seem to have Docker installed.\n';
  printf -- 'Get it: https://www.docker.com/community-edition\n';
  printf -- 'Exiting with code 127...\n';
  exit 127;
fi;
# ...
```
 
### 独立于当前工作目录
 
从不同的目录执行脚本可能会发生错误，这样的脚本没有人会喜欢。要解决这个问题，请使用绝对路径（`/path/to/something`）和脚本的相对路径（如下所示）。
 
可以使用`dirname $0`引用脚本的当前路径：
 
``` 
#!/bin/sh
CURR_DIR="$(dirname $0);"
printf -- 'moving application to /opt/app.jar';
mv "${CURR_DIR}/application.jar" /opt/app.jar;
```
 
### 如何读取输入：环境变量 vs. 标记
 
脚本通过两种方式接受输入：环境变量和选项标记（参数）。根据经验，对于不影响脚本行为的值，可以使用环境变量，而对于可能触发脚本不同流程的值，可以使用脚本参数。
 
不影响脚本行为的变量可能是访问令牌和 ID 之类的东西：
 
``` 
#!/bin/sh
# do this
export AWS_ACCESS_TOKEN='xxxxxxxxxxxx';
./provision-everything
# and not
./provisiong-everything --token 'xxxxxxxxxxx';
```
 
影响脚本行为的变量可能是需要运行实例的数量、是异步还是同步运行、是否在后台运行等参数：
 
``` 
#!/bin/sh
# do this
./provision-everything --async --instance-count 400
# and not
INSTANCE_COUNT=400 ASYNC=true ./provision-everything
```
 
### 打印对系统执行的所有操作
 
脚本通常会对系统执行有状态的更改。不过，由于我们不知道用户何时会向发送`SIGINT`，也不知道脚本错误何时可能导致脚本意外终止，因此很有必要将正在做的事情打印在终端上，这样用户就可以在不去查看脚本的情况下回溯这些步骤：
 
``` 
#!/bin/sh
printf -- 'Downloading required document to ./downloaded... ';
wget -o ./downloaded https://some.site.com/downloaded;
printf -- 'Moving ./downloaded to /opt/downloaded...';
mv ./downloaded /opt/;
printf -- 'Creating symlink to /opt/downloaded...';
ln -s /opt/downloaded /usr/bin/downloaded;
```
 
### 在必要时提供`--silent`选项
 
有些脚本是为了将其输出传给其他脚本。虽说脚本都应该能够单独运行，不过有时候也有必要让它们把输出结果传给另一个脚本。可以利用`stty -echo`来实现`--silent`标记：
 
``` 
#!/bin/sh
if [ ${#@} -ne 0 ] && [ "${@#"--silent"}" = "" ]; then
  stty -echo;
fi;
# ...
# before point of intended output:
stty +echo && printf -- 'intended output\n';
# silence it again till end of script
stty -echo;
# ...
stty +echo;
exit 0;
```
 
### 重新开启显示
 
在使用`stty -echo`关闭脚本显示之后，如果发生致命错误，脚本将终止，而且不会恢复终端输出，这样对用户来说是没有意义的。可以使用`trap`来捕捉`SIGINT`和其他操作系统级别的信号，然后使用`stty echo`打开终端显示：
 
``` 
#!/bin/sh
error_handle() {
  stty echo;
}
if [ ${#@} -ne 0 ] && [ "${@#"--silent"}" = "" ]; then
  stty -echo;
  trap error_handle INT;
  trap error_handle TERM;
  trap error_handle KILL;
  trap error_handle EXIT;
fi;
# ...
```
 
### 用动画的方式显示进度
 
有些命令需要运行很长时间，并非所有脚本都提供了进度条。在用户等待异步任务完成时，可以通过一些方式告诉他们脚本仍在运行。比如在`while`循环中打印一些信息：
 
``` 
#!/bin/sh
printf -- 'Performing asynchronous action..';
./trigger-action;
DONE=0;
while [ $DONE -eq 0 ]; do
  ./async-checker;
  if [ "$?" = "0" ]; then DONE=1; fi;
  printf -- '.';
  sleep 1;
done;
printf -- ' DONE!\n';
```
 
或者可以做一些更好玩的小玩意儿，比如 http://mywiki.wooledge.org/BashFAQ/034。
 
### 用颜色编码输出
 
在脚本中调用其他二进制文件或脚本时，对它们的输出进行颜色编码，这样就可以知道哪个输出来自哪个脚本或二进制文件。这样我们就不需要在满屏的黑白输出文本中查找想要的输出结果。
 
理想情况下，脚本应该输出白色（默认的，前台进程），子进程应该使用灰色（通常不需要，除非出现错误），使用绿色表示成功，红色表示失败，黄色表示警告。
 
``` 
#!/bin/sh
printf -- 'doing something... \n';
printf -- '\033[37m someone else's output \033[0m\n';
printf -- '\033[32m SUCCESS: yay \033[0m\n';
printf -- '\033[33m WARNING: hmm \033[0m\n';
printf -- '\033[31m ERROR: fubar \033[0m\n';
```
 
可以使用`\033[Xm`，其中`X`代表颜色代码。有些脚本使用`\e`而不是`\033`，但要注意`\e`不适用于所有的 UNIX 系统。
 
![][1]
 
正确示范
 
可在`.sh` 中使用的所有颜色和修饰符 https://misc.flogisoft.com/bash/tip_colors_and_formatting。
 
### 出现错误立即退出脚本
 `set -e`表示从当前位置开始，如果出现任何错误都将触发`EXIT`。相反，`set +e`表示不管出现任何错误继续执行脚本。
 
如果脚本是有状态的（每个后续步骤都依赖前一个步骤），那么请使用`set -e`，在脚本出现错误时立即退出脚本。如果要求所有命令都要执行完（很少会这样），那么就使用`set +e`。
 
``` 
#!/bin/sh
set +e;
./script-1;
./script-2; # does not depend on ./script-1
./script-3; # does not depend on ./script-2
set -e;
./script-4;
./script-5; # depends on success of ./script-4
# ...
```
 
### 自己执行清理工作
 
大多数脚本在出现错误时不会执行清理工作，能够做好这方面工作的脚本实属罕见，但这样做其实很有用，还可以省下不少时间。前面已经给出过示例，让`stty`恢复正常，并借助`trap`命令来执行清理工作：
 
``` 
#!/bin/sh
handle_exit_code() {
  ERROR_CODE="$?";
  printf -- "an error occurred. cleaning up now... ";
  # ... cleanup code ...
  printf -- "DONE.\nExiting with error code ${ERROR_CODE}.\n";
  exit ${ERROR_CODE};
}
trap "handle_exit_code" EXIT;
# ... actual script...
```
 
### 在退出时使用不同的错误码
 
在绝大多数 shell 脚本中，exit 0 表示执行成功，exit 1 表示发生错误。对错误与错误码进行一对一的映射，这样有助于脚本调试。
 
``` 
#!/bin/sh
# ...
if [ "$?" != "0" ]; then
  printf -- 'X happened. Exiting with status code 1.\n';
  exit 1;
fi;
# ...
if [ "$?" != "0" ]; then
  printf -- 'Y happened. Exiting with status code 2.\n';
  exit 2;
fi;
```
 
这样做有另一个额外的好处，就是其他脚本在调用你的脚本时，可以根据错误码来判断发生了什么错误。
 
### 在结束时打印一个新行
 
如果你有在遵循脚本的最佳实践，那么可能会使用`printf`代替`echo`（它在不同系统中的行为有所差别）。问题是`printf`在命令结束后不会自动添加一个新行，导致控制台看起来是这样的：
 
  
![][2]
 
看起来是多么的平淡
 
这样一点也不酷，可以通过简单的方式打印一个新行：
 
``` 
#!/bin/sh
# ... your awesome script ...
printf -- '\n';
exit 0;
```
 
这样就可以得到：
 
![][2]
 
好多了哈
 
 
别人会感谢你这么做的。
 
总结   
 
这篇文章大致总结了一些简单易用的技巧，让 shell 脚本更易于调试和使用。
 
原文链接：https://codeburst.io/13-tips-tricks-for-writing-shell-scripts-with-awesome-ux-19a525ae05ae
 

[0]: https://img0.tuicool.com/vQf6FzQ.jpg 
[1]: https://img1.tuicool.com/QJvUfqQ.png 
[2]: https://img0.tuicool.com/6z6BZvQ.png 
[3]: https://img0.tuicool.com/6z6BZvQ.png 

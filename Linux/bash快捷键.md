#### CTRL 键相关的快捷键:
```
Ctrl + a - Jump to the start of the line  
Ctrl + b - Move back a char  
Ctrl + c - Terminate the command  //用的最多了吧?  
Ctrl + d - Delete from under the cursor  
Ctrl + e - Jump to the end of the line  
Ctrl + f - Move forward a char  
Ctrl + k - Delete to EOL  
Ctrl + l - Clear the screen  //清屏，类似 clear 命令  
Ctrl + r - Search the history backwards  //查找历史命令  
Ctrl + R - Search the history backwards with multi occurrence  
Ctrl + u - Delete backward from cursor // 密码输入错误的时候比较有用  
Ctrl + xx - Move between EOL and current cursor position  
Ctrl + x @ - Show possible hostname completions   
Ctrl + z - Suspend/ Stop the command  
补充:  
Ctrl + h - 删除当前字符  
Ctrl + w - 删除最后输入的单词
```
#### ALT 键相关的快捷键:

平时很少用。有些和远程登陆工具冲突。
```
Alt + < - Move to the first line in the history  
Alt + > - Move to the last line in the history  
Alt + ? - Show current completion list  
Alt + * - Insert all possible completions  
Alt + / - Attempt to complete filename  
Alt + . - Yank last argument to previous command  
Alt + b - Move backward  
Alt + c - Capitalize the word  
Alt + d - Delete word  
Alt + f - Move forward  
Alt + l - Make word lowercase  
Alt + n - Search the history forwards non-incremental  
Alt + p - Search the history backwards non-incremental  
Alt + r - Recall command  
Alt + t - Move words around  
Alt + u - Make word uppercase  
Alt + back-space - Delete backward from cursor   
// SecureCRT 如果没有配置好，这个就很管用了。
```
#### 其他特定的键绑定:

输入 bind -P 可以查看所有的键盘绑定。这一系列我觉得更为实用。
```
Here "2T" means Press TAB twice  
$ 2T - All available commands(common) //命令行补全，我认为是 Bash 最好用的一点   
$ (string)2T - All available commands starting with (string)  
$ /2T - Entire directory structure including Hidden one  
$ ./2T - Only Sub Dirs inside including Hidden one  
$ *2T - Only Sub Dirs inside without Hidden one  
$ ~2T - All Present Users on system from "/etc/passwd" //第一次见到，很好用  
$ $2T - All Sys variables //写Shell脚本的时候很实用  
$ @2T - Entries from "/etc/hosts"  //第一次见到  
$ =2T - Output like ls or dir //好像还不如 ls 快捷  
补充:  
Esc + T - 交换光标前面的两个单词
```

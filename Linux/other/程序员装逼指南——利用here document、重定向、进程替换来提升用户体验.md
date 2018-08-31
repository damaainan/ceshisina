## 程序员装逼指南——利用here document、重定向、进程替换来提升用户体验

来源：[http://www.jianshu.com/p/f6b98e9769bd](http://www.jianshu.com/p/f6b98e9769bd)

时间 2018-05-09 21:55:51


程序员们一般都有个毛病，在他们内心都有这样的逻辑

``` 
if (我的程序.功能 == 你的程序.功能):
    if(我的程序.用户操作步骤 < 你的程序.用户操作步骤):
        assertTrue(我的程序.牛逼程度 > 你的程序.牛逼程度 )
```

所以程序员一般会殚精竭虑，舍生忘死的减少用户的操作步骤。本文就讲述了这么一个小故事。

  
#### 强迫症程序员诸葛建国的烦恼

诸葛建国现在有这么一个简单的shell脚本，叫做good.sh

``` 
#! /bin/bash
echo "run command 1"
echo "run command 2"
echo "run command 3"
echo "run command 4"

echo "run command 9527"
```

用户只要用

``` 
./good.sh
```


命令就可以执行。到目前为止，一切良好，但是现在有个小瑕疵，用户运行了命令之后，输出的日志都在控制台屏幕上。诸葛建国现在需要把这些日志同时放到一个文件里，这样如果运行中出现了错误，用户可以把日志文件发给诸葛建国而不用拿手机给屏幕拍照。同时运行日志也要同时出现在控制台上，这样用户可以实时查看运行状态。

让shell进程的标准输出同时出现在文件和控制台，最简单的方法是tee命令，所以诸葛建国只要让用户输入如下命令就行

``` 
./good.sh | tee install.log
```

等等，现在用户要多输入一大堆字母了么？诸葛建国陷入了深深的烦恼。

  
#### 牺牲小我，完成大我

不让用户多干活，就只能自己多干活。所以诸葛建国把代码立刻改造为如下形式

``` 
#! /bin/bash
echo "run command 1" | tee install.log
echo "run command 2" | tee -a install.log
echo "run command 3" | tee -a install.log
echo "run command 4" | tee -a install.log

echo "run command 9527" | tee -a install.log
```

注意除了第一个命令之外，tee都要使用-a选项表示追加。

这次客户是满意了，可是打字好累啊。怎么能站着把钱挣了呢？诸葛建国陷入了深深的烦恼。

  
#### here document

诸葛建国想到了一个绝招，写一个perfect.sh，里面内容如下

``` 
./good.sh | tee install.log
```

不过这样就需要给用户两个shell脚本了，能不能一个脚本解决问题呢？诸葛建国决定试试here document

``` 
#! /bin/bash
cat << EOF > poor.sh
echo "run command 1"
echo "run command 2"
echo "run command 3"
echo "run command 4"

echo "run command 9527"
EOF

chmod +x poor.sh
./poor.sh | tee install.log
rm -f poor.sh
```

利用here document先把命令都写到了一个poor.sh文件中，赋予它执行权限，然后执行，之后为了不留下这个文件存在的痕迹，立刻卸磨杀驴、过河拆桥，好像赵匡胤杯酒释兵权。

能不能把代码量再缩减一些呢？诸葛建国陷入了深深的烦恼。

  
#### 重定向和进程替换

这时同事夏侯富贵来到了诸葛建国旁边，默默加了一行代码，然后事了拂衣去，深藏身与名。

``` 
#! /bin/bash

exec &> >(tee -a install.log)

echo "run command 1"
echo "run command 2"
echo "run command 3"
echo "run command 4"

echo "run command 9527"
```

就多了一行代码！！！多的这行代码什么意思呢？


`exec`是重定向，例如 `exec &> a.log` 的意思是当前shell的标准输出和错误输出都重定向到a.log文件中。 `exec &> >(tee -a install.log)` 意思就是当前shell的标准输出和错误输出都重定向到了 `>(tee -a install.log)` 中。

那么 `>(tee -a install.log)` 又是何方神圣？这是一个进程替换（Process Substitution），像 `>(tee -a install.log)` 就会产生一个文件，向这个文件的写入会作为`tee -a install.log`的input。

所以 `>(tee -a install.log)` 会变现为一个文件，然后用`exec`把输出到重定向到这个文件就行了。

被打败了。。。诸葛建国陷入了深深的烦恼。

        
参考文档

[https://www.gnu.org/software/bash/manual/bashref.html#index-exec][0]

[https://www.gnu.org/software/bash/manual/bashref.html#Redirecting-Standard-Output-and-Standard-Error][1]

[https://www.gnu.org/software/bash/manual/bashref.html#Process-Substitution][2]
    

  

[0]: https://link.jianshu.com?t=https%3A%2F%2Fwww.gnu.org%2Fsoftware%2Fbash%2Fmanual%2Fbashref.html%23index-exec
[1]: https://link.jianshu.com?t=https%3A%2F%2Fwww.gnu.org%2Fsoftware%2Fbash%2Fmanual%2Fbashref.html%23Redirecting-Standard-Output-and-Standard-Error
[2]: https://link.jianshu.com?t=https%3A%2F%2Fwww.gnu.org%2Fsoftware%2Fbash%2Fmanual%2Fbashref.html%23Process-Substitution
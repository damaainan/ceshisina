## Linux(ubuntu18.04)切换python版本

来源：[https://blog.csdn.net/lishanleilixin/article/details/82908423](https://blog.csdn.net/lishanleilixin/article/details/82908423)

时间：


版权声明：本文为博主原创文章，未经博主允许不得转载。					https://blog.csdn.net/lishanleilixin/article/details/82908423				

### 前言


Ubuntu18.04系统在安装python时会安装两个版本:2.7和3.6．默认情况下系统环境使用的是python2，但是我们有时需要使用python3来作为我们的开发环境，所以需要自由切换python版本．

### python2切换成python3

```sh
sudo update-alternatives --install /usr/bin/python python /usr/bin/python2 100
sudo update-alternatives --install /usr/bin/python python /usr/bin/python3 150
```


执行上面两句命令即可将2.7版本切换成python3.6版本，想要查看是否切换成功：

```sh
shanlei@shanlei-Lenovo-ideapad-110-15ISK:~$ python --version
Python 3.6.5
shanlei@shanlei-Lenovo-ideapad-110-15ISK:~$ 
```

### python3切换成python2

```sh
shanlei@shanlei-Lenovo-ideapad-110-15ISK:~$ sudo update-alternatives --config python
[sudo] shanlei 的密码： 
有 2 个候选项可用于替换 python (提供 /usr/bin/python)。

  选择       路径            优先级  状态
------------------------------------------------------------
* 0            /usr/bin/python3   150       自动模式
  1            /usr/bin/python2   100       手动模式
  2            /usr/bin/python3   150       手动模式

要维持当前值[*]请按<回车键>，或者键入选择的编号：1
update-alternatives: 使用 /usr/bin/python2 来在手动模式中提供 /usr/bin/python (python)
shanlei@shanlei-Lenovo-ideapad-110-15ISK:~$ python --version
Python 2.7.15rc1
shanlei@shanlei-Lenovo-ideapad-110-15ISK:~$ 
```


之后我们在切换python版本时就可以使用这个命令键入选择进行切换了：

```sh
sudo update-alternatives --config python
```


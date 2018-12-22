## Ubuntu 下 vi 输入方向键会变成 ABCD 的解决方法

来源：[https://www.cnblogs.com/Amedeo/p/8969258.html](https://www.cnblogs.com/Amedeo/p/8969258.html)

2018-04-28 18:49

Ubuntu 下 vi 输入方向键会变成 ABCD，这是 Ubuntu 预装的是 vim tiny 版本，安装 vim full 版本即可解决。

先卸载vim-tiny：

```LANG
$ sudo apt-get remove vim-common
```

再安装vim full：

```LANG
$ sudo apt-get install vim
```

然后我们就惊喜地发现恢复正常了。

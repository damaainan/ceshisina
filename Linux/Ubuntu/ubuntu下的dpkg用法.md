## 【转】ubuntu下的dpkg用法

来源：[http://www.jackpu.com/zhuan-ubuntuxia-de-dpkgyong-fa/](http://www.jackpu.com/zhuan-ubuntuxia-de-dpkgyong-fa/)

时间 2018-09-29 10:19:28


原文出处    [https://blog.csdn.net/wanghuohuo13/article/details/78916821?utm_source=copy][0]

[dpkg][1]
是一个 Debian 的一个命令行工具，它可以用来安装、删除、构建和管理Debian的软件包。

下面是它的一些命令解释：


1）安装软件

命令行：`dpkg -i <.deb file name>`示例：

```
dpkg -i avg71flm_r28-1_i386.deb
```


2）安装一个目录下面所有的软件包

命令行：`dpkg -R`示例：

```
dpkg -R /usr/local/src
```

3）释放软件包，但是不进行配置

命令行：dpkg –unpack package_file 如果和-R一起使用，参数可以是一个目录

示例：

```
dpkg –unpack avg71flm_r28-1_i386.deb
```

4）重新配置和释放软件包

命令行：dpkg –configure package_file

如果和-a一起使用，将配置所有没有配置的软件包
示例：

```
dpkg –configure avg71flm_r28-1_i386.deb
```

5）删除软件包（保留其配置信息）

命令行：`dpkg -r`示例：

```
dpkg -r avg71flm
```

6）替代软件包的信息

命令行：dpkg –update-avail</packages-file>

7）合并软件包信息

```
dpkg –merge-avail <Packages-file>
```

8）从软件包里面读取软件的信息

命令行：dpkg -A package_file

9）删除一个包（包括配置信息）

命令行：`dpkg -P`10）丢失所有的Uninstall的软件包信息

命令行：`dpkg –forget-old-unavail`11）删除软件包的Avaliable信息

命令行：

```
dpkg –clear-avail
```

12）查找只有部分安装的软件包信息

命令行：`dpkg -C`13）比较同一个包的不同版本之间的差别

命令行：

```
dpkg –compare-versions ver1 op ver2
```

14）显示帮助信息

命令行：`dpkg –help`15）显示dpkg的Licence

命令行：

```
dpkg –licence (or) dpkg –license
```

16）显示dpkg的版本号

命令行：`dpkg –version`17）建立一个deb文件

命令行：`dpkg -b direc×y [filename]`18）显示一个Deb文件的目录

命令行：`dpkg -c filename`19）显示一个Deb的说明

命令行：

```
dpkg -I filename [control-file]
```


20）搜索Deb包

命令行：

```
dpkg -l package-name-pattern
```

21)显示所有已经安装的Deb包，同时显示版本号以及简短说明

命令行：`dpkg -l`22）报告指定包的状态信息

命令行：

```
dpkg -s package-name
```

示例：

```
dpkg -s ssh
```

23）显示一个包安装到系统里面的文件目录信息

命令行：

```
dpkg -L package-Name
```

示例：dpkg -L apache2
24）搜索指定包里面的文件（模糊查询）

命令行：

```
dpkg -S filename-search-pattern
```

25）显示包的具体信息

命令行：`dpkg -p package-name`示例：

```
dpkg -p cacti
```

最后：

1、很多人抱怨用了Ubuntu或者Debian以后，不知道自己的软件给安装到什么地方了。其实可以用上面的dpkg -L命令来方便的查找。看来基础还是非常重要的，图形界面并不能够包办一切。

2、有的时候，用“新力得”下载完成以后，没有配置，系统会提示用“dpkg –configure -all”来配置，具体为什么也可以从上面看到。

3、现在Edgy里面可以看到Deb的信息。不过是在没有安装的时候（当然也可以重新打开那个包），可以看到Deb的文件路径。

4、如果想暂时删除程序以后再安装，第5项还是比较实用的，毕竟在Linux下面配置一个软件也并非容易。


[0]: https://blog.csdn.net/wanghuohuo13/article/details/78916821?utm_source=copy
[1]: https://www.dpkg.org/
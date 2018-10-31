## IntelliJ IDEA2017 修改缓存文件的路径

来源：[https://www.cnblogs.com/acm-bingzi/p/ideaCachePath.html](https://www.cnblogs.com/acm-bingzi/p/ideaCachePath.html)

2017-05-08 11:05

IDEA的缓存文件夹.IntelliJIdea2017.1，存放着IDEA的破解密码，各个项目的缓存，默认是在C盘的用户目录下，目前有1.5G大小。现在想要把它从C盘移出。

![][0] 
在IDEA的安装路径下中，进入bin目录后找到属性文件：idea.properties 用记事本打开，找到如下代码段：

```
#---------------------------------------------------------------------
# Uncomment this option if you want to customize path to IDE config folder. Make sure you're using forward slashes.
#---------------------------------------------------------------------
# idea.config.path=${user.home}/.IntelliJIdea/config

#---------------------------------------------------------------------
# Uncomment this option if you want to customize path to IDE system folder. Make sure you're using forward slashes.
#---------------------------------------------------------------------
# idea.system.path=${user.home}/.IntelliJIdea/system

#---------------------------------------------------------------------
# Uncomment this option if you want to customize path to user installed plugins folder. Make sure you're using forward slashes.
#---------------------------------------------------------------------
# idea.plugins.path=${idea.config.path}/plugins
```

需要注意，在IDEA2017中，默认这些配置是 **`注释掉`** 的，我在这可吃了大亏，没想到配置没有生效的原因是如此简单。

更改配置文件如下：

```LANG
#---------------------------------------------------------------------
# Uncomment this option if you want to customize path to IDE config folder. Make sure you're using forward slashes.
#---------------------------------------------------------------------
idea.config.path=E:/jiashubing/.IntelliJIdea/config

#---------------------------------------------------------------------
# Uncomment this option if you want to customize path to IDE system folder. Make sure you're using forward slashes.
#---------------------------------------------------------------------
idea.system.path=E:/jiashubing/.IntelliJIdea/system

#---------------------------------------------------------------------
# Uncomment this option if you want to customize path to user installed plugins folder. Make sure you're using forward slashes.
#---------------------------------------------------------------------
idea.plugins.path=${idea.config.path}/plugins
```

更改了以后重启IDEA，会弹出如下界面 **`Complete Installation`** ，选择 **`Previous version`** 

![][1]

此时，在目录下生成了一个.IntelliJIdea文件

![][2]

此时把.IntelliJIdea2017.1删除了也可以，原来的项目也都能找得到

　　PS：初始缓存文件夹.IntelliJIdea2017.1的来历，之前安装的是IDEA2015，那时的缓存文件是.IntelliJIdea，后来将IDEA2015升级成了IDE2017，在同一个目录下生成了这个.IntelliJIdea2017.1文件夹。可能是IDEA内部的某种设置，如果文件中已经存在了.IntelliJIdea文件，就要在后面增加版本号后缀。


[0]: ./img/1851727027.png
[1]: ./img/1272511200.png
[2]: ./img/1591523270.png
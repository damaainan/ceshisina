## SublimeCodeIntel插件安装配置

来源：[https://blog.csdn.net/zSY_snake/article/details/79795039](https://blog.csdn.net/zSY_snake/article/details/79795039)

时间：

#### **`SublimeCodeIntel是Sublime Text的一款全功能代码智能自动完成插件`**  

## 安装： 

方法1：

安装SublimeCodeIntel最简单的方法是通过Package Control，在安装有packagecontrol插件的前提下按快捷键Ctrl+Shift+P输入pc如图选择install package

![][0]

然后输入SublimeCodeIntel选择回车即可

![][1]


方法2：

从  [http://github.com/SublimeCodeIntel/SublimeCodeIntel][3]下载最新的源代码 并将整个目录复制到Packages目录中。

=================================================================== 

## 配置： 


1.打开preferences->packages settings ->Package Control ->Settings-User

![][2]


如图添加上“SublimeCodeTntel”

2.


1) 点选sublime的preference中的browse packages找到我们安装的插键的目录

2)看是否有这样一个文件夹和这样一个文件 
3) (2如果找到可以不看）如果找不到，可以直接在SublimeCodeIntel目录下新建一个文件夹，文件夹名字为 **`.codeintel.   (注意这里面有两个点）`** ，在 文件夹里建一个txt然后另存为config.log 


4) 找到文件后在config.log中输入

```
{
    “PHP”：{
        “php”：'/ usr / bin / php'
        “phpExtraPaths”：[]
        “phpConfigFile”：'php.ini'
    }，
    “JavaScript”：{
        “javascriptExtraPaths”：[]
    }，
    “Perl”：{
        “perl”：“/ usr / bin / perl”，
        “perlExtraPaths”：[]
    }，
    “Ruby”：{
        “ruby”：“/ usr / bin / ruby​​”，
        “rubyExtraPaths”：[]
    }，
    “Python”：{
        “python”：'/ usr / bin / python'
        “pythonExtraPaths”：[]
    }，
    “Python3”：{
        “python”：'/ usr / bin / python3'
        “pythonExtraPaths”：[]
    }
}
```

3.  实现JS代码自动补全功能

preferences->packages settings ->SublimeCodeIntel ->Settings-User

添加  

```
{
    "JavaScript": {
        "codeintel_selected_catalogs": ["JavaScript"]
    } 
}


```


[3]: http://github.com/SublimeCodeIntel/SublimeCodeIntel
[0]: ../img/20180402210033364.png
[1]: ../img/20180402210443144.png
[2]: ../img/20180402211806407.png
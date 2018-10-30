## sublime text 3 在Windows下配置sublimelinter-php的路径问题

来源：[https://www.cnblogs.com/zzyyxxjc/p/5992159.html](https://www.cnblogs.com/zzyyxxjc/p/5992159.html)

2016-10-24 10:57

首先用package control安装sublimelinter和sublimelinter-php，然后依次点击菜单preference-package settings-sublimelinter-“settings-user“，内容中输入：

```
{
    "user":{
        "paths": {
            "linux": [],
            "osx": [],
            "windows": [
                "C://Users//zyx//myprogram//php7//php.exe"
            ]
        }
    }
}
```


其中`"C://Users//zyx//myprogram//php7//php.exe"`代表自己windwos系统中php存放的路径，不要写成`"C:\\Users\\zyx\\myprogram\\php7\\php.exe"`，百度上有些资料是这么写的，反复试过，没有任何作用，误人不浅，保存之后，系统会自动为你生成其他部分的配置，这样就可以显示php的语法错误了。

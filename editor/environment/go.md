Windows 下环境配置

 由于墙的存在，很多网址无法下载，推荐[https://studygolang.com/dl][100]去下载。

windows需要配置几个环境变量，我是下载的压缩文件，所以需要自己配置，通过安装程序安装的应该不需要设置环境变量，也可以去看下环境变量是否设置起。

![][0]主要就是这三个环境变量

GOBIN 是安装go目录里面的bin文件夹

GOPATH是你的工作目录

GOROOT是安装go的根目录

另外需要把GOBIN加入到path里面

![][1]

到这里go的基本配置就ok了，另外需要注意的是，你的go项目文件，都是放在工作目录里面的src下面的，一个项目一个文件。

![][2]

通过git拿取的项目都放到这个目录里面，一些go的扩展，可以用go get -v 地址 安装（要先进到src目录下面通过go get 安装）。

go的IDE工具，推荐用jetbrains发布的goland，很好用。

### 另一种安装方式

使用压缩包

设置环境变量  
GOROOT安装的文件夹,默认安装时已经设置好了  
GOPATH你自己的go工作空间地址,就是你后面项目的地址.允许多个  
工作空间中需要三个文件夹bin,pkg,src,分别是用来放执行的命令,包,源码的.GOBIN就是你的bin目录的位置.  
执行go version 查看输出信息,执行go env查询配置是否正确  


## Gosublime安装
GoSublime采用**`development`**分支，master分支好像已不维护了


#### 自动补全解决方法  
在windows和mac上使用sublime text3安装Gosublime插件后都无法自动补全代码，经过多日的研究找到如下解决方法。  
打开Perferences–Browse Packages…，进入Gosublime：  
1、在src目录下创建`margo`目录；  
2、拷贝`src/margo.sh/extension-example/extension-example.go`文件到`margo`目录下，改名为`margo.go`；  
3、拷贝`margo`文件夹（所有文件和目录）到`src/margo.sh/vendor`目录下；  
4、重新打开sublime text3，稍等几分钟就可以自动补全代码了。  

```
// user 配置文件
{
    "env": {
        "GOPATH": "G:/gopro",
        "GOROOT": "D:/go",
        "PATH": "$GOROOT/bin"
    }
}
```

## Gosublime配置

```
"gscomplete_enabled": false,
// Whether or not gsfmt is enabled
"fmt_enabled": false,
// 改为

"gscomplete_enabled": true,
 // Whether or not gsfmt is enabled
"fmt_enabled": true,

```


`go.sublime-build`

```
{ 
    "cmd": ["go", "run", "$file_name"], 
    "file_regex": "^[ ]*File \"(…*?)\", line ([0-9]*)", 
    "working_dir": "$file_path", 
    "selector": "source.go" 
}
```



[0]: ../img/1183845-20171114111434452-295129469.png
[1]: ../img/1183845-20171114112554327-2126910000.png
[2]: ../img/1183845-20171114111808921-1979885996.png
[100]: https://studygolang.com/dl
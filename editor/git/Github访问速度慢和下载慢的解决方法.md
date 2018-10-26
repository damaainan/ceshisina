## Github访问速度慢和下载慢的解决方法

来源：[https://blog.csdn.net/maiduoudo/article/details/81033898](https://blog.csdn.net/maiduoudo/article/details/81033898)

时间：

## 原因

为什么访问速度慢、下载慢？github的CDN被某墙屏了，由于网络代理商的原因，所以访问下载很慢。Ping github.com 时，速度只有300多ms。
## <a name="t1"></a>解决方法

绕过dns解析，在本地直接绑定host，该方法也可加速其他因为CDN被屏蔽导致访问慢的网站。
## <a name="t2"></a>具体解决过程

在本地host文件中添加映射，关于hosts的作用这里就不做声明了。

* windows系统的hosts文件的位置如下：

    C:\Windows\System32\drivers\etc\hosts

* mac/linux系统的hosts文件的位置如下：

    /etc/hosts

具体步骤如下：


* 用文本编辑器打开hosts文件
* 访问ipaddress网站[https://www.ipaddress.com/][0]，查看网站对应的IP地址，输入网址则可查阅到对应的IP地址，这是一个查询域名映射关系的工具
* 查询 `github.global.ssl.fastly.net` 和 `github.com` 两个地址
* 多查几次，选择一个稳定，延迟较低的 ip 按如下方式添加到host文件的最后面
* 保存hosts文件
* 重启浏览器，或刷新DNS缓存，告诉电脑hosts文件已经修改，linux/mac执行`sudo /etc/init.d/networking restart`命令；windows在cmd中输入`ipconfig /flushdns`命令即可。
* 起飞！！！


[0]: https://www.ipaddress.com/
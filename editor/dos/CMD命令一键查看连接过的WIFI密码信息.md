# CMD命令一键查看连接过的WIFI密码信息

 [实用资源][0] / 2017-12-02 更新于 2018-01-11 / [5 条评论][1] / [沈唁][2]

![][3]

经常在电脑上连接了别人的wifi或自己的wifi..忘记密码..想要在其它设备连接时可以使用这个命令直接查看连接过的wifi密码

### 用法

很简单

复制一键命令
```
    for /f "skip=9 tokens=1,2 delims=:" %i in ('netsh wlan show profiles') do  @echo %j | findstr -i -v echo | netsh wlan show profiles %j key=clear
```
直接打开“运行”（win键+R）

输入“cmd”确定

粘贴命令

回车

连接过的信息一目了然

![wifi][4]

wifi名称最前面是全部名称

往后翻就是每个连接的名称和密码等信息

![wifi信息][5]

wifi信息  
  
  
沈唁志，一个PHPer的成长之路！ 原创文章采用[CC BY-NC-SA 4.0协议][6]进行许可，转载请注明：转载自：[CMD命令一键查看连接过的WIFI密码信息][7]

[0]: https://qq52o.me/category/resource
[1]: https://qq52o.me/1456.html#comments
[2]: https://qq52o.me/author/shenyan
[3]: https://img.qq52o.me/wp-content/uploads/2017/12/2017120202412558.png
[4]: https://img.qq52o.me/wp-content/uploads/2017/12/2017120202394895.jpg
[5]: https://img.qq52o.me/wp-content/uploads/2017/12/2017120202402775.jpg
[6]: https://creativecommons.org/licenses/by-nc-sa/4.0/deed.zh
[7]: https://qq52o.me/1456.html
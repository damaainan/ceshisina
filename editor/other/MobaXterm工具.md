## MobaXterm工具

来源：[https://blog.csdn.net/juyin2015/article/details/79056687/](https://blog.csdn.net/juyin2015/article/details/79056687/)

时间：


版权声明：本文为博主原创文章，未经博主允许不得转载。	https://blog.csdn.net/juyin2015/article/details/79056687				

## 1    登录



## 1.1   远程登录



### 1.1.1 内建命令行模式


1)      点击

![][0]图标，在弹出的窗口中选择SSH连接，然后填入Remote host IP及用户名。 

![][1]


2)      点击确认后输入密码即可 

![][2]



### 1.1.2 Gnome模式(类似VNC)


1)      点击

![][0]图标，在弹出的窗口中选择SSH连接，然后填入Remote host IP及用户名。 

![][4] 

2)      点击Advanced SSH settings，设置Remote environment为 

![][5] 

3)      点击确认后输入密码即可 

![][6]



## 1.2   串口登录


1)      点击

![][0]图标，在弹出的窗口中选择Serial连接，然后选择port和speed。 

![][8]


2)      点击确认后即可



## 1.3   其他


MobaXterm还支持其他协议登录


1)      FTP 

![][9]


2)      TFTP 

![][10]



## 2    查看文件、上传下载文件


当远程登录后，我们在左侧会出现文件列表，我们可以直接从windows上传文件，下载文件。 

![][11]



## 3    多窗口同时执行


* 登录多个待同时执行的窗口后，点击

![][12]图标，如下： 

![][13]

* 点击

![][14]按钮，可以退出多窗口模式。




## 4    执行Linux下命令



## 4.1   gedit



## 4.2   wireshark


wireshark同样可以在这里执行，这样我们就可以在windows下查看Linux的抓包。 

![][15]



## 5   宏的使用


执行，MobaXterm具有录制宏、重放宏、编辑宏。如下图： 

![][16]

-----


Juyin@2018/1/14

[0]: ../img/20180114144501802.png
[1]: ../img/20180114144532444.png
[2]: ../img/20180114144546606.png
[3]: ../img/20180114144501802.png
[4]: ../img/20180114144620259.png
[5]: ../img/20180114144809273.png
[6]: ../img/20180114144734529.png
[7]: ../img/20180114144501802.png
[8]: ../img/20180114144921623.png
[9]: ../img/20180114144944352.png
[10]: ../img/20180114145000174.png
[11]: ../img/20180114145136940.png
[12]: ../img/20180114145157349.png
[13]: ../img/20180114145455645.png
[14]: ../img/20180114145509838.png
[15]: ../img/20180114145526807.png
[16]: ../img/20180114212555461.png
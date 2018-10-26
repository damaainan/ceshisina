## ubuntu搭建php开发环境记录

来源：[https://www.cnblogs.com/impy/p/8040684.html](https://www.cnblogs.com/impy/p/8040684.html)

2017-12-15 00:09

　　这两天自己在阿里云上面买了一个ecs，系统选的是ubuntu16.04，第一件事就是先搭环境，这次准备使用lamp组合。

　　Apache安装

　　首先安装apache服务器，ubuntu下面使用apt-get来下载安装软件。

　　
![][0]

　　输入密码后，便开始下载安装了，安装好后打开浏览器，输入localhost查看是否安装成功

　　
![][1]

　　如果如上显示的话，说明安装成功 了 。

　　PHP安装

　  这里我准备安装PHP7.0版本的，在命令行输入:sudo apt-get install php7.0

　　
![][2]

　　安装完成后输入：php -v 查看PHP是否安装成功

　　
![][3]

　　PHP和Apache都安装好后就需要让Apache能够识别解析PHP文件，我们先搜一下有没有适合PHP7的插件，输入命令：apt-cache search libapache2-mod-php

　　
![][4]

　　可以看到搜出来的结果里面有一个是PHP7.0版本的，我们就安装这个：sudo apt-get install libapache2-mod-php7.0

　　
![][5]

　　下面我们就可以随便写一个php文件看是否可以解析访问。输入命令: cd /var/www/html 切换到apache项目目录下，新建文件：sudo vim test.php

　　
![][6]

　　保存后浏览器访问：localhost/test.php

　　
![][7]

　　如果一切正常的话，就会看到php的一些信息。

　　Mysql安装

　接下来就是安装数据库Mysql了,数据库需要装服务端和客户端两个，输入命令：sudo apt-get install mysql-server mysql-client

　　
![][8]

　　安装过程中会提示设置root账号的登录密码，输入后选择OK继续安装

　　
![][9]

　　安装完成后，输入mysql -V 查看安装的版本信息

　　
![][10]

　　同样的，我们还需要让mysql能够和php互动，安装php的mysql插件：sudo apt-get install php7.0-mysql

　　
![][11]

　　最后我们还可以安装一些常用的php扩展

　　
![][12]

　　到此关于lamp的软件就安装完成了，最后还可以安装一下composer：sudo apt-get install composer

　　
![][13]

　　安装好后输入命令：composer  查看是否成功

　　
![][14]

　　如图显示，安装过程就全部完成了。

[0]: ../img/1232737799.png
[1]: ../img/1215136517.png
[2]: ../img/206953980.png
[3]: ../img/870726331.png
[4]: ../img/1991329782.png
[5]: ../img/1114684878.png
[6]: ../img/1040218057.png
[7]: ../img/1043090460.png
[8]: ../img/1910120873.png
[9]: ../img/394077377.png
[10]: ../img/586981707.png
[11]: ../img/174145489.png
[12]: ../img/1419576323.png
[13]: ../img/1273756875.png
[14]: ../img/206514006.png
# [Windows下本地RabbitMQ服务的安装(V3.01)][0]

 2016-03-30 15:06  1120人阅读  


 分类：

当然这些内容页可以通过[RabbitMQ官方网站][5]获得。

RabbitMQ安装 ：

一、 RaibbitMQ 服务器安装

1． 准备工作。如果之前安装过 RabbitMQ 软件，若想重新安装，必须先把之前的 RabbitMQ 相关软件卸载。

2． 安装 ERLANG 语言包。首先到 [http://www.erlang.org/download.html][5]这个页面下载  [Erlang Windows Binary File][5]并且运行。这个过程大约 5 分钟左右。

安装具体过程：

1.右键，**管理员身份运行** ： **otp_win32_R16801.exe**( 不同版本可能命名字不一样 ) ，选择 next

如果不是管理员身份运行，启动Rabbitmq不能成功：**Failed to start service RabbitMQ. Error: 拒绝访问。**

解决办法：[卸载，清理注册表重新安装erlang][5]，先卸载RabbitMQ和Erlang，再清理注册表。

清理的时候如果找不到注册表项，可以用注册表的搜索功能搜索：RabbitMQ、 ErlSrv，将对应的项全部删除。

2. 默认安装在 C 盘，建议程序安装在非系统盘比如 D 盘（如果安装在 C 盘可能会出现一些权限问题），修改好安装路径后，选 next ：

3. 进入安装程序，选择 install ，即可完成安装：

3． 安装 RabbitMQ 服务器软件。到这个页面下载：

[http://www.rabbitmq.com/releases/rabbitmq-server/v3.1.3/**rabbitmq-server-3.1.3.exe**][5]。然后运行安装。

安装具体过程：

1.**双击rabbitmq-server-3.1.1.exe**。选择 next:

2. 默认安装在 C 盘，直接安装即可。

4． 还需要配置环境变量 :

 4.1**给path变量添加内容** ，在其后面增加： ;%RABBITMQ_SERVER%\sbin （注意前面的分号），然后确定即可

4.2 **添加环境变量： RABBITMQ_SERVER**

环境变量RABBITMQ_SERVER 的值为：**D:\My-Softwar-Installed\RabbitMQ Server\rabbitmq_server-3.1.3**

现在打开 windows 命令行（“ cmd ”），输入 **rabbitmq-service**

如果出现如下所示提示，即表示环境变量配置成功。

安装完成之后可以在系统的服务中看到RabbitMQ这个服务。

![][6]

5． Rabbit 还自带监控功能 .   
cmd 进到 sbin 目录，键入 rabbitmq-plugins enable rabbitmq_management 启用监控管理，然后重启 Rabbitmq 服务器。 打开网址 http://localhost:55672 ，用户名和密码都是 guest 。

6． 现在打开浏览器，输入：[http://localhost:15672/][5] ，如果出现以下页面，则表示服务器配置成功。

默认用户名为 guest, 密码： guest

如果没有出现以上页面，尝试在 windows 命令行中输入 ( 以管理员方式运行 ) ：

    rabbitmq-plugins enable rabbitmq_management

然后运行下面的命令来安装：

    rabbitmq-service stop
    rabbitmq-service install
    rabbitmq-service start

[0]: http://blog.csdn.net/xingxing513234072/article/details/51014695
[5]: http://demo.netfoucs.com/calmreason/article/details/23335237#
[6]: http://img.blog.csdn.net/20151208163221798?watermark/2/text/aHR0cDovL2Jsb2cuY3Nkbi5uZXQv/font/5a6L5L2T/fontsize/400/fill/I0JBQkFCMA==/dissolve/70/gravity/Center
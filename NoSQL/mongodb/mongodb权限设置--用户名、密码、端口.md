# [mongodb 权限设置--用户名、密码、端口][0]

## 一、关于权限的默认配置

在默认情况下，mongod是监听在0.0.0.0之上的，任何客户端都可以直接连接27017，且没有认证。这样做的好处是，用户可以即时上手，不用担心被一堆配置弄的心烦意乱。然而坏处也是显而易见，如果直接在公网服务器上如此搭建MongoDB，那么所有人都可以直接访问并修改数据库数据了。

默认情况下，mongod也是没有管理员账户的。因此除非你在admin数据库中使用db.addUser()命令添加了管理员帐号，且使用–auth参数启动mongod，否则在数据库中任何人都可以无需认证执行所有命令。包括delete和shutdown。

此外，mongod还会默认监听28017端口，同样是绑定所有ip。这是[一个mongod自带的web监控界面][1]。从中可以获取到数据库当前连接、log、状态、运行系统等信息。如果你开启了–rest参数，甚至可以直接通过web界面查询数据，执行mongod命令。

其实MongoDB本身有非常[详细的安全配置准则][2]，显然开发者也是想到了，然而他是将安全的任务推给用户去解决，这本身的策略就是偏向易用性的，对于安全性，则得靠边站了。

## 二、MongoDB用户类型

MongoDB的用户分为两种，一种是admin用户，另一种是特定数据库用户。admin用户拥有最高的权限，而特定数据库用户则只能访问特定的数据库。当MongoDB的admin库里没有任何用户的时候，也就是说整个MongoDB没有一个MongoDB用户的时候，即便–auth权限需求打开了，用户还是可以通过localhost界面进入MongoDB进行用户设置，否则的话整个MongoDB就完全没法访问了。而当这个用户创建完成之后，之后的用户登录和操作就需要授权了，不是直接登录就能使用的了。

MongoDB有一个比较奇怪的设置是，即便是一个admin用户，授权也必须在admin数据库下进行，而不能在其他数据库下进行。而授权之后admin用户就可以在任何数据库下进行任何操作了。当然数据库级别的用户在他自己的数据库下授权之后是不能到其他数据库进行操作的。举例来说：

> use test  
> db.auth(“someAdminUser”, password)

操作失败，提示还没有在admin数据库下对afmin用户进行授权。

## 三、操作实例

启动MongoDB，在cmd命令框里进入数据库的bin目录；

1. 输入命令：`show dbs`，你会发现它内置有两个数据库，一个名为admin，一个名为local；本文只对admin库进行描述

2. 输入命令：`use admin`，你会发现该DB下包含了一个名为`system.user`的`collection`，这是用户表，用来存放超级管理员的

    > 备注：本文使用的数据库版本是2.0.1，没有默认的admin数据库，但是在执行第二步之后自动创建了一个admin库； 当然也没有默认的system.user表，运行后面的第三步后会自动创建 system.user和system.indexes )

3. 输入命令：`db.addUser('root','root')`，这里我添加一个超级管理员用户，username为root，password也为root。先退出 (ctrl+c)程序，测试重启服务后再次连接MongoDB是否需要按提示输入用户名、密码进行操作。

4. 输入命令：`use admin`

5. 输入命令：`show collections`，查看该库下所有的表，你会发现，MongoDB并没有提示你输入用户名、密码，原因是，在文章最开始提到了，MongoDB默认设置为无权限访问限制，我们需要先把它设置成为需要权限访问

6. 从新打开cmd，在mongodb路径的bin目录下，执行mongod --dbpath d:\work\data\mongodb\db --**auth**

7. 输入命令：`use admin`

8. 输入命令：`show collections`，提示："$err" : "unauthorized db:admin lock type:-1 client:127.0.0.1"

    显然，已经提示没有权限；用刚才设置的用户名、密码来访问集合

9. 输入命令：`db.auth(“root”,”root”)`，输出一个结果值为1，说明这个用户匹配上了，如果用户名、密码不对，输出为0

10. 输入命令：`show collections`，将成功显示结果

    继续操作，可以访问已经存在的数据库，但对于新建的数据库仍然没有权限；继续操作，先退出(ctrl+c)服务

11. 输入命令：`mongo TestDB`

12. 输入命令：`show collections`，提示：没有权限

13. 输入命令：`db.auth(“root”, “root”)`，输出结果为0，说明用户名或者密码有问题，刚刚前面才创建，怎么会不对呢？原因在于：当我们 **单独访问**MongoDB的数据库时，需要权限访问的情况下，用户名密码并非超级管理员，而是该库的`system.user`表中的用户，注意，我这里说的是 **单独访问**的情况，什么是 **不单独访问**的情况呢？后面再讲。针对上述情况，接下来操作：

14. 输入命令：`db.addUser('test','111111')`，仍然提示没有权限，新的数据库使用超级管理员也无法访问，创建用户也没有权限，不过即然设定了超级管理员用户，那它就一定有权限访问所有的库

15. 输入命令：`use admin`

16. 输入命令：`db.auth(“root”, “root”)`

17. 输入命令：`use TestDB`

18. 输入命令：`show collections`，之后可以利用超级管理员用户访问其它库了，这个就是**不单独访问**的情况。在上述操作过程中，我们是先进入admin库，再转到其它库来的，admin相当于是一个最高级别用户所在的区域，对数据库操作，需要经过最高级别用户，之后可以创建每个数据库的用户。

19. 输入命令：`db.addUser('test','12345')`，我们给TestDB库添加一个用户，以后每次访问该库，我都使用刚刚创建的这个用户，我们先退出（ctrl+c）

20. 输入命令：`mongo TestDB`

21. 输入命令：`show collections`，提示没有权限

22. 输入命令：`db.auth('test','12345')`，输出结果1，用户存在，验证成功

23. 输入命令：`show collections`，成功显示结果

## 四、启动和关闭MongoDB的各种参数

详见：[http://blog.csdn.net/pgwindwind/article/details/8005262](http://blog.csdn.net/pgwindwind/article/details/8005262)

比如要改变MongoDB的默认端口，则可以这样使用`--port`参数：

打开cmd，在mongodb路径的bin目录下，执行
    
    mongod --port 50107 --dbpath d:\work\data\mongodb\db --auth

这样访问MongoDB就是以50107的端口访问了

[0]: http://www.cnblogs.com/valor-xh/p/6369432.html
[1]: http://www.mongodb.org/display/DOCS/Http+Interface
[2]: http://www.mongodb.org/display/DOCS/Security+and+Authentication
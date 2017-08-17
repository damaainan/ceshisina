##### 0.服务端和客户端安装git，ssh

    sudo apt-get install git
    sudo apt-get install ssh

##### 1.服务器创建一个用户

我创建一个用户名为server的用户

    sudo adduser server

创建一个用户
管理员帐户不需要加sudo
##### 2.在服务器server用户文件夹配置信息

在server用户文件夹中，创建`.ssh`文件夹

    mkdir .ssh
在`.ssh`中`touch authorized_keys`文件

    touch .ssh/authorized_keys

root 用户自带的有，此步可省

##### 3.用户生成key

这一步既可以在服务器端直接生成，也可以在客户端生成。不管在哪里生成，只要能得到两个文件即可：

私钥
公钥
下面以在客户端生成为例：

在客户端打开Git Bash，执行：

    ssh-keygen -t rsa
之后会要求输入一个用户名，我输入的是Mike。后面的直接按回车即可。


生成用户`ssh key`
完成后，会生成2个文件： Mike和Mike.pub，分别是私钥和公钥


##### 4.客户端将私钥放入客户端的工作目录下

查看当前客户端工作目录：

    cd ~
    pwd
将Mike文件放入该路径（我的是`C;/Users/KKDes/`）下的`.ssh`文件夹中；如果是第一次搭建，还要新建一个`config`的文件，并写入以下内容：
```
host git-server 
    user server
    hostname 119.29.147.xxx
    port 22 
    identityfile ~/.ssh/Mike
```
注意除第一行，其余要缩进一个`tab`
这里的Mike替换为自己之前创建key时输入的用户名
`hostname` 后面替换为你的服务器IP地址
##### 5.服务器将公钥追加到服务器的`authorized_keys`文件中

    vim authorized_keys

编辑authorized_keys文件
将公钥的内容追加到此文件中

##### 6.服务器初始化一个bare的git仓库

在服务器server用户的文件夹下创建一个文件夹repo（名字任意）用以存放代码仓库，进入此文件及，开始创建bare的git仓库

    git init --bare test.git

在服务器中初始化一个bare的git仓库
至此，服务器端的git服务器就搭建好了；接下来，要在客户端进行clone和push操作。

##### 7. 在客户端Clone远程的代码仓库

    git clone git-server:/home/server/repo/test.git

从服务器clone到本地
git-server：表示我们在config文件配置的服务器IP地址，直接写“git-server”即可，当然，你也可以修改config文件里的名字
/home/server/repo/test.git：这个是远程服务器的仓库地址，按照实际情况自行修改
这样，会在gitclient/test/下创建一个名为test的文件夹（.git会被省略）。
我们可以做一个测试，在gitclient/test/test文件夹中添加一个文件，并提交。

推送到远程：

    git push git-server:/home/server/repo/test.git master
注意，这里的master代表推送到master分支，如果要推送到其他分支，修改它。


提交更新并push到远程服务器
我们去其他目录再clone下来：


在另一个目录下clone远程代码
可以看到，之前提交的文件已经可以看到了


来自 [http://www.jianshu.com/p/10b6a1ee7f64](http://www.jianshu.com/p/10b6a1ee7f64)
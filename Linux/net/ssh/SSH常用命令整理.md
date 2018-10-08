## SSH常用命令整理

来源：[http://www.jianshu.com/p/cc3df9978874](http://www.jianshu.com/p/cc3df9978874)

时间 2018-02-08 20:00:47


  
### 1. 生成密钥

```sh
ssh-keygen
```

默认情况下在用户目录下创建`.ssh`文件夹，并生成了公钥（id_rsa.pub），以及私钥（id_rsa）

  
### 2. ssh 远程登陆主机

```sh
ssh user@host -p port
// 或者
ssh host -l user -p port
```


user: 登陆的用户

host: 登陆的主机

参数 -p 为ssh端口

若是第一次登陆该主机，会显示目标主机的公钥指纹，提示无法确认主机的身份，并询问是否继续；用户需要自行确认主机的其实性，这是为了防止ssh的中间人攻击。

      
### 3. 无密码登陆主机


若无其他配置，使用 2 中的方法登陆主机时，每次都要求输入密码，不仅繁琐，还不安全。

如何设置无密码登陆：


* 假设两台主机A, B，A无密码登陆B；
* A，B主机各自生成密钥，这是基础；
* B主机的`~/.ssh`目录下新建文件`authorized_keys`，并将A主机的公钥拷贝进去；该文件可以存多个公钥，一行一个；      
* 此时A主机可无密码登陆B主机。
    

  
### 4. 使用别名登陆主机

新建文件`~/.ssh/config`，并写入如下内容（注意缩进）：

```
Host myhost
    HostName 138.xxx.xxx.xxx
    Port xxxx
    User root
    IdentityFile ~\.ssh\id_rsa
```


HostName：要登陆的主机名或 IP 地址

Port：目标主机的 ssh 端口

User：登陆的用户

IdentityFile：用于登陆的私钥

      
### 5. 主机之间发送文件

  
#### （1）从本机复制到远程主机

```sh
// 将本机 file 文件发送到host主机的folder目录下, -p 为ssh端口（大写）
scp -P port file user@host:folder

// 复制目录
scp -r  -P port folder user@host:folder
```

  
#### （2）从远程主机复制到本机

```sh
// 将本机 file 文件发送到host主机的folder目录下
scp -P port user@host:folder file

// 复制目录
scp -P port -r user@host:folder folder
```


## Centos7下搭建SS

来源：[http://bustlingv.com/2018/03/29/Centos7下搭建SS/](http://bustlingv.com/2018/03/29/Centos7下搭建SS/)

时间 2018-03-29 17:14:50

```LANG
sudo yum -y install python-pip #安装pip
pip install --upgrade pip #升级pip

```

### 2.安装ss 

```LANG
pip install shadowsocks

```

### 3.服务端配置 

```LANG
vi /etc/shadowsocks.json #新建一个配置

{
    "server":"0.0.0.0",
    "port_password":{
      "端口号":"密码",
      "端口号2":"密码2"
    },
    "timeout":300,
    "method":"aes-256-cfb",
    "fast_open":false
}

```

### 4.防火墙打开端口 

```LANG
firewall-cmd --zone=public --add-port=端口/tcp --permanent

```

### 5.开启关闭服务 

```LANG
ssserver -c /etc/shadowsocks.json -d start 
ssserver -c /etc/shadowsocks.json -d stop

```

### 6.安装锐速 

```LANG
#更换内核为 3.10.0-229.1.2.el7.x86_64
rpm -ivh http://soft.91yun.org/ISO/Linux/CentOS/kernel/kernel-3.10.0-229.1.2.el7.x86_64.rpm --force

#重启服务器
reboot
#shutdown -r now

#安装锐速
wget -N --no-check-certificate https://raw.githubusercontent.com/91yun/serverspeeder/master/serverspeeder-all.sh && bash serverspeeder-all.sh

#启用锐速
service serverSpeeder start

```

### 7.客户端使用 

#### 6.1Windows下使用方法 
[下载地址][8] , 下载完之后解压 双击运行Shadowsocks.exe

点击 添加，输入服务器ip\端口\密码\选择加密方式，代理端口默认1080无需更改, 保存即可。
![][0]
然后更新下PAC启用即可
![][1]

#### 6.1.1 使用Proxy SwitchyOmega 
步骤 a - b

a.新建情景模式，名称任意，类型选择代理服务器
![][2]
协议选择SOCK5、代理服务器填写127.0.0.1
![][3]
b.新建情景模式，名称任意，类型选择自动切换模式
![][4]
设置如下图所示，

条件设置为

```LANG
*.github.com

```

规则列表网址：

```LANG
https://raw.githubusercontent.com/gfwlist/gfwlist/master/gfwlist.txt

```

情景模式 都选择之前设置的a情景，然后点击下方“立即更新情景模式” 即可。
![][5]

到此windows下可以科学上网了、

#### 6.2 IOS端 

安装Shadowrocket.(商店太贵，XX助手可以下载)

a.选用配置文件，点击右上角+，地址填写

```LANG
https://raw.githubusercontent.com/lhie1/Surge/master/Shadowrocket.conf

```

然后点击新添加的配置文件，下载并选中使用
![][6]

b.回到首页，点击右上角+出现如图所示界面。 服务器、端口配置同windows下配置即可。保存
![][7]

c.回到首页、点击链接。就可以了。

其他详细配置：

```LANG
https://www.hinwen.com/3662.html

```

[8]: https://github.com/shadowsocks/shadowsocks-windows
[0]: ../IMG/VZnyIz7.png 
[1]: ../IMG/Fbqu2az.png 
[2]: ../IMG/vqURfmV.png 
[3]: ../IMG/mqYj6bM.png 
[4]: ../IMG/2U3Ejy7.png 
[5]: ../IMG/RVbmUfA.png 
[6]: ../IMG/If6jAvf.png 
[7]: ../IMG/ZNbyE3V.png 
## Nginx-深度学习篇

来源：[https://segmentfault.com/a/1190000014717018](https://segmentfault.com/a/1190000014717018)


## Nginx-深度学习篇
## 一、动静分离

通过中间件将动态请求和静态请求进行分离。
 **`原因：`** 分离资源，减少不必要的请求消耗，减少请求延时。 **`动态和静态请求图例：`** 


![][0]

* 基本配置

```nginx
upstream php_api{
    server 127.0.0.1:8080;
}
server {
    root filePath;
    location ~ \.php$ {
        proxy_pass http://php_api;
        index index.html index.htm;
    }
    location ~ \.(jpg|png|gif) {
        expires 1h;
        gzip on;
    }
}
```
## 二、Rewrite规则
### 1、场景：


* URL访问跳转，支持开发设计（页面跳转、兼容性支持、展示效果等）
* SEO优化
* 维护（后台维护、流量转发等）
* 安全


### 2、配置语法
#### rewrite


* 配置语法：rewrite regex replacement [flag];
* 默认：无
* Context：server，location，if

 **`示例：`** ` rewrite ^(.*)$ /pages/main.html break; `
* regex（正则）

Linux中 **`pcregrep`** 命令可以用来测试正则表达式。

| 元字符 |含义 |
|-|-|
| . | 匹配除换行符以外的任意字符 |
| ? | 重复0次或1次 |
| + | 重复1次或更多次 |
| d | 匹配数字 |
| * | 贪婪模式，有多少匹配多少 |
| ^ | 匹配开头 |
| $ | 匹配结尾 |
| {n} | 重复n次 |
| {n,} | 重复n次或更多次 |
| [c] | 匹配单个字符c |
| [a-z] | 匹配a-z小写字母的任意一个 |
| \ | 转移字符 |
| ( ) | 用于匹配()之间的内容，通过`$1`、`$2`调用 |

* flag

| flag | 含义 |
|-|-|
| last | 停止rewrite检测 |
| break | 停止rewrite检测 |
| redirect | 返回302临时重定向，地址栏会显示跳转后的地址 |
| permanent | 返回301永久重定向，地址栏会显示跳转后的地址 |

* 301永久重定向：除非用户清理缓存，否则下次请求还是会请求到重定向
* 302临时重定向：用户下次请求还会经过服务端重定向
* last 和 break的区别：last会新建一个连接，往下继续进行匹配。break会直接停留在那一级。
* redirect：关闭nginx后，重定向会失效。
* permanent：关闭nginx，也会重定向到新的地址。


 **`实例：`** 
```nginx
location / {
    # 文件不存在，直接访问4399
    if (!-f $request_filename) {
        rewrite ^/(.*)$ http://www.4399.com;
    }
}
```

* 优先级


* 执行server块的rewrite指令
* 执行location匹配
* 执行选中的location中的rewrite

## 三、Nginx的高级模块
#### 1. secure_link_module模块

（1）制定并允许检查请求的链接的真实性以及保护资源免遭未经授权的访问
（2）限制链接生效周期 **`图例：`** 


![][1]

* 配置语法


* secure_link


* 配置语法：secure_link expression;
* 默认：无
* Context：http，server，location

* secure_link_md5


* 配置语法：secure_link_md5 expression;
* 默认：无
* Context：http，server，location

 **`简单配置实例：`** 

```nginx
root /opt/app/code;

location / {
    secure_link $arg_md5,$arg_expires;
    secure_link_md5 "$secure_link_expires$uri 自定义字符串";

    if ($secure_link = "") {
        return 403;
    }
    if ($secure_link = "0") {
        return 410;
    }
}
```
 **`生成url的脚本：`** 

```
#!/bin/bash

servername="你的servername"
download_file="/download/test.img"
time_num=$(date -d "2018-10-18 00:00:00" +%s)
secure_num="自定义字符串"

res=$(echo -n "${time_num}${download_file} ${secure_num}"|openssl md5 -binary | open
ssl base64 | tr +/ -_ | tr -d =)

echo "http://${servername}${download_file}?md5=${res}&expires=${time_num}"
```

 **`注意：`** 1、生成脚本中自定义字符串和配置中的自定义字符串要保持一致。2、验证规则保持一致。3、如果没有openssl，可以yum安装。
#### 2. geoip_module模块

基于IP地址匹配 **`MaxMine GeoIP`** 二进制文件，读取IP所在地域信息。
默认安装的Nginx是没有安装geoip这个模块的，安装命令：
`yum install nginx-module-geoip`

* 使用场景：


* 区别国内外做HTTP访问规则
* 区别国内城市地域做HTTP访问规则

* 使用步骤：


* 安装geoip：`yum install nginx-module-geoip`，安装完成查看/etc/nginx/module目录下，如果有对应的so文件，则说明安装成功
* 在/etc/nginx/nginx.conf配置文件开头加入


* `load_module "modules/ngx_http_geoip_module.so";`
* `load_module "modules/ngx_stream_geoip_module.so";`

* 下载地域分区文件：


* `wget http://geolite.maxmind.com/download/geoip/database/GeoLiteCountry/GeoIP.dat.gz`
* `wget http://geolite.maxmind.com/download/geoip/database/GeoLiteCity.dat.gz`

* 使用`gunzip`命令解压下载下来的文件

 **`配置示例`** 

```nginx
geoip_country /etc/nginx/geoip/GeoIP.dat;
geoip_city /etc/nginx/geoip/GeoLiteCity.dat;
server{
    location /myip {
        default_type text/plain;
        return 200 "$remote_addr $geoip_country_name $geoip_country_code $geoip_city";
    }
}
```
## 四、基于Nginx的HTTPS服务
#### 1、为什么需要HTTPS

* 原因：HTTP不安全


* 传输数据被中间人盗用、信息泄露
* 数据内容劫持、篡改

#### 2、HTTPS协议的实现

对传输内容进行加密以及身份验证

* 对称加密和非对称加密
* HTTPS加密协议原理
* 客户端在使用HTTPS方式与Web服务器通信的步骤


* 客户使用https的URL访问Web服务器，要求与Web服务器建立SSL连接
* Web服务器收到客户端请求后，会将网站的证书信息（证书中包含公钥）传送一份给客户端
* 客户端的浏览器与Web服务器开始协商SSL连接的安全等级，也就是信息加密的等级
* 客户端的浏览器根据双方同意的安全等级，建立会话密钥，然后利用网站的公钥将会话密钥加密，并传送给网站
* Web服务器利用自己的私钥解密出会话密钥
* Web服务器利用会话密钥加密与客户端之间的通信

 **`通信原理图：`** 


![][2]
#### 3、证书签名生成
 **`准备步骤：`** 


* 确认openssl有无安装，`openssl version`
* nginx有无编译http-ssl-module，`nginx -V`

 **`生成自签证书步骤：`** 


* 生成key密钥

* `openssl genrsa -idea -out ronaldo.key 1024`


* 生成证书签名请求文件（csr文件）


* `openssl req -new -key ronaldo.key -out ronaldo.csr`
* 当提示输入 A challenge password时，表示ca文件需要更改的另外一个密码，直接回车即可。

打包上面两个步骤生成的文件发送给签名机构即可完成证书签名
* 生成证书签名文件（CA文件）

* openssl x509 -req -days 3650 -in ronaldo.csr -signkey ronaldo.key -out ronaldo.crt


 **`配置语法：`** 


* ssl


* 配置语法：ssl on | off;
* 默认：ssl off;
* Context：http，server

* ssl_certificate（crt文件所在位置）


* 配置语法：ssl_certificate file;
* 默认：无
* Context：http，server

* ssl_certificate_key（key文件所在位置）


* 配置语法：ssl_certificate_key file;
* 默认：无
* Context：http，server

 **`简单示例：`** 

```nginx
server {
    listen 443;
    server_name locahost;
    ssl on;
    ssl_certificate /etc/nginx/ssl_key/ronaldo.crt;
    ssl_certificate_key /etc/nginx/ssl_key/ronaldo.key;

    index index.html index.htm;
    location / {
        root /opt/app/code;
    }
}
```
 **`配置完成后：`** 


* 停止Nginx：`nginx -s stop -c /etc/nginx/nginx.conf`，会要求你输入ronaldo.key的密码。
* 启动Nginx：`nginx -c /etc/nginx/nginx.conf`，也会要求你输入密码。
* 查看是否启用了443端口：`netstat -luntp | grep 443`


#### 4、配置苹果要求的证书

* 服务器所有的连接使用TLS1.2以上的版本（openssl 1.0.2）


* 版本：`openssl version`
* 自签证书加密签名算法类型以及公钥位数：`openssl x509 -noout -text -in ./ronaldo.crt`
* 升级openssl的脚本

```
#!/bin/bash
cd /opt/download
wget https://www.openssl.org/source/openssl-1.0.2k.tar.gz
tar zxf openssl-1.0.2k.tar.gz
cd openssl-1.0.2k
./config --prefix=/usr/local/openssl
make && make install
mv /usr/bin/openssl /usr/bin/openssl.OFF
mv /usr/include/openssl /usr/include/openssl.OFF
ln -s /usr/local/openssl/bin/openssl /usr/bin/openssl
ln -s /usr/local/openssl/include/openssl /usr/include/openssl
echo "/usr/local/openssl/lib" >> /etc/ld.so.conf
ldconfig -v
openssl version -a
```


* HTTPS证书必须使用SHA256以上哈希算法签名
* HTTPS证书必须使用RSA 2048位或ECC 256位以上公钥算法
* 使用向前加密技术


通过自签方式、符合苹果要求、通过key文件直接生成crt文件：

* `openssl req -days 36500 -x509 -sha256 -nodes -newkey rsa:2048 -keyout ronaldo.key -out ronaldo_apple.crt`
* `-keyout`参数会同时再生成一个key文件（没有保护码），reload Nginx就不用再次输入密码。
* 生成crt文件后，只需要修改配置文件即可
* 直接生成没有保护码的key：`openssl rsa -in ./ronaldoold.key -out ./ronaldonew.key`


#### 5、HTTPS服务优化


* 激活keepalive长链接

* 在配置文件写入：`keepalive_timeout 100`


* 设置ssl session缓存

* 在配置文件写入：`ssl_session_cache shared:SSL:10m`

## 五、Nginx与Lua的开发

 **`Nginx+Lua优势：`** 
充分的结合Nginx的并发处理epoll优势和Lua的轻量实现简单的功能且高并发的场景。
#### 1、Lua

是一个简洁、轻量、可扩展的脚本语言

* 安装：`yum install lua`
* 运行：


* `lua`命令进入交互界面，输入：`print("Hello World")`即可
* 执行lua脚本：

```lua
#!/usr/bin/lua
print("Hello world")
```


* 注释


* - - 行注释
* - -[[块注释- -]]

* 变量


* a = 'alon123"'
* a = "alon123""
* a = '97lo1004923"'
* a = [[alo
* 123"]]
* 上述是同一个意思，第三点用的是ASCII表

 **`注意：`** 
Lua数值类型只有double类型
Lua布尔类型只有nil和false是false，数字0、空字符串都是true
Lua中的变量如果没有特殊说明，全是全局变量；如果希望是局部变量，签名加个local
Lua没有++或是+=这样的操作
~=：不等于
..：字符串拼接
io库的分别从stdin和stdout读写的read和write函数
* while循环语句

```lua
sum = 0
num = 1
while num <= 100 do
    sum = sum + num
    num = num + 1
end
print("sum =", sum)
```

* for循环语句

```lua
sum = 0
for i = 1,100 do
    sum = sum + i
end
```

* if-else判断语句

```lua
if age == 40 and sex == "Male" then
    print("大于40岁的男人")
elseif age>60 and sex ~= "Female" then
    print("非女人而且大于60")
else
    local age = io.read()
    print("Your age is"..age)
end
```
#### 2、Nginx + Lua环境

* 所需下载以及安装：


* LuaJIT
* ngx_devel_kit和lua-nginx-module
* 重新编译Nginx
* 详细的下载和安装步骤参见：

#### 3、Nginx调用lua模块指令

Nginx的可插拔模块化加载执行，共11个处理阶段

| 指令 | 含义 |
|-|-|
| set_by_lua,set_by_lua_file | 设置nginx变量，可以实现复杂的赋值逻辑 |
| access_by_lua,access_by_lua_file | 请求访问阶段处理，用于访问控制 |
| content_by_lua,content_by_lua_file | 内容处理器，接收请求处理并输出响应 |


#### 4、Nginx Lua API

| API | 含义 |
|-|-|
| ngx.var | nginx变量 |
| ngx.req.get_headers | 获取请求头 |
| ngx.req.get_uri_args | 获取url请求参数 |
| ngx.redirect | 重定向 |
| ngx.print | 输出响应内容体 |
| ngx.say | 同nginx.print，但是会回车 |
| ngx.header | 输出响应头 |
| ... | |


#### 5、灰度发布

按照一定的关系区别，分不分的代码进行上线，使代码的发布能平滑过渡上线。

* 根据用户的信息cookie等信息区别
* 根据用户的ip地址

 **`实现灰度发布示意图：`** 


![][3]

[0]: ../img/bV9UI7.png
[1]: ../img/bV9UJm.png
[2]: ../img/bV9UJn.png
[3]: ../img/bV9UI1.png




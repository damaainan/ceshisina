## Nginx总结

来源：[https://chenjiabing666.github.io/2018/06/06/Nginx总结/](https://chenjiabing666.github.io/2018/06/06/Nginx总结/)

时间 2018-06-06 10:23:10

 
 
* 是俄罗斯程序员开发的一款高性能的web服务器 
* Nginx可以承受高并发，可以同时承受近百万请求 
* 利用Nginx和Tomcat(应用服务器)组合搭建反向代理服务器集群，可以解决WEB的高并发问题 
 
 
## WEB 服务器 
 
```nginx
Http
```
 
## 安装Nginx 
 
### yum 安装 
 
 
* `yum -y install nginx
` 
* `nginx`启动
  
* `nginx -s stop`关闭
  
 
 
#### 启动命令 
 
 
* `systemctl start nginx.service` 
* `systemctl stop nginx.service` 
* `systemctl restart nginx.service` 
* `systemctl enable nginx.service` 
* `systemctl disable nginx.service` 
* `ps -A | grep nginx`: 检查  
* 启动之后在浏览器直接输入`http://IP地址`即可访问到主页，这里的端口号默认是`80` 
 
 
#### 配置文件 
 
 
* `/etc/nginx/nginx.conf` 
 
 
#### web目录 
 
 
* `/usr/share/nginx/html` 
 
 
### 安装包安装 
 
 
* [安装地址][9]  
 
 
## Nginx 配置文件 
 
```
/etc/nginx/nginx.conf
```
 
```nginx
worker_processes 1;   //worker进程，一般是电脑几核处理器就写几

events{
	worker_connections 1024;   //一个worker进程能够承受多少线程
}

http{
	http协议通用参数

	server{
		虚拟主机参数
	}
	server{
		虚拟主机参数
	}
}
```
 
 
* `nginx -t -c /etc/nginx/nginx.conf
`修改完成之后执行该命令，测试配置文件，热加载(不停机)配置文件  
* `nginx -s reload`: 重新启动`nginx` 
 
 
## 虚拟主机的三种方式 
 
 
* 基于端口的虚拟主机，80,8080，需要使用80以外的其他端口，客户端使用不方便 
* 基于IP虚拟机，一个服务器可以绑定多个IP 
* **`基于域名的虚拟主机，共享一个IP，共享一个80端口`**   
 
 
## 外网配置 
 
###前提
 
 
* 我们的服务器`Ｃｅｎｔｏｓ７`使用`yum install nginx`安装了`Nginx`，那么这个Nginx的`web`目录就在`/usr/share/nginx/html` 
* 配置文件的路径为：`/etc/nginx/nginx.conf
` 
* #### 有自己的域名
  
 
 
### 配置开始 
 
 
* 申请自己的域名： 比如`chenjiabing.org`，并且将自己的`域名解析`绑定在服务器的`ip地址`上 
 
 
* 比如将下面需要用到的`t1.chenjiabing.org`和`t2.chenjiabing.org`解析在上面，那么我们在访问`t1.chenjiabing.org`的时候才能找到`ip`，随之找到自己的服务器，之后再根据在Nginx中配置的虚拟主机访问到对应的页面    
   
  
* ![][0]
  
* 在`/etc/nginx/nginx.conf
`的配置文件中只需要配置虚拟主机即可，这里我们配置两个虚拟主机    
 
 
```nginx
      # 自己配置的虚拟主机
   server{
        listen 80;    # nginx的端口
        server_name t1.chenjiabing.org;     # 访问的地址
        location / {
                root t1;     # 这个虚拟主机对应的web目录，这里设置的路径为/usr/share/t1
                index index.html;   # 默认显示的首页
        }
}

# 自己配置的虚拟主机
   server{
        listen 80;  
        server_name t2.chenjiabing.cn;    # 访问的地址
        location / {
                # /usr/share/nginx/t2    
                root t2;            # 这个虚拟主机的web目录
                index index.html;  # 显示的首页
        }
}

# nginx默认的虚拟主机
server {
        listen       80 default_server;
        listen       [::]:80 default_server;
        server_name  _;
        root         /usr/share/nginx/html;    # 默认的web目录，其实这里可以写 html 是一样的

        # Load configuration files for the default server block.
        include /etc/nginx/default.d/*.conf;

        location / {
        }

        error_page 404 /404.html;
            location = /40x.html {
        }

        error_page 500 502 503 504 /50x.html;
            location = /50x.html {
        }
    }
```
 
 
* 配置上面的两个虚拟主机之后，我们需要配置对应的`web`目录，我们只需要在`/usr/share/nginx/`这个目录中新建`t1`和`t2`两个文件夹作为两个虚拟主机的`web`目录，之后，在其中创建`index.html`作为显示的主页即可  
* 测试启动`Nginx`:`nginx -t -c /etc/nginx/nginx.conf
` 
* 重新启动`nginx`：`nginx -s reload` 
* 之后在浏览器中输入`t1.chenjiabing.org`,那么浏览器就会自动访问到`/usr/share/nginx/t1/index.html`显示首页内容，同样的输入`http://t2.chenjiabing.org` 
 
 
## 内网配置 
 
### 前提 
 
 
* 没有自己的域名，但是我们想要使用域名访问服务器上的Nginx的内容，比如，我们输入`t1.tedu.cn`就想要访问到服务器地址为：`47.104.192.157`中Nginx配置的虚拟主机
  
* 使用的是Linux，hosts文件所在目录为`/etc/hosts` 
* 使用的是windows，那么hosts文件所在的目录为`C:\Windows\System32\Drivers\etc\hosts` 
 
 
### 本地配置 
 
 
* 我们需要在`/etc/hosts`中添加对应需要的域名和服务器的`IP地址` 
* `sudo vi /etc/hosts`,输入以下内容  
 
 
```nginx
47.104.192.157 t1.tedu.cn    ## 前面是服务器的IP地址，后面是需要访问的域名，这个是没有申请的域名，可以直接写

47.104.192.157 t2.tedu.cn
```
 
 
* 配置完成之后，使用`ping t1.tedu.cn`查看是否能够找到对应的IP地址`47.104.192.157` 
 
 
### 服务器配置虚拟主机 
 
 
* 和上面的配置一样，不过是这次配置的域名是`t1.tedu.cn` 
 
 
```nginx
      # 自己配置的虚拟主机
   server{
        listen 80;    # nginx的端口
        server_name t1.tedu.org;     # 访问的地址
        location / {
                root t1;     # 这个虚拟主机对应的web目录，这里设置的路径为/usr/share/t1
                index index.html;   # 默认显示的首页
        }
}

# 自己配置的虚拟主机
   server{
        listen 80;  
        server_name t2.tedu.cn;    # 访问的地址
        location / {
                # /usr/share/nginx/t2    
                root t2;            # 这个虚拟主机的web目录
                index index.html;  # 显示的首页
        }
}

# nginx默认的虚拟主机
server {
        listen       80 default_server;
        listen       [::]:80 default_server;
        server_name  _;
        root         /usr/share/nginx/html;    # 默认的web目录，其实这里可以写 html 是一样的

        # Load configuration files for the default server block.
        include /etc/nginx/default.d/*.conf;

        location / {
        }

        error_page 404 /404.html;
            location = /40x.html {
        }

        error_page 500 502 503 504 /50x.html;
            location = /50x.html {
        }
    }
```
 
 
* 同样的创建`t1`和`t2`的web目录  
* 测试加载，重启 
* 在本地机器的浏览器中输入`t1.tedu.cn`即可访问到，不过这次只能在配置`hosts`的本地机器使用，如果该机器没有配置这个`hosts`文件，那么是不能访问的    
 
 
### 搜索过程 
 
 
* 在本地配置过的`hosts`文件的机器的浏览器中输入`http://t1.tedu.cn`,那么浏览器会查找本地的`hosts`文件中是否存在对应的`IP地址` 
* 查找到对应的IP地址之后，会向服务器发出请求，此时服务器端就会根据浏览器发出的域名在Nginx的虚拟主机中查找匹配`server_name`,然后找到响应的页面  
* ![][1]
  
 
 
## ping 
 
 
* 判断域名能够解析成对应的IP 
* 判断IP地址是否能够访问 
 
 
## HTTPS 
 
## what 
 
 
* 基于`SSL`加密的`HTTP`通讯  
* 底层是`SSL`加密的`TCP`协议  
* 应用层还是传统的HTTP编程 
* 默认的通讯端口是`443` 
* 需要去`CA`申请证书，配置到服务器上  
* ![][2]
  
 
 
## 配置HTTPS证书 
 
### 前提 
 
```nginx
yum -y install nginx
```
 
### 内网配置 
 
 
* 我们在`aliyun.com`中使用`tom.canglaoshi.org`这个域名申请了HTTPS证书  
* ![][3]
  
* ![][4]
  
* 因为我们没有域名和自己的服务器IP地址绑定在一起，因此我们使用内网配置
  
* 在本地的机器的`/etc/hosts`文件中添加对应的`服务器的IP地址 tom.canglaoshi.org`
 
* 这里的服务器的IP地址是远程服务器的地址，等会我们需要在远程服务器中配置Nginx 
   
  
 
 
```nginx
47.104.192.157  tom.canglaoshi.org    # 服务器IP地址  域名
```
 
 
* 在远程服务器的Ngix配置文件中`/etc/nginx/ngxin.conf`添加一个虚拟主机`server`，
 
 
 
* 我们可在其中添加一个`include tom.conf;`这句话，那么我们再在`/etc/nginx`创建一个`tom.conf`配置文件即可，这样看的更加清除，其实就是使用了引入文件  
* `tom.conf`的内容如下，下面的路径都是使用的相对路径  
* http协议默认访问的是`80端口`，因此假如我们在浏览器中输入`http://tom.canglaoshi.org`那么将不会显示安全证书，因为`https`协议使用的是`443`端口，但是我们可以添加一个监听80端口的虚拟主机，设置`server_name`为`tom.canglaoshi.org`，同时使用`301`重定向到`https://tom.canglaoshi.org`，那么当在浏览器中输入`http://tom.canglaoshi.org`的时候就会自动跳转到`https://tom.canglaoshi.org` 
   
  
 
 
```nginx
server{
    liseten  80;   # 默认端口，使用的http协议
    server_name tom.canglaoshi.org;
    return 301 https://tom.canglaoshi.org;  # 这里定义的是重定向，如果使用http://tom.canglaoshi.org ，那么就会重定向到https://tom.canglaoshi.org
}


server {
    listen 443;        # 端口号  必须开启,必须使用Http
s协议才能访问到
    server_name tom.canglaoshi.org;   # 开启证书的域名，这个域名不能改变，因为我们就是使用这个域名开启证书的
    ssl on;
    ssl_certificate   cert/214462831460580.pem;     # 这个是开启证书的时候下载的文件，放在/etc/nginx/cert文件中
    ssl_certificate_key  cert/214462831460580.key;  # 这个是开启证书的时候下载的文件，放在/etc/nginx/cert文件中
    ssl_session_timeout 5m;
    ssl_ciphers ECDHE-RSA-AES128-GCM-SHA256:ECDHE:ECDH:AES:HIGH:!NULL:!aNULL:!MD5:!ADH:!RC4;
    ssl_protocols TLSv1 TLSv1.1 TLSv1.2;
    ssl_prefer_server_ciphers on;
    location / {
        root tom;      # 这个是访问域名的时候显示的web目录，这需要自己在/usr/share/nginx中创建
        index index.html;  # 这个是显示的首页
    }
}

```
 
 
* 我们将开启证书的时候下载的两个文件，分别为`214462831460580.key`和`214462831460580.pem`放到`/etc/nginx/cert`文件中，当然这个`cert`文件需要我们自己创建  
* 至此就配置完成 
* 测试Nginx :`nginx -t -c /etc/nginx/ngxin.conf` 
* 重启nginx ：`nginx -s reload` 
* 此时在本地配置的机器的浏览器中输入`https://tom.canglaoshi.org`访问即可，我们将会看见地址栏中将会出现`安全`两个字，那么证书就配置上了 
 
 
* 这里使用的`403`端口，必须使用`https`访问  
   
  
 
 
### 外网配置 
 
```nginx
chenjiabibing.org
tom.chengjiabing.org
```
 
## Nginx反向代理集群 
 
## what 
 
 
* 通过互联网访问远程的服务器，Nginx分发请求给各种web容器(Tomcat…..)处理就叫反向代理 
* ![][5]
  
 
 
## 内网模拟 
 
 
* 我们需要5台电脑，一台是本地的，使用浏览器访问域名为`http://tts.tedu.cn`,一台远程服务器(IP地址:47.104.192.157)，这台远程服务器使用Nginx分发请求给另外的三台，另外的三台使用的是Tomcat处理Nginx分发的请求，IP地址为：`192.168.0.231`，`192.168.0.176，`，`192.168.0.174` 
* 必须确保三台的`Tomcat`容器都是开启的状态，我们可以在本地使用`http://192.168.0.231:8080/`访问看看是否能够访问到该机器的Tomcat  
* ![][6]
  
* 因为没有申请域名，这个`tts.tedu.cn`没有和远程服务器的IP绑定，因此需要在本地机器配置`/etc/hosts`文件中配置才可以用浏览器访问 
 
 
* 在`/etc/hosts`文件中添加`47.104.192.157 tts.tedu.cn`即可  
* 使用`ping tts.tedu.cn`查看能够成功  
   
  
* 此时我们在远程服务器中配置另外三台的集群信息即可。 
 
 
* 在`/etc/nginx/nginx.conf
`添加一句`include tts.conf` 
* 那么我们只需要将自己的集群配置信息文件`tts.conf`放在`/etc/nginx/`文件夹下即可  
   
  
* `tts.conf`配置文件如下：  
 
 
```nginx
upstream toms {
    server 192.168.0.231:8080;    # 配置三台tomcat的处理器，端口是8080，因为tomcat的默认端口
    server 192.168.0.176:8080;
    server 192.168.0.174:8080;
}

server {
    listen 80;     # 监听的是80端口，浏览器默认使用80，当在本地机器上输入`http://tts.tedu.cn`
    server_name tts.tedu.cn;     ## 对应的域名
    location / {
        proxy_pass http://toms;   # 这里的toms就是上面定义的集群信息

        proxy_redirect     off;
        proxy_set_header   Host             $host;
        proxy_set_header   X-Real-IP        $remote_addr;
        proxy_set_header   X-Forwarded-For  $proxy_add_x_forwarded_for;
        proxy_next_upstream error timeout invalid_header http_500 http_502 http_503 http_504;
        proxy_max_temp_file_size 0;
        proxy_connect_timeout      90;
        proxy_send_timeout         90;
        proxy_read_timeout         90;
        proxy_buffer_size          4k;
        proxy_buffers              4 32k;
        proxy_busy_buffers_size    64k;
        proxy_temp_file_write_size 64k;
    }

}
```
 
 
* 此时我们配置成功 
* 测试配置 
* 重启Nginx 
* 在配置`/etc/hosts`的本地机器上输入`http://tts.tedu.cn`即可访问，我们看到Nginx是将请求均匀分发到不同的集群机器上进行处理    
 
 
## Nginx集群的负载均衡策略 
 
### 轮训策略 
 
 
* 默认策略
  
* 可以增加权重`weight`
 
* 对于一些应用服务器的性能可能不一样，我们需要给性能更好的应用服务器分配更多的请求处理，因此这里就涉及到权重问题 
* 直接在后面添加权重即可，如下 
   
  
 
 
```nginx
upstream toms {
    server 192.168.0.231:8080 weigth 10 ;    # 配置三台tomcat的处理器，端口是8080，因为tomcat的默认端口
    server 192.168.0.176:8080 weight 20;
    server 192.168.0.174:8080 weight 30;
}
```
 
 
* 可以配合`Redis`实现session共享问题  
 
 
### ip_hash ip 散列 
 
 
* 根据用户的Ip地址映射到固定的服务器 
* 如果用户登录网站，那么在当前的服务器中已经保存了`session`的信息，此时就不需要重新登录了，但是可能当再次请求的时候，Nginx又将其分发到其他的应用服务器中了，但是这个应用服务器没有当前当前用户登录的`session`信息，此时就需要重新登录，这个就是问题所在    
* ![][7]
  
 
 
### 原理 
 
 
* 就是根据用户的`IP地址`通过`散列算法`每次请求都保证Nginx将对应的Ip分发到同一台应用服务器  
* ip散列可以和轮训策略结合使用 
* 直接添加一个`ip_hash`即可  
 
 
```nginx
upstream toms {
	ip_hash;
    server 192.168.0.231:8080 weigth 10 ;    # 配置三台tomcat的处理器，端口是8080，因为tomcat的默认端口
    server 192.168.0.176:8080 weight 20;
    server 192.168.0.174:8080 weight 30;
}
```
 
### url_hash 
 
 
* 根据url映射到固定的服务器 
 
 
### 服务器的临时下线 
 
 
* 如果服务器需要更新升级，我们需要将应用服务器临时下线维护，我们可以将其删除，或者添加一个`down`即可，比如`server 192.168.0.174:8080 weight 30 down`就表示该服务器下线了  
 
 
## MySQL远程连接 
 
 
* 前提是远程的服务器需要开启`3306端口`，这个在`阿里云`的服务器开启即可
  
* `grant all privileges on *.* to 用户名@"IP地址" identified by "密码"`
 
* `grant all privileges on tedu_store.* to tedu@"%" identified by "tedu";`
 
* 连接远程数据库`tedu_store`，用户名为`tedu`,用户密码为`tedu` 
     
  
   
  
* 在本地连接远程数据库 
 
 
* `mysql -h IP地址 -u 用户名 -p`
 
* `mysql -h 47.104.152.197 -u tedu -p`，之后直接输入密码即可，那么连接的就是远程数据库  
     
  
   
  
* 远程数据库开启之后，我们就可以在自己的项目中配置数据库的连接`url`为远程数据库了，那么就可实现多个应用服务器共享一个数据库，实现数据的共享了，不会导致数据错乱了    
 
 
## 项目集群部署 
 
### 需求 
 
 
* 一台Nginx服务器分发请求，部署反向集群 
* 多台应用服务器处理请求(Tomcat) 
* 一台`MySQL`数据库服务器，存储项目数据  
* 一台`Redis`服务器，实现session共享，实现Redis缓存  
* ![][8]
  
 
 


[9]: http://nginx.org/download/nginx-1.14.0.tar.gz
[0]: ./img/uqIbu2z.png 
[1]: ./img/BVzMn2Z.png 
[2]: ./img/Zb2qUrJ.png 
[3]: ./img/rmYJFnR.png 
[4]: ./img/7vyQ7vq.png 
[5]: ./img/yE7Bbuq.png 
[6]: ./img/6ZnINva.png 
[7]: ./img/uuqyuuY.png 
[8]: ./img/7FJ7zuM.png 
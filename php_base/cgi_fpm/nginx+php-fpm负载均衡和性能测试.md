## nginx+php-fpm负载均衡和性能测试

来源：[https://segmentfault.com/a/1190000010334337](https://segmentfault.com/a/1190000010334337)

一直知道nginx本身能进行负载均衡，但没有测试过,今天实验了下,以下是笔记记录
![][0]
## 0.准备工作

* vagrant+centos6.7 vbox


## 1.搭建和配置

规划3台web服务器,做负载均衡,由于之前已经有一台虚拟机,因此我现在增加2台.

依次进行以下操作

1.E盘建立一个servers目录初始化vagrant init

2.配置文件vagrantfile,增加了2台虚拟机分别在192.168.33.11,192.168.33.12与之前的192.168.33.10组成3台集群

```
Vagrant.configure(2) do |config|
  config.vm.define "web_1" do |web_1|
    web_1.vm.box = "centos67"
    web_1.vm.network "private_network", ip: "192.168.33.11"
  web_1.vm.provider "virtualbox" do |v|
    v.memory = "1024"
  end
  end
   
  config.vm.define "web_2" do |web_2|
    web_2.vm.box = "centos67"
    web_2.vm.network "private_network", ip: "192.168.33.12"
  web_2.vm.provider "virtualbox" do |v|
    v.memory = "1024"
  end
  end
end
```

3.为新增的2台服务器安装php和一些必要的软件,为了提高效率,随意写了个脚本2台机器上运行

```
yum install -y gcc vim
su -c 'rpm -Uvh http://dl.fedoraproject.org/pub/epel/6/x86_64/epel-release-6-8.noarch.rpm'
rpm -Uvh http://mirror.webtatic.com/yum/el6/latest.rpm
yum install -y nginx
yum install -y php71w-* --skip-broken

groupadd dev
useradd -s /bin/bash -g dev vison
```

本实验中web_1和web_2 2台服务器其实只用到php-fpm,之前的一台host_1(192.168.33.10)会用到nginx和php-fpm

4.配置web_1和web_2的php-fpm 的www.conf配置文件

主要涉及到的配置是listen 和 listen.allowed_clients
前者表示php-fpm 监听的ip 和端口,由于要让host_1的nginx反向代理到,所以应该使用局域网ip,而不是默认的127.0.0.1.

```ini
#192.168.33.11的www.conf
listen = 192.168.33.11:9000
listen.allowed_clients = 192.168.33.10

#192.168.33.11的www.conf
listen = 192.168.33.12:9000
listen.allowed_clients = 192.168.33.10
```

配置好后注意重载配置 service php-fpm reload

5.配置host_1 的nginx 进行负载均衡

```nginx
    #nginx.conf
    #配置均衡日志 可以看到具体代理到了哪台机器的fpm
     log_format upstreamlog '[$time_local] $remote_addr - $remote_user - $server_name  to: $upstream_addr: $request upstream_response_time $upstream_response_time msec $msec request_time $request_time';

    access_log  /var/log/nginx/$host.access.log  upstreamlog;
    upstream php-fpm-backend {
        #轮叫调度(Round-RobinScheduling)模式
        server 127.0.0.1:9000;
        server 192.168.33.11:9000;
        server 192.168.33.12:9000;
    }
    #test-dev.conf  vhost 配置
    server_name  test.dev;
    root         /home/vison/www/demaya/webroot;

    location ~ \.php$ {
            fastcgi_pass   php-fpm-backend;
            fastcgi_index  index.php;
            fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
            include        fastcgi_params;
    }
```

6.同步php文件

由于不同的fpm都会找本机的root目录即之前配置的/home/vison/www/demaya/webroot,所以需在192.168.33.10-12 3台机器上都有相同的代码. 为了方便测试,我这里故意更改了3台机器的index.php文件代码.host_1 输出hello,host_1,web_1输出hello,web_1依次类推。
## 2.测试

浏览器访问test.dev

结果:

第一次 hello,host_1

第二次 hello,web_1

第三次 hello,web_2

...依次
 **`说明:nginx已经按照轮流模式代理到了host_1 web_1 web_2,查看host_1的access.log也同样会发现`** 
## 3.性能测试
 **`多台服务器代理就一定会提升性能吗?`** 

笔者用ab在负载均衡之前和之后都测试过,答案却是否定的

在多服务器之前,单核cpu 1G内存 nginx和php-fpm 都按默认配置RPS能达到900+,然而增加了2台同样配置的虚拟机代理却只能达到800+了。
php-fpm 进程数auto的配置会奏效,会自动增加php-fpm数.但是性能提升效果并不明显,然而nginx 的auto 并没奏效，仍然只有一个.通过手动增加nginx配置,发现rps有所提升,但效果很不明显。

运用vmstat查看分析性能瓶颈时,感觉上是CPU上到了瓶颈,vmstat显示r挺多说明CPU处理不过来。于是我更改了host_1的cpus 配置改为2.再次ab,结果RPS能达到1500左右,差不多翻倍了！
## 4.结论

性能问题并不那么容易解决,需要耐心的排查原因.

[0]: ./img/1460000010334340.gif
# docker命令手册

 时间 2017-08-09 16:06:45  

原文[http://kekefund.com/2017/08/09/docker-command/][2]


`docker run`

`–name` 指定容器名

`-p` 指定端口映射

`-v` 挂载数据卷或者本地目录映射 :ro 挂载为只读

`-d` 后台持续运行

`-i` 交互式操作

`-t` 终端

`-rm` 容器退出后随之将其删除(与-d 冲突)

eg:

    docker run--name ghost1 -p 80:2368 -v /c/Dev/server/blogtest2:/var/lib/ghost ghost
    docker run-it --rm ubuntu:14.04 bash
    docker runubuntu:14.04 /bin/echo'Hello world'
    docker run--name webserver-d-p 80:80 nginx
    

## 管理容器 

    docker ps 列出正在运行的容器 
    docker kill $(docker ps -q) 停止所有正在运行的容器 
    docker ps -a 查看所有容器，包括运行和停止的
    docker start 启动一个已有容器 
    docker stop 终止一个运行中的容器 
    docker restart 重启某个容器 
    docker rm xxxx 删除容器 -f 删除运行中的 
    docker rm $(docker ps -a -q) 删除所有终止的容器 
    docker logs [container id or names] 获取输出log -f 实时打印日志
    docker diff 容器名 查看我们定制以及修改 
    docker volume ls 列出所有本机的数据卷
    

## 管理镜像 

    docker pull [option] [url]  获取镜像, 例如: docker pull ubuntu:14.04 
    docker images 列出本地镜像 
    docker build -t nginx:v3 .   在当前目录构建镜像,-t 是指定镜像名称 tag
    docker rmi xxxxxx 删除本地镜像
    docker commit 选项 容器名/id 仓库名 tag ：可以把修改定制过的容器保存为镜像
     
    docker images -f dangling=true 列出所有虚悬镜像(dangling image)
    docker rmi $(docker images -q -f dangling=true) 删除所有虚悬镜像
    docker histroy 镜像名:标签 查看镜像修改的历史纪录
    

## 查询单个容器详细信息 

可以看到容器的完整ID、运行状态、网络设置、镜像等信息。 

    [root@VM_25_5_centos ~]# docker inspect splash
    [
        {
            "Id": "b5a387e5f9064113e48c06384be045675e12047c7ef5564f76ae8bf0c7f95304",
            "Created": "2017-04-05T04:49:40.025249222Z",
            "Path": "python3",
            ....   
            "State": {
                "Status": "running",
                "Running": true,
             
                "Networks": {
                    "bridge": {
                        "IPAMConfig": null,
                        "Links": null,
                        "Aliases": null,
                        "NetworkID": "b3063867b30c820bb92ee198edad8d5cb8974135d0490e956d3646364ccca711",
                        "EndpointID": "979a8d0bbfde532c45dfbf97bab3c2d874100b4ca448a460b81904709260eb3b",
                        "Gateway": "172.17.0.1",
                        "IPAddress": "172.17.0.3",
                        "IPPrefixLen": 16,
                        "IPv6Gateway": "",
                        "GlobalIPv6Address": "",
                        "GlobalIPv6PrefixLen": 0,
                        "MacAddress": "02:42:ac:11:00:03"
                    }
                }
            }
        }
    ]
    

## 查询日志 

    [root@VM_25_5_centos ~]# docker logs splash
    2017-04-10 12:32:49.050352 [-] "101.226.66.173" - - [10/Apr/2017:12:32:48 +0000] "GET/4e5e5d7364f443e28fbf0d3ae744a59a HTTP/1.1" 404 153 "-" "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.101 Safari/537.36"
    

## 实时打印日志 

加上-f参数 

    [root@VM_25_5_centos ~]# docker logs -f splash
    2017-04-10 12:32:49.050352 [-] "101.226.66.173" - - [10/Apr/2017:12:32:48 +0000] "GET/4e5e5d7364f443e28fbf0d3ae744a59a HTTP/1.1" 404 153 "-" "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.101 Safari/537.36"
    

## 查看容器所占用的系统资源 

如CPU使用率、内存、网络和磁盘开销。 

    [root@VM_25_5_centos ~]# docker stats splash
    
    CONTAINER           CPU %               MEM USAGE / LIMIT      MEM %               NET I/O              BLOCK I/O             PIDS
    splash              0.04%               230.5 MiB / 7.64 GiB   2.95%               50.7 MB / 33.77 MB   547.9 MB / 131.1 kB   7
    

## 查看容器使用了哪些进程 

    [root@VM_25_5_centos ~]# docker exec api1 ps aux
    USER       PID %CPU %MEM    VSZ   RSS TTY      STAT START   TIME COMMAND
    root        1 0.0  0.312548025136 ?        Ss+  Mar31   0:00 python ./manage.py runserver 0.0.0.0:8000
    root        6 0.9  0.874796064664 ?        Sl+  Mar31 139:37 /usr/local/bin/python ./manage.py runserver 0.0.0.0:8000
    root      886 0.0  0.0 191801300?        Rs   16:56   0:00 ps aux
    

## 转移Docker的数据目录到大的磁盘分区上 

    service docker stop
    mkdir /data/dockerData/
    mv /var/lib/docker/data/dockerData/
    ln -s /data/dockerData/docker /var/lib/docker
    service docker start
    

## centos7 安装docker 

    rpm -Uvh http://download.fedoraproject.org/pub/epel/6/i386/epel-release-6-8.noarch.rpm
    yum -y install docker-io
    

可通过以下命令启动 Docker 服务： 

    service docker start
    chkconfig docker on # 设置开机启动
    

可使用以下命令，查看 Docker 是否安装成功： 

    [root@localhost ~]# docker version
    Client:
     Version:         1.12.6
     API version:     1.24
     Package version: docker-1.12.6-32.git88a4867.el7.centos.x86_64
     Go version:      go1.7.4
     Git commit:      88a4867/1.12.6
     Built:           Mon Jul  3 16:02:02 2017
     OS/Arch:         linux/amd64
    
    Server:
     Version:         1.12.6
     API version:     1.24
     Package version: docker-1.12.6-32.git88a4867.el7.centos.x86_64
     Go version:      go1.7.4
     Git commit:      88a4867/1.12.6
     Built:           Mon Jul  3 16:02:02 2017
     OS/Arch:         linux/amd64
    

## centos7 卸载docker 

    [root@localhost ~]# yum list installed | grep docker
    docker.x86_64                          2:1.12.6-28.git1398f24.el7.centos
    docker.x86_64                          2:1.12.6-32.git88a4867.el7.centos
    docker-client.x86_64                   2:1.12.6-28.git1398f24.el7.centos
    docker-client.x86_64                   2:1.12.6-32.git88a4867.el7.centos
    docker-common.x86_64                   2:1.12.6-28.git1398f24.el7.centos
    docker-common.x86_64                   2:1.12.6-32.git88a4867.el7.centos 
    [root@localhost ~]# yum -y remove docker.x86_64
    [root@localhost ~]# yum -y remove docker-common.x86_64


[2]: http://kekefund.com/2017/08/09/docker-command/

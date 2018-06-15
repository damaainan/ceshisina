### 更改CentOS7的yum更新源


1. 备份现有源:

    mv /etc/yum.repos.d/CentOS-Base.repo /etc/yum.repos.d/CentOS-Base.repo.bak

2.下载163源
    
    wget http://mirrors.163.com/.help/CentOS7-Base-163.repo
    mv CentOS7-Base-163.repo CentOS7-Base.repo
 
3.清理并生成缓存

    yum clean all
    yum makecache


### 增加源 
```
cd  /tmp
wget http://rpms.famillecollet.com/enterprise/remi-release-6.rpm
wget http://mirrors.yun-idc.com/epel/6/x86_64/epel-release-6-8.noarch.rpm
rpm -Uvh remi-release-6.rpm epel-release-6-8.noarch.rpm
```

```
rpm -Uvh http://dl.fedoraproject.org/pub/epel/7/x86_64/e/epel-release-7-5.noarch.rpm
rpm -Uvh http://rpms.famillecollet.com/enterprise/remi-release-7.rpm
```
## 安装 nginx
```
# 安装nginx
sudo apt-get install nginx
# 查看下载的目录
dpkg -S nginx
# nginx默认安装地址：/etc/nginx

# 启动nginx
sudo systemctl start nginx 
sudo /etc/init.d/nginx start
# 重启nginx
sudo systemctl reload nginx 
# 查看80端口是否已经被LISTEN状态，可以使用：sudo lsof -i :80
# 然后在浏览器中输入：127.0.0.1，出现nginx默认的欢迎界面，nginx启动成功
```
## 安装 php
```
apt-get install php7.2
```

### 安装 php-cli
```

```
### 安装 php-fpm
```

```
### 安装 php-cgi

```
# 安装常用扩展
apt-get install php7.2-fpm php7.2-mysql php7.2-curl php7.2-json php7.2-mbstring php7.2-xml  php7.2-intl -y
 
php7.2-cgi // fpm 自带 cgi 已不需要安装

php7.2-gd 

#  安装其他扩展（按需安装）
sudo apt-get install  \
php7.2-soap \
php7.2-gmp \
php7.2-odbc \
php7.2-pspell \
php7.2-bcmath \
php7.2-enchant \
php7.2-imap \
php7.2-ldap \
php7.2-opcache \
php7.2-readline \
php7.2-sqlite3 \
php7.2-xmlrpc \
php7.2-bz2 \
php7.2-interbase \
php7.2-pgsql \
php7.2-recode \
php7.2-sybase \
php7.2-xsl \
php7.2-dba \
php7.2-phpdbg \
php7.2-snmp \
php7.2-tidy \
php7.2-zip

 php7.2 php7.2-cgi php7.2-cli php7.2-common php7.2-curl php7.2-dev php7.2-gd php7.2-gmp php7.2-json php7.2-ldap php7.2-mysql php7.2-odbc php7.2-opcache php7.2-pgsql php7.2-pspell php7.2-readline php7.2-recode php7.2-snmp php7.2-sqlite3 php7.2-tidy php7.2-xml php7.2-xmlrpc php7.1-mapi php7.2-bcmath php7.2-bz2 php7.2-dba php7.2-enchant php7.2-fpm php7.2-imap php7.2-interbase php7.2-intl php7.2-mbstring php7.2-phpdbg php7.2-soap php7.2-sybase php7.2-xsl php7.2-zip php7cc
```

### nginx 和 php 配合

##### nginx  配置
```cfg
# /etc/nginx/sites-available/default

```

##### php-fpm  配置
```ini
; /etc/php/7.1/fpm/pool.d/www.conf

```

重启  
```
systemctl restart nginx
systemctl restart php7.2-fpm
```

#### 安装 composer




## 安装 MySQL
### 安装 MySQL 8
```

```
## 安装 Apache
```

```
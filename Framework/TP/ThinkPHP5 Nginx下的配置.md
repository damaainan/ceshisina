## ThinkPHP5 Nginx下的配置

来源：[http://www.uedbox.com/thinkphp5-nginx-linux/](http://www.uedbox.com/thinkphp5-nginx-linux/)

时间 2017-06-22 12:45:43



出于安全的考虑，TP5的入口文件改成放在public下了，因为这样的话能防止被恶意用户访问到“/thinkphp/”、“/vendor/”等等这些目录下的文件。所以当你以之前的习惯将网站documentroot配置为项目根目录的时候就会需要在url后面加上/public/来访问。当然可能也会有童鞋把入口文件放回到根目录下，然后还是以之前3.x版那样的形式访问了。

但是很显然，这么做并不是那么的科学。

假设项目目录为“/web/wwwroot/augsky.com”，那么我们在网站的nginx配置文件里面将root配置为：

```
root  /web/wwwroot/augsky.com/public;


```

但是一定要记得将open_basedir设置为上一级项目的根目录下，不然应用会没有权限调用除public目录下的其他文件，网站会报500无法访问。（open_basedir的配置默认在php.ini里面，但如果是多个虚拟机环境的话有可能会在各个网站的user.ini文件里，这个要根据自己的实际情况来。）具体配置如下：

```
open_basedir=/web/wwwroot/augsky.com:/tmp/:/proc/


```


### 隐藏入口文件index.php：

```nginx
location /
        {
                try_files $uri $uri/ /index.php?s=$uri&$args;
                #如果请求不是文件或目录，则将uri交给index.php处理，同时保留参数
        }


```

说一下try_files：

```
try_files
语法: try_files file1 [file2 ... filen] uri
                 OR
        try_files file1 [file2 ... filen] =code
默认值: 无
作用域: server location


```

try_files支持多个参数，每个参数代表一个文件，系统将按顺序检查这些文件是否存在，存在就直接执行，斜线“/”结尾代表目录，若都不存在，则会重定向到最后一个参数指向的文件或者返回指定的http状态码。


### pathinfo配置

在配置文件里面增加这一段（如果你是lnmp一键包用户，请略过这一段往下翻）：

```nginx
location ~ [^/]\.php(/|$) {
        set $path_info "";
        #定义变量 $real_script_name，用于存放真实地址
        set $real_script_name $fastcgi_script_name;
        #如果地址与引号内的正则表达式匹配
        if ($fastcgi_script_name ~ "^(.+?\.php)(/.+)$") {
        #将文件地址赋值给变量 $real_script_name
        set $real_script_name $1;
        #将文件地址后的参数赋值给变量 $path_info
        set $path_info $2;
        }
        #配置fastcgi的一些参数
        fastcgi_pass  unix:/tmp/php-cgi.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$real_script_name;
        fastcgi_param SCRIPT_NAME $real_script_name;
        fastcgi_param PATH_INFO $path_info;
        include        fastcgi_params;
}


```


上面需要说明的是fastcgi_pass的设置，这个得根据你实际的php版本和安装目录来设定。

好，做完这些之后，保存，重启nginx和php就能生效了。如果你是lnmp一键包用户的话不用上面这样设置，往下面看：

  
### lnmp一键包pathinfo的设置

其实lnmp一键包里面的pathinfo军哥已经都写好了，我们只需要把include enable-php.conf;修改成include enable-php-pathinfo.conf;然后重启lnmp就搞定了。

```
#include enable-php.conf;或者，你直接注释掉这一行，在下面添加新的一行
include enable-php-pathinfo.conf;


```

就是这样简单。下面顺便附上在url里隐藏模块的方法


### url隐藏默认模块index

很简单，在入口文件里面定义常量BIND_MODULE为你使用的模块就好了，以默认的index为例：

```
define('BIND_MODULE', 'index');


```


这样，在url里面不会在有模块名这一级目录了。要知道，层级少一些的url对SEO是有好处的。

下面是nginx里面location的匹配规则，摘自

[CSDN谢厂节的博客][0]

    
  
#### Nginx location的匹配规则


~ 波浪线表示执行一个正则匹配，区分大小写

~* 表示执行一个正则匹配，不区分大小写

^~ ^~表示普通字符匹配，如果该选项匹配，只匹配该选项，不匹配别的选项，一般用来匹配目录

= 进行普通字符精确匹配

@ #"@" 定义一个命名的 location，使用在内部定向时，例如 error_page, try_files

      
#### location 匹配优先级


= 精确匹配会第一个被处理。如果发现精确匹配，nginx停止搜索其他匹配。

普通字符匹配，正则表达式规则和长的块规则将被优先和查询匹配，也就是说如果该项匹配还需去看有没有正则表达式匹配和更长的匹配。

^~ 则只匹配该规则，nginx停止搜索其他匹配，否则nginx会继续处理其他location指令。

最后匹配理带有"~"和"~*"的指令，如果找到相应的匹配，则nginx停止搜索其他匹配；当没有正则表达式或者没有正则表达式被匹配的情况下，那么匹配程度最高的逐字匹配指令会被使用。


[0]: http://blog.csdn.net/xundh/article/details/45225555
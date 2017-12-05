# 2017 Nov 23 娇弱的 PHP [ php-fpm.conf & php.ini 安全优化实践 ]

 时间 2017-12-02 20:06:22  

原文[https://klionsec.github.io/2017/11/23/phpsec/][1]


0x01 关于 php 

    关于php的历史,相对已经比较久远了,这里就不废话了,属弱类型中一种解释型语言
    除了web开发以及写些简单的exp,暂未发现其它牛逼用途,以中小型web站点开发为主
    另外,低版本的php自身漏洞就比较多,建议,从现在开始就在新项目中使用php 5.6.x以后的版本
    好在官方维护的一直比较勤奋,主次版本都迭代的比较快,最新版已经到7.2.0
    哼哼……是,'最好的语言'... :)
    

0x02 演示环境 

    CentOS6.8 x86_64   最小化,带基础库安装   eth0: 192.168.3.42 eth1: 192.168.4.14  eth2: 192.168.5.14
    php-5.6.32.tar.gz
    

0x03 下载 php-5.6.32.tar.gz ,并安装好所需各种依赖库 

    #wget http://au1.php.net/distributions/php-5.6.32.tar.gz
    #yum install epel-release -y
    #yum install -y zlib-devel libxml2-devel freetype-devel
    #yum install -y libjpeg-devel libpng-devel gd-devel curl-devel libxslt-devel
    #yum install openssl openssl-devel libmcrypt libmcrypt-devel mcrypt mhash mhash-devel -y
    #tar xf libiconv-1.15.tar.gz
    #cdlibiconv-1.15
    #./configure --prefix=/usr/local/libiconv-1.15 && make && make install
    #ln -s /usr/local/libiconv-1.15/ /usr/local/libiconv
    #ll /usr/local/libiconv/
    

0x04 开始编译安装php5.6.32,要带的编译参数比较多,大家下去以后,可以仔细了解下这些参数都是干什么用的,其实,都是一些php内置功能模块,这里默认就已经启用了一些比较常用的模块,如,pdo,mysqli,关于下面的模块,并不用全部都装,根据你自己实际的开发业务需求,用什么装什么即可,切记不要一上来不管用不用就先装一大堆,你不用,很可能就会被别人利用 

    # tar xf php-5.6.32.tar.gz
    # cd php-5.6.32
    # ./configure --help
    # ./configure \
    --prefix=/usr/local/php-5.6.32 \
    --with-config-file-path=/usr/local/php-5.6.32/etc \
    --with-mysql=/usr/local/mysql \
    --with-mysqli=/usr/local/mysql/bin/mysql_config \
    --with-pdo-mysql=/usr/local/mysql \
    --with-iconv-dir=/usr/local/libiconv\
    --with-freetype-dir \
    --with-jpeg-dir \
    --with-png-dir \
    --with-zlib \
    --with-libxml-dir=/usr \
    --with-curl \
    --with-mcrypt \
    --with-gd \
    --with-openssl \
    --with-mhash \
    --with-xmlrpc \
    --with-xsl \
    --with-fpm-user=nginx \
    --with-fpm-group=nginx \
    --enable-xml \
    --disable-rpath \
    --enable-bcmath \
    --enable-shmop \
    --enable-sysvsem \
    --enable-inline-optimization \
    --enable-mbregex \
    --enable-fpm \
    --enable-mbstring \
    --enable-gd-native-ttf \
    --enable-pcntl \
    --enable-sockets \
    --enable-soap \
    --enable-short-tags \
    --enable-static \
    --enable-ftp \
    --enable-opcache=no
    

    #make && make install
    #ln -s /usr/local/php-5.6.32/ /usr/local/php
    #cp php.ini-production /usr/local/php/etc/php.ini 创建php解释器的配置文件
    

0x05 编辑,配置并优化 php-fpm.conf ,即 fastcgi 的服务端,如下 

    #让php进程以一个系统伪用户的身份起来,在能满足实际业务需求的情况下,最大限度降低php进程权限
    #useradd -s /sbin/nologin -M phpfpm
    #cp /usr/local/php/etc/php-fpm.conf.default /usr/local/php/etc/php-fpm.conf
    #vi /usr/local/php/etc/php-fpm.conf
    #mkdir /usr/local/php/logs
    #egrep -v"^$|;"/usr/local/php/etc/php-fpm.conf 简化php-fpm配置文件
    

    # php-fpm 的全局配置模块
    [global]
    
    # 指定php-fpm的进程id号文件存放位置
    pid = /usr/local/php/logs/php-fpm.pid       
    
    # 指定php-fpm进程自身的错误日志存放位置
    error_log = /usr/local/php/logs/php-fpm.log 
    
    # 指定要记录的php-fpm日志级别
    log_level = error                           
    rlimit_files = 32768
    events.mechanism = epoll
    
    # php-fpm web配置模块
    [www]
    
    # 最好把web服务用户和php-fpm进程用户的权限分开,分别用两个完全不同的系统伪用户来跑对应的服务,防止意外的越权行为
    # 其实,你也可以不分开,特定条件下,关系也并不是非常大,不过,个人建议,最好还是分开
    user = phpfpm           
    group = phpfpm
    
    # 务必要监听在127.0.0.1的9000端口,另外,该端口严禁对外开放,防止别人通过fastcgi进行包含
    listen = 127.0.0.1:9000 
    listen.owner = phpfpm
    listen.group = phpfpm
    
    pm = dynamic
    pm.max_children = 1024
    pm.start_servers = 16
    pm.min_spare_servers = 5
    pm.max_spare_servers = 20
    pm.max_requests = 2048
    slowlog = /usr/local/php/logs/$pool.log.slow
    request_slowlog_timeout = 10
    php_admin_value[sendmail_path] = /usr/sbin/sendmail -t -i -f klion@protonmail.com
    
    # 只让为php的后缀执行,一般这里还有`.php3 .php4 .php5`,把那些默认给的可执行后缀统统去掉,只留`.php`即可
    # 一般会配合php.ini文件中的cgi.fix_pathinfo参数一起使用,避免入侵者构造利用解析漏洞进行上传
    security.limit_extensions = .php
    

    #/usr/local/php/sbin/php-fpm 启动php-fpm
    #ps -le | grep"php-fpm"
    #netstat -tulnp | grep":9000"
    #echo"/usr/local/php/sbin/php-fpm">> /etc/rc.local && cat /etc/rc.local
    #killall php-fpm 如果想关闭php-fpm,直接把它进程kill掉即可
    

尝试让php与mysql,nginx 进行联动,看看php能不能被正常解析 

    #vi connect.php
    

    <?php
        $link = mysql_connect("localhost","root","admin") or die(mysql_error());
        if($link){
            echo "yeah , mysql connect succeed!";
        }else{
            echo mysql_error();
        }
    ?>
    

0x06 最后,我们就来好好关注下php解析器自身的安全,php解析器的设置全部依靠 php.ini 文件来实现,所以,下面就来详细说明一下针对 php.ini的安全配置    #vi /usr/local/php/etc/php.ini
    

将 register_globals 项设为Off ,本身的意思就是注册为全局变量,也就是说,设置为On的时候,从客户端传过来的参数值会被直接注册到php全局变量中后端直接可以拿到该变量进行使用,如果为Off,则表示只能到特定的全局数组中才能取到该数据,建议关闭,容易造成变量覆盖问题,不过在php高版本[如,> 5.6.x]中,已经去除对此项的设置,官方给的说明是这样的 本特性已自 PHP 5.3.0 起废弃并将自 PHP 5.4.0 起移除 ,如果你用的还是低版本的php就需要注意把此项关闭 

    register_globals = Off
    

    <?php
        if($mark){
            echo "login succeed! "; # 此处会直接显示登陆成功,因事先没有定义$mark,导致$mark直接被覆盖掉了
        }else{
            echo "login failed!";
        }
    ?>
    

将 cgi.fix_pathinfo的值设为 0 ,默认cgi.fix_pathinfo 项是开启的,即值为1,它会对文件路径自动进行修正,我们要把它改成0,不要让php自动修正文件路径,防止入侵者利用此特性构造解析漏洞来配合上传webshell 

    cgi.fix_pathinfo = 0
    

建议同时关闭以下两项,如果实在有业务需求,请在代码中严格限制检查用户传过来的数据 

    # 为On时,则表示允许,也就是说,此时可以通过file_get_contents(),include(),require()等函数直接从远端获取数据
    # 容易造成任意文件读取和包含问题,注意,此项默认就是开启的
    allow_url_fopen = Off
    
    # 容易造成远程包含,强烈建议关闭此项
    allow_url_include = Off
    

禁用各种高危函数,尽可能让各种 webshell [ 一句话,大马 ] 无法再靠php内置函数来执行各种系统命令,下面是一些比较常见的命令和代码执行函数,如果你还发现有其它的一些不常用的高危函数,也可以一并加进来,防止被入侵者率先发现并利用,此项默认为空 

    disable_functions = dl,eval,assert,popen,proc_close,gzinflate,str_rot13,base64_decode,exec,system,ini_alter,readlink,symlink,leak,proc_open,pope,passthru,chroot,scandir,chgrp,chown,escapeshellcmd,escapeshellarg,shell_exec,proc_get_status,max_execution_time,opendir,readdir,chdir,dir,unlink,delete,copy,rename,ini_set
    

转义开关,主要用来转义各种特殊字符,如, 单引号,双引号,反斜线和空字符... 个人建议,在这里先把它关闭,因为它并不能很好的防住sql注入,或者,基本是防不住的,比如,利用宽字节 说到这儿,顺便再补充一句,对付宽字节的最好办法就是全站统一使用 utf-8 ,这里还是建议大家采用sql语句预编译和绑定变量的方式来预防sql注入,这也是目前为止比较切实有效的预防手段,对于从客户端过来的各种其它数据,可以单独写个检查类,如果你想安全就不要对这些开关寄予太大的希望,可能php官方也发现,这个开关实质就是个摆设,所以给出了这样的说明 本特性已自 PHP 5.3.0 起废弃并将自 PHP 5.4.0 起移除    magic_quotes_gpc = Off
    magic_quotes_runtime = Off
    

关闭php自身的各种错误回显,反正只要记得,项目上线后,所有的程序错误一律接收到我们自己事先准备好的地方,一旦被入侵者在前端看到,极易造成敏感信息泄露,高版本的php,默认这些危险项就是处于关闭状态的 

    display_errors = Off                # 切记千万不让让php错误输出到前端页面上
    error_reporting = E_WARING & ERROR      # 设置php的错误报告级别,只需要报告警告和错误即可
    log_errors = On                 # 开启php错误日志记录
    error_log = /usr/local/php/logs/php_errors.log  # 指定php错误日志存放位置
    log_errors_max_len = 2048           # 指定php错误日志的最大长度
    ignore_repeated_errors = Off            # 不要忽略重复的错误
    display_startup_errors = Off            # 另外,不要把php启动过程中的错误输出到前端页面上
    

隐藏php的详细版本号,即 X-Powered-By 中显示的内容,不得不再次强调,有些漏洞只能针对特定的类型版本,在实际渗透过程中,如果让入侵者看到详细的版本号,他很可能就会直接去尝试利用该版本所具有的一些漏洞特性再配合着其它漏洞一起使用 

    expose_php = Off
    

限制php对本地文件系统的访问,即把所有的文件操作都限制指定的目录下,让php其实就是限制了像 fopen() 这类函数的访问范围,一般主要用来防止旁站跨目录,把webshell死死控制在当前站点目录下,此项默认为空,不建议直接写到php.ini中,可以参考前面nginx安全部署中的,直接在每个站点目录下新建一个 .user.ini 然后再把下面的配置写进去即可,比较灵活 

    open_basedir = "/usr/local/nginx/html/bwapp/bWAPP:/usr/local/nginx/html/dvws/"
    

关于对服务端session的一些处理

隐藏后端使用的真正脚本类型,扰乱入侵者的渗透思路,另外,切记不要把敏感数据直接明文存在session中,有泄露风险 

    session.name = JSESSIONID   表示jsp程序,php的则是PHPSESSID
    

修改session文件存放路径,最好不要直接放在默认的 /tmp 目录下,实际中可能是一台单独的session服务器,比如,memcached 

    session.save_handler = memcache
    session.save_path = "tcp://192.168.3.42:11211"
    

安全模式可根据实际业务需求选择性开启,安全模式的意思就是操作文件的函数只能操作与php进程UID相同的文件,但php进程的uid并不一定就是web服务用户的uid,这也就造成了麻烦,也就是说,你想避免这种麻烦,可能就需要在最开始配置时就让php进程和web服务使用同一个系统用户身份,但这又正好跟我前面说的相背了,我们在前面说过,最好把php进程用户和web服务用户分开,这样更容易进行权限控制,另外,高版本的php [ > php5.4 ] 已不再支持安全模式,因为官方可能也觉得它并没什么卵用,而且低版本php的安全模式,还可被绕过,所以,如果你用的是低版本的php,请根据自身实际业务做取舍 

    safe_mode = On
    safe_mode_gid = off
    

限制php单脚本执行时长,防止服务器资源被长期滥用而产生拒绝服务的效果 

    max_execution_time = 30
    max_input_time = 60
    memory_limit = 8M
    

关于上传,如果实际的业务根本不涉及到上传,直接把上传功能关掉即可,如果需要上传,再根据需求做出调整即可,对防入侵来讲,这里对我们意义并不是非常大 

    file_uploads = On       # 开启php上传功能
    upload_tmp_dir =        # 文件传上来的临时存放目录
    upload_max_filesize = 8M    # 允许上传文件的文件大小最大为多少
    post_max_size = 8M      # 通过POST表单给php的所能接收的文件大小最多为多少
    

0x07 利用 chattr 锁定一些不需要经常改动的重要配置文件,如,php-fpm.conf,php.ini,my.cnf…,为了防止chattr工具被别人滥用,你可以把它改名隐藏到系统的某个角落里,用的时候再拿出来 

锁定 

    #chattr +i /usr/local/php/etc/php.ini
    #chattr +i /usr/local/php/etc/php-fpm.conf
    #chattr +i /etc/my.cnf
    

解锁 

    #chattr -i /usr/local/php/etc/php.ini
    #chattr -i /usr/local/php/etc/php-fpm.conf
    #chattr -i /etc/my.cnf
    

0x08 务必勤于关注php官方的高危补丁发布及说明,和其它工具不同,php 自身bug多,因为关注的人多,搞的人更多,所以暴露出来的各种安全问题也就更多更多

0x09 最后,告诉大家一个怎么把曾经yum下来的包保留着的办法,在系统断网的情况下也许用的着,只需到yum配置里面去调整下即可,保留的包的路径也在yum配置中定义好了 

    # vi /etc/yum.conf
      keepcache=1
    

小结:

至此为止,关于 整套LNMP架构 的安全部署及优化,也就差不多完成了,我们关注的点,更多可能还是集中在防御入侵上,捎带了一点性能优化,这里所给的参数选项基本全部都可直接用于实战部署,但并不是所有的都是必须的,还需要你好好根据自己实际的业务需求做出适当取舍,或者在此基础进行定制改进,有些地方都是基于自己平时实战经验的考量来的,并不一定完全是对的,如果有性能更好,更安全的方案,也非常欢迎大家一起来私信交流 ^_^


[1]: https://klionsec.github.io/2017/11/23/phpsec/

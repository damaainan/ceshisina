# PHPTaint-检测xss/sqli/shell注入的php扩展模块 

 时间 2014-06-06 18:41:00  博客园-所有随笔区

原文[http://www.cnblogs.com/cuoreqzt/p/3773186.html][1]


  web渗透者习惯采用黑盒或灰盒的方面来检测一款web应用是否存在漏洞，这种检测方法可以屏蔽不少漏洞，特别是程序逻辑中的漏洞。但如果能配合白盒的源码审计（也可以叫漏洞挖掘），效果将会更好，当然人力成本也会增加，其中，对于源码审计工作将交给谁做，是比较争议的话题，是开发、测试还是安全人员呢？    
个人觉得，开发若能做一下粗略的源码自查，然后安全（弱没有安全人员，就交给白盒测试人员）负责做整体的源码审查，将是极好的安排。   
除了人力成本，还有一个信任的问题，是否愿意将源码开放给安全人员？这比较敏感，但从技术角度来看，掌握源码审计的技巧是非常棒的加分点。 

本篇文章将介绍两种开源的PHP源码审计工具，其中Taint适合开发源码自查，RIPS适合安全源码审查。

**一、Taint**

**1、介绍**

php taint一个用于检测xss/sqli/shell注入的php扩展模块。

原理，检查某些 [关键函数][5] （是否直接使用（没有经过过滤或转义处理）了来自$_GET,$_POST,$_COOKIE的数据，如使用则给出提示。 

可用于php源码审计，对快速定位漏洞有帮助

**2、安装**  
第一步：下载安装taint 

    wget http://pecl.php.net/get/taint-1.2.2.tgz （下载最新的taint）
    tar zxvf taint-1.2.2.tgz
    cd taint-1.2.2
    phpize（如果找不到该命令，则写上路径 /usr/local/php5/bin/phpize）
    ./configure --with-php-config=path
    make
    make install

第二步：修改php.ini配置文件，使其支持taint模块

    vim /etc/php5/apache2/php.ini 

增加

    extension=/usr/lib/php5/20090626+lfs/taint.so
    taint.enable=1
    display_errors = On
    error_reporting = E_ALL & ~E_DEPRECATED
    apache2ctl restart

注意：只能在开发环境开启该扩展

第三步：测试该模块是否开启   
vim phpinfo.php 

    <?php
    phpinfo();
    ?>

![][6]

如上图所示，则表示成功开启该扩展

3、测试(以DVWA 为主要测试对象）   
实例1：sql注入漏洞 

    $user = $_GET['username'];
    $pass = $_GET['password'];
    $pass = md5($pass);
    $qry = "SELECT * FROM `users` WHERE user='$user' AND password='$pass';";
    $result = mysql_query( $qry ) or die( '<pre>' . mysql_error() . '</pre>' );

运行页面，警告信息如下所示

    Warning: mysql_query(): SQL statement contains data that might be tainted in /var/www/dvwa/vulnerabilities/brute/source/low.php on line 11

如果PHP源码使用以下函数，则不会发出警告

    mysql_real_escape_string （不转义%与_)
    stripslashes
    is_numeric

实例2：命令执行漏洞

    <?php
    if( isset( $_POST['submit'])){
        $target = $_REQUEST['ip'];// Determine OS and execute the ping command.if(stristr(php_uname('s'),'Windows NT')){ 
    
        $cmd = shell_exec('ping  '. $target );
        echo '<pre>'.$cmd.'</pre>';}else{ 
    
        $cmd = shell_exec('ping  -c 3 '. $target );
        echo '<pre>'.$cmd.'</pre>';}}?>

运行页面，警告信息如下所示

    Warning: shell_exec(): CMD statement contains data that might be tainted in /var/www/dvwa/vulnerabilities/exec/source/low.php on line 15

实例3：文件包含漏洞（常伴随着目录遍历漏洞）

    <?php
    $file=$_GET['file'];
    include($file);?>

运行页面，警告信息如下所示

    Warning: include(): File path contains data that might be tainted in /var/www/dvwa/vulnerabilities/fi/index.php on line 35

实例4：xss漏洞

    <?php
    if(!array_key_exists ("name", $_GET)|| $_GET['name']== NULL || $_GET['name']==''){
     $isempty =true;}else{
    
     echo '<pre>';
     echo 'Hello '. $_GET['name'];
     echo '</pre>';}?>

运行页面，警告信息如下所示

![][7]

实例5：代码执行eval

    <?php
    $cmd=$_GET['cmd'];eval("$cmd;");?>

![][8]

实例6：文件读取操作

    <?php
    print"<h2>Number 3: file()  functions: </h2>";
    $path=$_GET['path'];
    $contents=file($path);foreach($contents as $line_num => $line){
    echo "Line #<b>{$line_num}</b> : ".htmlspecialchars($line)."<br>\n";}?>

![][9]

**二、RIPS**

[Taint][10] 可以在运行时提醒开发，未过滤参数带来的危害。而集中性的PHP源码安全审计工作还是交给 [RIPS][11] 比较友好。 我们将上面的实例代码使用该工具检查，报告如下所示 

![][12]

上图显示了该工具找到了7种安全漏洞，效果不错。   
PHP源码审计是个很成熟的问题了，网络上有不少详细介绍如何做源码审计的资料，也开源了不少源码审计工具，感谢这些信息分享的人。


[1]: http://www.cnblogs.com/cuoreqzt/p/3773186.html
[5]: http://www.php.net/manual/en/taint.detail.taint.php
[6]: ./img/ZnANZj.jpg
[7]: ./img/JRfq6n.jpg
[8]: ./img/jI3UBr.jpg
[9]: ./img/rInuAz.jpg
[10]: http://www.php.net/manual/en/book.taint.php
[11]: http://sourceforge.net/projects/rips-scanner/
[12]: ./img/IR3INj.jpg
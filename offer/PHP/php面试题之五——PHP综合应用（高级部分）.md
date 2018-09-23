# [php面试题之五——PHP综合应用（高级部分）][0]

### 五、PHP综合应用

###### 1.写出下列服务的用途和默认端口（新浪网技术部）

ftp、ssh、http、telnet、https

* ftp：File Transfer Protocol，文件传输协议，是应用层的协议，它基于传输层，为用户服务，它们负责进行文件的传输，其默认端口是21。
* ssh：Secure Shell，安全外壳协议，建立在应用层和传输层基础上的安全协议。SSH是目前较可靠，专为远程登录会话和其他网络服务提供安全性的协议，其默端口是22。
* http：hypertext transport protocol，超文本传送协议，是一种详细规定了浏览器和万维网服务器之间互相通信的规则，通过因特网传送万维网文档的数据传送协议，其默认端口是80。
* telnet：Telnet协议是TCP/IP协议族中的一员，是Internet远程登陆服务的标准协议和主要方式。它为用户提供了在本地计算机上完成远程主机工作的能力，其默认端口是23。
* https：Hypertext Transfer Protocol over Secure Socket Layer，是以安全为目标的HTTP通道，用于安全的HTTP数据传输，它的主要作用可以分为两种：  
一种是建立一个信息安全通道，来保证数据传输的安全；  
另一种就是确认网站的真实性，其默认端口是443。

###### 2.你用什么方法检查PHP脚本的执行效率（通常是脚本执行时间）和数据库SQL的效率（通常是数据库Query时间），并定位和分析脚本执行和数据库查询的瓶颈所在？（腾讯）

脚本执行时间，启用xdebug，使用WinCacheGrind分析。  
数据库查询，mysql使用EXPLAIN分析查询，启用slow query log记录慢查询。

###### [!!!]3.对于大流量的网站,您采用什么样的方法来解决访问量问题?

1. 确认服务器硬件能否支持当前的流量  
对于普通的pc server来说，它能够独立支持每天10万个独立ip访问，如果访问量过大，最好更好性能更高的专用服务器。
1. 优化数据库的访问服务器的负载过大，一个重要的原因就是CPU和内存负载过高，而读写数据在这块占据较多的资源。可以从页面静态化、memcache缓存和mysql优化几个方面着手。
1. 禁止外部盗链  
占用较大的流量，防盗链，使用reference来判断一下。如果是图片的话，使用添加水印即可很好的防止。
1. 控制大文件的下载  
最好把文件下载的容量控制为相对较小的一个值，如果有大文件下载，最好使用专用的服务器。
1. 使用多台主机实现分流，集群
1. 使用流量分析软件进行分析统计谷歌和百度

###### 4.请简单阐述您最得意的开发之作

根据实际情况自由发挥

###### 5.谈谈asp,php,jsp的优缺点

ASP全名Active Server Pages，是一个WEB服务器端的开发环境，利用它可以产生和运行动态的、交互的、高性能的WEB服务应用程序。ASP采用脚本语言VB Script作为自己的开发语言。  
PHP是一种跨平台的服务器端的嵌入式脚本语言。它大量地借用C、Java和Perl语言的语法，并结合自己的特性，使WEB开发者能够快速地写出动态生成页面。它支持目前绝大多数数据库。还有一点，PHP是完全免费的，不用花钱，你可以从PHP官方站点自由下载。而且你可以不受限制地获得源码，甚至可以从中加进你自己需要的特色。  
JSP是Sun公司推出的新一代站点开发语言，他完全解决了目前ASP和PHP的一个通病-----脚本级执行（据说PHP4也已经在Zend的支持下，实现编译运行）。Sun公司借助自己在上的不凡造诣，将Java从Java应用程序和Java Applet之外，又有新的硕果，就是Java Server Page。JSP可以在Serverlet和JavaBean的支持下，完成功能强大的站点。  
三者都提供在HTML代码中混合某种程序代码、由语言引擎解释执行程序代码的能力。但JSP代码被编译成Servlet并由Java虚拟机解释执行，这种编译操作仅在对JSP页面的第一次请求时发生。  
在ASP、PHP、JSP环境下，HTML代码主要负责描述信息的显示样式，而程序代码则用来描述处理逻辑。普通的HTML页面只依赖于Web服务器，而ASP、PHP、JSP页面需要附加的语言引擎分析和执行程序代码。程序代码的执行结果被重新嵌入到HTML代码中，然后一起发送给浏览器。  
ASP、PHP、JSP三者都是面向Web服务器的技术，客户端浏览器不需要任何附加的软件支持。

###### 6.请举例说明在你的开发过程中用什么方法来加快页面的加载速度。

要用到服务器资源时才打开，及时关闭服务器资源，数据库添加索引，页面可生成静态，图片等大文件单独服务器，使用代码优化工具等。

###### 7.Is PHP better than Perl?–Discuss.（Yahoo）

我们不要为一个简单的问题引发一场舌战，为工作选择适合的语言，不要为工作迁就语言。Perl十分适合用作命令行工具，虽然它在网页应用上也有不错的表现，但是它的真正实力在命令行上才能充分发挥。同样地，PHP虽然可以在控制台的环境中使用，但是它在网页应用上有更好的表现，PHP有大量专门为网页应用而设计的函数，Perl则似乎以命令行为设计之本。

###### 8.What's the difference between the way PHP and Perl distinguish between arrays and hashes?（Yahoo）

这正是为何我老是告诉别人选择适当的编程语言，若果你只用一种语言的话你怎么能回答这道问题？这道问题很简单，Perl所变量都是以@开头，[例如@myArray][1]，PHP则沿用$作为所有变量的开头，例如$myArray。  
至于Perl表示散列表则用%，例如%myHash，PHP则没有分别，仍是使用$，例如$myHash。

###### 9.How do you debug a PHP application?（Yahoo）

使用Xdebug或者Advanced PHP Debugger

###### 10.PEAR中的数据库连接字符串格式是____。

```php
    $dsn='mysql://username:password@localhost/test'
    $options=array(
        'debug'=>2,
        'portability'=>DB_PORTABILITY_ALL,
    )
    DB::connect($dsn,$options)//其中options参数是可选的。
```

PEAR是PHP扩展与应用库（the PHP Extension and Application Repository）的缩写。它是一个PHP扩展及应用的一个代码仓库，PEAR处理数据库的模块是PEAR DB。

###### 11.如何实现PHP、JSP交互？

题目有点含糊不清,SOAP,XML_RPC,Socket function,CURL都可以实现这些,如果是考虑PHP和Java的整合，PHP内置了这种机制(如果考PHP和.NET的整合，也可以这么回答)。  
PHP提供了支持JAVA的类库文件，或者通过HTTP协议来交互数据。

###### [!!!]12.apache+mysql+php实现最大负载的方法

1. 问的太笼统,生成静态html页面，squid反向代理，apache，mysql的负载均衡。
1. 可以采取数据缓存的方法，我们通常在统计数据的时候，需要在原始数据的基础上经过计算等一系列操作，才会得到最终的结果，如果每做一个查询都需要这样一系列操作，当数据量大时，势必会带来很多问题。可以建立一个结果表，写一个脚本，用crontab定时触发脚本去原始表取数据，计算，写入到结果表，前端查询从结果表取数据，这也是比较常用的一种做法。
1. 采用分布式，多个apache，多个mysql，其实就是dns负载均衡，dns根据当前用户解析几个ip的ping值，将用户转移到某一台最快的服务器，或者平均分配。
1. money不是问题的话，可以考虑F5硬件负载均衡!
1. 可以使用Microsoft Windows Server系统的负载均衡设置

###### 13.已知姓名A,姓名B,给一个求他们缘份的算法（51.com）

开放性题目，没有固定的算法，可以通过计算两个名字的笔画差来确定缘分指数。

###### 14.你觉得在PV10W的时候,同等配置下,LUNIX比WIN快多少?（51.com）

不做优化的情况下一样。

###### [!!]15.Ajax,数据库触发器，GUI，中断机制的共同思想。谈一谈该种思想（机制）（百度）

主要就是异步，主进程不会被一个异步任务阻塞，当进程发出命令之后，继续执行主任务，不用等待子任务执行完，这样效率更高。  
数据库触发器和中断机制是数据库自动完成的，而ajax触发器是用户激发的。ajax把GUI和数据库异步优化。

###### 16.把一篇英文文档中所有单词的首字母转为大写，文档存在doc.txt中。可以在多种编程语言中选择（C\C++,JAVA,PHP...)写出你的思路，尽量优化你的程序。（百度）

    $str=file_get_contents('doc.txt');
    $str=ucwords($str);
    file_put_contents('doc.txt',$str);

###### 17.防止SQL注射漏洞一般用_____函数

addslashes

###### 18.综合运用，PHP+MySQL编程，文件操作(CBSI)以下请用PHPMYADMIN完成

(1).创建新闻发布系统，表名为message有如下字段

字段名描述 id 文章id title 文章标题 content 文章内容 category_id 文章分类id hits 点击量 

创建表语句如下：

```sql
    CREATE TABLE message(
    id iNT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200)NOT NULL DEFAULT'',
    content TEXT,
    category_id INT UNSIGNED NOT NULL DEFAULT 0,
    hits INT UNSIGNED NOT NULL DEFAULT 0
    )engine=InnoDB default charset=utf8
```
(2).同样上述新闻发布系统：表comment记录用户回复内容，字段如下

字段名描述 comment_id 回复id id 文章id，关联message表中的id comment_content 回复内容 

现通过查询数据库需要得到以下格式的文章标题列表,并按照回复数量排序，回复最高的排在最前面  
文章id文章标题点击量回复数量  
用一个SQL语句完成上述查询，如果文章没有回复则回复数量显示为0  
查询语句如下：

```sql
    SELECT message.id AS id,title,hits,IF(comment.id is NULL,0,count(*))AS number
    FROM message
    LEFT JOIN comment
    ON message.id=comment.id
    GROUP BY message.id
```

(3).上述内容管理系统，表category保存分类信息，字段如下

字段名描述 category_id int(4)not null auto_increment; categroy_name varchar(40)not null; 

用户输入文章时，通过选择下拉菜单选定文章分类  
写出如何实现这个下拉菜单

```php
    function categoryList(){
        $result=mysql_query("select category_id,category_name from category")or die("Invalid
        query:".mysql_error());
        echo"<select name='category'value=''>";
        while($row=mysql_fetch_array($result)){
            echo"<option value='".$row['category_id']."'>".$row['category_name']."</option>";
        }
        echo"</select>";
    }
```

文件操作部分：上述内容管理系统，用户提交内容后，系统生成静态HTML页面，写出实现的基本思路。  
要生成静态HTML页面，需要使用输出缓冲output buffering及文件操作，首先使用ob_start()函数开启输出缓冲，在页面内容执行完成时，使用ob_get_contents()函数获取保存在输出缓冲区中的内容，然后使用file_put_contents()函数，生成静态HTML页面即可。

###### 19.请问cgi和fastcgi有什么不同，你在什么情况下会选择哪个？（酷讯）

原理一样，都是利用标准输入输出流处理HTTP之类的文本协议，都是通过多进程模式处理多请求。不同之处在于FastCGI的一个进程处理完一个请求之后重置状态并挂起，待下一个请求来时继续处理；而CGI的一个进程则处理完一个请求后退出，下一个请求来时再创建新进程。

###### 20.zend optimizer是什么？（酷讯）

Zend Optimizer可以加速PHP脚本的执行，提高PHP应用程序的执行速度。实现的原  
理是对那些程序在被最终执行之前由运行编译器(Run-Time Compiler)产生的代码进行优化。  
一般情况下，执行使用Zend Optimizer的PHP程序比不使用的要快40%到100%。这意  
味着网站的访问者可以更快的浏览网页，从而完成更多的事务，创造更好的客户满意度。  
Zend Optimizer还可以给用Zend加密的文件解密。

###### [!!]21.列举web开发中的安全性问题

sql注入攻击。  
数据库操作安全，UPDATE、DELETE、INSERT的操作没有限制用户操作权限，这将是一件很危险的事情。  
没有验证用户http请求的方式POST或者GET，GET请求被合法通过。  
没有验证表单来源的唯一性，不能识别是合法的表单提交还是黑客伪造的表单提交。  
XSS攻击。

###### [!]22.如何通过php程序防止外部页面提交表单？编写一段代码

```php
    <?php
        session_start();
        if(isset($_POST['name'])&&!empty($_POST['name'])){
            if($_POST['check']==$_SESSION['check']){
                echo'正常访问';
                }else{
                echo'外部访问';
            }
        }
        $token=md5(uniqid(rand(),true));
        $_SESSION['check']=$token;
    ?>
    <form method="post"action="">
    <input type="text"name="name">
    <input type="hidden"name="check"value="<?php echo$token;?>">
    <input type="submit">
    </form>
```

###### [!]23.如果某段与数据库交互的程序运行较慢你将如何处理?

一是首先提高数据库的查询速度，比如增加索引，优化表的结构。  
二是优化程序代码，如果查询比较多，可以尽量用条件查询，减少查询语句，比如能用一条查询语句就不用两条。  
三就是提高服务器的速度，优化服务器，把不必要的进程关掉。

24.以下代码会产生什么问题，如何解决？

```php
    <?php
    $dir=$_POST['dir'];
    include("/usr/local/apache/htdoc/inc/$dir");
    ?>
```

不安全，必须对用户的输入进行验证和过滤。

###### [!!]25.请简述操作系统的线程与进程的区别。列举LINUX下面你使用过的软件？

进程是具有一定独立功能的程序关于某个数据集合上的一次运行活动，进程是系统进行资源分配和调度的一个独立单位。  
线程是进程的一个实体是CPU调度和分派的基本单位，它是比进程更小的能独立运行的基本单位。  
进程和线程的主要差别在于它们是不同的操作系统资源管理方式。进程有独立的地址空间，一个进程崩溃后，在保护模式下不会对其它进程产生影响，而线程只是一个进程中的不同执行路径。线程有自己的堆栈和局部变量，但线程之间没有单独的地址空间，一个线程死掉就等于整个进程死掉，所以多进程的程序要比多线程的程序健壮，但在进程切换时，耗费资源较大，效率要差一些。但对于一些要求同时进行并且又要共享某些变量的并发操作，只能用线程，不能用进程。  
Linux下常用软件，vim，emacs，tar，openoffice，putty，wget，links，ssh等。

###### 26.用户在网站表单提交数据的时候，为了防止脚本攻击（比如用户输入），`<script>alert（111）;</script>`php端接收数据的时候，应该如何处理？

可以对用户输入数据进行转义，如htmlspecialchars($_POST['title']);

###### [!!!]27.使用过Memcache缓存吗，如果使用过，能够简单的描述一下它的工作原理吗？

Memcahce是把所有的数据保存在内存当中，采用hash表的方式，每条数据由key和value组成，每个key是独一无二的，当要访问某个值的时候先按照找到值，然后返回结果。  
Memcahce采用LRU算法来逐渐把过期数据清除掉。

###### 28.一个Web开发团队开发中，大致说说你所了解的所有成员的分工合作情况

每个公司的分工合作情况各不相同，一般会有策划，美工，前端开发，后台开发，维护，优化和推广等。

###### [!!!]29.假设给你5台服务器，请大致的描述一下，如何使用你所熟悉的开源软件，搭建一个日PV 300万左右的中型网站？

参考结构：  
3台Web服务器，两台MySQL数据库服务器，采用Master/Slave同步的方式减轻数据库负载，Web服务器可以结合Memcache缓存来减少负载，同时三台Web服务器内容一致，  
可以采用DNS轮询的方式来进行负载平衡。

###### 30.谈谈对你PHP认识或你擅长的技术？

自由发挥

###### [!!!]31.什么是Ajax？Ajax的原理是什么？Ajax的核心技术是什么？Ajax的优缺点是什么？

Ajax是Asynchronous JavaScript and XML的缩写，是JavaScript、XML、CSS、DOM等多个技术的组合。  
Ajax的工作原理是一个页面的指定位置可以加载另一个页面所有的输出内容，这样就实现了一个静态页面也能获取到数据库中的返回数据信息了。所以Ajax技术实现了一个静态网页在不刷新整个页面的情况下与服务器通信，减少了用户等待时间，同时也从而降低了网络流量，增强了客户体验的友好程度。  
Ajax的核心技术是XMLHttpRequest，它是JavaScript中的一个对象。  
Ajax的优点是：  
(1).减轻了服务器端负担，将一部分以前由服务器负担的工作转移到客户端执行，利用客户端闲置的资源进行处理；  
(2).在只局部刷新的情况下更新页面，增加了页面反应速度，使用户体验更友好。  
Ajax的缺点是不利于SEO推广优化，因为搜索引擎无法直接访问到Ajax请求的内容。

###### 32.请用PHP实现一个函数，将一个2进制数的无符号非负电位字符串非浮点字符串转成一个10进制数，返回该10进制数。不许使用BIN等系统内置函数（嘀嗒团）

题目意思有些模糊，题目本意可能是将一个无符号的2进制字符串转成10进制数，如'10100010'，应该得到10100010的十进制表示162。

```php
<?php
function bin2dec($bin){
    $temp = strrev($bin);
    $result = 0;
    for ($i=0,$len = strlen($temp); $i < $len; $i++) {
        $result += pow(2,$i) * $temp[$i];
    }
    return $result;
}

$a = '10100010';
echo bin2dec($a);//结果162
?>
```

###### 33.请使用PHP设计一个函数，对学生英语考试得分从高到低排序，输入时所有学生的学号和考试得分，返回排好序的考试得分和对应学生的学号。考试满分为100，得分可能会有小数，由于考试评分要求，小数位只会是0或0.5

要求：  
请不要使用qsort等系统内置排序函数  
请使用你认为最快最优的方法实现该函数并使排序的性能最高。（嘀嗒团）

```php
<?php
// 快速排序实现
function array_sort(&$arr,$left,$right){
    if ($left < $right) {
        $pivot = $arr[$left];
        $low = $left;
        $high = $right;

        while ($low < $high) {
            while ($low < $high && $arr[$high]['score'] >= $pivot['score']) {
                $high--;
            }
            $arr[$low] = $arr[$high];
            while ($low < $high && $arr[$low]['score'] <= $pivot['score']) {
                $low++;
            }
        }
        $arr[$low] = $pivot;
        array_sort($arr,$left,$low-1);
        array_sort($arr,$low+1,$right);
    }
}

$english = array(
        array('sid'=>1,'score'=>76),
        array('sid'=>2,'score'=>93),
        array('sid'=>3,'score'=>68.5),
        array('sid'=>4,'score'=>82.5),

    );
$left = 0;
$right = count($english) - 1;
array_sort($english,$left,$right);

print_r($english);
?>
```

###### 34.需要设置一个有效期为31天，的memcach值，请补充下面的代码（奇矩互动）

```php
<?php
$memcache_obj=new memcache
$memcache_obj->connect('memcache_host,11211');
$memcache_obj->set('varKey','varValue',0,____);
?>
```

time()+3600_24_31

###### 35.你从_____时候开启接触PHP的？从可以写出链接mysql数据库查询更改数据到现在大约有____时间?（奇矩互动）  
根据自身情况填写

###### 36.现在请你设计一个留言板系统，请简要的写出你设计的其中分页算法的思路。（奇矩互动）

主要是数据库的设计系统的架构思想  
分页算法的原理是limit offset,pagesize其中，pagesize是设定好的，而offset则要通过计算得到，不同的页数对应的offset也不同，设当前页为currentpage，则offset=(currentpage-1)*pagesize。

###### 37.假设有"123_abc_456_def_789"这么一个字符串,写一个函数，可以传入一个字符串，和一个要截取的长度。返回截取后的结果。（小米）

要求：  
(1)._和_标记不得计算在长度之内。  
(2).截取后的字符串，要保留原有_标签，不过如果最后有一个标签没有闭合，则  
去掉其开始标签。  
示例：题中的字符串，要截取长度5，则返回的字符串应该为123ab，要截取长度8，  
应返回123_abc_45。_

```php
<?php
function cut($str,$len=null){
    $last=0;
    $str_len=strlen($str);
    $result='';
    $result_len=0;
    do{
        $pattern='/<em>(.*?)<\/em>/i';
        $num=preg_match($pattern,$str,$m,PREG_OFFSET_CAPTURE,$last);
        if($num){
            $result.=substr($str,$last,
            $add_len=($m[0][1]-$last<$len-$result_len)?$m[0][1]-$last:$len-$result_len);
            $result_len+=$add_len;
            $last=$m[0][1]+strlen($m[0][0]);

            if($result_len<$len){
                if($len-$result_len>=strlen($m[1][0])){
                    $result.=$m[0][0];
                    $result_len+=strlen($m[1][0]);
                }else{
                    $result.=substr($m[1][0],0,$len-$result_len);
                    break;
                }
            }
        }else{
            $result.=substr($str,$last,$len-$result_len);
            break;
        }
    }while($last<$str_len&&$result_len<$len);
    return$result;
}
?>
```

###### 38.请仅使用一次正则替换，将下面内容

private long contract_id;  
private string contract_number;  
private string customer_name;  
替换为  
private long contractId;  
private string contractNumber;  
private string customerName;（鑫众人云）

```php
<?php
$str = "private long contract_id;
private string contract_number;
private string customer_name;";

$pattern = '/_(\w)/em';
$result = preg_replace($pattern,"strtoupper('\\1')",$str);
echo $result;
?>
```

###### [!!]39.列举流行的Ajax框架？说明Ajax实现原理是什么及json在Ajax中起什么作用？（鑫众人云）

流行的Ajax框架有jQuery，Prototype，Dojo，MooTools。  
Ajax的工作原理是一个页面的指定位置可以加载另一个页面所有的输出内容，这样就实现了一个静态页面也能获取到数据库中的返回数据信息了。所以Ajax技术实现了一个静态网页在不刷新整个页面的情况下与服务器通信，减少了用户等待时间，同时也从而降低了网络流量，增强了客户体验的友好程度。  
在使用Ajax时，涉及到数据传输，即将数据从服务器返回到客户端，服务器端和客户端分别使用不同的脚步语言来处理数据，这就需要一种通用的数据格式，XML和json就是最常用的两种，而json比XML更简单。

###### 40.在UNIX或windows系统内以（）为单位分配资源以（）单位分配时间调度（亿邮）

进程，时间片

###### 41.正则表达式中_？+的作用分别是什么（亿邮）_？+都有用来匹配数量的，*表示0或多个，？表示0个或1个，+表示1个或多个。

###### 42.写出你所知道的XML解析器（亿邮）

DOM，SAX，SimpleXML，其中前两种是通用的解析器，和具体语言无关，而SimpleXML则是PHP提供的解析器。

###### 43.在程序中表示时间可以使用哪几种变量类型（亿邮）

在PHP中可以使用int或字符串来表示（php中没有日期时间类型），在MySQL中，可以使用int，date，datetime，timestamp。

###### 44.使用Utf-8编码存储中文姓名，一般会分配多少个字节的存储空间（亿邮）

UTF-8编码是可变长编码，对于中文而言，一个字符使用3个字节来存储。

###### 45.用正则表达式判断$a是否是一个以半角逗号分隔的多个手机号码组成的字符串，是输出yes（卓望）

```php
<?php
$pattern = '/^1[358]\d{9}(,1[358]\d{9})*$/';
$subject = '13507224985,13833103237';

if (preg_match($pattern,$subject)) {
    echo "yes";
}
?>
```

###### 46.如果要求每隔5分钟执行一次脚本five.php，如何实现？（卓望）

用到的函数ignore_user_abort(),set_time_limit(0),sleep($interval)，此代码只要运行一次后关闭浏览器即可。

```php
<?php
ignore_user_abort();//关掉浏览器，PHP脚本也可以继续执行.
set_time_limit(0);//通过set_time_limit(0)可以让程序无限制的执行下去

$interval=60*5;//每隔5分钟运行
do{
    //这里是你要执行的代码
    sleep($interval);//等待5分钟
}while(true);

?>
```

###### 47.假设有一个博客系统，数据库存储采用mysql，用户数量为1000万，预计文章总数为10亿，每天有至少10万的更新量，每天访问量为5000万，对数据库的读写操作的比例超过10：1，你如何设计该系统，以确保其系统高效，稳定的运行？提示：可以从数据库设计，系统框架，及网络架构方面进行描述，可以自由发挥（新浪网技术部）

###### 相关题目：我们希望开发一个门户系统，数据存储采用MySQL，用户数量为1000万，预计文章总数为10亿，日更新量至少为10万，日访问量为5000万，对数据库的读写操作比例超过10:1，你如何设计该系统，以确保其高效，稳定的运行？（提示：可以从数据库设计，系统框架及网络架构方面进行描述，自由发挥）（鑫众人云）

###### 项目设计：假设有一个包含Tag功能的博客系统，数据库存储采用mysql，用户数量为1000万，预计文章总数为10亿，每天有至少10万的更新量，每天访问量为5000万，对数据库的读写操作的比例超过10：1。你如何设计该系统，以确保其系统高效，稳定的运行?提示：可以从数据库设计，系统框架，及网络架构方面进行描述，可以写代码/伪代码辅助说明，可以自由发挥(小米)

学习的热情不因季节的变化而改变

[0]: http://www.cnblogs.com/-shu/p/4601002.html
[1]: mailto:%E4%BE%8B%E5%A6%82@myarray
#  [PHP数据库保存session会话][0]

 标签： [session][1][php][2][mysqli][3][php会话控制][4]

 2016-06-12 13:41  858人阅读  

版权声明：本文为博主原创文章，未经博主允许不得转载。

 目录

1. [前言][10]
1. [创建会话表][11]
1. [定义会话函数][12]
1. [使用新会话处理程序][13]
1. [测试使用][14]

## 前言:

在默认的情况下,[PHP][15]会把全部的会话数据保存在服务器上的文本文件里面,这些文件通常都是保存在服务器上的临时目录里边。

那为什么我们要把session会话保存在[数据库][16]中呢?

1. 主要原因:提高系统的安全性。在共享服务器上，在没有进行特别的设置，所有的网站站点都会使用同一个临时目录，这意味着数十个程序都在同一个位置对文件进行读写操作。不仅速度下降了，而且别人也有可能窃取到我的站点的用户数据。
1. 把会话数据保存到数据库还可以更方便的搜索web站点会话的更多信息，我们可以查询活动会话的数量（同时在线的用户量），还可以对会话数据进行备份。
1. **假如我的站点同时运行于多个服务器**，那么某个用户在一个会话过程中，可能会对不同的服务器发送多个请求，但是会话数据如果保存在某一个服务器上，那么其他服务器就不能使用到这些会话数据。假如我的某一台服务器仅仅是数据库的角色，那你把会话数据全保存在数据库中，不是很方便么？

更多的关于[php][15] session的理解可以参考该博客 [彻底理解PHP的SESSION机制][17]

## 1、创建会话表

由于 session 数据是保存在服务器上面的，而在客户端中保存的是一个索引（sessionID）,这个索引对应于服务器上的某一条 session 数据。因此该表必须包含的两个字段是 id、data，还有就是会话会有过期时间，所以在这里还有个字段就是 last_accessed，这里我把该表建在test数据库下：
```sql
CREATE TABLE sessions(
    id CHAR(32) NOT NULL,
    data TEXT,
    last_accessed TIMESTAMP NOT NULL,
    PRIMARY KEY(id)
);
```

![][18]

PS：如果程序需要在会话保存大量的数据，则 data 字段可能就需要定义为 MEDIUMTEXT 或 LONGTEXT 类型了。

## 2、定义会话函数：

这里我们主要有两个步骤：

1. 定义与数据库交互的函数
1. 使PHP能使用这些自定义函数

在第二步中，是通过调用函数 session_set_save_handler()来完成的，调用它需要6个参数，分别是 open(启动会话)、close(关闭会话)、read(读取会话)、write(写入会话)、destroy(销毁会话)、clean(垃圾回收)。

我们新建php文件 sessions.inc.php ，代码如下：

```php
<?php

$sdbc = null;  //数据库连接句柄，在后面的函数里面让它成为全局变量

//启动会话
function open_session()
{
    global $sdbc;      //使用全局的$sdbc
    $sdbc = mysqli_connect('localhost', 'root', 'lsgogroup', 'test');     //数据库 test
    if (!$sdbc) {
        return false;
    }
    return true;
}

//关闭会话
function close_session()
{
    global $sdbc;
    return mysqli_close($sdbc);
}

//读取会话数据
function read_session($sid)
{
    global $sdbc;
    $sql = sprintf("SELECT data FROM sessions WHERE id='%s'", mysqli_real_escape_string($sdbc, $sid));
    $res = mysqli_query($sdbc, $sql);
    if (mysqli_num_rows($res) == 1) {
        list($data) = mysqli_fetch_array($res, MYSQLI_NUM);
        return $data;
    } else {
        return '';
    }
}

//写入会话数据
function write_session($sid, $data)
{
    global $sdbc;
    $sql = sprintf("INSERT INTO sessions(id,data,last_accessed) VALUES('%s','%s','%s')", mysqli_real_escape_string($sdbc, $sid), mysqli_real_escape_string($sdbc, $data), date("Y-m-d H:i:s", time()));
    $res = mysqli_query($sdbc, $sql);
    if (!$res) {
        return false;
    }
    return true;
}

//销毁会话数据
function destroy_session($sid)
{
    global $sdbc;
    $sql = sprintf("DELETE FROM sessions WHERE id='%s'", mysqli_real_escape_string($sdbc, $sid));
    $res = mysqli_query($sdbc, $sql);
    $_SESSION = array();
    if (!mysqli_affected_rows($sdbc) == 0) {
        return false;
    }
    return true;
}

//执行垃圾回收（删除旧的会话数据）
function clean_session($expire)
{
    global $sdbc;
    $sql = sprintf("DELETE FROM sessions WHERE DATE_ADD(last_accessed,INTERVAL %d SECOND)<NOW()", (int)$expire);
    $res = mysqli_query($sdbc, $sql);
    if (!$res) {
        return false;
    }
    return true;
}

//告诉PHP使用会话处理函数
session_set_save_handler('open_session', 'close_session', 'read_session', 'write_session', 'destroy_session', 'clean_session');

//启动会话，该函数必须在session_set_save_handler()函数后调用，不然我们所定义的函数就没法起作用了。
session_start();

//由于该文件被包含在需要使用会话的php文件里面，因此不会为其添加PHP结束标签
```

PS：

1. 处理“读取”函数外，其他函数必须返回一个布尔值，“读取”函数必须返回一个字符串。
1. .每次会话启动时，“打开”和“读取”函数将会立即被调用。当“读取”函数被调用的时候，可能会发生垃圾回收过程。
1. 当脚本结束时，“写入”函数就会被调用，然后就是“关闭”函数，除非会话被销毁了，而这种情况下，“写入”函数不会被调用。但是，在“关闭”函数之后，“销毁”函数将会被调用。
1. .session_set_save_handler()函数参数顺序不能更改，因为它们一一对应 open 、close、read、、、、
1. 会话数据最后将会以数据序列化的方式保存在数据库中。

## 3、使用新会话处理程序

使用新会话处理程序只是调用session_set_save_handler()函数，使我们的自定义函数能够被自动调用而已。其他关于会话的操作都没有发生变化（以前怎么用现在怎么用，我们的函数会在后台自动被调用），包括在会话中存储数据，访问保存的会话数据以及销毁数据。

在这里，我们新建 sessions.php 文件，该脚本将在没有会话信息时创建一些会话数据，并显示所有的会话数据，在用户点击 ‘log out’（注销）时销毁会话数据。

代码：

```php
<?php

//引入sessions.inc.php文件，即上面的代码
require('sessions.inc.php');

?>
<!doctype html>
<html lang='en'>
<head>
    <meta charset="utf-8">
    <title>DB session test</title>
</head>
<body>
<?php

//创建会话数据
if(empty($_SESSION)){
    $_SESSION['blah'] = "umlaut";
    $_SESSION['this'] = 12345;
    $_SESSION['that'] = 'blue';
    echo "<p>Session data stored</p>";
}else{
    echo "<p>Session data exists:<pre>".print_r($_SESSION,1)."</pre></p>";
}

if(isset($_GET['logout'])){
    //销毁会话数据
    session_destroy();
    echo "<p>session destroyed</p>";
}else{
    echo "<a href='sessions.php?logout=true'>log out</a>";
}


echo "<p>session data :<pre>".print_r($_SESSION,1)."</pre></p>";

echo '</body></html>';

session_write_close();  //下面重点解析
?>

</body>
```
解析 session_write_close():

顾名思义，该函数就是先写入会话数据，然后关闭session会话，按道理这两步在脚本执行完后会自动执行，为什么我们还要显式调用它呢？因为这里涉及到了数据库的连接！

由于我们知道，PHP会在脚本执行完后自动关闭数据库的所有连接，而同时会话函数会尝试向数据库写入数据并关闭连接。这样一来就会导致会话数据没法写入数据库，并且出现一大堆错误，例如write_session()、close_session()函数中都有用到数据库的连接。

为了避免以上说的问题，我们在脚本执行完之前调用 session_write_close()函数，他就会调用“写入”函数和“关闭”函数，而此时数据库连接还是存在的！

PS：在使用header()函数重定向浏览器之前也应该调用session_write_close()函数，假如有数据库的操作时！

## 4、测试使用

在浏览器中打开 sessions.php，刷新页面，然后再看看数据库有没有添加数据。在另一个浏览器打开 sessions.php ，看看数据库中有没有添加另一条数据。。。。。

本博客主要是参考自《深入理解PHP高级技巧、面向对象与核心技术》，希望能帮到大家。

[0]: http://blog.csdn.net/baidu_30000217/article/details/51644539
[1]: http://www.csdn.net/tag/session
[2]: http://www.csdn.net/tag/php
[3]: http://www.csdn.net/tag/mysqli
[4]: http://www.csdn.net/tag/php%e4%bc%9a%e8%af%9d%e6%8e%a7%e5%88%b6
[9]: #
[10]: #t0
[11]: #t1
[12]: #t2
[13]: #t3
[14]: #t4
[15]: http://lib.csdn.net/base/php
[16]: http://lib.csdn.net/base/mysql
[17]: http://www.cnblogs.com/acpp/archive/2011/06/10/2077592.html
[18]: ../img/20160612122519075.png
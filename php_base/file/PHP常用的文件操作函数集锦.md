# PHP常用的文件操作函数集锦

 [流年二哥][0]  2017年7月13日  [PHP基础][1]  [0][2]

### **一 、解析路径：**

**1 获得文件名:**  
 basename();   
给出一个包含有指向一个文件的全路径的字符串，本函数返回基本的文件名。如果文件名是以 suffix 结束的，那这一部分也会被去掉。
```php
 <?php   
 $path  =  "/home/httpd/html/index.php" ;   
 $file  = [basename][3] ( $path , ".php" ) ;  // $file is set to "index"
```
**2 得到目录部分**：

dirname();   
给出一个包含有指向一个文件的全路径的字符串，本函数返回去掉文件名后的目录名。
```php
 <?php   
 $path  =  "/etc/passwd" ;   
 $file  = [dirname][4] ( $path ) ;  // $file is set to "/etc"
```
**3 得到路径关联数组**  
 pathinfo();   
得到一个指定路径中的三个部分：目录名，基本名，扩展名。
```php
 <?php   
 $pathinfo  = [pathinfo][5] ( "www/test/index.html" ) ;   
[var_dump][6] ( $pathinfo ) ;   
 // $path['dirname']   
 $path [ 'basename' ]   
 $path [ 'extenssion' ]
```
**二、文件类型**  
 1. filetype();   
返回文件的类型。可能的值有 fifo，char，dir，block，link，file 和 unknown。
```php
 <?php   
 echo [filetype][7] ( '/etc/passwd' ) ;  // file   
 echo [filetype][7] ( '/etc/' ) ;  // dir
```
**三、得到给定文件有用信息数组**(很有用)

1、 fstat();   
通过已打开的文件指针取得文件信息  
获取由文件指针 handle 所打开文件的统计信息。本函数和 stat() 函数相似，除了它是作用于已打开的文件指针而不是文件名。
```php
 <?php   
 // 打开文件   
 $fp  = [fopen][8] ( "/etc/passwd" ,  "r" ) ;   
 // 取得统计信息   
 $fstat  = [fstat][9] ( $fp ) ;   
 // 关闭文件   
[fclose][10] ( $fp ) ;   
 // 只显示关联数组部分   
[print_r][11] ( [array_slice][12] ( $fstat ,  13 ) ) ;
```
2、 stat()   
获取由 filename 指定的文件的统计信息(类比fstat())

**四、计算大小**  

1、 filesize()   
 返回文件大小的字节数，如果出错返回 FALSE 并生成一条 E_WARNING 级的错误。
```php
 <?php   
 // 输出类似：somefile.txt: 1024 bytes   
 $filename  =  'somefile.txt' ;   
 echo  $filename  .  ': '  . [filesize][13] ( $filename )  .  ' bytes' ;
```

2、 disk_free_space()   
 获得目录所在磁盘分区的可用空间（字节单位）
```php
 <?php   
 // $df 包含根目录下可用的字节数   
 $df  = [disk_free_space][14] ( "/" ) ;   
 //在 Windows 下:   
[disk_free_space][14] ( "C:" ) ;   
[disk_free_space][14] ( "D:" ) ;
```

3、 disk_total_space()   
 返回一个目录的磁盘总大小  
 eg:(同上，换掉函数)

另：如需要计算一个目录大小，可以编写一个递归函数来实现
```php
<?php
function dir_size($dir){
    $dir_size = 0;
    if($dh = @opendir($dir)){
        while(($filename = readdir($dh)) != false){
            if($filename !='.' and $filename !='..'){
                 if(is_file($dir.'/'.$filename)){
                    $dir_size +=filesize($dir.'/'.$filename);
                  }else if(is_dir($dir.'/'.$filename)){
                          $dir_size +=dir_size($dir.'/'.$filename);
                    }
            }
        }#end while
  }# end opendir
   @closedir($dh);
    return $dir_size;
} #end function
```
**五、 访问与修改时间**  
 1. fileatime() : 最后访问时间  
 2. filectime() : 最后改变时间（任何数据的修改）  
 3. filemtime() : 最后修改时间（指仅是内容修改）

**六、 文件的I/O操作**

1、 fopen -- 打开文件或者 URL  
mode 说明  
'r' 只读方式打开，将文件指针指向文件头。  
'r+' 读写方式打开，将文件指针指向文件头。  
'w' 写入方式打开，将文件指针指向文件头并将文件大小截为零。如果文件不存在则尝试创建之。  
'w+' 读写方式打开，将文件指针指向文件头并将文件大小截为零。如果文件不存在则尝试创建之。  
'a' 写入方式打开，将文件指针指向文件末尾。如果文件不存在则尝试创建之。  
'a+' 读写方式打开，将文件指针指向文件末尾。如果文件不存在则尝试创建之。  
'x' 创建并以写入方式打开，将文件指针指向文件头。如果文件已存在，则 fopen() 调用失败并返回 FALSE，  
'x+' 创建并以读写方式打开，将文件指针指向文件头。如果文件已存在，则 fopen() 调用失败并返回 FALSE  
eg:

$handle = fopen("/home/rasmus/file.txt", "r");

2、 file -- 把整个文件读入一个数组中(此函数是很有用的)

和 file_get_contents() 一样，只除了 file() 将文件作为一个数组返回。数组中的每个单元都是文件中相应的一行，包括换行符在内。如果失败 file() 返回 FALSE。
```php
<?php
$lines = file('http://www.example.com/');
// 在数组中循环，显示 HTML 的源文件并加上行号。
foreach ($lines as $line_num => $line) {
    echo "Line #<b>{$line_num}</b> : " . htmlspecialchars($line) 
. "<br />\n";
}
// 另一个例子将 web 页面读入字符串。参见 file_get_contents()。
$html = implode('', file ('http://www.example.com/'));
```
3、 fgets -- 从文件指针中读取一行

从 handle 指向的文件中读取一行并返回长度最多为 length - 1 字节的字符串。碰到换行符（包括在返回值中）、EOF 或者已经读取了 length - 1 字节后停止（看先碰到那一种情况）。如果没有指定 length，则默认为 1K，或者说 1024 字节。
```php
<?php
$handle = @fopen("/tmp/inputfile.txt", "r");
if ($handle) {
    while (!feof($handle)) {
        $buffer = fgets($handle, 4096);
        echo $buffer;
    }
    fclose($handle);
}
```
4、 fgetss -- 从文件指针中读取一行并过滤掉 HTML 标记

和 fgets() 相同，只除了 fgetss 尝试从读取的文本中去掉任何 HTML 和 PHP 标记。

可以用可选的第三个参数指定哪些标记不被去掉

另：对的目录的操作：  

 1. opendir -- 打开目录句柄，打开一个目录句柄，可用于之后的 closedir()，readdir() 和 rewinddir() 调用中。  
 2. readdir -- 从目录句柄中读取条目，返回目录中下一个文件的文件名。文件名以在文件系统中的排序返回。
```php
<?php
// 注意在 4.0.0-RC2 之前不存在 !== 运算符

if ($handle = opendir('/path/to/files')) {
    echo "Directory handle: $handle\n";
    echo "Files:\n";
  
    while (false !== ($file = readdir($handle))) {
        echo "$file\n";
    }
   
    while ($file = readdir($handle)) {
        echo "$file\n";
    }
     closedir($handle);
}
```
 3.scandir -- 列出指定路径中的文件和目录(很有用),返回一个 array，包含有 directory 中的文件和目录。  
默认的排序顺序是按字母升序排列。如果使用了可选参数 sorting_order（设为 1），则排序顺序是按字母降序排列。

·

    <?php
    $dir    = '/tmp';
    $files1 = scandir($dir);
    $files2 = scandir($dir, 1);
    
    print_r($files1);
    print_r($files2);
​

另外注：

**七、 对文件属性的操作**（操作系统环境不同，可能有所不一样，这点要注意）

1 文件是否可读：

    boolis_readable ( string filename )

如果由 

filename

指定的文件或目录存在并且可读则返回 TRUE。

记住 PHP 也许只能以运行 webserver 的用户名（通常为 'nobody'）来访问文件。不计入安全模式的限制。

2 文件是否可写

    bool is_writable ( string filename )

如果文件存在并且可写则返回 TRUE。

filename

参数可以是一个允许进行是否可写检查的目录名。

记住 PHP 也许只能以运行 webserver 的用户名（通常为 'nobody'）来访问文件。不计入安全模式的限制

3 检查文件是否存在

    boolfile_exists ( string filename )

如果由 

filename

指定的文件或目录存在则返回 TRUE，否则返回 FALSE

**=====================================PHP文件操作类=========================================**

```php
<?php
/***************************************************************************************
文件名：File.cls.php
文件简介：类clsFile的定义，对文件操作的封装
版本：2.0  最后修改日期：2011-8-23
****************************************************************************************/
!defined('INIT_PHPV') && die('No direct script access allowed');
class clsFile
{
   private $fileName_str;         //文件的路径
   private $fileOpenMethod_str;   //文件打开模式
   
   function __construct($fileName_str='',$fileOpenMethod_str='readOnly')//路径，默认为空；模式，默认均为只读
   {
       //构造函数，完成数据成员的初始化
       $this->fileName_str=$fileName_str;
       $this->fileOpenMethod_str=$fileOpenMethod_str;
   }
   
   function __destruct()
   {
       //析构函数
   }
   
   public function __get($valName_val)//欲取得的数据成员名称
   {
       //特殊函数，取得指定名称数据成员的值
          return $this->$valName_val;
   }
   
   private function on_error($errMsg_str='Unkown Error!',$errNo_int=0)//错误信息，错误代码
   {
        echo '程序错误：'.$errMsg_str.'错误代码：'.$errNo_int;//出错处理函数
   }
   
   public function open()
   {
       //打开相应文件，返回文件资源标识
          //根据fileOpenMethod_str选择打开方式
          switch($this->fileOpenMethod_str)
          {
                 case 'readOnly':
                    $openMethod_str='r';      //只读，指针指向文件头
                    break;
                 case 'readWrite':
                    $openMethod_str='r+';     //读写，指针指向文件头
                    break;
                 case 'writeAndInit':
                    $openMethod_str='w';      //只写，指针指向文件头将大小截为零，不存在则创建
                    break;
                 case 'readWriteAndInit':
                    $openMethod_str='r+';     //读写，指针指向文件头将大小截为零，不存在则创建
                    break;
                 case 'writeAndAdd':
                    $openMethod_str='a';      //只写，指针指向文件末尾，不存在则创建
                    break;
                 case 'readWriteAndAdd':
                    $openMethod_str='a+';     //读写，指针指向文件末尾，不存在则创建
                    break;
                 default:
                    $this->on_error('Open method error!',310);//出错处理
                    exit;
          }
          
          //打开文件       
          if(!$fp_res=fopen($this->fileName_str,$openMethod_str))
          {
                 $this->on_error('Can\'t open the file!',301);//出错处理
                 exit;
          }
          
          return $fp_res;
   }
   
   public function close($fp_res)//由open返回的资源标识
   {
       //关闭所打开的文件
          if(!fclose($fp_res))
          {
                 $this->on_error('Can\'t close the file!',302);//出错处理
                 exit;
          }
   }
   
   public function write()//$fp_res,$data_str,$length_int:文件资源标识，写入的字符串，长度控制
   {
       //将字符串string_str写入文件fp_res，可控制写入的长度length_int
          //判断参数数量，调用相关函数
          $argNum_int=func_num_args();//参数个数
          
          $fp_res=func_get_arg(0);          //文件资源标识
          $data_str=func_get_arg(1);        //写入的字符串
          
          if($argNum_int==3)
          {
                 $length_int=func_get_arg(2);  //长度控制
              if(!fwrite($fp_res,$data_str,$length_int))
              {
                    $this->on_error('Can\'t write the file!',303);//出错处理
                    exit;
              }
          }
          else
          {
                 if(!fwrite($fp_res,$data_str))
              {
                    $this->on_error('Can\'t write the file!',303);//出错处理
                    exit;
              }
          }
   }
   
   public function read_line()//$fp_res,$length_int:文件资源标识，读入长度
   {
       //从文件fp_res中读入一行字符串，可控制长度
          //判断参数数量
          $argNum_int=func_num_args();
          $fp_res=func_get_arg(0);
          
          if($argNum_int==2)
          {
              $length_int=func_get_arg(1);
              if($string_str=!fgets($fp_res,$length_int))
              {
                    $this->on_error('Can\'t read the file!',304);//出错处理
                    exit;
              }
              return $string_str;
       }
       else
       {
              if(!$string_str=fgets($fp_res))
              {
                    $this->on_error('Can\'t read the file!',304);//出错处理
                    exit;
              }
              return $string_str;
          }
   }
   
   public function read($fp_res,$length_int)//文件资源标识，长度控制
   {
       //读入文件fp_res，最长为length_int
          if(!$string_str=fread($fp_res,$length_int))
          {
                 $this->on_error('Can\'t read the file!',305);//出错处理
                 exit;
          }
          return $string_str;
   }
   
   public function is_exists($fileName_str)//文件名
   {
       //检查文件$fileName_str是否存在，存在则返回true，不存在返回false
          return file_exists($fileName_str);
   }

/******************取得文件大小*********************/
/*
取得文件fileName_str的大小
$fileName_str 是文件的路径和名称
返回文件大小的值
*/
   public function get_file_size($fileName_str)//文件名
   {
       return filesize($fileName_str);
   }

/******************转换文件大小的表示方法*********************/
/*
$fileSize_int文件的大小，单位是字节
返回转换后带计量单位的文件大小
*/
   public function change_size_express($fileSize_int)//文件名
   {
       if($fileSize_int>1024)
       {
          $fileSizeNew_int=$fileSize_int/1024;//转换为K
          $unit_str='KB';
            if($fileSizeNew_int>1024)
             {
              $fileSizeNew_int=$fileSizeNew_int/1024;//转换为M
              $unit_str='MB';
             }
          $fileSizeNew_arr=explode('.',$fileSizeNew_int);
          $fileSizeNew_str=$fileSizeNew_arr[0].'.'.substr($fileSizeNew_arr[1],0,2).$unit_str;
       }
       return $fileSizeNew_str;
   }
/******************重命名文件*********************/
/*
将oldname_str指定的文件重命名为newname_str
$oldName_str是文件的原名称
$newName_str是文件的新名称
返回错误信息
*/ 
   public function rename_file($oldName_str,$newName_str)
   {
          if(!rename($oldName_str,$newName_str))
          {
                 $this->on_error('Can\'t rename file!',308);
                 exit;
          }
   }

/******************删除文件*********************/
/*
将filename_str指定的文件删除
$fileName_str要删除文件的路径和名称
返回错误信息
*/
   public function delete_file($fileName_str)//
   {
          if(!unlink($fileName_str))
          {
                 $this->on_error('Can\'t delete file!',309);//出错处理
                 exit;
          }
   }

/******************取文件的扩展名*********************/
/*
取filename_str指定的文件的扩展名
$fileName_str要取类型的文件路径和名称
返回文件的扩展名
*/
   public function get_file_type($fileName_str)
   {
          $fileNamePart_arr=explode('.',$fileName_str);
          while(list(,$fileType_str)=each($fileNamePart_arr))
          {
           $type_str=$fileType_str;
          }
           return $type_str;
   }

/******************判断文件是否是规定的文件类型*********************/
/*
$fileType_str规定的文件类型
$fileName_str要取类型的文件路径和名称
返回false或true
*/
   public function is_the_type($fileName_str,$fileType_arr)
   {
       $cheakFileType_str=$this->get_file_type($fileName_str);
       if(!in_array($cheakFileType_str,$fileType_arr))
       {
        return false;
          }
       else
       {
          return true;
       }
   }

/******************上传文件，并返回上传后的文件信息*********************/
/*
$fileName_str本地文件名
$filePath上传文件的路径，如果$filePath是str则上传到同一目录用一个文件命名，新文件名在其加-1，2，3..，如果是arr则顺序命名
$allowType_arr允许上传的文件类型，留空不限制
$maxSize_int允许文件的最大值，留空不限制
返回的是新文件信息的二维数组：$reFileInfo_arr
*/
   public function upload_file($fileName_str,$filePath,$allowType_arr='',$maxSize_int='')
{      
       $fileName_arr=$_FILES[$fileName_str]['name'];  //文件的名称
       $fileTempName_arr=$_FILES[$fileName_str]['tmp_name'];  //文件的缓存文件
       $fileSize_arr=$_FILES[$fileName_str]['size'];//取得文件大小
       $reFileInfo_arr=array();
       $num=count($fileName_arr)-1;
       for($i=0;$i<=$num;$i++)
      {
           if($fileName_arr[$i]!='') 
        {
          if($allowType_arr!='' and !$this->is_the_type($fileName_arr[$i],$allowType_arr))//判断是否是允许的文件类型
          {
           $this->on_error('The file is not allowed type!',310);//出错处理
           break;
          }

          if($maxSize_int!='' and $fileSize_arr[$i]>$maxSize_int)
          {
           $this->on_error('The file is too big!',311);//出错处理
           break;
          }
  
          $j=$i+1;
          $fileType_str=$this->get_file_type($fileName_arr[$i]);//取得文件类型
          if(!is_array($filePath))
          {
          $fileNewName_str=$filePath.'-'.($j).'.'.$fileType_str;
          }
          else
          {
          $fileNewName_str=$filePath_arr[$i].'.'.$fileType_str;
          }
          copy($fileTempName_arr[$i],$fileNewName_str);//上传文件
          unlink($fileTempName_arr[$i]);//删除缓存文件

          //---------------存储文件信息--------------------//
          $doFile_arr=explode('/',$fileNewName_str);
          $doFile_num_int=count($doFile_arr)-1;
          $reFileInfo_arr[$j]['name']=$doFile_arr[$doFile_num_int];
          $reFileInfo_arr[$j]['type']=$fileType_str;
          $reFileInfo_arr[$j]['size']=$this->change_size_express($fileSize_arr[$i]);
      }
   }
   return $reFileInfo_arr;
}

/******************备份文件夹*********************/
}

?>
```

[0]: http://www.phpfudao.com/?author=1
[1]: http://www.phpfudao.com/?cat=43
[2]: http://www.phpfudao.com/?p=9710#respond
[3]: http://www.php.net/basename
[4]: http://www.php.net/dirname
[5]: http://www.php.net/pathinfo
[6]: http://www.php.net/var_dump
[7]: http://www.php.net/filetype
[8]: http://www.php.net/fopen
[9]: http://www.php.net/fstat
[10]: http://www.php.net/fclose
[11]: http://www.php.net/print_r
[12]: http://www.php.net/array_slice
[13]: http://www.php.net/filesize
[14]: http://www.php.net/disk_free_space
[15]: http://www.php.net/opendir
[16]: http://www.php.net/readdir
[17]: http://www.php.net/is_file
[18]: http://www.php.net/is_dir
[19]: http://www.php.net/closedir
[20]: http://www.php.net/file
[21]: http://www.php.net/htmlspecialchars
[22]: http://www.php.net/implode
[23]: http://www.php.net/feof
[24]: http://www.php.net/fgets
[25]: http://www.php.net/scandir
[26]: http://www.php.net/defined
[27]: http://www.php.net/die
[28]: http://www.php.net/exit
[29]: http://www.php.net/func_num_args
[30]: http://www.php.net/func_get_arg
[31]: http://www.php.net/fwrite
[32]: http://www.php.net/fread
[33]: http://www.php.net/file_exists
[34]: http://www.php.net/explode
[35]: http://www.php.net/substr
[36]: http://www.php.net/rename
[37]: http://www.php.net/unlink
[38]: http://www.php.net/list
[39]: http://www.php.net/each
[40]: http://www.php.net/in_array
[41]: http://www.php.net/array
[42]: http://www.php.net/count
[43]: http://www.php.net/is_array
[44]: http://www.php.net/copy
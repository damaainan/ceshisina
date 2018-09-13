PHP SPL中提供了[SplFileInfo][0]和SplFileObject两个类来处理文件操作。

**SplFileInfo用来获取文件详细信息：**
```php  
$file = new SplFileInfo('foo-bar.txt');  
  
print_r(array(  
    'getATime' => $file->getATime(), //最后访问时间  
    'getBasename' => $file->getBasename(), //获取无路径的basename  
    'getCTime' => $file->getCTime(), //获取inode修改时间  
    'getExtension' => $file->getExtension(), //文件扩展名  
    'getFilename' => $file->getFilename(), //获取文件名  
    'getGroup' => $file->getGroup(), //获取文件组  
    'getInode' => $file->getInode(), //获取文件inode  
    'getLinkTarget' => $file->getLinkTarget(), //获取文件链接目标文件  
    'getMTime' => $file->getMTime(), //获取最后修改时间  
    'getOwner' => $file->getOwner(), //文件拥有者  
    'getPath' => $file->getPath(), //不带文件名的文件路径  
    'getPathInfo' => $file->getPathInfo(), //上级路径的SplFileInfo对象  
    'getPathname' => $file->getPathname(), //全路径  
    'getPerms' => $file->getPerms(), //文件权限  
    'getRealPath' => $file->getRealPath(), //文件绝对路径  
    'getSize' => $file->getSize(),//文件大小，单位字节  
    'getType' => $file->getType(),//文件类型 file dir link  
    'isDir' => $file->isDir(), //是否是目录  
    'isFile' => $file->isFile(), //是否是文件  
    'isLink' => $file->isLink(), //是否是快捷链接  
    'isExecutable' => $file->isExecutable(), //是否可执行  
    'isReadable' => $file->isReadable(), //是否可读  
    'isWritable' => $file->isWritable(), //是否可写  
));
```
SplFileObject继承SplFileInfo并实现[RecursiveIterator , SeekableIterator接口][1] ，用于对文件遍历、查找、操作

**遍历：**
```php 
try {  
    foreach(new SplFileObject('foo-bar.txt') as $line) {  
        echo $line;  
    }  
} catch (Exception $e) {  
    echo $e->getMessage();  
}
```
  
**查找指定行：**  
```php  
try {  
    $file = new SplFileObject('foo-bar.txt');  
    $file->seek(2);  
    echo $file->current();  
} catch (Exception $e) {  
    echo $e->getMessage();  
}

```
**写入csv文件：**  
```php 
$list = array (  
    array( 'aaa' , 'bbb' , 'ccc' , 'dddd' ),  
    array( '123' , '456' , '7891' ),  
    array( '"aaa"' , '"bbb"' )  
);  
  
$file = new SplFileObject ( 'file.csv' , 'w' );  
  
foreach ( $list as $fields ) {  
    $file -> fputcsv ( $fields );  
}
```
[0]: http://php.net/manual/zh/class.splfileinfo.php
[1]: http://www.jb51.net/article/65853.htm
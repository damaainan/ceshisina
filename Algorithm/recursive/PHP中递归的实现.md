## PHP中递归的实现

递归函数是一种调用自己的函数。写递归函数时要小心，因为可能会无穷递归下去。必须确保有充分的方法来终止递归。

一：使用参数引用完成递归函数。操作的是同一块内存地址。
```php
<?php
$i=1;
function test(&$i)
{
    echo $i;
    $i++;
   if ($i < 10)
    {
        test($i);
    }
}
test($i);//输出123456789

test($i);//输出10

?>
```
二：使用全局变量完成递归函数。在函数域内部用 global 语句导入的一个真正的全局变量实际上是建立了一个到全局变量的引用。例子中，test()函数内部的 $i 实际上只是程序第一行中（$i = 1;）的变量 $i 的一个应用；
```php
<?php
$i = 1;
function test()
{
    global $i;
    echo $i;
    $i++;
    if ($i <10)
    {
        test();
    }
}
test();//输出123456789

test();//输出10
```
 

三：使用静态变量完成递归函数。static的作用：仅在第一次调用函数的时候对变量进行初始化，并且保留变量值。
```php
<?php
function test()
{
    static $i = 1;
    echo $i;

　　　　$i++;
    if ($i < 10) {
        test();
    }
    $i--;//在每一层递归结束时自减，这一句可以帮助理解递归函数的执行过程
}

test();//输出123456789

test();//输出123456789
```
 
例1. 使用全局变量的情况递归遍历文件夹下的所有文件
```php
function getFiles($dir)
{
    global $arr;
    if(is_dir($dir)){
        $hadle = @opendir($dir);
        while($file=readdir($hadle) )
        {
            if(!in_array($file,array('.', '..')) )
            {
                $dirr = $dir.'/'.$file;
                if(is_dir($dirr))
                {
                    getFiles($dirr);
                }else{
                    array_push($arr, $dirr);
                }
            }
        }
    }
}
$arr = array();
getFiles('E:/logs');
print_r($arr);　　
```
　　

例2：使用静态变量的情况递归遍历文件夹下的所有文件
```php
function getFiles ($dir)
{
    static $arr = array();
    if(is_dir($dir)){
        $hadle = opendir($dir);
        while($file=readdir($hadle))
        {
            if(!in_array($file,array('.','..')) )
            {
                $dirr = $dir."/".$file;
                if(is_dir($dirr))
                {
                    getFiles ($dirr);
                }else{
                    array_push($arr,$dirr);
                }
               
            }
        }
    }
    return $arr;
}
 
$rows= array();
$rows = getFiles ('E:/logs');
print_r($rows);
```
<?php
/** 
 * CRC32 Hash function 
 * @param $str 
 * @return int 
 */ 
function hash32($str) 
{ 
    return crc32($str) >> 16 & 0x7FFFFFFF; 
}

/** 
 * Times33 Hash function 
 * @param $str 
 * @return int 
 */ 
function hash33($str) 
{ 
    $hash = 0; 
    for($i=0; $i<strlen($str); $i++) { 
        $hash += 33 * $hash + ord($str{$i}); 
    } 
    return $hash & 0x7FFFFFFF; 
}


$n = 10;

// Test Case 1 
$stat = array(); 
for($i=0; $i<10000; $i++){ 
    $str = substr(md5(microtime(true)), 0, 8); 
    $p = hash32($str) % $n; 
    if(isset($stat[$p])){ 
        $stat[$p]++; 
    }else{ 
        $stat[$p] = 1; 
    } 
} 
print_r($stat);

// Test Case 2 
$stat = array(); 
for($i=0; $i<10000; $i++){ 
    $str = substr(md5(microtime(true)), 0, 8); 
    $p = hash33($str) % $n; 
    if(isset($stat[$p])){ 
        $stat[$p]++; 
    }else{ 
        $stat[$p] = 1; 
    } 
} 
print_r($stat);





/**
 * 以上有两个测试用例。第一个，用CRC32的方法；第二个是Times33的算法实现。

效果：

结果分布，两种算法不相上下（估计是数据源的问题，md5只有0-f）。也有文章说CRC32的分布更均匀（参考链接：https://yq.aliyun.com/wenji?spm=5176.8246799.blogcont.17.HnoscD）
但耗费时间，CRC32比Times33快将近一倍。

为什么是33?

即是素数（质数），也是奇数。除了33，还有131, 1313, 5381等。PHP内置的Hash函数用的是5381，在“鸟哥”的一篇博文中也有提到。

以上是云栖社区小编为您精心准备的的内容，在云栖社区的博客、问答、公众号、人物、课程等栏目也有的相关内容，欢迎继续使用右上角搜索按钮进行搜索php ， 算法 ， hash Times33 php教程、php下载、php是什么、php开发工具、php文件怎么打开，以便于您获取更多的相关知识。
 */
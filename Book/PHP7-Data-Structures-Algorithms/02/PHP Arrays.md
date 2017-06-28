### PHP数组的分类

索引数组  
关联数组  
多维数组  

#### 用多维数组描述数据结构

#### 用 `SplFixedArray ` 创建固定大小的数组
```
$array = new SplFixedArray(10);
for ($i = 0; $i < 10; $i++)
$array[$i] = $i;
for ($i = 0; $i < 10; $i++)
echo $array[$i] . "\n";
```


### 普通的 php 数组 和 SplFixedArray 数组的性能比较

> 普通的 php 数组

```
$startMemory = memory_get_usage();
$array = range(1,100000);
$endMemory = memory_get_usage();
echo ($endMemory - $startMemory)." bytes";
```

PHP数组每个元素实现的代码占用空间：
相关文章[地址](https://nikic.github.io/2011/12/12/How-big-are-PHP-arrays-really-Hint-BIG.html)

- | 32 bit | 64 bit
-|-|-
zval | 16 bytes | 24 bytes
+ cyclic GC info | 4 bytes | 8 bytes
+ allocation header | 8 bytes | 16 bytes
zval (value) total | 28 bytes | 48 bytes
bucket | 36 bytes | 72 bytes
+ allocation header | 8 bytes | 16 bytes
+ pointer | 4 bytes | 8 bytes
bucket (array element) total | 48 bytes | 96 bytes
Grand total (bucket+zval) | 76 bytes | 144 bytes


$array = Range(1,100000) | 32 bit | 64 bit
-|-|-
PHP 5.6 or below | 7.4 MB | 14 MB
PHP 7 | 3 MB | 4 MB

> SplFixedArray 数组 

```
$items = 100000;
$startMemory = memory_get_usage();
$array = new SplFixedArray($items);
for ($i = 0; $i < $items; $i++) {
$array[$i] = $i;
}$
endMemory = memory_get_usage();
$memoryConsumed = ($endMemory - $startMemory) / (1024*1024);
$memoryConsumed = ceil($memoryConsumed);
echo "memory = {$memoryConsumed} MB\n";
```



100,000 items | Using PHP array | SplFixedArray
-|-|-
PHP 5.6 or below | 14 MB | 6 MB
PHP 7 | 5 MB | 2 MB

##### 普通数组转化为  `SplFixedArray`
```
$array =[1 => 10, 2 => 100, 3 => 1000, 4 => 10000];
$splArray = SplFixedArray::fromArray($array);
print_r($splArray);
```


    $splArray = SplFixedArray::fromArray($array,false);


##### `SplFixedArray` 转化为  普通数组
```
$items = 5;
$array = new SplFixedArray($items);
for ($i = 0; $i < $items; $i++) {
$array[$i] = $i * 10;
} $
newArray = $array->toArray();
print_r($newArray);
```



##### 改变 `SplFixedArray` 大小
```
$items = 5;
$array = new SplFixedArray($items);
for ($i = 0; $i < $items; $i++) {
$array[$i] = $i * 10;
} $
array->setSize(10);
$array[7] = 100;
```



##### 创建多维 `SplFixedArray`

```
$array = new SplFixedArray(100);
for ($i = 0; $i < 100; $i++)
$array[$i] = new SplFixedArray(100);
```


### 理解 哈希表


### 用数组实现结构体


### 用数组实现集合


### PHP数组最佳应用


### PHP数组，是性能杀手？
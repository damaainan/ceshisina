# PHP核心技术与最佳实践之Hash算法

 时间 2015-04-14 21:51:24  

原文[http://blog.csdn.net/u012675743/article/details/45048369][2]


#### PHP核心技术与最佳实践之Hash算法

Hash表又称散列表，通过把关键字Key映射到数组中的一个位置来访问记录，以加快查找速度。这个映射函数称为Hash函数，存放记录的数组称为Hash表。

 1.  Hash  函数

作用是把任意长度的输入，通过Hash算法变换成固定长度的输出，该输出就是Hash值。这种转换是一种压缩映射，也就是Hash值得空间通常远小于输入的空间，不输入可能会散列成相同的输出，而不可能从Hash值来唯一的确定输入值。

一个好的hash函数应该满足以下条件：每个关键字都可以均匀的分布到Hash表任意一个位置，并与其他已被散列到Hash表中的关键字不发生冲突。这是Hash函数最难实现的。

 2.  Hash  算法

 1)  直接取余法

直接取余法比较简单，直接用关键字k除以Hash表的大小m取余数，算法如下：

H(k) = k mod m

例如：Hash表的大小为m=12，所给关键字为k=100，则h(k) = 4.这种算法是一个求余操作，速度比较快。

 2)  乘积取整法

乘积取整法首先使用关键字k乘以一个常数A(0<A<1),并抽取kA的小数部分，然后用Hash表大小m乘以这个值，再取整数部分即可。算法如下：

H(k) = floor (m*(kA mod 1))

其中，kA mod1表示kA的小数部分，floor是取整操作。

当关键字是字符串的时候，就不能用上面的Hash算法。因为字符串是由字符组成，所以可以把字符串所有的字符的ASCII码加起来得到一个整数，然后再按照上面的Hash算法去计算即可。

算法如下：

```php
    function hash($key,$m){
        $strlen= strlen($key);
        $hashval= 0;
        for($i=0;$i< $strlen;$i++){
            $hashval+=ord($key{$I});
        }
        return $hashval % $m;
    }
```
 3)  经典  Hash  算法  Times33

```php
    unsigned int DJBHash(char *str){
        unsignedint hash = 5381;
        while(*str){
            Hash+=(hash <<5)+(*str++);
        }
        return (hash &0x7FFFFFFF)
    }
```
算法思路就是不断乘以33，其效率和随机性都非常好，广泛运用于多个开源项目中，如Apache、Perl和PHP等。

 3.  Hash  表

Hash表的时间复杂度为O(1)，Hash表结构可用图表示：

![][6]

要构造一个Hash表必须创建一个足够大的数组用于存放数据，另外还需要一个Hash函数把关键字Key映射到数组的某个位置。 

Hash表的实现步骤：

1) 创建一个固定大小的数组用于存放数据。

2) 设计Hash函数。

3) 通过Hash函数把关键字映射到数组的某个位置，并在此位置上进行数据存取。

 4.  使用  PHP  实现  Hash  表

首先创建一个HashTable类，有两个属性$buckets和$size。$buckets用于存储数据的数组，$size用于记录$buckets数组大小。然后在构造函数中为$buckets数组分配内存。代码如下：

```php
    <?php
    Class HashTable{
        private $buckets;
        private $size =10;
        public function __construct(){
            $this-> buckets =new SplFixedArray($this->size);
        }
    }
    ?>
```
在构造函数中，为$buckets数组分配了一个大小为10的数组。在这里使用了SPL扩展的SplFixedArray数组，不是一般的数组(array)

这是因为SplFixedArray数组更接近于C语言的数组，而且效率更高。在创建其数组时需要为其提供一个初始化的大小。

 注意： 要使用SplFixedArray数组必须开启SPl扩展。如果没有开启，可以使用一般的数组代替。 

接着为Hash表指定一个Hash函数，为了简单起见，这里使用最简单的Hash算法。也就是上面提到了把字符串的所有字符加起来再取余。代码如下：

```php
    public function hashfunc($key){
        $strlen= strlen($key);
        $hashval= 0;
        for($i=0;$i< $strlen;$i++){
            $hashval+=ord($key{$I});
        }
        return $hashval % $this->size;
    }
```

有了Hash函数，就可以实现插入和查找方法。插入数据时，先通过Hash函数计算关键字所在Hash表的位置，然后把数据保存到此位置即可。代码如下：

    public function insert($key,$val){
        $index= $this -> hashfunc($key);
        $this-> buckets[$index] = $val;
    }

查找数据方法与插入数据方法相似，先通过Hash函数计算关键字所在Hash表的位置，然后返回此位置的数据即可。代码如下：

    public function find($key){
        $index= $this -> hashfunc($key);
        return $this ->buckets[$index];
    }

至此，一个简单的Hash表编写完成，下面测试这个Hash表。代码清单如下：

```php
<?php
$ht= new HashTable();
$ht->insert(‘key1’,’value1’);
$ht->insert(‘key2’,’value2’);
Echo$ht ->find(‘key1’);
Echo$ht ->find(‘key2’);
?>
```
完整代码：#hash.php

```php
<?php
Class HashTable{
  private $buckets;
  private $size=10;
  public function __construct(){
      $this-> buckets =new SplFixedArray($this->size);
  }
  public function hashfunc($key){
      $strlen= strlen($key);
      $hashval= 0;
      For($i=0;$i< $strlen;$i++){
          $hashval+=ord($key{$i});
      }
      return$hashval % $this->size;
  }
  public function insert($key,$val){
      $index= $this -> hashfunc($key);
      $this-> buckets[$index] = $val;
  }
  public function find($key){
      $index= $this -> hashfunc($key);
      return $this ->buckets[$index];
  }
}
$ht = new HashTable();
$ht->insert('key1','value1');
$ht->insert('key2','value2');
echo $ht->find('key1');
echo $ht->find('key2');
```

[2]: http://blog.csdn.net/u012675743/article/details/45048369

[6]: https://img0.tuicool.com/neUzIr.png
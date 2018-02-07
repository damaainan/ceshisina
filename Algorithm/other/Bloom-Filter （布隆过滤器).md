# Bloom-Filter （布隆过滤器)

 时间 2018-02-06 16:00:06  

原文[http://www.jianshu.com/p/16e5f3481366][1]


本文参考：http://blog.csdn.net/hguisu/article/details/7866173（ [海量数据处理算法—Bloom Filter][3] ）； 

我们今天学习一种海量数据的查询过滤算法，就是判断一个元素是否在一个集合中，我们平常的算法，肯定就是遍历比较了，这样对小量数据可以，但对海量数据肯定是不适用的，就算是二叉树其时间复杂度也是O(logn),所以有个叫Burton Bloom 在1970年提出了Bloom Filter算法，其时间复杂度是O(1)。 

但是这个算法不能保证100%正确，所以不适合那些“零错误”的应用场合。我们现在就简单介绍下其思想：

我们用一个哈希算法(Hash函数)将一个集合元素映射到一个二进制位数组（位数组）中的某一位。如果该位已经被置为1，那么表示该元素已经存在。为了减少hash冲突问题,所以引用了多个哈希函数,如果通过其中的一个Hash值得出某元素不在集合中，那么该元素肯定不在集合中。只有在所有的Hash函数告诉我们该元素在集合中时，才能确定该元素存在于集合中。这便是Bloom-Filter的基本思想。

1。首先要有表示集合的数据结构，在Bloom-Filter中，使用的是一个二进制数组（位数组）；

假设Bloom Filter使用一个m比特的数组来保存信息，初始状态时，Bloom Filter是一个包含m位的位数组，每一位都置为0，即Bloom-Filter整个数组的元素都设置为0

![][4]

初始状态的bloomfilter

2。如果我们现在有一个集合S={x1, x2,…,xn},包含n个元素。现在我们需要k个hash函数对n个元素进行计算并映射到我们的位数组中。这个计算k的公式为：

**k = ln2· (m/n)**

m: bit数组的宽度（bit数）

n:集合中元素的个数

k:使用的hash函数的个数

最优的哈希函数的个数 = ln2*(数组大小/元素个数)

当我们往Bloom Filter中增加任意一个元素x时候，我们使用k个哈希函数得到k个哈希值，然后将数组中对应的比特位设置为1。即第i个哈希函数映射的位置hashi(x)就会被置为1（1≤i≤k）。 **注意，如果一个位置多次被置为1，那么只有第一次会起作用，后面几次将没有任何效果。在下图中，k=3，且有两个哈希函数选中同一个位置（从左边数第五位，即第二个“1“处）** 。 

![][5]

x1和x2哈希后有相同的位置

3。现在我们就可以判断一个元素是否在这个集合中了，比如判断y是否在这个集合中，我们只需要对y使用k个哈希函数得到k个哈希值，如果所有hashi(y)的位置都是1（1≤i≤k），即k个位置都被设置为1了，那么我们就认为y是集合中的元素，否则就认为y不是集合中的元素。下图中y1就不是集合中的元素（因为y1有一处指向了“0”位）。y2或者属于这个集合。或者刚好是一个false positive(假阳性)。

![][6]

有个hash计算y1的位置是0,表示不在集合中

显然这 个判断并不保证查找的结果是100%正确的,这个假阳性false positiver比率的计算公式为：

![][7]

False Positive的比率

我们现在用一段代码来看下实现原理,是用php实现的。

```php
    // BloomFilter类
    class BloomFilter {     
    protected $m;  //bit数组的宽度（bit数）
    protected $k;  //使用的hash函数的个数
    protected $n;  //集合中元素的个数
    protected $bitset;  //二进制数组
    //构造函数初始化
    public function __construct($m, $k) {        
      $this->m = $m;      
       $this->k = $k;       
       $this->n = 0;        
      $this->bitset = array_fill(0, $this->m - 1, false);    //初始化二进制数组全部为0
      }  
    //计算最优的hash函数个数：当hash函数个数k=(ln2)*(m/n)时错误率最小 
     public static function getHashCount($m, $n) {        
          return ceil(($m / $n) * log(2));      
     } 
    //添加一个元素，并计算集合长度n
    public function add($key) {     
         if (is_array($key)) {//如果key是一个数组，则回调添加其中的元素
            foreach ($key as $k){        
              $this->add($k);             
             }           
              return;      
           }          
         $this->n++;          
         foreach ($this->getSlots($key) as $slot) {   //将计算得到的位置置为true
               $this->bitset[$slot] = true;      
          }     
     }  
    //判断某个元素是否在集合中
    public function contains($key) {     
         if (is_array($key)) {      //如果key是数组，则判断数组元素是否在集合中 
           foreach ($key as $k) {        
              if ($this>contains($k) == false) {       
                   return false;         
             }           
         }              
          return true;    
         }         
       foreach ($this->getSlots($key) as $slot) {      //判断单个元素是否在集合中
            if ($this>bitset[$slot] == false) {     
                 return false;           
            }       
       }       
         return true;     
      }  
    //对key元素进行k次随机计算并返回其在二进制数组中的位置
     protected function getSlots($key) {  
        $slots = array();       
        $hash = self::getHashCode($key);         
        mt_srand($hash);      //用哈希值做随机数种子，这样既有一定随机性，对一样的值，也有确定性。
        for ($i = 0; $i < $this->k; $i++) {     
             $slots[] = mt_rand(0, $this->m - 1);      
         }            
        return $slots;    
      } 
    /*使用CRC32产生一个32bit（位）的校验值。由于CRC32产生校验值时源数据块的每一bit（位）都会被计算，所以数据块中即使只有一位发生了变化，也会得到不同的CRC32值。*/ 
     protected static function getHashCode($string) {    
          return crc32($string);     
     } 
    $items = array("first item", "second item", "third item");  //定义一个集合items
    //定义一个BloomFilter对象并将集合元素添加进过滤器中.
    $filter = new BloomFilter(100, BloomFilter::getHashCount(100, 3));  
    $filter->add($items);  
    //判断items1中的元素是否在items集合中
    $items1 = array("firsttem", "seconditem", "thirditem"); 
     foreach ($items as $item) { 
      var_dump(($filter->contains($item)));
      }  
    }
```

OK，对Bloom-filter的介绍就到这里，水平有限，更多内容，大家可以再看看其他的文章介绍。

作者：区块链研习社比特币源码研读班，black

[1]: http://www.jianshu.com/p/16e5f3481366
[3]: https://link.jianshu.com?t=http%3A%2F%2Fblog.csdn.net%2Fhguisu%2Farticle%2Fdetails%2F7866173
[4]: ../img/jUv2I3j.png
[5]: ../img/aQbmEze.png
[6]: ../img/aQzmiiJ.png
[7]: ../img/U7J7nmN.png
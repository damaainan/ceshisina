## Bloom Filter布隆过滤器

来源：[http://www.jianshu.com/p/d675fd8ee60d](http://www.jianshu.com/p/d675fd8ee60d)

时间 2018-04-20 19:23:38

 
## 一、简介
 
Bloom Filter是1970年由Bloom提出的，最初广泛用于拼写检查和数据库系统中。近年来，随着计算机和互联网技术的发展，数据集的不断扩张使得 Bloom filter获得了新生，各种新的应用和变种不断涌现。Bloom filter是一个空间效率很高的数据结构，它由一个位数组和一组hash映射函数组成。Bloom filter可以用于检索一个元素是否在一个集合中，它的优点是空间效率和查询时间都远远超过一般的算法，缺点是有一定的误识别率和删除困难。
 
## 二、基本原理
 
查找或判断一个元素是否存在于一个指定集合中，这是计算机科学中一个基本常见问题。通常，我们会采用线性表（数组或链表）、树（二叉树、堆、红黑树、 B+/B-/B*树）等数据结构对所有元素进行存储，并在其上进行排序和查找。这里的查找时间复杂性通常都是O(n)或O(logn)的，如果集合元素非常庞大，不仅查找速度非常慢，对内存空间的需求也非常大。假设有10亿个元素，每个元素节点占用N个字节，则存储这个集合大致需求N GB内存。大家可能很快会想到hashtable，它的查找时间复杂性是O(1)的，可以对元素进行映射索引并定位，但它并没有减少内存需求量。hash 函数的一个问题是可能会发生碰撞，即两个不同的元素产生相同的hash值，在某些场合下需要通过精确比较来解决这个问题。
 
实际上，判断一个元素是否存在于一个指定集合中，可能并不需要把所有集合元素的原始信息都保存下来，我们只需要记住“存在状态”即可，这往往仅仅需要几个bit就可表示。Hash函数可将一个元素映射成一个位数组中一个点，为了降低碰撞率可采用多个hash函数将元素映射成多个点。这样一来，只要看看几个位点是0或1 就可以判断某个元素是否存在于集合当中。这就是Bloom filter的基本思想，不仅可大大缩减内存空间，查找速度非常快。
 
Bloom filter使用一个位数组来记录元素存在状态，并使用一组hash函数(h1, h2, hk...)来对元素进行位映射。插入元素时，对该元素分别进行K次hash计算，并将映射到位数组的相应bit置1。查找元素时，任何其中一个映射位为 0则表示该元素不存在于集合当中，只要当所有映射位均为1时才表示该元素有可能存在于集合当中。换句话说，如果Bloom filter判断一个元素不在集合中，那肯定就不存在；而如果判断存在，则不一定存在，虽然这个概率很低。这个问题是由hash函数会发生碰撞的特性所决定的，它造成了Bloom filter的错误率产生。这个错误率可通过改变Bloom filter数组大小，或改变hash函数个数进行调节控制。由此可见，Bloom filter也不是完美的，它的高效也是有一定代价的，它通过容忍一定的错误率发生换取了存储空间的极大节省。另外，Bloom filter不能支持元素的删除操作，如果删除会影响其他元素的存在性正确判断。因此，Bloom Filter不适合那些“零错误”的应用场合，但是这个错误是正向的（false positive），不会发生反向的错误（false negative），判断元素不存在集合中是绝对正确的。Bloom filter使用可控的错误率获得了空间的极大节省和极快的查找性能，得到广泛应用也是理所当然的。
 
## 三、优缺点
 
与其它数据结构相比较，Bloom filter的最大优点是空间效率和查找时间复杂性，它的存储空间和插入/查询时间都是常数。Hash函数之间没有相关性，可以方便地由硬件并行实现。Bloom filter不需要存储元素本身，在某些对保密要求非常严格的场合有优势。另外，Bloom filter一般都可以表示大数据集的全集，而其它任何数据结构都难以做到。
 
Bloom filter的缺点和优点一样显著，首先就是错误率。随着插入的元素数量增加，错误率也随之增加。虽然可以通过增加位数组大小或hash函数个数来降低错误率，但同时也时影响空间效率和查找性能，而且这个错误率是无法从根本上消除的。这使得要求“零错误”的场合无法应用Bloom filter。其次，一般情况下不能从Bloom filter中删除元素。一方面是我们不能保证删除的元素一定存在Bloom filter中，另一方面是不能保证安全地删除元素，可能会对其他元素产生影响，究其原因还是hash函数可能产生的碰撞造成的。计数Bloom filter可以在一定程度上支持元素删除，但保证安全地删除元素并非如此简单，它也不能从根本上解决这个问题，而且计数器回绕也会有问题。这两方面也是目前Bloom filter的重点研究方向，有不少工作，使得出现了很多Bloom filter的变种。
 
## 四、简单例子
 
下面是一个简单的 Bloom filter 结构，开始时集合内没有元素
 
  
   
   
![][0]
 
  
 
1.jpg
 
 
 
当来了一个元素 a，进行判断，这里哈希函数有两个，计算出对应的比特位上为 0 ，即是 a 不在集合内，将 a 添加进去：
 
  
   
   
![][1]
 
  
 
2.jpg
 
 
 
之后的元素，要判断是不是在集合内，也是同 a 一样的方法，只有对元素哈希后对应位置上都是 1 才认为这个元素在集合内（虽然这样可能会误判）：
 
  
   
   
![][2]
 
  
 
3.jpg
 
 
 
随着元素的插入，Bloom filter 中修改的值变多，出现误判的几率也随之变大，当新来一个元素时，满足其在集合内的条件，即所有对应位都是 1 ，这样就可能有两种情况，一是这个元素就在集合内，没有发生误判；还有一种情况就是发生误判，出现了哈希碰撞，这个元素本不在集合内。
 
  
   
   
![][3]
 
  
 
4.jpg
 
 
 
## 五、Java代码实现
 
```java
import java.util.BitSet;
public class  SimpleBloomFilter {
    private static final  int  DEFAULT_SIZE = 2 << 24 ;
    private static final  int [] seeds = new  int []{5, 7, 11 , 13 , 31 , 37 , 61};
    private  BitSet bits = new  BitSet(DEFAULT_SIZE);
    private  SimpleHash[] func = new  SimpleHash[seeds.length];

    public static void  main(String[] args) {
        String value = "zheng" ;
        SimpleBloomFilter filter = new  SimpleBloomFilter();
        System.out.println(filter.contains(value));
        filter.add(value);
        System.out.println(filter.contains(value));
    }
    
    public  SimpleBloomFilter() {
        for( int  i= 0 ; i < seeds.length; i ++ ) {
            func[i] = new  SimpleHash(DEFAULT_SIZE, seeds[i]);
        }
    }
    
    public void  add(String value) {
        for(SimpleHash f : func) {
            bits.set(f.hash(value),  true );
        }
    }
    
    public boolean contains(String value) {
        if(value == null ) {
            return false ;
        }
        
        boolean ret = true ;
        for(SimpleHash f : func) {
            ret = ret && bits.get(f.hash(value));
        }
        
        return  ret;
    }


    public static class SimpleHash {
        private int  cap;
        private int  seed;

        public  SimpleHash(int cap, int seed) {
            this.cap = cap;
            this.seed =seed;
        }

        public int hash(String value) {
            int  result = 0 ;
            int  len = value.length();

            for  (int i = 0 ; i < len; i++ ) {
                result = seed * result + value.charAt(i);
            }

            return (cap - 1 ) & result;
        }
    }
}
```
 
运行结果：
 
```
false
true
```
 
## 六、参考
 
（1） [https://blog.csdn.net/liuaigui/article/details/6602683][5]
 
（2） [https://www.cnblogs.com/arkia123/archive/2012/10/30/2743850.html][6]
 
  
TopCoder & Codeforces & AtCoder交流QQ群：648202993
 
更多内容请关注微信公众号
 
   
    
    
![][4]
 
   
 
wechat_public.jpg
 
  
 
 
 


[5]: https://link.jianshu.com?t=https%3A%2F%2Fblog.csdn.net%2Fliuaigui%2Farticle%2Fdetails%2F6602683
[6]: https://link.jianshu.com?t=https%3A%2F%2Fwww.cnblogs.com%2Farkia123%2Farchive%2F2012%2F10%2F30%2F2743850.html
[0]: ../img/MJNrAbB.png 
[1]: ../img/ArqUJ3Q.png 
[2]: ../img/MrMBni7.png 
[3]: ../img/Bze2E32.png 
[4]: ../img/Zj2eueR.jpg 
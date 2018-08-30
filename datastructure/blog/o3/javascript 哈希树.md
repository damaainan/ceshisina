## javascript 哈希树

来源：[https://segmentfault.com/a/1190000013010524](https://segmentfault.com/a/1190000013010524)


## 哈希树的理论基础

质数分辨定理

n个不同的质数可以“分辨”的连续整数的个数和他们的乘积相等。“分辨”就是指这些连续的整数不可能有完全相同的余数序列。

（这个定理的证明详见：[http://wenku.baidu.com/view/1...][1]）

例如：

从2起的连续质数，连续10个质数就可以分辨大约M(10) =2 3 5 7 11 13 17 19 23*29= 6464693230 个数，已经超过计算机中常用整数（32bit）的表达范围。连续100个质数就可以分辨大约M(100) = 4.711930 乘以10的219次方。

而按照目前的CPU水平，100次取余的整数除法操作几乎不算什么难事。在实际应用中，整体的操作速度往往取决于节点将关键字装载内存的次数和时间。一般来说，装载的时间是由关键字的大小和硬件来决定的；在相同类型关键字和相同硬件条件下，实际的整体操作时间就主要取决于装载的次数。他们之间是一个成正比的关系。

## 程序实现

我们选择质数分辨算法来建立一棵哈希树。

选择从2开始的连续质数来建立一个十层的哈希树。第一层结点为根结点，根结点下有2个结点；第二层的每个结点下有3个结点；依此类推，即每层结点的子节点数目为连续的质数。到第十层，每个结点下有29个结点。

除了根结点，不放东西，其他都动态生成一个对象，添加上key, value, occupied三个属性。occupied是用于表示这个结点是否已经被删除，因为我并不真正删除节点，避免递归处理下面的子节点。在插入过程，已经发现有对象点着，并且occupied不为false，那么就取下一个质数重新计算，以当前对象为起点进行插入操作。

![][0]

哈希树主要有三个方法 insert, search与remove，它们的结构都差不多。

```js

class HashTree{
  constructor(){
      this.root = {}
  }
  insert(key, value){
       var primes = [2,3,5,7,11,13,17,19,23,29], cur = this.root
       for(var i = 0; i < 10; i++){
          var prime = primes[i]
          var a = key % prime
          var obj = cur[a]
          if(!obj){ //插入成功
              cur[a] = {
                  key : key,
                  value: value,
                  occupied: true
              }
              break
          }else if(!obj.occupied){
              obj.key = key
              obj.value = value
              obj.occupied = true
              break
          }else{
              cur = obj
          }
       }
  }
  search(key){
       var primes = [2,3,5,7,11,13,17,19,23,29], cur = this.root
       for(var i = 0; i < 10; i++){
          var prime = primes[i]
          var a = key % prime
          var obj = cur[a]
          if(obj){
              if(obj.key === key){
                  console.log(key)
                  return obj.value
              }else{
                  cur = obj
              }
          }else{
              return null
          }
       }
  }
  remove(key){
       var primes = [2,3,5,7,11,13,17,19,23,29], cur = this.root
       for(var i = 0; i < 10; i++){
          var prime = primes[i]
          var a = key % prime
          var obj = cur[a]
          if(obj){
              if(obj.key === key){
                  obj.occupied = false
                  break
              }else{
                  cur = obj
              }
          }else{
              break
          }
       }
  }
}
//自己在chrome控制台下查看
var tree = new HashTree
tree.insert(7807, "a")
tree.insert(249, "b")
tree.insert(1073, "c")
tree.insert(658, "d")
tree.insert(930, "e")
tree.insert(2272, "f")
tree.insert(8544, "g")
tree.insert(1878, "h")
tree.insert(8923, "i")
tree.insert(8709, "j")
console.log(tree)
console.log(tree.search(1878))

```

## 优点

1、结构简单

从哈希树的结构来说，非常的简单。每层节点的子节点个数为连续的质数。子节点可以随时创建。因此哈希树的结构是动态的，也不像某些哈希算法那样需要长时间的初始化过程。哈希树也没有必要为不存在的关键字提前分配空间。

需要注意的是哈希树是一个单向增加的结构，即随着所需要存储的数据量增加而增大。即使数据量减少到原来的数量，但是哈希树的总节点数不会减少。这样做的目的是为了避免结构的调整带来的额外消耗。

2、查找迅速

从算法过程我们可以看出，对于整数，哈希树层级最多能增加到10。因此最多只需要十次取余和比较操作，就可以知道这个对象是否存在。这个在算法逻辑上决定了哈希树的优越性。

一般的树状结构，往往随着层次和层次中节点数的增加而导致更多的比较操作。操作次数可以说无法准确确定上限。而哈希树的查找次数和元素个数没有关系。如果元素的连续关键字总个数在计算机的整数（32bit）所能表达的最大范围内，那么比较次数就最多不会超过10次，通常低于这个数值。

3、结构不变

从删除算法中可以看出，哈希树在删除的时候，并不做任何结构调整。这个也是它的一个非常好的优点。常规树结构在增加元素和删除元素的时候都要做一定的结构调整，否则他们将可能退化为链表结构，而导致查找效率的降低。哈希树采取的是一种“见缝插针”的算法，从来不用担心退化的问题，也不必为优化结构而采取额外的操作，因此大大节约了操作时间。

## 缺点

1、非排序性

哈希树不支持排序，没有顺序特性。如果在此基础上不做任何改进的话并试图通过遍历来实现排序，那么操作效率将远远低于其他类型的数据结构。

[1]: http://wenku.baidu.com/view/16b2c7abd1f34693daef3e58.html
[0]: ./img/1460000013010527.png
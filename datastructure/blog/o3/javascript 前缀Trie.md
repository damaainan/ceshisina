## javascript 前缀Trie

来源：[https://segmentfault.com/a/1190000013018855](https://segmentfault.com/a/1190000013018855)


## 引子

前缀Trie, 又叫字符Tire, trie来自单词retrieval, 一开始念作tree，后来改念try， 毕竟它与树是不一样的东西。网上许多文章都搞混了trie与树。 trie是通过”边“来储存字符的一种树状结构，所谓边就是节点与节点间的连接。trie每条边只能存放一个字符。

![][0]

它与hash树很相似，或者说它是哈希树的变种，哈希树是用边来存放一个整数（可以是一位数或两位数）。前树Tire与哈希树都是多叉树，换言之，父节点有N个子节点。

前缀Tire主要用于字符串的快速检索，查询效率比哈希表高。

前缀Trie的核心思想是空间换时间。利用字符串的公共前缀来降低查询时间的开销以达到提高效率的目的。

前缀Trie树也有它的缺点, 假定我们只对字母与数字进行处理，那么每个节点至少有52＋10个子节点。为了节省内存，我们可以用链表或数组。在JS中我们直接用数组，因为JS的数组是动态的，自带优化。

## 基本性质

* 根节点不包含字符，除根节点外的每一个子节点都包含一个字符
* 从根节点到某一节点。路径上经过的字符连接起来，就是该节点对应的字符串
* 每个节点的所有子节点包含的字符都不相同

## 程序实现

```js

// by 司徒正美
class Trie {
  constructor() {
    this.root = new TrieNode();
  }
  isValid(str) {
    return /^[a-z1-9]+$/i.test(str);
  }
  insert(word) {
    // addWord
    if (this.isValid(word)) {
      var cur = this.root;
      for (var i = 0; i < word.length; i++) {
        var c = word.charCodeAt(i);
        c -= 48; //减少”0“的charCode
        var node = cur.edges[c];
        if (node == null) {
          var node = (cur.edges[c] = new TrieNode());
          node.value = word.charAt(i);
          node.numPass = 1; //有N个字符串经过它
        } else {
          node.numPass++;
        }
        cur = node;
      }
      cur.isEnd = true; //樯记有字符串到此节点已经结束
      cur.numEnd++; //这个字符串重复次数

      return true;
    } else {
      return false;
    }
  }
  remove(word){
      if (this.isValid(word)) {
          var cur = this.root;
          var array = [], n = word.length
          for (var i = 0; i < n; i++) {
              var c = word.charCodeAt(i);
              c = this.getIndex(c)
              var node = cur.edges[c];
              if(node){
                  array.push(node)
                  cur = node
              }else{
                  return false
              }

          }
          if(array.length === n){
              array.forEach(function(){
                  el.numPass--
              })
              cur.numEnd --
              if( cur.numEnd == 0){
                  cur.isEnd = false
              } 
          }
      }else{
          return false
      }
  }
  preTraversal(cb){//先序遍历
        function preTraversalImpl(root, str, cb){  
            cb(root, str);
            for(let i = 0,n = root.edges.length; i < n; i ++){
                let node = root.edges[i];
                if(node){
                    preTraversalImpl(node, str + node.value, cb);
                }
            }
        }  
        preTraversalImpl(this.root, "", cb);
   }
  // 在字典树中查找是否存在某字符串为前缀开头的字符串(包括前缀字符串本身)
  isContainPrefix(word) {
    if (this.isValid(word)) {
      var cur = this.root;
      for (var i = 0; i < word.length; i++) {
        var c = word.charCodeAt(i);
        c -= 48; //减少”0“的charCode
        if (cur.edges[c]) {
          cur = cur.edges[c];
        } else {
          return false;
        }
      }
      return true;
    } else {
      return false;
    }
  }
  isContainWord(str) {
    // 在字典树中查找是否存在某字符串(不为前缀)
    if (this.isValid(word)) {
      var cur = this.root;
      for (var i = 0; i < word.length; i++) {
        var c = word.charCodeAt(i);
        c -= 48; //减少”0“的charCode
        if (cur.edges[c]) {
          cur = cur.edges[c];
        } else {
          return false;
        }
      }
      return cur.isEnd;
    } else {
      return false;
    }
  }
  countPrefix(word) {
    // 统计以指定字符串为前缀的字符串数量
    if (this.isValid(word)) {
      var cur = this.root;
      for (var i = 0; i < word.length; i++) {
        var c = word.charCodeAt(i);
        c -= 48; //减少”0“的charCode
        if (cur.edges[c]) {
          cur = cur.edges[c];
        } else {
          return 0;
        }
      }
      return cur.numPass;
    } else {
      return 0;
    }
  }
  countWord(word) {
    // 统计某字符串出现的次数方法
    if (this.isValid(word)) {
      var cur = this.root;
      for (var i = 0; i < word.length; i++) {
        var c = word.charCodeAt(i);
        c -= 48; //减少”0“的charCode
        if (cur.edges[c]) {
          cur = cur.edges[c];
        } else {
          return 0;
        }
      }
      return cur.numEnd;
    } else {
      return 0;
    }
  }
}

class TrieNode {
  constructor() {
    this.numPass = 0;//有多少个单词经过这节点
    this.numEnd = 0; //有多少个单词就此结束
    this.edges = [];
    this.value = ""; //value为单个字符
    this.isEnd = false;
  }
}

```

我们重点看一下TrieNode与Trie的insert方法。 由于字典树是主要用在词频统计，因此它的节点属性比较多, 包含了numPass, numEnd但非常重要的属性。

insert方法是用于插入重词，在开始之前，我们必须判定单词是否合法，不能出现 特殊字符与空白。在插入时是打散了一个个字符放入每个节点中。每经过一个节点都要修改numPass。

## 优化

现在我们每个方法中，都有一个`c=-48`的操作，其实数字与大写字母与小写字母间其实还有其他字符的，这样会造成无谓的空间的浪费

```js

// by 司徒正美
getIndex(c){
      if(c < 58){//48-57
          return c - 48
      }else if(c < 91){//65-90
          return c - 65 + 11
      }else {//> 97 
          return c - 97 + 26+ 11
      }
  }

```

然后相关方法将`c-= 48`改成`c = this.getIndex(c)`即可

## 测试

```js

var trie = new Trie();  
    trie.insert("I");  
    trie.insert("Love");  
    trie.insert("China");  
    trie.insert("China");  
    trie.insert("China");  
    trie.insert("China");  
    trie.insert("China");  
    trie.insert("xiaoliang");  
    trie.insert("xiaoliang");  
    trie.insert("man");  
    trie.insert("handsome");  
    trie.insert("love");  
    trie.insert("Chinaha");  
    trie.insert("her");  
    trie.insert("know");  
    var map = {}
    trie.preTraversal(function(node, str){
       if(node.isEnd){
          map[str] = node.numEnd
       }
    })
    for(var i in map){
        console.log(i+" 出现了"+ map[i]+" 次")
    }
    console.log("包含Chin（包括本身）前缀的单词及出现次数：");  
    //console.log("China")
    var map = {}
    trie.preTraversal(function(node, str){
        if(str.indexOf("Chin") === 0 && node.isEnd){
           map[str] = node.numEnd
        }
     })
    for(var i in map){
        console.log(i+" 出现了"+ map[i]+" 次")
    }

```

![][1]

## 前缀Trie和其它数据结构的比较

### 前缀Trie与二叉搜索树

二叉搜索树应该是我们最早接触的树结构了，我们知道，数据规模为n时，二叉搜索树插入、查找、删除操作的时间复杂度通常只有`O(log n)`，最坏情况下整棵树所有的节点都只有一个子节点，退变成一个线性表，此时插入、查找、删除操作的时间复杂度是`O(n)`。

通常情况下，前缀Trie的高度n要远大于搜索字符串的长度m，故查找操作的时间复杂度通常为`O(m)`，最坏情况下的时间复杂度才为`O(n)`。很容易看出，前缀Trie最坏情况下的查找也快过二叉搜索树。

文中前缀Trie都是拿字符串举例的，其实它本身对key的适宜性是有严格要求的，如果key是浮点数的话，就可能导致整个前缀Trie巨长无比，节点可读性也非常差，这种情况下是不适宜用前缀Trie来保存数据的；而二叉搜索树就不存在这个问题。

### 前缀Trie与Hash表

   考虑一下Hash冲突的问题。Hash表通常我们说它的复杂度是`O(1)`，其实严格说起来这是接近完美的Hash表的复杂度，另外还需要考虑到hash函数本身需要遍历搜索字符串，复杂度是`O(m)`。在不同键被映射到“同一个位置”（考虑closed hashing，这“同一个位置”可以由一个普通链表来取代）的时候，需要进行查找的复杂度取决于这“同一个位置”下节点的数目，因此，在最坏情况下，Hash表也是可以成为一张单向链表的。

   前缀Trie可以比较方便地按照key的字母序来排序（整棵树先序遍历一次就好了），这跟绝大多数Hash表是不同的（Hash表一般对于不同的key来说是无序的）。

   在较理想的情况下，Hash表可以以O(1)的速度迅速命中目标，如果这张表非常大，需要放到磁盘上的话，Hash表的查找访问在理想情况下只需要一次即可；但是Trie树访问磁盘的数目需要等于节点深度。

   很多时候前缀Trie比Hash表需要更多的空间，我们考虑这种一个节点存放一个字符的情况的话，在保存一个字符串的时候，没有办法把它保存成一个单独的块。前缀Trie的节点压缩可以明显缓解这个问题，后面会讲到。

## 前缀Trie树的改进

### 按位Trie树（Bitwise Trie）

原理上和普通Trie树差不多，只不过普通Trie树存储的最小单位是字符，但是Bitwise Trie存放的是位而已。位数据的存取由CPU指令一次直接实现，对于二进制数据，它理论上要比普通Trie树快。

### 节点压缩

分支压缩：将一些连结线与节点进行合并，比如i-n-n可以合并成inn。这种压缩后的Tire被唤作前缀压缩Tire，或直接叫前缀树， 字典树。

![][2]

节点映射表：这种方式也是在前缀Trie的节点可能已经几乎完全确定的情况下采用的，针对前缀Trie中节点的每一个状态，如果状态总数重复很多的话，通过一个元素为数字的多维数组（比如Triple Array Trie）来表示，这样存储Trie树本身的空间开销会小一些，虽说引入了一张额外的映射表。

## 前缀Trie的应用

前缀树还是很好理解，它的应用也是非常广的。

（1）字符串的快速检索

字典树的查询时间复杂度是`O(logL)`，L是字符串的长度。所以效率还是比较高的。字典树的效率比hash表高。

（2）字符串排序

从上图我们很容易看出单词是排序的，先遍历字母序在前面。减少了没必要的公共子串。

（3）最长公共前缀

inn和int的最长公共前缀是in，遍历字典树到字母n时，此时这些单词的公共前缀是in。

（4）自动匹配前缀显示后缀

我们使用辞典或者是搜索引擎的时候，输入appl，后面会自动显示一堆前缀是appl的东东吧。那么有可能是通过前缀Trie实现的，前面也说了前缀Trie可以找到公共前缀，我们只需要把剩余的后缀遍历显示出来即可。

## 参考链接

* [http://blog.csdn.net/abcd_d_/...][3]
* [http://blog.csdn.net/u0139490...][4]

[3]: http://blog.csdn.net/abcd_d_/article/details/40116485
[4]: http://blog.csdn.net/u013949069/article/details/78056102
[0]: ./img/bV2MZW.png
[1]: ./img/bV2MWK.png
[2]: ./img/bV2SCr.png
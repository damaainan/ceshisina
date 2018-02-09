## javascript 后缀数组

来源：[https://segmentfault.com/a/1190000012999754](https://segmentfault.com/a/1190000012999754)

后缀数组是处理字符串的利器, 它本身涉及许多辅助概念.
## 基本概念

### 1.1子串

表示字符串的某一小段, 如awbcdewg拥有 awbc， awbcd， awbcde等子串。

### 1.2后缀

后缀是字符串从某个位置起到达末尾的一种特殊子串。后缀可以等于自身，相等于从一个字符开始. 假令我们设计一个取后缀的函数, 它可以这样实现:

```js
function suffix(str, i ){
    if(i >= 0 || i <= str.length-1){
       return str.slice(i)
    }
    throw "i越界了"
}
```

后缀必须包含最后一个字符.

字符串rubylouvre，它的后缀就包含rubylouvre、ubylouvre、bylouvre、 ylouvre、louvre、ouvre，uvre、vre、 re、 e 它们都必须包含最后一个字符e。

### 1.3 字典排序

字符串默认的比较算法, "aa" < "ab" 返回true而不是返回false就是依靠这个标准进行. 

首先从左到右, 各自取得第一个字符, "a"与"a", 如果相同, 则比较各自的第二个字符. 否则, 比较其charCode值.  如i 和 b比, i的charCode为105, b的charCode为98, b肯定比i小, 那么不用再比较.

如果其中之一是另一个的前缀，则短的那个排前面：aaa < aaab

### 1.4后缀数组

后端数组就是某个字符串的所有后缀按照字典顺序进行排序后得到的位置数组. 如字符串ADCEFD, 当i从0到5递增时,我们通过上面的suffix函数得到其所有 后缀.
| index | A | D | C | E | F | D |
| 0 | A | D | C | E | F | D |
| 1 |   | D | C | E | F | D |
| 2 |   |   | C | E | F | D |
| 3 |   |   |   | E | F | D |
| 4 |   |   |   |   | F | D |
| 5 |   |   |   |   |   | D |


按字典排序后
| index | A | D | C | E | F | D |
| 0 | A | D | C | E | F | D |
| 2 |   |   | C | E | F | D |
| 5 |   |   |   |   |   | D |
| 1 |   | D | C | E | F | D |
| 3 |   |   |   | E | F | D |
| 4 |   |   |   |   | F | D |


这个[0,2,5,1,3,4]就是字符串的后缀数组.

```js
// by 司徒正美
var str = "ADCEFD", arr = []
function spawnSuffix(str, arr){
     if(str){
        arr.push(str)
        spawnSuffix(str.slice(1), arr)
     }
 }
spawnSuffix(str, arr)
console.log(arr)
//["ADCEFD", "DCEFD", "CEFD", "EFD", "FD", "D"]
// 对abadefg的所有后缀子串数组进行加工
var sa = arr.map(function(str, i){
  return {el: str, index: i}
}).sort(function(a, b){
 return a.el > b.el
}).map(function(obj){
 return obj.index  //可以加1，也可以不加，看你的习惯
})
console.log(sa)// [0, 2, 5, 1, 3, 4]
```
## 倍增算法

上面我们通过非常朴素的方式,逐个取得它的所有后缀,然后通过语言本身的sort方法进行字典排序.这个sort在不同的宿主环境中,内部采取的排序算法都不一样,就是一个黑箱.

整个过程大家可以参考罗穗骞的论文，但由于语言的差异，我看不  懂他在写什么，直接对着它的那张图搞出来了。


![][0]


```js
// by 司徒正美
function getSuffix(str) {
    var len = str.length, 
        max = str.charCodeAt(0), 
        min = max,
        xbuckets = [],
        sa = [],
        rank = [];
    // 将用户传入的字符串全部转换为charCode值，并求出最大最小值
    for (let i = 0; i < len; i++) {
        let c = str.charCodeAt(i);
        rank[i] = c;
        max = Math.max(max, c);
        min = Math.min(min, c);
    }
    //我们要对rank进行计数排序，但是它们太大，都是90－128左右，
    // 这样我们要创建上百个桶
    //我们通过减去最小值，来缩小规模, 现在只需2－10个桶就够了。

    //这些新得到的数字其实在原论文中构成一个叫 x 的数组
    //但是我们并没有这样用，而是让它作为rank对象数组的一个属性
    rank.forEach(function(el, i){
        rank[i] = {x: el - min + 1 };
    });
  
    var hasDuplicate = true, k = 0;
    while(hasDuplicate){
        //重置数据
        hasDuplicate = false;
        xbuckets.length = 0;
        //k倍增，隔空拼凑y数组
        //y作为基数排序的第二个关键字
        var d = 1 << k; k ++;
        rank.forEach(function(el, i){
            el.y = rank[i+ d] ? rank[i+ d].x: 0;
        });
        //根据关键字x，进行基数排序
        rank.forEach(function(el){
            var index = el.x;
            if(!xbuckets[index]){
                xbuckets[index] = [el];
            }else{
                xbuckets[index].push(el);
            }
        });
        //对每个桶内xbucket根据y进行排序
        var newIndex = 1, last = {};
        xbuckets.forEach(function(bucket){
            if(bucket){
                let cache = {};
                bucket.sort(function(a, b){
                    return a.y - b.y;
                }).forEach(function(el ){
                    //重写x
                    if(el.y !== last.y){
                        el.x = newIndex++;
                        cache[el.y] = el.x;
                    }else{
                        hasDuplicate = true;
                        el.x = cache[el.y];
                    }
                    last = el;
                });
            }
        });
    }
    //rank是从1开始的，因此这里面要减1
    rank = rank.map(function(el, i ){
        sa[el.x - 1] = i;
        return el.x;
    });
    console.log("rank数组", rank);
    console.log("后缀数组", sa);
    return sa;
}

var ret = getSuffix("aabaaaab"); 
//ret:  3, 4, 5, 0, 6, 1, 7, 2 

```

但这依赖于原生的sort, 我们可以将sort改成计数排序。

```js
// by 司徒正美
function getSuffix(str) {
    var len = str.length, 
        max = str.charCodeAt(0), 
        min = max,
        xbuckets = [],
        sa = [],
        rank = [];
    // 字符串转charCode
    for (let i = 0; i < len; i++) {
        let c = str.charCodeAt(i);
        rank[i] = c;
        max = Math.max(max, c);
        min = Math.min(min, c);
    }
    //压缩charCode值
    rank.forEach(function(el, i){
        rank[i] = {x: el - min + 1 };
    });
    var hasDuplicate = true, k = 0;
    while(hasDuplicate){
        //重置数据
        hasDuplicate = false;
        xbuckets.length = 0;
        //倍增，目的是求关键字y
        var d = 1 << k; k ++;

        rank.forEach(function(el, i){
            //根据关键字x，进行基数排序，并同时计算关键字y
            el.y = rank[i+ d] ? rank[i+ d].x: 0;
           
            var index = el.x;
            if(!xbuckets[index]){
                xbuckets[index] = [el];
            }else{
                xbuckets[index].push(el);
            }
        });

        var newIndex = 1, last = {};
        xbuckets.forEach(function(bucket){
            if(bucket){
                //使用计数排序对每个桶再进行排序
                var cache = {};
                var yxbuckets = [];
                bucket.forEach(function(el){
                    var index = el.y;
                    if(!yxbuckets[index]){
                        yxbuckets[index] = [el];
                    }else{
                        yxbuckets[index].push(el);
                    }
                });
                var j = 0;
                yxbuckets.forEach(function(ybucket){
                    if(ybucket){
                        ybucket.forEach(function(el){
                            if(el.y !== last.y){
                                el.x = newIndex++;
                                cache[el.y] = el.x;
                            }else{
                                hasDuplicate = true;
                                el.x = cache[el.y];
                            }
                            bucket[j++] = el; //这里可以不要
                            last = el;
                        });
                    }
                });
            }
        });
     
    }
    //rank是从1开始的，因此这里面要减1
    rank = rank.map(function(el, i ){
        sa[el.x - 1] = i;
        return el.x;
    });
    console.log("rank数组", rank);
    console.log("后缀数组", sa);
    return sa;
}
var a = getSuffix("aabaaaab"); 
```
## height数组

那么如何计算height？我们定义h[i]=height[rank[i]]，也就是Suffix[i]和它前一名的最长公共前缀，那么很明显有h[i]>=h[i-1]-1。因为h[i-1]是Suffix[i-1]和它前一名的最长公共前缀，设为Suffix[k]，那么Suffix[i]和Suffix[k+1] 的最长公共前缀为h[i-1]-1，所以h[i]至少是h[i-1]-1。所以我们可以按照求h[1],h[2],h[3] 顺序计算所有的height。代码如下

```js
//by司徒正美
function getHeight(str, sa){
    var n = str.length, k = 0, rank = [], height = []
    for(var i = 1;i<=n;i++) {
        rank[sa[i]]=i;
    }
    for(var i=0;i<n;i++){
         if(k) k--;
         var j=sa[rank[i]-1];
         while(i+k < n && j+k<n &&rank[i+k]==rank[j+k]) {
            k++;
         }
         height[rank[i]]=k;
    }
    return height
}
```
## DC3算法

DC3算法(Difference Cover mod 3)是J. Kärkkäinen和P. Sanders在2003年发表的论文 "Simple Linear Work Suffix Array Construction"中描述的线性时间内构造后缀数组的算法。详见下文

[http://spencer-carroll.com/th...][1]

太难了，略过。 此外还有其他构造算法，如SA-IS：

[https://zhuanlan.zhihu.com/p/...][2] 

我应该是搞错学习顺序了，应该先学hash树再学trie树再学压缩树再学后缀树再学后缀自动机。即便我头脑这么好使，跨度这么大，还是碰得一脸灰的。
## 参考链接


* [http://blog.csdn.net/sojisub_...][3]
* [http://blog.csdn.net/yxuanwke...][4]
* [https://wenku.baidu.com/view/...][5] (PPT)


[1]: http://spencer-carroll.com/the-dc3-algorithm-made-simple/
[2]: https://zhuanlan.zhihu.com/p/28331415
[3]: http://blog.csdn.net/sojisub__0173/article/details/50286319
[4]: http://blog.csdn.net/yxuanwkeith/article/details/50636898
[5]: https://wenku.baidu.com/view/f3f9a1ba33d4b14e852468dc.html
[0]: ../img/1460000012999757.png
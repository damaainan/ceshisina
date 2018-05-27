## 字符串匹配的KMP算法+PHP实现

来源：[http://www.jianshu.com/p/8f96cde5d671](http://www.jianshu.com/p/8f96cde5d671)

时间 2018-05-12 17:38:37

 
#### 1. 前言
 
看了阮一峰的字符串匹配的KMP算法，写得很好，推荐看看。
 
不过我想自己写个例子描述一下这个算法，顺便写个PHP实现，于是有了这篇博文。
 
#### 2. 概述 [来自维基百科]
 
 
* 字符串搜索算法
字符串搜索算法（String searching algorithms）又称字符串比对算法（string matching algorithms）是一种搜索算法，是字符串算法中的一类，用以试图在一长字符串或文章中，找出其是否包含某一个或多个字符串，以及其位置。
  
* 部分算法比较 [克努斯-莫里斯-普拉特算法即KMP算法]
令 m 为模式的长度， n 为要搜索的字符串长度， k为字母表长度。

[][0]
 
  
 
 
#### 3. 算法解读
 
#### 3.1 例子
 
给定字符串：
 
``` 
RXYZAHXFXYZAXYZAXYZ
```
 
需要搜索的字符串是：
 
``` 
XYZAXY
```
 
朴素匹配的步骤很简单，先对比两个字符串的第一个字母，如果不一样，对比给定字符串的下一个字母，如果一样，那么对比两个字符串的第二个字母，以此类推。这种算法的缺点是效率低，因为做了很多无用工，比如，我在匹对 RXYZAHXFXYZAXYZAXYZ和XYZAXY字符串的时候，我在匹对给定字符串的XYZAH和要搜索的字符串XYZAXY这一步，前四个字母的已经匹对成功的，这四个字母是
 
``` 
XYZA
```
 
这个字符串的前缀和后缀
 
``` 
前缀:
[X,XY,XYZ]
后缀:
[YZA,ZA,A]
```
 
没有共同的元素，这表示在这个长度内，不会有能和要搜索字符串的前缀匹配的部分，那么就可以直接跳过这一部分字符串的对比。这就是KMP算法的核心。所以，我们首先要对要搜索的字符串生成一个匹配表。
 
#### 3.2 部分匹配表
 
 
* #### 如何给字符串生成一个匹配表？
  
 
 
``` 
XYZAXY 
- X   前缀和后缀都是空，所以共有元素的长度0；
- XY 前缀[X]，后缀[Y], 共有元素的长度0；
- XYZ 前缀[X,XY]，后缀[Y,YZ]，共有元素的长度0；
- XYZA 前缀[X,XY,XYZ]，后缀[YZA,ZA,A]，共有元素的长度0；
- XYZAX 前缀[X,XY,XYZ,XYZA]，后缀[YZAX,ZAX,AX,X]，共有元素X的长度1；
- XYZAXY 前缀[X,XY,XYZ,XYZA,XYZAX]，后缀[YZAXY,ZAXY,AXY,XY,Y]，共有元素XY的长度2；
```
 
 
* #### 因此生成的匹配表：
  
 
 
  
   
   
![][1]
 

#### 3.3 匹配
 
好了，现在来看一下怎么使用上一步骤的表。
 
首先，移位的计算公式：
 
``` 
移动位数 = 已匹配的字符数 - 对应的部分匹配值
```
 
下面看具体步骤：
 
 
* 步骤1

![][2]
 
匹配第一个字符，不一致，则将要搜索的字符串右移一位，直至到匹配第1位字母。
  
* 步骤2

![][3]
 
从第一个字符串的第一个X开始，已匹配是4位，查表最后一位匹配的字母对应的匹配值是0，所以右移4位。
  
* 步骤3

![][4]
 
移位后发现不匹配，又要继续右移一位，直至匹配第1位字母。
  
* 步骤4

![][5]
 
发现匹配了X之后，下一位右不匹配了，查表得到X对应的匹配值是0，1-0=1，再右移一位。
  
* 步骤5

![][6]
 
继续匹配，666，发现完全匹配，所以呀，就是找到了一个出现的地方。这时候，已匹配是6个，查表部分匹配值2，移动位数6-2=4.
  
* 步骤6

![][7]
 
又继续匹配，发现又发现一个。这时候移位位数是4，给定字符串已经没了，所以匹配终止。
  
 
 
#### 3.4 结论
 
要搜索的字符串在给定字符串出现的次数是2次，如图：
 

![][8]
 

#### 4. 伪代码[来自《算法导论》]
 
 
* 预处理，生成部分匹配表 
 
 
``` 
//注意：伪代码下标都是从1开始，这是《算法导论》约定俗成的
COMPUTE-PREFIX-FUNCTION(P)
m = P.length
let π[1...m] be a new array
π[1] = 0
k = 0
for q = 2 to m
    while k > 0 and P[k +1] != P[q] //这一步很巧妙，但是比较难理解，下面解释
        k = π[k]
    if P[k + 1] == P[q] //对比上一个部分匹配值的下一个字母是否与当前字母一致
        k = k + 1 
    π[q] = k //保存q对于的部分匹配值
return π
```
 
下面解释上面伪代码COMPUTE-PREFIX-FUNCTION的关键步骤
 
``` 
while k > 0 and P[k +1] != P[q] 
        k = π[k]
```
 
举个比较好理解的例子：
 
``` 
P = XYXYXZT
假设当前运行到Z，则之前生成的 π为
 π[1~5] = [0,0,1,2,3];
此时 k = 3, q = 6, P[4] != P[6] 符合while循环的条件，进入循环。
1.P[4]= P[6]：
首先，在这里为什么要比较P[4] 和 P[6]？这是因为Z前面X对于的π[5] = 3,
这说明P[1~3] = P[3~5],所以一旦P[4]= P[6]，则立马可以
进行下一步的k=k+1(这时if的判断结果肯定的true)；
2.如果P[4] != P[6]：
这时候我们把注意力放在P[1~3],这时候我们求的是P[1~3]前缀和P[4~6]的后缀的公共元素。
这时候我们获取P[3]的部分匹配值，π[3]=1,说明P[1] = P[3]。由上面的P[1~3] = P[3~5]知道，P[1] = P[3] = P[5]，那么我们只需比较P[2] = P[6]，无需从头匹配一遍P[1~3]=P[4~6]，假设相等，这时候就有P[1~2] = P[5~6]，对应的部分匹配值+1，但是，很不幸，这时候依然不等，所以继续循环。
3. 结论：
k = π[k]其实是利用了部分匹配值提供的信息减少比较次数。
```
 
 
* 匹配 
 
 
``` 
KMP-MATCHER(T, P)
n = T.length
m = P.length
π = COMPUTE-PREFIX-FUNCTION(P)
q = 0
for i = 1 to n
    while q > 0 and P[q + 1] != T[i] //这一步和上面的那一步本质上意义是相同的
        q = π[q]
   if P[q + 1] == T[i]
        q = q + 1
   if q == m
       print "Pattern occurs with shift" i - m
       q = π[q]
```
 
 
* 运行时间分析
运行摊还分析的聚合方法进行分析，过程COMPUTE-PREFIX-FUNCTION的运行时间为Θ(m)。唯一微妙的部分是while循环总共执行时间是O(m)。下面将说明它至多进行了m-1次迭代。我们从观察k的值开始:
1）初始值0，并且增加k的唯一方法是
  
 
 
``` 
if P[k + 1] == P[q]
        k = k + 1
```
 
这个在for循环每次迭代至多执行一次，因此，k总共增加m-1次；
 
2） 在进行for循环时，k < q，并且在for循环体的每次迭代中，q的值都增加，所以k < q总成立。这意味着每次while循环迭代时k的值都递减；
 
3）k永远不可能为负值。
 
综上，k的递减来自于while循环，它由k在所有for循环迭代中的增长所限定，k总共下降m-1。因此，while循环最多迭代m-1次，并且COMPUTE-PREFIX-FUNCTION的运行时间为Θ(m)。
 
同样的方法分析可知KMP-MATCHER的时间复杂度是Θ(n)。
 
#### 5. PHP实现
 
```php
<?php

class Kmp {

    /**
     * 生成部分匹配表
     * @param string $p
     * @return array
     */
    public function ComputePrefix($p)
    {
        $m = strlen($p);
        $table = [];
        $table[0] = 0;
        $k = 0;
        for($q = 1; $q < $m; $q++)
        {
            while ($k > 0 && $p[$k] != $p[$q])
                $k = $table[$k];
            if($p[$k] == $p[$q])
                $k = $k + 1;
            $table[$q] = $k;
        }
        return $table;
    }

    /**
     * 匹配
     * @param string $str
     * @param string $p
     */
    public function Matcher($T, $p)
    {
        $n = strlen($T);
        $m = strlen($p);
        $table = $this->ComputePrefix($p);
        $q = 0;
        $match = [];
        for($i = 0; $i < $n; $i++)
        {
            while ($q > 0 && $p[$q] != $T[$i])
                $q = $table[$q];
            if($p[$q] == $T[$i])
                $q = $q + 1;
            if($q == $m) {
                $match[] = ['begin' => $i - $m + 1, 'end' => $i];
                $q = $table[$q - 1];
            }
        }
        return $match;
    }
}

$kmp = new Kmp();
$match = $kmp->Matcher('RXYZAHXFXYZAXYZAXYZ', 'XYZAXY');
```
 
运行结果：
 
  
   
   
![][9]
 

即匹配结果有两个，分别是
 
``` 
$p[8] ~ $p[13]
和
$p[12] ~ $p[17]
```
 


[0]: ../img/UryQbyj.png 
[1]: ../img/Eni2qeE.png 
[2]: ../img/mAV7Fbr.png 
[3]: ../img/bE3a6v6.png 
[4]: ../img/Ivaaie7.png 
[5]: ../img/mQ7RBjM.png 
[6]: ../img/ZBnyQz7.png 
[7]: ../img/VzYVfq7.png 
[8]: ../img/M3MVNnv.png 
[9]: ../img/VVbyArf.png 
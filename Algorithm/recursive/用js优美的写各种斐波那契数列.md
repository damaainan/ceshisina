# 用js优美的写各种斐波那契数列

[Rezeroer][0] 关注 2017.12.17 11:13 字数 1122  


fibonacci

  
在阅读BuckleScript官方文档时，发现一个斐波那契的code让我眼前一亮，实现思路是我从未想到过的。犹记得初学编程时斐波那契数列让我理解的递归的威力，现又让我从新认识了递归。这里我会总结我所有价值的斐波那契数列实现。如后续有新的认知会在文章末尾处更新。

让我从新认识递归的code(代码是Ocaml,后面我会转换为js)

```js
    let fib n =
      let rec aux n a b =
        if n = 0 then a
        else
          aux (n - 1) b (a+b)
      in aux n 1 1
```

这段代码会在后面js的版本介绍

## 正常递归版本

先说下正常的递归版本，这个版本在我初学编程时经常遇到的，它让我知道了递归的强大。

```js
    function fibonacci (n) {
       if(n==0) return 0
       else if(n==1) return 1
       else return fibonacci(n-1) + fibonacci(n-2)
    }
```
    

代码优美逻辑清晰。但是这个版本有一个问题即存在大量的重复计算。如：当n为5的时候要计算fibonacci(4) + fibonacci(3)当n为4的要计算fibonacci(3) + fibonacci(2) ，这时fibonacci(3)就是重复计算了。运行 fibonacci(50) 等半天才会出结果。

## for循环版本

递归有性能问题，用循环来做。

```js
    function fibonacci(n){
      var last = 1
      var last2 = 0
      var current = last2
      for(var i=1;i<=n;i++){
        last2 = last
        last = current
        current = last + last2
      }
      return current
    }
```
    

这个版本没有重复计算问题，速度也明显快了很多。这并不代表循环比递归好。循环的问题在于状态变量太多，为了实现fibonacci这里使用了4个状态变量(last,last2,current,i) 而状态变量 在写、修改、删除的过程中需要格外小心，它会让我有不安全感。状态变量多了阅读起来也不那么优美了。

## 去除重复计算的递归版本

这个就是文章开头的例子转换为js的版本

```js
    function fib(n){
      function fib_(n,a,b){
        if(n==0)  return a
        else return fib_(n-1,b,a+b)
       }
       return fib_(n,0,1)
    }
    

把前两位数字做成参数巧妙的避免了重复计算，性能也有明显的提升。n做递减运算，前两位数字做递增（斐波那契数列的递增）,这段代码一个减，一个增，初看时有点费脑力。按照我的习惯一般是全增，让n从0开始到n。

## 使用记忆函数优化正常递归版本

正常版本fibonacci是纯函数（[什么是纯函数?][2]）纯函数可以用记忆函数进行优化,把那些需要重复计算的都放到缓存中。

```js
    function memozi(fn){
      var r = {}
      return function(n){
        if(r[n] == ){
          r[n] = fn(n)
          return r[n]
        }else{
            return r[n]
        }
      }
    }
    
    var fibfn = memozi(function(n){
        if(n==0){
            return 0
        }else if(n==1){
            return 1
        }else{
            return fibfn(n-1) + fibfn(n-2)
        }
    })
```
    

既达到了性能提升的目的，又不破坏代码本身的优雅。

## 有趣的惰性序列

fibonacci本身是一个数列，只不过无限大。直接使用一个无限大的"数组" 来存储fibonacci 不就好了  
不过js中没有无限大的数组，需要自己动手构造一个。

```js
    // 空序列
    var _empty = {"@placeholder@":"@@"}
    var _end = _empty
    // 序对构造 惰性序列的值只有在需要用到的时候才进行求值 这里用function来代表
    function pair(a,fn){
      return {
        left:a,
        right:fn
      }
    }
    function isFunction(p){
      return Object.prototype.toString.call(p) == "[object Function]"
    }
    function left(p){
      return p.left
    }
    function right(p){
      if(isEmpty(p.right)){
        return p.right
      }else if(isFunction(p.right)){
        return p.right(p)
      }else{
        throw "序列的第二个参数必须是一个函数"
      }
    }
    function isEmpty(seq){
      return seq == _empty
    }
    function isArrEmpty(arr){
      return arr.length == 0
    }
    
    function toArray(seq){
      if(isEmpty(seq)){
        return []
      }else{
        return [left(seq)].concat(toArray(right(seq)))
      }
    }
    function toSeq(arr){
      if(isArrEmpty(arr)){
        return _end
      }else{
        return pair(arr[0],p=>toSeq(arr.slice(1)))
      }
    }
    function map(fn,seq){
      if(isEmpty(seq)){
        return _end
      }else{
        return pair(fn(left(seq)),p=>map(fn,right(seq)))
      }
    }
    function take(n,seq){
      if(isEmpty(seq)){
        return _end
      }else if(n==0){
        return _end
      }else{
        return pair(left(seq),p=>take(n-1,right(seq)))
      }
    }
    
    function zip(fn,seq1,seq2){
      if(isEmpty(seq1)){
        return _end
      }else if(isEmpty(seq2)){
        return _end
      }else{
        var l1 = left(seq1)
        var l2 = left(seq2)
        return pair(fn(l1,l2),p=>zip(fn,right(seq1),right(seq2)))
      }
    }
    
    var fibonacci = pair(0,p=>pair(1,p1=>zip((a,b)=>a+b,p,p1)))
```
    

可以在console下运行 toArray(take(20,fibonacci))看看输出结果 ，运行toArray(take(30,fibonacci))结果出现需要等待一段时间，不要toArray(fibonacci)这样会卡死，因为fibonacci是无穷大的 toArray会一直求值直到内存耗尽。上面这段代码每个函数代码行数都是聊聊几行，直接看代码分析胜过冗余的文字解释。

这个惰性fibonacci 也有性能问题，跟第一个递归版本的是一样的有大量的重复计算，最直接的解决办法是加缓存即求值过的值不要再重新求值了。

## 惰性序列优化版本

```js
    // 空序列
    var _empty = {"@placeholder@":"@@"}
    var _end = _empty
    // 序对构造 惰性序列的值只有在需要用到的时候才进行求值 这里用function来代表
    function pair(a,fn){
      return {
        left:a,
        right:fn,
        rightCache:
      }
    }
    function isFunction(p){
      return Object.prototype.toString.call(p) == "[object Function]"
    }
    function left(p){
      return p.left
    }
    function right(p){
      if(isEmpty(p.right)){
        return p.right
      }else if(isFunction(p.right)){
        if(p.rightCache != ){
          return p.rightCache
        }else{
          p.rightCache = p.right(p)
          return p.rightCache
        }
      }else{
        throw "序列的第二个参数必须是一个函数"
      }
    }
    function isEmpty(seq){
      return seq == _empty
    }
    function isArrEmpty(arr){
      return arr.length == 0
    }
    
    function toArray(seq){
      if(isEmpty(seq)){
        return []
      }else{
        return [left(seq)].concat(toArray(right(seq)))
      }
    }
    function toSeq(arr){
      if(isArrEmpty(arr)){
        return _end
      }else{
        return pair(arr[0],p=>toSeq(arr.slice(1)))
      }
    }
    function map(fn,seq){
      if(isEmpty(seq)){
        return _end
      }else{
        return pair(fn(left(seq)),p=>map(fn,right(seq)))
      }
    }
    function take(n,seq){
      if(isEmpty(seq)){
        return _end
      }else if(n==0){
        return _end
      }else{
        return pair(left(seq),p=>take(n-1,right(seq)))
      }
    }
    
    function zip(fn,seq1,seq2){
      if(isEmpty(seq1)){
        return _end
      }else if(isEmpty(seq2)){
        return _end
      }else{
        var l1 = left(seq1)
        var l2 = left(seq2)
        return pair(fn(l1,l2),p=>zip(fn,right(seq1),right(seq2)))
      }
    }
    
    var fibonacci = pair(0,p=>pair(1,p1=>zip((a,b)=>a+b,p,p1)))
```
    

调用 toArray(take(30,fibSeq)) 对比下上一个版本 速度上有质的提升

## 纯箭头函数版本

条件苛刻一些，使用匿名函数实现fibonacci功能，正常的递归版本上面有提到如下：

```js
    let fib = n => n > 1 ? fib(n-1) + fib(n-2) : n
```
    

把fib名字去掉就是匿名版本，但去掉fib就无法递归也就是调用自身了。事实上是可以间接的调用自身，代码如下：（ 注：不知道怎么描述好 ）

```js
    (f=>n=>n>1?f(f)(n-1)+f(f)(n-2):n)(f=>n=>n>1?f(f)(n-1)+f(f)(n-2):n)(10)
```
    

返回 55。用f来巧妙的代表剪头函数本身

[在线运行上面的代码][4]

## Y Combinator + 箭头函数版本

将上面的箭头函数调用自身的功能抽象出来就是Y Combinator (注: 谢谢[李思立][5]的补充

```js
    let Y = f => (g=>f(a=>g(g)(a)))(g=>f(a=>g(g)(a)))
```
    

现在可以这样写剪头函数版本的代码了

```js
    Y(f=>n=>n>1?f(n-1)+f(n-2):n)(10)
```
    

f代表箭头函数自身，使用f进行递归。[在线运行上面代码][6]

[0]: /u/0ae2cbda9264
[2]: https://link.jianshu.com?t=http://www.diqye.com/pure-function.html
[4]: https://link.jianshu.com?t=http://repl.diqye.com/#KGY9Pm49Pm4+MT9mKGYpKG4tMSkrZihmKShuLTIpOm4pKGY9Pm49Pm4+MT9mKGYpKG4tMSkrZihmKShuLTIpOm4pKDEwKQ==
[5]: https://link.jianshu.com?t=https://www.zhihu.com/people/li-si-li
[6]: https://link.jianshu.com?t=http://repl.diqye.com/#bGV0IFkgPSBmID0+IChnPT5mKGE9PmcoZykoYSkpKShnPT5mKGE9PmcoZykoYSkpKQpZKGY9Pm49Pm4+MT9mKG4tMSkrZihuLTIpOm4pKDEwKQo=
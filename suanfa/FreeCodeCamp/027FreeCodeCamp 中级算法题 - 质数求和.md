# FreeCodeCamp 中级算法题 - 质数求和

 时间 2017-06-26 14:05:52  [S1ngS1ng][0]

_原文_[http://singsing.io/blog/fcc/intermediate-sum-all-primes/][1]

 主题 [算法][2]

* [中文链接][3]
* [英文链接][4]
* 级别：中级 (Intermediate Algorithm Scripting)

## 问题解释 

* 这个 function 接收一个数字参数 num 。返回小于等于 num 的质数之和
* 如果 num 是 4 ，那么返回值应为 5 。如果 num 是 10 ，那么返回值应为 17

## 解题思路 

* 这道题会涉及一些数学知识，其实代码不难写
* 质数的定义是，如果一个数 **只能** 被 1 和这个数自己整除，那么这个数就是质数。与这个概念相对应的叫合数
* 1 既不是质数也不是合数
* 比如，20 以内的质数，有且仅有这些：2, 3, 5, 7, 11, 13, 17, 19
* 那么首先我们需要写一个判断质数的方法。根据定义，可以这样写：
```js
    function isPrime(num){
        for (var i = 2; i < num; i++) {
            if (num % i === 0) {
                return false;
            }
        }
        return true;
    }
```

* 1 是不用判断的，因为任何整数都可以被 1 整除。 num 本身也是不用判断的，因为 num 肯定可以被 num 整除
* 我们先把这个写法用到基础解法中，后面再优化

## 基本解法 - 遍历 

## 思路提示 

* 上面我们已经写好了判断，那么只需要从 2 开始一直到 num 遍历一遍，每一个数都进行一次判断，是质数的我们加起来就可以了

## 代码 

    function sumPrimes(num){
        var sum = 0;
        for (var i = 2; i <= num; i++) {
            if (isPrime(i)) {
                sum += i;
            }
        }
    
        function isPrime(current){
            for (var i = 2; i < current; i++) {
                if (current % i === 0) {
                    return false;
                }
            }
            return true;
        }
    
        return sum;
    }
    

## 优化 - 数学方法 

## 思路提示 

* 首先，对于一个数字 x ，我们不需要从 2 一直循环到 x 来验证它是否为质数，只需要验证到 x/2 就够了，也就是 x 的一半。因为 x 除以 x/2 到 x 的范围内的任何数，商一定是小于 2 的。因此，对于 isPrime 方法，我们就可以写成这样：
```js
    function isPrime(num){
        for (var i = 2; i < num / 2; i++) {
            if (current % i === 0) {
                return false;
            }
        }
        return true;
    }
```

* 因此，现在代码就是：
```js
    function isPrime(num){
        for (var i = 2; i < Math.sqrt(num); i++) {
            if (current % i === 0) {
                return false;
            }
        }
        return true;
    }
```

由于这里只是替换一个判断方法，就不再粘贴全部代码了。大家可以粘贴进去试一试。这样的优化对于较小的数看起来可能不明显，但当数字比较大的时候，速度确实会优化一些

## 优化 - Sieve of Eratosthenes 

## 思路提示 

* 判断质数的方式有很多。详情可以参考维基百科词条 [素性测试][5] 及其 [英文版本][6]
* 相比之下，最容易实现的应该是 [Sieve of Eratosthenes(埃拉托斯特尼筛法)][7] 了。详细内容请参考其 [英文版本][8]
* 简单说一下，这个算法就是在 2 至 num 范围内，筛掉 2 至 Math.sqrt(num) 范围中质数的倍数，结果就可以得到 2 至 num 范围内的所有质数
* 执行过程是，先生成一个长度为 num 的数组，把所有的元素都设为 true 。然后遍历这个数组，如果索引是 2 的倍数 (不包含 2)，就把元素标记为 false ；再从头遍历，如果索引是 3 的倍数 (不包含 3)，就把元素标记为 false …… 以此类推
* 最终，索引范围在 2 至 num 之间，元素为 true 的就是质数

## 代码 

    function sumPrimes(num){
        var flagArr = [];
    
        // 生成标记数组，初始化为 true
        for (var i = 0; i <= num; i++) {
            flagArr.push(true);
        }
    
        for (var i = 2; i <= Math.sqrt(num); i++) {
            var j = Math.pow(i, 2);
            // 如果本身就是 `false`，说明已经标记过，因此不需要再进入循环
            while (flagArr[i] && j <= num) {
                flagArr[j] = false;
                j += i;
            }
        }
    
        // 计算最终标记为 true 的 index 之和
        return flagArr.reduce(function(accum, value, index){
            return accum + (value ? index : 0);
        }, -1);
    }

[0]: /sites/q22mEzq
[1]: http://singsing.io/blog/fcc/intermediate-sum-all-primes/
[2]: /topics/11000083
[3]: https://www.freecodecamp.cn/challenges/https://freecodecamp.cn/challenges/sum-all-primes
[4]: https://www.freecodecamp.com/challenges/https://freecodecamp.cn/challenges/sum-all-primes
[5]: https://zh.wikipedia.org/wiki/%E7%B4%A0%E6%80%A7%E6%B5%8B%E8%AF%95
[6]: https://en.wikipedia.org/wiki/Primality_test
[7]: https://zh.wikipedia.org/wiki/%E5%9F%83%E6%8B%89%E6%89%98%E6%96%AF%E7%89%B9%E5%B0%BC%E7%AD%9B%E6%B3%95
[8]: https://en.wikipedia.org/wiki/Sieve_of_Eratosthenes
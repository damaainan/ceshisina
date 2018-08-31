# FreeCodeCamp 中级算法题 - 斐波那契数列奇数项求和

 时间 2017-06-12 13:59:16  [S1ngS1ng][0]

_原文_[http://singsing.io/blog/fcc/intermediate-sum-all-odd-fibonacci-numbers/][1]

 主题 [算法][2]

* 整体上，思路可以有以下几种： 
  * 建立三个变量，分别为当前位置的前两个数以及当前的总和。以当前值大于 num 为跳出条件，循环并动态地给这三个变量赋值，最后返回总和
  * 根据 num 建立一个斐波那契数组(可以用循环写，也可以用递归) 
    * 遍历这个数组，在遍历过程中判断奇偶，求和
    * 直接用 filter 方法过滤掉偶数，然后求数组元素总和
* 两种思路都不太难。可以先尝试一下

## 第一种思路 

## 思路提示 

* 由于斐波那契数列的计算方式是，当前的值等于前一个数与再之前一个数的和，因此我们要设置两个初始值。分别代表第一个元素与第二个元素。如果只设置一个，那么第二个元素是没法计算出来的
* 循环方面，不管用 for 写还是用 while ，都要注意初始值与边界条件的选择。以下只给出 while 的写法
* 还有就是对 num 的特殊值判断。可以在外面直接用 if 去判断，当然也可以在计算逻辑中处理。对于 num 为 0 的情况，显然结果是 0 。对于 num 为 1 的情况，显然结果是 1

## 代码 
```js
    function sumFibs(num){
        // num 为 0 时，结果应为 0
        if (num === 0) {
            return 0;
        }
    
        var former = 1;
        var beforeFormer = 1;
        // 若 num 大于等于 1，那么以和为 2 作为初始值
        var sum = 2;
    
        while (former + beforeFormer <= num) {
            var current = former + beforeFormer;
            if (current % 2 === 1) {
                sum += current;
            }
            beforeFormer = former;
            former = current;
        }
    
        return sum;
    }
    
```
## 解释 

* 首先，设置了 beforeFormer 为数列的第一个值， former 为数列的第二个值，均为 1 。也可以说， beforeFormer 为当前值的前两个数， former 为当前值的前一个数
* 至于 sum 值的初始值，这里我是按照 sum 为前两个数的和来定义的。也就是说，一切后续计算都从数列中的第三个数开始。之所以可以这么写，是因为不论传入的 num 是 1 还是 2 ，都应该返回这两个数之和，也就是 2
* 但当传入的 num 为 0 的时候，就应该得到 0 了。这需要在一开始就处理
* 跳出条件就是当前值大于 num 。由于计算过程中，当前值是根据之前的两个值来计算的，因此只要把这两个变量加起来，就可以得到当前值
* 如果当前值是奇数，那我们就把它加给 sum 。但无论当前值是否为奇数，我们都要更新 beforeFormer 和 former
* 这个循环不可能成为无限循环。因为我们在循环结束前修改了 beforeFormer 与 former 这两个值。由于 current 一直在增大，因此 former 和 beforeFormer 也一直在增加

## 第二种思路 - 生成数组 

## 思路提示 

* 与第一种思路类似，我们可以先生成不大于 num 的斐波那契数组，然后在用 reduce 求和的过程中判断奇偶

## 代码 - 求和中判断 
```js
    function sumFibs(num){
        if (num === 0) {
            return 0;
        }
    
        // 当然这里也可以定义成 [1, 1]，然后 current 定义成 2
        var fibsArr = [1];
        var current = 1;
    
        while(current <= num) {
            fibsArr.push(current);
    
            // 通过最后两位数字来求下一位
            var lastTwo = fibsArr.slice(-2);
            current = lastTwo[0] + lastTwo[1];
            // 至于是否需要保存进 fibsArr，要先判断是否小于等于 num
        }
    
        return fibsArr.reduce(function(prev, next){
            if (next % 2 === 1) {
                return prev + next;
            }
            return prev;
        }, 0);
    }
    
```
## 第三种思路 - 递归 

## 思路提示 

* 题目中说到，”此题不能用递归来实现”，个人觉得是不够准确的
* 这道题当然可以用递归，只是要注意优化。否则， num 较大时确实会造成栈溢出
* 先来简单说一下，栈溢出是怎么回事，这样我们才能知道该如何避免

## 栈溢出的产生 

### 关于 Call Stack 

* 递归的写法注定了它在执行过程中会存储很多 “临时” 的值。这些值不会像我们定义变量那样，放到某个作用域下，更不会像全局变量那样存到 window 里面，而是放到 “调用栈” (Call Stack) 里面
* 如果你打开浏览器的调试工具（此处以 Chrome 为例），点开 Sources Tab，你会看到右边的面板里是有一个东西叫 Call Stack 的
* 其实你肯定是用到过 Call Stack 的，只是没有注意到。比如 setTimeout(foo, 0) 方法就会把参数中的函数放到调用栈尾部。浏览器读到这一行，就会说：好，我先继续往后执行其他代码，你这个 foo 函数先等一等，我一会儿再来执行你
* 这也就很好解释，就算 setTimeout 的第二个参数传入的是 0，也会让这个函数在其他内容执行完成后再执行。印象中有一道常见的面试题和这个有关 

### 一个简单的例子 

可能，还是上一段代码大家自己玩儿一下比较容易理解
```js
    function a(){
        console.log('a');
        setTimeout(c, 0);
        b();
    }
    
    function b(){
        console.log('b');
        debugger;
    }
    
    function c(){
        console.log('c');
        debugger;
    }
    
    a();
    
```
* 放到 console 中执行，就会自动进入 Sources 选项卡的断点模式。这就是关键字 debugger 的作用
* 先输出 a 应该没有问题。但尽管 a 函数中，在 b() 前有一个 setTimeout(c, 0) ，仿佛 c 是会在 b() 之前执行的，但在 b 里面的断点，我们可以看到之前执行了 a ，但 Call Stack 里并没有 c
* 这时候按下 Resume script execution 按钮 (快捷键 F8)，走到下一个断点，Call Stack 里才会出现 c 。这也就表明， a 和 b 都执行完了，才会执行 c
* 你可以在断点中清晰地看到那时候的函数执行顺序。如果没有断点，那么这些都是一瞬间发生的

### 递归的执行 

现在我们回到斐波那契数列，看一看递归究竟是如何执行的，以获取斐波那契数列第 n 位为例 (注：如无声明，以下均用 F 来代替这里的 fibonacci 方法)： 
```js
    function fibonacci(n){
        if (n < 2){
            return 1;
        }else{
            debugger;
            return fibonacci(n - 2) + fibonacci(n - 1);
        }
    }
    
```
* 在调试这段代码的时候需要注意，最好点击左边第三个按钮 Step into next function call （快捷键 F11）进行调试。你可以在 Watch 那一栏加上个 n ，这样方便观察。或者，你也可以留意一下左边面板上， fibonacci(n) { 旁边的 n ，这个 n 表示 **当前传入** 的实际参数
* 比如，我们执行 F(3) ，你可以按 F11，对照着下文看，它的过程是这样的： 
  * n < 2 不成立，因此执行 else 的部分 return F(n - 2) + F(n - 1) ，即为 F(1) + F(2)
    * 执行 F(1) ，此时 n < 2 成立，因此 **这一步** 返回 1
    * 执行 F(2) ，此时 n < 2 不成立，执行 else 部分，即 F(0) + F(1)
      * 执行 F(0) ，此时 n < 2 成立，因此 **这一步** 返回 1 。注意观察，此时右边的 Call Stack 里面有 3 个 fibonacci ，你可以分别点一下他们，它们的 n 分别为 3，2 和 0
      * 执行 F(1) ，此时 n < 2 成立，因此 **这一步** 返回 1 。此时右边 Call Stack 里面也有 3 个 fibonacci ，它们的 n 分别为 3，2 和 1
    * 得出 F(0) + F(1) 为 2
  * 得出 F(1) + F(2) 为 3

这就是我们执行 fibonacci(3) 的全过程。接下来，我们试试这样做： 
```js
    var count = 0;
    function fibonacci(n){
        count += 1;
        if (n < 2){
            return 1;
        } else {
            return fibonacci(n - 2) + fibonacci(n - 1);
        }
    }
    fibonacci(5)
    
```
没错，这段代码作用就是记录下来 fibonacci 调用了多少次。如果 n 为 5 ，那么 count 为 8 。但 n 为 10 的时候， count 就达到了 89 。简直惊悚 

更可怕的在于，对于比较大的 n ，如果你也按照上面的方式列出来执行过程，就会发现层级非常深。再换句话说，Call Stack 也就会有一长串的调用 

对于这一种现象，我们可以通过一个叫做 “时间复杂度” 的概念去描述。假设我们用 for 循环遍历长度为 n 的数组，不难看出执行要花费的时间与数组长度成正比。忽略掉他们之间的具体关系，我们记为 O(n) 上面写的这个函数，执行需要花费的时间显然与调用次数成正比。不管 n 具体是几， F(n) 都是需要去调用两次 fibonacci 的，分别是 F(n - 2) 和 F(n - 1) ，我们称之为 “递归入口”。对于每一个入口，他们最深都可以走到第 n 层。有的朋友可能会说，那如果 n 是 0 ， 1 和 2 呢？那就没这些事儿了啊。确实，但在广义的讨论中，如果 n 足够大，我们就可以忽略掉 n < 2 对执行时间的影响 

所以这里的时间复杂度究竟是什么？这样看可能清晰一些： 

    F(n)  =   F(n - 2)   +   F(n - 1)
                  |              |
                 / \            / \
            F(n-4) F(n-3)  F(n-3) F(n-2)
    

第一行我们调用了两次 F ，把这两个 F 分解，得到四个 F 调用。继续分解，显然是 8 个，然后就是 16，32 … 所以，这里的时间复杂度是 O(2^n) 。如果你熟悉函数图像，就会明白指数级的增长是多可怕，它比 O(n^2) 要增长更快。比指数级增长再快的，就是 O(n!) 了 

这就是递归导致内存溢出的根本原因

### 优化 

* 你会发现，上面写的递归会有很多的重复调用。比如，对于 F(3) ， F(1) 被调用了两次。对于更大的 n ，可以预见地， F(1) 和 F(0) 会被调用更多次
* 如果你多想一步，这些调用的根源在哪里呢？试想一下： 
  * 对于 n = 3 来说，会执行 F(1) 和 F(2) 。在 F(2) 里面会再调用 F(1)
  * 对于 n = 4 来说，会执行 F(2) 和 F(3) 。 F(3) 的调用中也会再去调用 F(2)
  * ……
  * 推广到 n ，对于 n 来说，会执行 F(n - 2) 和 F(n - 1) 。而 F(n - 2) 的调用中会再去调用 F(n - 1)

### 关键逻辑 

* 为了避免这种情况的发生，如果我们可以 “暂时存储” 之前的计算结果，或者说把当前的结果传给下一次调用，那就可以完美解决问题。比如，对于求 F(4) ，如果我们在求出 F(3) 的时候可以做这两件事： 
  * 给 F(3) 加一下 F(2) 并传给下一次 F(4) 的调用
  * 把这个 F(3) 值也传给下一次 F(4) 调用
* 那么到 F(4) 执行的时候，通过第一步我们可以直接在 F(4) 调用时得到当前的结果；通过第二步，我们可以计算出该传给下一次 F(5) 的结果

### 代码 

* 想要实现这个，最简单的方式就是给函数添加几个参数，一个表示当前值，称为 sum ；一个表示上一个值，称为 prev 。这是函数的定义
* 我们还应该先考虑如何让它结束。显然，这个求第 n 个数的递归是通过 n 来决定的。所以调用一次就让它执行一次 n - 1 ，然后我们根据 n 来判断是否该结束就行
* 再考虑一下函数的跳出。如果当前值就是我们想要的，直接返回 sum 即可
* 上面说到的， sum 是当前值， prev 是上一个值，是针对 “本次” 来说的。比如，我们调用 fibonacci(3) ，这时候 sum 应为 2 ， prev 应为 1 。对于下一次计算 fibonacci(4) ，我们需要让它在执行的时候 sum 为 3 ， prev 为 2 。这样我们才能通过 sum 得到当前值
* 递归的调用，其实就是设置下一次该是什么。如果你再多分析几个例子，就会明白，其实我们需要做的就是在参数 sum 传入 sum + prev ，同时参数 prev 传入当前的 sum 用于下一次调用。这就实现了我们所说的 “暂时存储”
```js
    function fibonacci(n, sum, prev){
        // 若调用是没有传入 sum 和 prev，则设定初始值
        sum = sum || 1;
        prev = prev || 0;
    
        if (n === 0) {
            return prev;
        }
        if (n === 1) {
            return sum;
        }
    
        return fibonacci(n - 1, sum + prev, sum)
    }
```

#### 请自己比较一下上面的两种写法，只要理解了这种处理思路，那么后面的代码才有可能看懂

### 性能比较 

* 不得不说，用循环还真的是最快的，因为只需要一个 while 循环即可解决，复杂度是 O(n)
* 请参考这个链接：jsperf 性能比较

## 代码 - 不会造成栈溢出的递归解法 

* 回到题目中，我的做法是在递归过程里判断奇偶。另外，为了方便，调用的时候直接传入了 curr 、 prev 和 sum 的初始值，就不需要再在 getSum 中处理了
* 另外，由于外面已经给了 sumFibs 这个函数，只有一个参数。所以，我们才需要在里面再写一个函数。 sumFibs 的返回值也只要是 getSum(0, 1, 0) 就可以了，因为这是在调用 getSum 方法

```js
    function sumFibs(num){
        function getSum(curr, prev, sum){
            // curr 为当前值，prev 为上一个
            // sum 为目前的和
            if (curr > num) {
                return sum;
            }
    
            if (curr % 2 === 1) {
                // 如果当前值为奇数，就把当前值加给 sum
                sum += curr;
            }
    
            // 传入 curr + prev 作为下一次的 curr
            // curr 作为下一次的 prev
            return getSum(curr + prev, curr, sum);
        }
    
        return getSum(0, 1, 0);
    }
```

[0]: /sites/q22mEzq
[1]: http://singsing.io/blog/fcc/intermediate-sum-all-odd-fibonacci-numbers/
[2]: /topics/11000083
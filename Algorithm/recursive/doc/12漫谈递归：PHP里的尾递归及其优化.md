# 漫谈递归：PHP里的尾递归及其优化

 [kelinlin][0]  2012-10-17  

不同的语言对尾递归的支持都有所不同，编译器的优化也不尽相同。我们之前看了C语言的尾递归，那么在PHP里又是如何的呢？  


### PHP对尾递归没有优化效果

先来看下实验。  

    <?php
    function factorial($n)
    {
        if($n == 0) {
            return 1;
        }   
        return factorial($n-1) * $n; 
    }
     
    var_dump(factorial(100));
    ?>

  
  
如果安装了XDebug的话，可能会遇到如下错误：  

    Fatal error: Maximum function nesting level of '100' reached, aborting!

  
  
这是XDebug的一个保护机制，可以通过max_nesting_level选项来设置。放开设置的话，程序还是能够正常运行的。  
  
即便代码能正常运行，只要我们不断增大参数，程序迟早会报错：  

    Fatal error:  Allowed memory size of … bytes exhausted

  
  
为什么呢？简单点说就是递归造成了栈溢出。按照之前的思路，我们可以试下用尾递归来消除递归对栈的影响，提高程序的效率。  

    <?php
    function factorial($n, $acc)
    {
        if($n == 0) {
            return $acc;
        }
        return factorial($n-1, $acc * $n);
    }
    
    var_dump(factorial(100, 1));
    ?>

  
  
XDebug同样报错，并且程序的执行时间并没有明显变化。  

    Fatal error: Maximum function nesting level of '100' reached, aborting!

  
  
事实证明，尾递归在php中是没有任何优化效果的。  


### PHP如何消除递归

先看看下面的源代码：  

    <?php
    function factorial($n, $accumulator = 1) {
        if ($n == 0) {
            return $accumulator;
        }
    
        return function() use($n, $accumulator) {
            return factorial($n - 1, $accumulator * $n);
        };
    }
    
    function trampoline($callback, $params) {
        $result = call_user_func_array($callback, $params);
    
        while (is_callable($result)) {
            $result = $result();
        }
    
        return $result;
    }
    
    var_dump(trampoline('factorial', array(100)));
    
    ?>

  
  
现在XDebug不再警报效率问题了。  
  
注意到trampoline()函数没？简单点说就是利用高阶函数消除递归。想更进一步了解 call_user_func_array，可以参看这篇文章：优化。在使用尾递归对代码进行优化的时候，必须先了解这门语言对尾递归的支持。  
  
在PHP里，除非能提升代码可读性，否则没有必要使用递归；迫不得已之时，最好考虑使用Tail Call或Trampoline等技术来规避潜在的栈溢出问题。

[0]: http://www.lai18.com/user/214130.html

# [php 调试利器debug_backtrace()][0]

* [调试][1]
* [php][2]

[**Corwien**][3] 2016年07月25日发布 


> debug_backtrace() 是一个很低调的函数,很少有人注意过它. 不过当我对着一个对象调用另一个对象再调用其它的对象和文件中的一个函数出错时,它正在一边笑呢。

**debug_print_backtrace(), debug_backtrace()** 只是前者直接打印出来了而已。查看整个程序的调用栈，用来查看瞬间函数调用栈，方便查错。

如果我们想知道某个方法被谁调用了? **debug_backtrace**可以解决。debug_backtrace() 可以打印出一个页面的调用过程 , 从哪儿来到哪儿去一目了然. 不过这是一个PHP5的专有函数,好在pear中已经有了实现,[http://pear.php.net/package/P...][12]

测试代码

    <?php 
    class a
    { 
       function say($msg) 
      { 
        echo "msg:".$msg; 
        echo "<pre>";
        // print_r(debug_backtrace()); 
        print_r(print_message_class());
      } 
    } 
    
    class b
    { 
        function say($msg)
        { 
          $a = new a(); 
         $a->say($msg); 
       } 
    } 
    
    class c
    { 
       function __construct($msg)
     { 
       $b = new b(); 
       $b->say($msg); 
      } 
    } 
    
    $c = new c("test"); 

输出结果：

    msg:test 
    a.say
    

将debug_backtrace封装为一个方法,只获取输出类名和方法名：

    /**
     * 打印类的标记
     * 
     * @return string
     */
    function print_message_class()
    {
       $backtrace  = debug_backtrace();
       $class_name = $backtrace[1]['class'];
       $func_name  = $backtrace[1]['function'];
       $message    = "{$class_name}.{$func_name}() ";
       return $message;
    }

[0]: https://segmentfault.com/a/1190000006062759
[1]: https://segmentfault.com/t/%E8%B0%83%E8%AF%95/blogs
[2]: https://segmentfault.com/t/php/blogs
[3]: https://segmentfault.com/u/corwien
[12]: http://pear.php.net/package/PHP_Compat
# call_user_func函数详解

 时间 2017-12-22 12:00:00  

原文[https://www.52bz.la/3524.html][1]

call_user_func函数类似于一种特别的调用函数的方法

    function a($b,$c) {
         echo $b;      
         echo $c;  
    }   
    call_user_func('a', "1","2");  
    call_user_func('a', "3","4");   //输出 1 2 3 4

注：a是公共方法

调用A类中的b方法并且传入参数$c

    class A {      
        function b($c) {
             echo $c;      
         }  
     }   
     call_user_func(array("A", "b"),"111");   //输出 111  ?>

相关函数

`call_user_func_array` 调用回调函数，并把一个数组参数作为回调函数的参数

`func_get_args()` 这个函数返回的是包含当前函数所有参数的一个数组

`func_get_arg()` 函数返回的是指定位置的参数的值

`func_num_args()` 这个函数返回的是当前函数的参数数量 返回的是数字

[1]: https://www.52bz.la/3524.html

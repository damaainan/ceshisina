# 也是拿來掃 PHP 程式碼的 PHPStan

 时间 2017-12-31 00:52:08  

原文[https://blog.gslin.org/archives/2017/12/31/8015/也是拿來掃-php-程式碼的-phpstan/][2]


[PHPStan][4] 也是 [PHP][5] 的靜態分析工具，官方給的 slogan 是「PHP Static Analysis Tool - discover bugs in your code without running it!」。然後官方給了一個 GIF，直接看就大概知道在幹什麼了： 

![][6]

跟 [Phan][7] 類似，也是要 PHP 7+ 才能跑，不過實際測試發現不像 Phan 需要 [php-ast][8] ： 

    PHPStan requires PHP ^gt;= 7.0. You have to run it in environment with PHP 7.x but the actual code does not have to use PHP 7.x features. (Code written for PHP 5.6 and earlier can run on 7.x mostly unmodified.)
    PHPStan works best with modern object-oriented code. The more strongly-typed your code is, the more information you give PHPStan to work with.
    Properly annotated and typehinted code (class properties, function and method arguments, return types) helps not only static analysis tools but also other people that work with the code to understand it.
    

拿上一篇「 [用 Phan 檢查 PHP 程式的正確性][9] 」的例子測試，也可以抓到類似的問題： 

    vendor/bin/phpstan analyse -l 7 src/
     1/1 [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓] 100%
    
     ------ --------------------------------------------------------------
      Line   src/Foo.php
     ------ --------------------------------------------------------------
      13     Method Gslin\Foo::g() should return string but returns null.
     ------ --------------------------------------------------------------
    
    
     [ERROR] Found 1 error

這樣總算把積壓在 tab 上關於 PHP 工具都寫完了，之後要用才有地方可以翻... XD

[2]: https://blog.gslin.org/archives/2017/12/31/8015/也是拿來掃-php-程式碼的-phpstan/
[4]: https://github.com/phpstan/phpstan
[5]: https://secure.php.net/
[6]: https://img0.tuicool.com/RZf6Rbq.gif
[7]: https://github.com/phan/phan'
[8]: https://github.com/nikic/php-ast
[9]: https://blog.gslin.org/archives/2017/12/31/8014/%E7%94%A8-phan-%E6%AA%A2%E6%9F%A5-php-%E7%A8%8B%E5%BC%8F%E7%9A%84%E6%AD%A3%E7%A2%BA%E6%80%A7/
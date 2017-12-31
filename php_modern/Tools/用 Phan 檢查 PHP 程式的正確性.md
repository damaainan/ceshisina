# 用 Phan 檢查 PHP 程式的正確性

 时间 2017-12-31 00:25:51  

原文[https://blog.gslin.org/archives/2017/12/31/8014/用-phan-檢查-php-程式的正確性/][2]


[Phan][4] 這套也是拿來檢查 [PHP][5] 程式用的，也是儘量避免丟出 false alarm。不過 Phan 只能用在 PHP 7+ 環境，原因是使用 [php-ast][6] ，另外有一些額外建議要裝的套件： 

    This version (branch) of Phan depends on PHP 7.1.x with the php-ast extension (0.1.5 or newer, uses AST version 50) and supports PHP version 7.1+ syntax. Installation instructions for php-ast can be found here. For PHP 7.0.x use the 0.8 branch. Having PHP's pcntl extension installed is strongly recommended (not available on Windows), in order to support using parallel processes for analysis (or to support daemon mode).
    

最新版還只能跑在 PHP 7.2 上面，用的時候要注意一下 XD (我在測試時， require-dev 指定 0.11.0 ，結果被說只有 PHP 7.1 不符合 dependency，後來放 * 讓他去抓適合的版本) 

像是這樣的程式碼：

    class Foo
    {
        /**
         * @param string $p
         * @return string
         */    function g($p) {
            if (!$p) {
                return ;
            }
            return $p;
        }
    }

就會產生出對應的警告訊息：

    src/Foo.php:13 PhanTypeMismatchReturn Returning type null but g() is declared to return string
    

也是掛進 CI 裡面的好東西...

[2]: https://blog.gslin.org/archives/2017/12/31/8014/用-phan-檢查-php-程式的正確性/
[4]: https://github.com/phan/phan
[5]: https://secure.php.net/
[6]: https://github.com/nikic/php-ast
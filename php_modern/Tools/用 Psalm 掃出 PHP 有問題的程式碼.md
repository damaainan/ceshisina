# 用 Psalm 掃出 PHP 有問題的程式碼

 时间 2017-12-31 00:04:09  

原文[https://blog.gslin.org/archives/2017/12/31/8013/用-psalm-掃出-php-有問題的程式碼/][1]


[Psalm][3] 的 slogan 是「A static analysis tool for PHP」，由 [Vimeo][4] 發展並開放出來的軟體：「 [vimeo/psalm][5] 」。 

目前是 v0.3.71，所以需要 [PHP][6] 5.6 以上才能跑： 

* v0.3.x supports checking PHP 5.4 - 7.1 code, and requires PHP 5.6+ to run.
* v0.2.x supports checking PHP 5.4 - 7.0 code and requires PHP 5.4+ to run.

Psalm 主要的目標是找出哪邊「已經發生錯誤」，而不像其他幾套的目標是「預防」，這樣可以避免過高的 false alarm.

[1]: https://blog.gslin.org/archives/2017/12/31/8013/用-psalm-掃出-php-有問題的程式碼/
[3]: https://getpsalm.org/
[4]: https://vimeo.com/
[5]: https://github.com/vimeo/psalm
[6]: https://secure.php.net/
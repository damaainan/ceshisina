# [PHP单元测试PHPUnit][0]

 标签： [PHP][1][单元测试][2][phpunit][3]

 2017-04-01 11:30  152人阅读  

版权声明：本文为博主原创文章，未经博主允许不得转载。

windows开发环境下，[PHP][8]使用单元[测试][9]可以使用PHPUnit。

安装

首先下载PHPUnit，官网：https://phpunit.de/ 根据自己的[php][8]版本下载对应的PHPUnit版本，我本地是PHP5.5，所以这里我下载PHPUnit4.8。下载完成得到phpunit-4.8.35.phar文件，放到任意目录，这边我放到D:\phpunit下，并把文件名改为：phpunit.phar 。配置环境变量：右击我的电脑-》属性-》高级系统设置-》环境变量-》编辑path在最后添加phpunit.phar的路径，这里我是D:\phpunit，所以在最后添加D:\phpunit 。

打开命令行win+R输入cmd，进入到D:\phpunit

    cd /d D:\phpunit

安装phpunit 

    echo @php "%~dp0phpunit.phar" %* > phpunit.cmd

查看是否安装成功 

    phpunit --version

如果显示phpunit的版本信息，说明安装成功了，这边我显示：PHPUnit 4.8.35 by Sebastian Bergmann and contributors.  
  
测试

先写一个需要测试的类，该类有一个eat方法，方法返回字符串：eating，文件名为Human.php

```php
    <?php
    
    class Human
    {
        public function eat()
        {
            return 'eating';
        }
    }
```

再写一个phpunit的测试类，测试Human类的eat方法，必须引入Human.php文件、phpunit，文件名为test1.php

```php
    <?php
    
    include 'Human.php';
    
    use PHPUnit\Framework\TestCase;
        class TestHuman extends TestCase
        {
            public function testEat()
            {
                $human = new Human;
                $this->assertEquals('eating', $human->eat());
            }
        }
    ?>
```

其中assertEquals方法为断言，判断eat方法返回是否等于'eating'，如果返回一直则成功否则返回错误，运行测试：打开命令行，进入test1.php的路径，然后运行测试：

    phpunit test1.php

返回信息：

    PHPUnit 4.8.35 by Sebastian Bergmann and contributors.
    
    .
    
    Time: 202 ms, Memory: 14.75MB
    
    OK (1 test, 1 assertion)

则表示断言处成功，即返回值与传入的参数值一致。

[0]: http://www.csdn.net/mxdzchallpp/article/details/68923009
[1]: http://www.csdn.net/tag/PHP
[2]: http://www.csdn.net/tag/%e5%8d%95%e5%85%83%e6%b5%8b%e8%af%95
[3]: http://www.csdn.net/tag/phpunit
[8]: http://lib.csdn.net/base/php
[9]: http://lib.csdn.net/base/softwaretest
[10]: #
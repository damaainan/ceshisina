## [PHP单元测试框架PHPUnit的使用](https://segmentfault.com/a/1190000011499301?_ea=2658096)

> 以前在学习IOS开发时有专门写过Objective-C的单元测试的文章，[IOS开发学习之单元测试][0]，今天再总结下怎么在PHP中使用单元测试。

## 一、前言

在这篇文章中，我们使用 composer 的依赖包管理工具进行phpunit包安装和管理，composer 官方地址 [https://getcomposer.org/][1]，按照提示进行全局安装即可，另外，我们也会使用一个非常好用的[Monolog][2]记录日志组件记录日志，方便我们查看。

在根目录下建立 coomposer.json 的配置文件,输入以下内容:

```json
    {
        "autoload": {
            "classmap": [
                "./"
            ]
        }
    }
```

上面的意思是将根目录下的所有的类文件都加载进来， 在命令行执行 composer install 后，在根目录会生成出一个vendor的文件夹，我们以后通过 composer 安装的任何第三方代码都会被生成在这里。

## 二、为什么要单元测试？

> **只要你想到输入一些东西到print语句或调试表达式中，就用测试代替它。** --Martin Fowler

PHPUnit 是一个用PHP编程语言开发的开源软件，是一个单元测试框架。PHPUnit由Sebastian Bergmann创建，源于Kent Beck的SUnit，是xUnit家族的框架之一。

单元测试是对单独的代码对象进行测试的过程，比如对函数、类、方法进行测试。单元测试可以使用任意一段已经写好的测试代码，也可以使用一些已经存在的测试框架，比如JUnit、PHPUnit或者Cantata++，单元测试框架提供了一系列共同、有用的功能来帮助人们编写自动化的检测单元，例如检查一个实际的值是否符合我们期望的值的断言。单元测试框架经常会包含每个测试的报告，以及给出你已经覆盖到的代码覆盖率。

总之一句话，使用 phpunit 进行自动测试，会使你的代码更健壮，减少后期维护的成本，也是一种比较标准的规范，现如今流行的PHP框架都带了单元测试，如Laraval,Symfony,Yii2等，单元测试已经成了标配。

另外，单元测试用例是通过命令操控测试脚本的，而不是通过浏览器访问URL的。

## 三、安装PHPUnit

使用 composer 方式安装 PHPUnit，其他安装方式[请看这里][3]

    composer require --dev phpunit/phpunit ^6.2

安装 Monolog 日志包,做 phpunit 测试记录日志用。

    composer require monolog/monolog

安装好之后，我们可以看coomposer.json 文件已经有这两个扩展包了：

```json
    {
     "require":{  
         "monolog/monolog":"^1.23",
        },
    
     "require-dev": {
            "phpunit/phpunit": "^6.2"
        }
    }
```

## 四、PHPUnit简单用法

### 1、单个文件测试

创建目录tests，新建文件 StackTest.php，编辑如下：

```php
    <?php
    /**
     * 1、composer 安装Monolog日志扩展，安装phpunit单元测试扩展包
     * 2、引入autoload.php文件
     * 3、测试案例
     *
     *
     */
    namespace App\tests;
    require_once __DIR__ . '/../vendor/autoload.php';
    define("ROOT_PATH", dirname(__DIR__) . "/");
    
    use Monolog\Logger;
    use Monolog\Handler\StreamHandler;
    
    use PHPUnit\Framework\TestCase;
    
    
    class StackTest extends TestCase
    {
        public function testPushAndPop()
        {
            $stack = [];
            $this->assertEquals(0, count($stack));
    
            array_push($stack, 'foo');
    
            // 添加日志文件,如果没有安装monolog，则有关monolog的代码都可以注释掉
            $this->Log()->error('hello', $stack);
    
            $this->assertEquals('foo', $stack[count($stack)-1]);
            $this->assertEquals(1, count($stack));
    
            $this->assertEquals('foo', array_pop($stack));
            $this->assertEquals(0, count($stack));
        }
    
        public function Log()
        {
            // create a log channel
            $log = new Logger('Tester');
            $log->pushHandler(new StreamHandler(ROOT_PATH . 'storage/logs/app.log', Logger::WARNING));
            $log->error("Error");
            return $log;
        }
    }
```

**代码解释：**

1. StackTest为测试类
1. StackTest 继承于 PHPUnit\Framework\TestCase
1. 测试方法testPushAndPop()，测试方法必须为public权限，一般以test开头，或者你也可以选择给其加注释@test来表
1. 在测试方法内，类似于 assertEquals() 这样的断言方法用来对实际值与预期值的匹配做出断言。

**命令行执行:**  
phpunit 命令 测试文件命名

    ➜  framework#  ./vendor/bin/phpunit tests/StackTest.php
    
    // 或者可以省略文件后缀名
    //  ./vendor/bin/phpunit tests/StackTest

执行结果：

    ➜  framework# ./vendor/bin/phpunit tests/StackTest.php
    PHPUnit 6.4.1 by Sebastian Bergmann and contributors.
    
    .                                                                   1 / 1 (100%)
    
    Time: 56 ms, Memory: 4.00MB
    
    OK (1 test, 5 assertions)

我们可以在app.log文件中查看我们打印的日志信息。

### 2、类文件引入

Calculator.php

```php
    <?php  
    class Calculator  
    {  
        public function sum($a, $b)  
        {  
            return $a + $b;  
        }  
    }  
    ?>  
```

单元测试类：  
CalculatorTest.php

```php
    <?php
    
    namespace App\tests;
    require_once __DIR__ . '/../vendor/autoload.php';
    require "Calculator.php";
    
    use PHPUnit\Framework\TestCase;
    
    
    class CalculatorTest extends TestCase
    {
        public function testSum()
        {
            $obj = new Calculator;
            $this->assertEquals(0, $obj->sum(0, 0));
    
        }
    
    }
```

命令执行：

    > ./vendor/bin/phpunit tests/CalculatorTest

执行结果：

    PHPUnit 6.4.1 by Sebastian Bergmann and contributors.
    
    F                                                                   1 / 1 (100%)
    
    Time: 117 ms, Memory: 4.00MB
    
    There was 1 failure:

如果我们把这里的断言故意写错，$this->assertEquals(1, $obj->sum(0, 0));  
看执行结果：

    PHPUnit 6.4.1 by Sebastian Bergmann and contributors.
    
    F                                                                   1 / 1 (100%)
    
    Time: 117 ms, Memory: 4.00MB
    
    There was 1 failure:
    
    1) App\tests\CalculatorTest::testSum
    Failed asserting that 0 matches expected 1.
    
    /Applications/XAMPP/xamppfiles/htdocs/web/framework/tests/CalculatorTest.php:22
    
    FAILURES!
    Tests: 1, Assertions: 1, Failures: 1.

会直接报出方法错误信息及行号，有助于我们快速找出bug

### 3、高级用法

你是否已经厌烦了在每一个测试方法命名前面加一个test，是否因为只是调用的参数不同，却要写多个测试用例而纠结？我最喜欢的高级功能，现在隆重推荐给你，叫做框架生成器。

Calculator.php

```php
    <?php  
    class Calculator  
    {  
        public function sum($a, $b)  
        {  
            return $a + $b;  
        }  
    }  
    ?>  
```
命令行启动测试用例，使用关键字 --skeleton    > ./vendor/bin/phpunit --skeleton Calculator.php

执行结果：

    PHPUnit 6.4.1 by Sebastian Bergmann and contributors.
    
    Wrote test class skeleton for Calculator to CalculatorTest.php.  
    

是不是很简单，因为没有测试数据，所以这里加测试数据，然后重新执行上边的命令

```php
    <?php  
    class Calculator  
    {  
        /** 
         * @assert (0, 0) == 0 
         * @assert (0, 1) == 1 
         * @assert (1, 0) == 1 
         * @assert (1, 1) == 2 
         */  
        public function sum($a, $b)  
        {  
            return $a + $b;  
        }  
    }  
    ?>  
```
原始类中的每个方法都进行@assert注解的检测。这些被转变为测试代码，像这样

```php
        /**
         * Generated from @assert (0, 0) == 0.
         */
        public function testSum() {
            $obj = new Calculator;
            $this->assertEquals(0, $obj->sum(0, 0));
        }
```
执行结果：

     ./vendor/bin/phpunit tests/CalculatorTest
    PHPUnit 6.4.1 by Sebastian Bergmann and contributors. 
      
    ....  
      
    Time: 0 seconds  
      
      
    OK (4 tests)  

### 4、其他用法

其他用法请参考官网：[PHPUnit中国官网][4]

[0]: https://segmentfault.com/a/1190000006663788
[1]: https://getcomposer.org/
[2]: https://github.com/Seldaek/monolog
[3]: http://www.phpunit.cn/manual/current/zh_cn/installation.html
[4]: http://www.phpunit.cn/
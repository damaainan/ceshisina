# [PHPUnit-函数依赖-数据提供-异常-忽略-自动生成][0]

### 1. 本文目的

本文目的是收录一些PHPUnit的有用技巧，这些技巧能够为给PHPUnit单元测试带来很多便利。本文将要介绍的技巧如下：

* 函数依赖测试
* 数据提供函数
* 异常测试
* 跳过忽略测试
* 自动生成测试框架

### 2. 函数依赖测试

有时候，类中的函数有依赖，而且你的逻辑需要被依赖函数正确执行，此时，你可以通过phpunit的依赖标签显示的标明这种依赖关系，如果任意被依赖的函数执行失败，那么依赖函数将会被自动跳过。如下所示代码（dependenceDemo.cpp）：

 
```php

<?php
class DependanceDemo extends PHPUnit_Framework_TestCase
{
    public function testOne()
    {
        echo "testOne\n";
        $this->assertTrue(TRUE);
    }
     
    public function testTwo()
    {
        echo "testTwo\n";
        $this->assertTrue(FALSE);
    }
     
    /**
     * @depends testOne
     * @depends testTwo
     */
    public function testThree()
    {
    }
}
?>
```

上面的代码执行结果如下图：

[![clip_image002](https://images.cnblogs.com/cnblogs_com/bourneli/201209/201209082031446871.jpg "clip_image002")](http://images.cnblogs.com/cnblogs_com/bourneli/201209/201209082031436904.jpg)

可以看到，testThree依赖testOne和testTwo，但是testTwo失败，所以testThree被跳过，使用S表示。

@depends标签还可以依赖返回值。如下例子所示（paramDependence.php），

 
```php

<?php
class DependanceDemo extends PHPUnit_Framework_TestCase
{
    public function testOne()
    {
        $this->assertTrue(TRUE);
        return "testOne";
    }
     
    public function testTwo()
    {
        $this->assertTrue(TRUE);
        return "testTwo";
    }
     
    /**
     * @depends testOne
     * @depends testTwo
     */
    public function testThree($param1, $param2)
    {
        echo 'First param:  '.$param1."\n";
        echo 'Second param: '.$param2."\n";
    }
}
?>
```

上面代码的执行结果如下：

[![clip_image002[5]](https://images.cnblogs.com/cnblogs_com/bourneli/201209/201209082031449314.jpg "clip_image002[5]")](http://images.cnblogs.com/cnblogs_com/bourneli/201209/201209082031449347.jpg)

值得注意的是，函数的顺序与依赖标签的数序一致。

### 3. 数据提供函数

函数一般会有多组不同的输入参数，如果每一组参数都写一个测试函数，那么写测试比较麻烦，如果能提供一种批量的参数输入方法，那么测试代码将会简洁许多。好在，phpunit提供@dataProvider标签，支持这种特性，看如下代码(dataProviderDemo.php):

 
```php

<?php
class DataTest extends PHPUnit_Framework_TestCase
{   
    /**
     * @dataProvider provider    
     */   
     public function testAdd($a, $b, $c)   
     {
        $this->assertEquals($c, $a + $b);
    }    
    public function provider()
    {
        return array(
            array(0, 0, 0),
            array(0, 1, 1),
            array(1, 1, 1),
            array(1, 2, 3)
        );  
    }
}?>
```

上面的代码输出如下所示：

[![clip_image002[9]](https://images.cnblogs.com/cnblogs_com/bourneli/201209/201209082031459247.jpg "clip_image002[9]")](http://images.cnblogs.com/cnblogs_com/bourneli/201209/201209082031455692.jpg)

可以看到，函数testAdd遍历了函数provider的返回的结果，并将他们作为参数，被@dataProvider标记的函数的唯一要求就是返回数组。

### 4. 异常测试

PHPUnit提供三种方法测试异常，如下面代码所示（exceptionsDemo.php）：

 
```php

<?php
class ExceptionsDemo extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testTagException()
    {  
        throw new InvalidArgumentException;
    }
     
    public function testApiException()
    {
        $this->setExpectedException('InvalidArgumentException');
        throw new InvalidArgumentException;
    }
     
    public function testTryException()
    {
        try
        {
            throw new InvalidArgumentException;
        }       
        catch (InvalidArgumentException $expected)
        {           
            return;       
        }        
        $this->fail('An expected exception has not been raised.');
    }
}
?>
```

当然，这三种方法各有用处，效果等同，使用时看需要而定。

### 5. 跳过忽略测试

在编写单元测试过程中，有时候只写出了测试方法名称，没有写具体的测试内容。这样，PHPUnit框架默认的认为此测试通过，这样，我们很可能忘记了该测试方法还没有实现，如果使用$this->fail()，只能表明该测试失败，但是该测试并没有失败，令人误解。所以，我们需要PHPUnit提供一组方法，使得可以跳过没有实现的测试，并且给与正确的提示。PHPUnit提供下面这四个方法，帮助我们办到这一点：

方法

意义

void markTestSkipped()

标记当前的测试被跳过，用“S”标记

void markTestSkipped(string $message)

标记当前的测试被跳过，用“S”标记，并且输出一段示消息

void markTestIncomplete

标记当前的测试不完全，用“I”标记

void markTestIncomplete(string $message)

标记当前的测试不完全，用“I”标记，并且输出一段提示消息

下面的代码演示了上面四个方法的使用（SIMarkDemo.php）：

 
```php

<?php
class SkipIncompleteMarkDemo extends PHPUnit_Framework_TestCase
{
    public function testSkipped()
    {
        $this->markTestSkipped();
    }
     
    public function testSkippedWithMessage()
    {
        $this->markTestSkipped("this is a skipped test.");
    }
     
    public function testIncomplete()
    {
        $this->markTestIncomplete();
    }
     
    public function testIncompleteWithMessage()
    {
        $this->markTestIncomplete("this is a incomplete test.");
    }
}
?>
```

输出结果如下

[![clip_image002[11]](https://images.cnblogs.com/cnblogs_com/bourneli/201209/201209082031454514.jpg "clip_image002[11]")](http://images.cnblogs.com/cnblogs_com/bourneli/201209/201209082031455626.jpg)

### 6. 自动生成测试框架

在编写单元测试的时候，你会发现有些代码都是千篇一律的，比如testXXXX(){…..}，所以基于这种考虑，PHPUnit提供了生成测试框架的命令。该命令可以给为被测试的类中的每一个方法生成一个默认的测试方法，该方法使用markTestIncomplete标记。

如下图面的代码表示的类，

 
```php

<?php
class Calculator
{   
    public function add($a, $b)
    {       
        return $a + $b;  
    }
     
    public function minus($a, $b) 
    {       
        return $a - $b;  
    }
}
?>
```

使用如下命令：

[![clip_image002[13]](https://images.cnblogs.com/cnblogs_com/bourneli/201209/201209082031466956.jpg "clip_image002[13]")](http://images.cnblogs.com/cnblogs_com/bourneli/201209/201209082031468625.jpg)

将会生成一个类CalculatorTest.php，内容如下：

 
```php

<?php
require_once 'PHPUnit/Framework.php';
 
require_once '/home/bourneli/test/UnitTestDemo/PHPUnitFeatures/Calculator.php';
 
/**
 * Test class for Calculator.
 * Generated by PHPUnit on 2011-05-24 at 20:54:59.
 */
class CalculatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Calculator
     */
    protected $object;
 
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new Calculator;
    }
 
    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }
 
    /**
     * @todo Implement testAdd().
     */
    public function testAdd()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }
 
    /**
     * @todo Implement testMinus().
     */
    public function testMinus()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }
}
?>
```

可以看到，该框架还是比较完整的，生成了setUp，tearDown函数,还为每一个函数生成了一个测试方法。当然，phpunit还提供替他框架函数，如果想要了解更多，可以参见参考文档中的链接。

### 7. 参考文档

* 测试技巧[http://www.phpunit.de/manual/3.4/en/writing-tests-for-phpunit.html#writing-tests-for-phpunit.exceptions][1]
* 测试框架[http://www.phpunit.de/manual/3.4/en/skeleton-generator.html][2]
* 标记测试[http://www.phpunit.de/manual/3.4/en/incomplete-and-skipped-tests.html][3]

[0]: http://www.cnblogs.com/bndong/p/6633720.html
[1]: http://www.phpunit.de/manual/3.4/en/writing-tests-for-phpunit.html#writing-tests-for-phpunit.exceptions
[2]: http://www.phpunit.de/manual/3.4/en/skeleton-generator.html
[3]: http://www.phpunit.de/manual/3.4/en/incomplete-and-skipped-tests.html
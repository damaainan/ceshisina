## PHP单元测试使用

来源：[http://www.cnblogs.com/chenqionghe/p/8984157.html](http://www.cnblogs.com/chenqionghe/p/8984157.html)

时间 2018-05-03 14:03:00

 
php与其他语言不太一样，单元测试需要自己安装和配置，相对麻烦一点，不过单元测试对于提高库的稳定性和健壮性还是非常给力的，下面教大家怎么配置PHP单元测试
 
注意：php需升级到7.1版本以上
 
## 配置说明
 
## 1.全局安装phpunit命令脚本
 
``` 
$ wget https://phar.phpunit.de/phpunit-7.0.phar
$ chmod +x phpunit-7.0.phar
$ sudo mv phpunit-7.0.phar /usr/local/bin/phpunit
$ phpunit --version
PHPUnit x.y.z by Sebastian Bergmann and contributors.
```
 
## 2.全局安装安装phpunit代码
 
``` 
composer global  require phpunit/phpunit
```
 
## 3.创建 phpunit.xml放在你的项目根目录, 这个文件是 phpunit 会默认读取的一个配置文件：
 
```xml
<phpunit bootstrap="vendor/autoload.php">
    <testsuites>
        <testsuite name="service">
            <directory>tests</directory>
        </testsuite>
    </testsuites>
</phpunit>
```
 
## 4.配置phpstorm单元phpunit.phar路径，Languages & Frameworks > PHP > PHPUinit
 
![][0]
 
## 5.配置单元测试类提示,Languages & Frameworks > PHP > include path
 
![][1]
 
## 6.单元测试编写
 
#### 1.Class为Demo的测试类为DemoTest
 
#### 2.测试类继承于 PHPUnit\Framework\TestCase
 
#### 3.测试方法
 
 
* 必须为public权限， 
* 一般以test开头，也可以给其加注释@test来标识 
* 在测试方法内，类似于 assertEquals() 这样的断言方法用来对实际值与预期值的匹配做出断言。 
 
 
```php
<?php
use Eoffcn\Utils\Arrays;
use PHPUnit\Framework\TestCase;
/**
 * Array测试用例
 * Class ArraysTest
 */
class ArraysTest extends TestCase
{
    public function testGet()
    {
        $array = [
            1 => [
                'b' => [
                    'c' => 'cqh'
                ]
            ],
            2 => [
                'b' => [
                    'c' => 'cqh'
                ] ]
        ];
        $this->assertEquals('cqh', Arrays::get($array, '1.b.c'));
    }
}
```
 
## 执行单元测试
 
## 1.执行单个文件单元测试
 
Phpstorm方式，当前测试类右键Run即可
 
![][2]
 
命令行的方式，进行项目目录执行
 
  
``` 
phpunit tests/ArraysTest.php
```
 
![][3]
 
 
 
## 2.执行全局单元测试
 
phpstorm方式
 
![][4]
 
![][5]
 
命令行方式，命令行下进入当前项目执行
 
``` 
phpunit
```
 
![][6]
 


[0]: ../img/Fbaiumi.png 
[1]: ../img/amqAfqN.png 
[2]: ../img/NnMJj2V.png 
[3]: ../img/fm2iM3V.png 
[4]: ../img/mMvaEju.png 
[5]: ../img/Uj6FVvm.png 
[6]: ../img/IfuyYv7.png 
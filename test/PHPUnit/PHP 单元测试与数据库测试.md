## [PHP 单元测试与数据库测试](https://segmentfault.com/a/1190000008953673)


我总感觉 PHP 的开发者们并没有对 PHP 的质量有所追求，可能是因为 PHP 的机制问题吧，让大部分的开发者总以为浏览器访问就没有问题，所以很多时候，做 PHP 开发的，就没有单元测试的这些概念了。能不能有点追求？

我个人也是 PHP，但同时我也比较讨厌那些完事就算了的开发者，作为一个开发者，或者说是一个产品的经手人，就应该用心地去做好每个细节，一次比一次要更好。

但是做单元测试，质量检查，是需要一定的时间和人力投入的，但我敢保证地说，你花时间投入的，绝对不会是没用的，一定对你，对项目来说，是一个质的提升，只要你肯投入时间用心去做。

屁话说太多了，那接下来简单讲讲 phpunit 吧，[官网][0]。

因为我们习惯用 composer，所以我们也使用 composer 安装吧。

### 安装与配置

    $ composer require phpunit/phpunit -vvv 

安装完 phpunit，bin 执行脚本会创建在 `vendor/bin` 目录下，命名为 `phpunit`, 执行 `php vendor/bin/phpunit` 执行测试脚本

配置 bin 目录:

```
    {
      "config": {
        "bin": "bin"
      }
    }
```

配置 bin 目录产生的目录，执行 `php bin/phpunit` 脚本开始测试。

phpunit 可以配置在当前执行路径添加一个配置文件 `phpunit.xml.dist` 或者 `phpunit.xml`，内容如下:

```xml
    <phpunit
             colors="true"
             bootstrap="./vendor/autoload.php"
            >
        <testsuites>
            <testsuite>
                <directory>dir1</directory>
            </testsuite>
            <testsuite>
                <directory>dir2</directory>
            </testsuite>
        </testsuites>
    </phpunit>
```

可以通过配置目录和初始化信息，让脚本自动执行对应的测试用例。

### 基础使用

使用 PHPUnit 创建我们的测试用例:

```php
    <?php
    
    class DemoTest extends PHPUnit_Framework_TestCase
    {
         public function testPushAndPop()
            {
                $stack = [];
                $this->assertEquals(0, count($stack));
        
                array_push($stack, 'foo');
                $this->assertEquals('foo', $stack[count($stack)-1]);
                $this->assertEquals(1, count($stack));
        
                $this->assertEquals('foo', array_pop($stack));
                $this->assertEquals(0, count($stack));
            }
    }
```

类名需要以 `*Test` 结尾，继承 `PHPUnit_Framework_TestCase`。需要测试的方法需要一 `test` 开头，表明是一个测试方法。

一般常用测试无非就是 "断言"，说白了，就是看看产生的结果是不是符合预期，如果是，那就证明，已经测试通过，否则，失败，说明逻辑处理，存在一定的差异，导致不符合预期。

更多的测试使用方法请看官网用例: [PHPUnit][1]

#### 初始化

当我们的测试对象继承了 PHPUnit 后，初始化方法就需要使用它本身提供的 setUp 方法，代表类初始化，可以在初始化方法中初始化一些资源，或者加载。

### 数据库测试

除了以上基础的测试之外，关键一点应该在动态的数据，需要去测试吗，如果需要，那应该怎么去测试? 生产环境，也需要这样测试? 这个曾经困惑这我的问题，已经解开。

解答:

1. composer 中，有 --no-dev 选项，用来部署生产环境，避免测试环境的数据或者代码跑在了生产环境下。并且生产环境上数据库操作是没有很高权限的操作，要是有的话，你得回去面壁思考一下了。
1. dbunit 每次测试都重置数据，其实在生产环境下，就重置不了了，第一个是composer --no-dev 已经没有执行权利了，要是有，数据库已经不允许清空操作了。
1. 要是生产环境不需要这些东西，那么应该怎么测试。其实需要有一个模拟生产环境的测试环境，去模拟生产环境测试，当所有测试都OK没有问题，那么就可以发布到生产环境上，要是严格一些，生产环境也是需要一轮测试。
```
    $ composer require phpunit/dbunit -vvv 
```
更多测试可看: [数据库测试][2]

```php
    <?php
    class DBTest extends PHPUnit_Extensions_Database_TestCase
    {
        /**
         * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
         */
        public function getConnection()
        {
            $pdo = new PDO('mysql::dbname=test;host=127.0.0.1', 'user', 'pass');
            return $this->createDefaultDBConnection($pdo, ':memory:');
        }
    
        /**
         * @return PHPUnit_Extensions_Database_DataSet_IDataSet
         */
        public function getDataSet()
        {
            return $this->createFlatXMLDataSet(dirname(__FILE__).'/_files/guestbook-seed.xml');
        }
    }
```
`getConnection` 方法是获取数据库连接，继承数据库测试后，必须实现的一个方法，并且需要返回 `PHPUnit_Extensions_Database_DB_IDatabaseConnection` 对象，可以仿照上述写法即可。

`getDataSet` 方法是数据集，在创建数据库测试的时候，自动填充，测试，和删除。他执行的流程是，每个测试用例，都会填充一次，以保证不会被其他测试用例影响。当当前测试用例测试完成后，会 `truncate` 掉填充的数据。

数据集支持挺多种方法，可以自定义数组，yml，xml，可以根据自己的使用习惯，自定义填充数据。数据集可看: [点我][3]

执行脚本 `php vendor/bin/phpunit`然后去对应查看自己的数据表，是否多了一些填充的数据呢?

#### 抽象自己的数据库测试类

在很多情况下，我们的业务可谓是各种各样吧，倘若 phpunit 提供的数据库测试还不能满足或者不够方便的时候，就需要扩展自己的数据库测试，来达到自己想要的效果。

幸好，phpunit 提供了灵活的扩展操作(肯定啦，别人肯定不会像你这么傻，写死吧。哈哈)，我们可以很容易地去实现自己的数据库测试类。

```php
    <?php
    
    abstract class MyApp_Tests_DatabaseTestCase extends PHPUnit_Extensions_Database_TestCase
    {
        // 只实例化 pdo 一次，供测试的清理和装载基境使用
        static private $pdo = null;
    
        // 对于每个测试，只实例化 PHPUnit_Extensions_Database_DB_IDatabaseConnection 一次
        private $conn = null;
    
        final public function getConnection()
        {
            if ($this->conn === null) {
                if (self::$pdo == null) {
                    self::$pdo = new PDO('mysql::dbname=test;host=127.0.0.1', 'user', 'pass');
                }
                $this->conn = $this->createDefaultDBConnection(self::$pdo, ':memory:');
            }
    
            return $this->conn;
        }
    }
```
至今为止，完成了最基础和入门的单元测试和数据库测试，最终数据库无非就是查看数据增删改查是否和预期一样。所以，配置完数据库测试后，就可以走回第一步，编写你的测试用例，断言测试了。

恭喜你，你已经构建完自己的单元测试环境了。接下来需要做的是，提高易用性，测试覆盖率。我只能帮你到这里了，接下来的路，自己走吧。

[0]: https://phpunit.de/manual/current/zh_cn/installation.html
[1]: https://phpunit.de/manual/current/zh_cn/
[2]: https://phpunit.de/manual/current/zh_cn/database.html
[3]: https://phpunit.de/manual/current/zh_cn/database.html#database.understanding-datasets-and-datatables
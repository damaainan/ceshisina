# phpunit实践笔记

 时间 2017-07-30 23:09:00  博客园精华区

原文[http://www.cnblogs.com/z1298703836/p/7260883.html][1]


phpunit成为单元测试的代名词已成为共识， 但很多在实际编写测试过程中遇到的很多问题通过手册、网上搜索都很难找到相关资料， 大部分都得通过查看源代码和实践的代码经验解决。欢迎大家拍砖。(在此之前请先阅读手册)

## 测试private/protected方法

类的封装不可避免地会导致private/protected方法的产生，那么如何解决非public的方法？ **利用反射** ，使用php提供的相关反射接口可以设置方法的访问权限。 
```php
    <?php
        ...
    
        public function testGetCellphoneByUserId()
        {
            $userId = 10;
            $cellphone = '1333333333';
    
            $method = new \ReflectionMethod('App\UserModel', 'getCellphoneByUserId');
            $method = $method->setAccessible(true);
    
            $result = $method->invoke(new App\UserModel(), $userId); // 参数的传递方式为一个参数接着一个参数
    
            $this->assertEquel($cellphone, $result);
        }
```
除此之外, 通过创建一个新类, 继承原类并设置其方法为public也可解决该问题, 但这种解决办法存在很多限制; 如:只能改变protected的方法, 新建文件/类不但麻烦而且降低了可维护性; 相较之下, 通过反射只是代码多了两行，但是一种更好的解决办法.

## 测试数据库相关代码

phpunit不能执行数据库相关测试, 需安装另一套件完成 [DbUnit][4] , 安装完成就可使用.按照手册中的四个阶段--建立基境, 执行被测系统, 验证结果, 拆除基境, 可以搭建一个可用测试环境. 在实际项目中, 对数据库的测试就是对模型层的测试, 测试的重点在于数据库方法的(业务的增删改查)封装, 而非ORM. 除了这些概念需要区分开外, 除此之外还有很多问题需要解决。 

### 测试代码的重点

进行数据库的测试是针对数据库方法封装的测试，这是对数据库进行测试的重点。

模型:

    public function getUserById($id)
    {
        return $this->find($id);  //orm提供查询单一记录的接口find
    }

测试:

    // 测试应为对getUserId的测试
    public function testGetUserById()
    {
        ...
    
        $result = $model->getUserById($id);
    
        $this->assertequel($dataSet, $result); // $dataSet为单一记录集
    }

### 建立数据库测试环境（完整示例）

在测试类中要使用 Trait -- PHPUnit_Extensions_Database_TestCase_Trait , 支持命名空间的话为 PHPUnit\DbUnit\TestCaseTrait . 该示例中包含了所需的四个阶段. 

```php
    <?php
    
    namespace Tests;
    
    use PHPUnit_Extensions_Database_TestCase_Trait; // 可选择使用命名空间的写法
    use PHPUnit_Framework_TestCase;
    use PHPUnit_Extensions_Database_DataSet_ArrayDataSet;
    
    class UserModelTest extends PHPUnit_Framework_TestCase
    {
        use PHPUnit_Extensions_Database_TestCase_Trait; // 使用DbUnit提供的Trait就可以集成数据库测试功能
    
        /**
        * 以数组格式建立数据库基境 (DbUint测试的重要一环)
        */
        protected function getDateSet()
        {
            return new PHPUnit_Extensions_Database_DataSet_ArrayDataSet($this->getInitDataSet());
        }
    
        /**
        * 返回PDO对象 (DbUint测试的重要一环)
        */
        protected function getConnection()
        {
            $pdo; // 一般的ORM中基本上都可以获取PDO对象, 直接返回即可
    
            return $this->createDefaultDBConnection($pdo, 'schemaName'); // 可能需要指定库名
        }
    
        /**
        * 测试方法
        */
        public function testGetUserById()
        {
            $user = new User();
            $id = $this->getInitDataSet()['user'][0]['id']; // 取值得根据基境数据而来
    
            $result = $user->getUserById($id);
    
            $this->assertEquel($this->getUserByIdDataSet(), $result);
        }
    
        /**
        * 数据库的初始化数据, 即每次测试之前, 数据库里的数据集就是该基境数据
        */
        private function getInitDataSet()
        {
            return [
                'user' => [
                    [
                       'id' => 1,
                       'name' => 'joy',
                    ]
                ],
            ];
        }
    
        /**
        * 与通过模型层查询出来的数据进行对比
        */
        private function getUserByIdDataSet()
        {
            return $this->getInitDataSet()['user'][0];
        }
    }
```
注意:

1. 不需要setUp 和tearDown 方法   
在 PHPUnit_Extensions_Database_TestCase_Trait 中已有setUp和taerDown方法, 再写则会 [覆盖trait中的方法][5] , 而且与基境相关的操作都已集成在trait中, 不能被覆盖, 即不能在本类中重写setUp和tearDown.
1. setUp建立了基境   
查看 PHPUnit_Extensions_Database_TestCase_Trait 源码, setUp完成了truncate, insert基境数据, 这样的数据库操作就初始化了数据库数据.
1. tearDown拆除了基境   
查看 PHPUnit_Extensions_Database_TestCase_Trait 源码, tearDown保持了数据库数据仍是基境数据.

### 如何建立基境

基境在示例中为数组格式, 格式一定是这样的: 表名做为键值, 表记录以数组表示, 且其中字段名为键名.数组为最方便的. 可以把基境理解成一个存在于内存中的数据库, 只不过查询或更改的数据集是手动代码构建而非数据库执行语句而来.

1. 一个模型测试中基境只建立一个表数据   
在一个模型中测试多表数据会增加测试的复杂性, 最好是只测试一个模型. 这与代码实现有关, 代码编写需只操作一个数据表.
1. 对表数据进行断言   
基境为测试的集合,所有进行断言的数据集都需在基境中, 否则容易可能出现数据错误. 示例中:查询的id值在基境数据中获得, 断言集合同样也在基境中获得; 当然, 可以把获取数据的方法进行封装以增强可读性;
1. 对多表数据进行测试   
上述提到, 只对一个数据表进行测试, 若对多个表进行测试不可避免, 如何解决? 只能建立另一表的基境数据,同时要构建多个模型所需断言的数据集.
1. 构建基境相关代码会越来越多   
对数据库测试的方法越多, 其需进行断言的数据集越多, 所有这些集合都需代码构建, 构建代码也会越来越多. 面对这种情况唯一能做的就是良好的命名和代码封装, 以达到多但不乱.

## 如何隔离开发环境

针对模型层的测试会直接对数据库进行增删改查, 所以不可避免的会出现调试/错误数据, 还有基境的构建, 这些属性就决定了数据库的测试不能在除开发环境外的其他任何环境使用. 若开发与测试共用一套数据库实例, 一定要考虑数据紊乱造成的错误, 由于数据错误的排错过程十分头疼。所以针对开发环境与测试的建议是:要么新建一个开发实例, 要么还是不要写数据库相关的测试了。

可以将个问题延伸一下，如何在生产环境使用开发环境的测试？

生产环境与通过测试的代码完全一致，不一样的就是数据。不能直接在生产环境下进行测试， 同时数据也是不能测试的，所以能在测试范围内的操作就是集成测试了。这一点可以通过Gitlab完成，而测试实例只有模拟的http请求即可。

## 如何测试无返回值的方法

方法中的代码之所以可以被封装成一个方法, 是因为它必然是执行了某一段逻辑, 那这样代码集合必然会改变某些值或状态, 所以测试代码的编写需 **找出这个变化的值** 来进行断言. 如: 

1. 删除了某条数据库的记录, 而没有返回值; 查询数据库, 断言无该记录.
1. 对一个数组元素进行了排序, 而没有返回值; 遍历数据, 断言一个比一个大或一个比一个小.
1. 更新了缓存; 断言更新前后, 有过期时间则断言过期时间为最新.

## 对一个类中的所有方法进行上桩

在手册中给出了很多示例, 都单个/具体的方法进行上桩,由此也容易理解桩的概念--改写类中方法的行为. 上桩就是对类进行了一次继承形成一个新对象, 从而改写了方法的行为 .但需要对所有方法进行上桩,即屏蔽整个类的操作时,如何处理? 应该对所有方法进行上桩. 

    $mock = $this->getMockBuilder(\Redis::class)
                ->disableOriginalConstructor()
                ->getMock();
    
    $mock->expects($this->any()) // 并不限定执行次数
        ->method(new \PHPUnit_Framework_Constraint_StringMatches("%a")) // 通过正则完成匹配
        ->willReturn(false);

method 方法可以接受抽象类 PHPUnit_Framework_Constraint 和字符串, PHPUnit_Framework_Constraint_StringMatches 则是字符串匹配的具体类, 其匹配算法也在该类中. 

## 小结

以上遇到的问题都是的编写代码时遇到的问题，其他问题也还在整理中，也不断遇到新的问题，待问题整理好了，我也会更新上来，欢迎交流拍砖。

## 参考资料：

开启method访问权限: [http://php.net/manual/en/reflectionmethod.setaccessible.php][6]

执行反射method: [http://php.net/manual/en/reflectionmethod.invoke.php][7]


[1]: http://www.cnblogs.com/z1298703836/p/7260883.html
[4]: https://phpunit.de/manual/current/zh_cn/database.html
[5]: http://www.php.net/manual/zh/language.oop5.traits.php
[6]: http://php.net/manual/en/reflectionmethod.setaccessible.php
[7]: http://php.net/manual/en/reflectionmethod.invoke.php
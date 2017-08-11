# PHP 设计模式系列 —— 数据映射模式（Data Mapper）

 Posted on [2015年12月22日2015年12月23日][0] by [学院君][1]

### **1、模式定义**

在了解[数据映射模式][2]之前，先了解下数据映射，它是在持久化数据存储层（通常是关系型[数据库][3]）和驻于内存的数据表现层之间进行双向数据传输的数据访问层。

数据映射模式的目的是让持久化数据存储层、驻于内存的数据表现层、以及数据映射本身三者相互独立、互不依赖。这个数据访问层由一个或多个映射器（或者数据访问对象）组成，用于实现数据传输。通用的数据访问层可以处理不同的实体类型，而专用的则处理一个或几个。

数据映射模式的核心在于它的数据模型遵循单一职责原则（Single Responsibility Principle）, 这也是和 Active Record 模式的不同之处。最典型的数据映射模式例子就是数据库 [ORM][4] 模型 （Object Relational Mapper）。

> 注：更多关于 Data Mapper 与 Active Record 的区别与联系请查看这篇文章了解更多 —— [> http://laravelacademy.org/post/966.html][5]

准确来说该模式是个[架构模式][6]。

### **2、UML类图**

![data-mapper-design-pattern][7]

### **3、示例代码**

#### **User.php**

```php
<?php

namespace DesignPatterns\Structural\DataMapper;

/**
 *
 * 这是数据库记录在内存的表现层
 *
 * 验证也在该对象中进行
 *
 */
class User
{
    /**
     * @var int
     */
    protected $userId;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $email;

    /**
     * @param null $id
     * @param null $username
     * @param null $email
     */
    public function __construct($id = null, $username = null, $email = null)
    {
        $this->userId = $id;
        $this->username = $username;
        $this->email = $email;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     */
    public function setUserID($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }
}
```

#### **UserMapper.php**

```php
<?php

namespace DesignPatterns\Structural\DataMapper;

/**
 * UserMapper类（数据映射类）
 */
class UserMapper
{
    /**
     * @var DBAL
     */
    protected $adapter;

    /**
     * @param DBAL $dbLayer
     */
    public function __construct(DBAL $dbLayer)
    {
        $this->adapter = $dbLayer;
    }

    /**
     * 将用户对象保存到数据库
     *
     * @param User $user
     *
     * @return boolean
     */
    public function save(User $user)
    {
        /* $data的键名对应数据库表字段 */
        $data = array(
            'userid'   => $user->getUserId(),
            'username' => $user->getUsername(),
            'email'    => $user->getEmail(),
        );

        /* 如果没有指定ID则在数据库中创建新纪录，否则更新已有记录 */
        if (null === ($id = $user->getUserId())) {
            unset($data['userid']);
            $this->adapter->insert($data);

            return true;
        } else {
            $this->adapter->update($data, array('userid = ?' => $id));

            return true;
        }
    }

    /**
     * 基于ID在数据库中查找用户并返回用户实例
     *
     * @param int $id
     *
     * @throws \InvalidArgumentException
     * @return User
     */
    public function findById($id)
    {
        $result = $this->adapter->find($id);

        if (0 == count($result)) {
            throw new \InvalidArgumentException("User #$id not found");
        }
        $row = $result->current();

        return $this->mapObject($row);
    }

    /**
     * 获取数据库所有记录并返回用户实例数组
     *
     * @return array
     */
    public function findAll()
    {
        $resultSet = $this->adapter->findAll();
        $entries   = array();

        foreach ($resultSet as $row) {
            $entries[] = $this->mapObject($row);
        }

        return $entries;
    }

    /**
     * 映射表记录到对象
     *
     * @param array $row
     *
     * @return User
     */
    protected function mapObject(array $row)
    {
        $entry = new User();
        $entry->setUserID($row['userid']);
        $entry->setUsername($row['username']);
        $entry->setEmail($row['email']);

        return $entry;
    }
}
```


### **4、测试代码**

#### **Tests/DataMapperTest.php**

```php
<?php

namespace DesignPatterns\Structural\DataMapper\Tests;

use DesignPatterns\Structural\DataMapper\UserMapper;
use DesignPatterns\Structural\DataMapper\User;

/**
 * UserMapperTest用于测试数据映射模式
 */
class DataMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UserMapper
     */
    protected $mapper;

    /**
     * @var DBAL
     */
    protected $dbal;

    protected function setUp()
    {
        $this->dbal = $this->getMockBuilder('DesignPatterns\Structural\DataMapper\DBAL')
                ->disableAutoload()
                ->setMethods(array('insert', 'update', 'find', 'findAll'))
                ->getMock();

        $this->mapper = new UserMapper($this->dbal);
    }

    public function getNewUser()
    {
        return array(array(new User(null, 'Odysseus', 'Odysseus@ithaca.gr')));
    }

    public function getExistingUser()
    {
        return array(array(new User(1, 'Odysseus', 'Odysseus@ithaca.gr')));
    }

    /**
     * @dataProvider getNewUser
     */
    public function testPersistNew(User $user)
    {
        $this->dbal->expects($this->once())
                ->method('insert');
        $this->mapper->save($user);
    }

    /**
     * @dataProvider getExistingUser
     */
    public function testPersistExisting(User $user)
    {
        $this->dbal->expects($this->once())
                ->method('update');
        $this->mapper->save($user);
    }

    /**
     * @dataProvider getExistingUser
     */
    public function testRestoreOne(User $existing)
    {
        $row = array(
            'userid'   => 1,
            'username' => 'Odysseus',
            'email'    => 'Odysseus@ithaca.gr'
        );
        $rows = new \ArrayIterator(array($row));
        $this->dbal->expects($this->once())
                ->method('find')
                ->with(1)
                ->will($this->returnValue($rows));

        $user = $this->mapper->findById(1);
        $this->assertEquals($existing, $user);
    }

    /**
     * @dataProvider getExistingUser
     */
    public function testRestoreMulti(User $existing)
    {
        $rows = array(array('userid' => 1, 'username' => 'Odysseus', 'email' => 'Odysseus@ithaca.gr'));
        $this->dbal->expects($this->once())
                ->method('findAll')
                ->will($this->returnValue($rows));

        $user = $this->mapper->findAll();
        $this->assertEquals(array($existing), $user);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage User #404 not found
     */
    public function testNotFound()
    {
        $this->dbal->expects($this->once())
                ->method('find')
                ->with(404)
                ->will($this->returnValue(array()));

        $user = $this->mapper->findById(404);
    }
}
```

[0]: http://laravelacademy.org/post/2739.html
[1]: http://laravelacademy.org/post/author/nonfu
[2]: http://laravelacademy.org/tags/%e6%95%b0%e6%8d%ae%e6%98%a0%e5%b0%84%e6%a8%a1%e5%bc%8f
[3]: http://laravelacademy.org/tags/%e6%95%b0%e6%8d%ae%e5%ba%93
[4]: http://laravelacademy.org/tags/orm
[5]: http://laravelacademy.org/post/966.html
[6]: http://laravelacademy.org/tags/%e6%9e%b6%e6%9e%84%e6%a8%a1%e5%bc%8f
[7]: ../img/data-mapper-design-pattern.png
[8]: http://laravelacademy.org/tags/php
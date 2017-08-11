# PHP 设计模式系列 —— 流接口模式（Fluent Interface）

 Posted on [2015年12月28日][0] by [学院君][1]

### **1、模式定义**

在软件工程中，流接口（Fluent Interface）是指实现一种面向对象的、能提高代码可读性的 API 的方法，其目的就是可以编写具有自然语言一样可读性的代码，我们对这种代码编写方式还有一个通俗的称呼 —— [方法链][2]。

Laravel 中[流接口模式][3]有着广泛使用，比如查询构建器，邮件等等。

### **2、UML 类图**

![Fluent-Interface-UML][4]

### **3、示例代码**

#### **Sql.php**

```php
<?php

namespace DesignPatterns\Structural\FluentInterface;

/**
 * SQL 类
 */
class Sql
{
    /**
     * @var array
     */
    protected $fields = array();

    /**
     * @var array
     */
    protected $from = array();

    /**
     * @var array
     */
    protected $where = array();

    /**
     * 添加 select 字段
     *
     * @param array $fields
     *
     * @return SQL
     */
    public function select(array $fields = array())
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * 添加 FROM 子句
     *
     * @param string $table
     * @param string $alias
     *
     * @return SQL
     */
    public function from($table, $alias)
    {
        $this->from[] = $table . ' AS ' . $alias;

        return $this;
    }

    /**
     * 添加 WHERE 条件
     *
     * @param string $condition
     *
     * @return SQL
     */
    public function where($condition)
    {
        $this->where[] = $condition;

        return $this;
    }

    /**
     * 生成查询语句
     *
     * @return string
     */
    public function getQuery()
    {
        return 'SELECT ' . implode(',', $this->fields)
                . ' FROM ' . implode(',', $this->from)
                . ' WHERE ' . implode(' AND ', $this->where);
    }
}
```

### **4、测试代码**

#### **Tests/FluentInterfaceTest.php**

```php
<?php

namespace DesignPatterns\Structural\FluentInterface\Tests;

use DesignPatterns\Structural\FluentInterface\Sql;

/**
 * FluentInterfaceTest 测试流接口SQL
 */
class FluentInterfaceTest extends \PHPUnit_Framework_TestCase
{

    public function testBuildSQL()
    {
        $instance = new Sql();
        $query = $instance->select(array('foo', 'bar'))
                ->from('foobar', 'f')
                ->where('f.bar = ?')
                ->getQuery();

        $this->assertEquals('SELECT foo,bar FROM foobar AS f WHERE f.bar = ?', $query);
    }
}
```

[0]: http://laravelacademy.org/post/2828.html
[1]: http://laravelacademy.org/post/author/nonfu
[2]: http://laravelacademy.org/tags/%e6%96%b9%e6%b3%95%e9%93%be
[3]: http://laravelacademy.org/tags/%e6%b5%81%e6%8e%a5%e5%8f%a3%e6%a8%a1%e5%bc%8f
[4]: ../img/Fluent-Interface-UML.png
[5]: http://laravelacademy.org/tags/php
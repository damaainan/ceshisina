==
## 断言
==

* 断言方法的用法：静态 vs. 非静态
    * assertArrayHasKey()
    * assertClassHasAttribute()
    * assertArraySubset()
    * assertClassHasStaticAttribute()
    * assertContains()
    * assertContainsOnly()
    * assertContainsOnlyInstancesOf()
    * assertCount()
    * assertDirectoryExists()
    * assertDirectoryIsReadable()
    * assertDirectoryIsWritable()
    * assertEmpty()
    * assertEqualXMLStructure()
    * assertEquals()
    * assertFalse()
    * assertFileEquals()
    * assertFileExists()
    * assertFileIsReadable()
    * assertFileIsWritable()
    * assertGreaterThan()
    * assertGreaterThanOrEqual()
    * assertInfinite()
    * assertInstanceOf()
    * assertInternalType()
    * assertIsReadable()
    * assertIsWritable()
    * assertJsonFileEqualsJsonFile()
    * assertJsonStringEqualsJsonFile()
    * assertJsonStringEqualsJsonString()
    * assertLessThan()
    * assertLessThanOrEqual()
    * assertNull()
    * assertObjectHasAttribute()
    * assertRegExp()
    * assertStringMatchesFormat()
    * assertStringMatchesFormatFile()
    * assertSame()
    * assertStringEndsWith()
    * assertStringEqualsFile()
    * assertStringStartsWith()
    * assertThat()
    * assertTrue()
    * assertXmlFileEqualsXmlFile()
    * assertXmlStringEqualsXmlFile()
    * assertXmlStringEqualsXmlString()


本附录列举可用的各种断言方法。


## 断言方法的用法：静态 vs. 非静态

PHPUnit 的各个断言是在 PHPUnit\\Framework\\Assert 中实现的。PHPUnit\\Framework\\TestCase 则继承于 PHPUnit\\Framework\\Assert。

各个断言方法均声明为 static，可以从任何上下文以类似于 PHPUnit\\Framework\\Assert::assertTrue() 的方式调用，或者也可以用类似于 $this->assertTrue() 或 self::assertTrue() 的方式在扩展自 PHPUnit\\Framework\\TestCase 的类内调用。

实际上，只要（手工）包含了 PHPUnit 中的 :file:`src/Framework/Assert/Functions.php` 源码文件，甚至可以在任何上下文中（甚至包括扩展自 PHPUnit\\Framework\\TestCase 的类中）以诸如 assertTrue() 这样的方式来调用全局函数封装。

有个常见的疑问——对于那些 PHPUnit 的新手尤甚——是究竟应该用诸如 $this->assertTrue() 还是诸如 self::assertTrue() 这样的形式来调用断言才是“正确的方式”？简而言之：没有正确方式。同时，也没有错误方式。这基本上是个人喜好问题。

对于大多数人而言，由于测试方法是在测试对象上调用，因此用 $this->assertTrue() 会“觉的更正确”。然而请记住断言方法是声明为 static 的，这使其可以（重）用于测试对象的作用域之外。最后，全局函数封装让开发者能再少打一些字（用 assertTrue() 代替 $this->assertTrue() 或者 self::assertTrue()）。


### assertArrayHasKey()

``assertArrayHasKey(mixed $key, array $array[, string $message = ''])``

当 ``$array`` 不包含 ``$key`` 时报告错误，错误讯息由 ``$message`` 指定。

``assertArrayNotHasKey()`` 是与之相反的断言，接受相同的参数。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class ArrayHasKeyTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertArrayHasKey('foo', ['bar' => 'baz']);
        }
    }
    ?>
```

    $ phpunit ArrayHasKeyTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 5.00Mb

    There was 1 failure:

    1) ArrayHasKeyTest::testFailure
    Failed asserting that an array has the key 'foo'.

    /home/sb/ArrayHasKeyTest.php:6

    FAILURES!
    Tests: 1, Assertions: 1, Failures: 1.


### assertClassHasAttribute()

``assertClassHasAttribute(string $attributeName, string $className[, string $message = ''])``

当 ``$className::attributeName`` 不存在时报告错误，错误讯息由 ``$message`` 指定。

``assertClassNotHasAttribute()`` 是与之相反的断言，接受相同的参数。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class ClassHasAttributeTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertClassHasAttribute('foo', stdClass::class);
        }
    }
    ?>
```

    $ phpunit ClassHasAttributeTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 4.75Mb

    There was 1 failure:

    1) ClassHasAttributeTest::testFailure
    Failed asserting that class "stdClass" has attribute "foo".

    /home/sb/ClassHasAttributeTest.php:6

    FAILURES!
    Tests: 1, Assertions: 1, Failures: 1.


### assertArraySubset()

``assertArraySubset(array $subset, array $array[, bool $strict = '', string $message = ''])``

当 ``$array`` 不包含 ``$subset`` 时报告错误，错误讯息由 ``$message`` 指定。

``$strict`` 是一个标志，用于表明是否需要对数组中的对象进行全等判定。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class ArraySubsetTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertArraySubset(['config' => ['key-a', 'key-b']], ['config' => ['key-a']]);
        }
    }
    ?>
```

    $ phpunit ArrayHasKeyTest
    PHPUnit 4.4.0 by Sebastian Bergmann.

    F

    Time: 0 seconds, Memory: 5.00Mb

    There was 1 failure:

    1) Epilog\EpilogTest::testNoFollowOption
    Failed asserting that an array has the subset Array &0 (
        'config' => Array &1 (
            0 => 'key-a'
            1 => 'key-b'
        )
    ).

    /home/sb/ArraySubsetTest.php:6

    FAILURES!
    Tests: 1, Assertions: 1, Failures: 1.


### assertClassHasStaticAttribute()

``assertClassHasStaticAttribute(string $attributeName, string $className[, string $message = ''])``

当 ``$className::attributeName`` 不存在时报告错误，错误讯息由 ``$message`` 指定。

``assertClassNotHasStaticAttribute()`` 是与之相反的断言，接受相同的参数。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class ClassHasStaticAttributeTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertClassHasStaticAttribute('foo', stdClass::class);
        }
    }
    ?>
```

    $ phpunit ClassHasStaticAttributeTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 4.75Mb

    There was 1 failure:

    1) ClassHasStaticAttributeTest::testFailure
    Failed asserting that class "stdClass" has static attribute "foo".

    /home/sb/ClassHasStaticAttributeTest.php:6

    FAILURES!
    Tests: 1, Assertions: 1, Failures: 1.


### assertContains()

``assertContains(mixed $needle, Iterator|array $haystack[, string $message = ''])``

当 ``$needle`` 不是 ``$haystack``的元素时报告错误，错误讯息由 ``$message`` 指定。

``assertNotContains()`` 是与之相反的断言，接受相同的参数。

``assertAttributeContains()`` 和 ``assertAttributeNotContains()`` 是便捷包装(convenience wrapper)，以某个类或对象的 ``public``、``protected`` 或 ``private`` 属性为搜索范围。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class ContainsTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertContains(4, [1, 2, 3]);
        }
    }
    ?>
```

    $ phpunit ContainsTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 5.00Mb

    There was 1 failure:

    1) ContainsTest::testFailure
    Failed asserting that an array contains 4.

    /home/sb/ContainsTest.php:6

    FAILURES!
    Tests: 1, Assertions: 1, Failures: 1.

``assertContains(string $needle, string $haystack[, string $message = '', boolean $ignoreCase = false])``

当 ``$needle`` 不是 ``$haystack`` 的子字符串时报告错误，错误讯息由 ``$message`` 指定。

如果 ``$ignoreCase`` 为 ``true``，测试将按大小写不敏感的方式进行。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class ContainsTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertContains('baz', 'foobar');
        }
    }
    ?>
```

    $ phpunit ContainsTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 5.00Mb

    There was 1 failure:

    1) ContainsTest::testFailure
    Failed asserting that 'foobar' contains "baz".

    /home/sb/ContainsTest.php:6

    FAILURES!
    Tests: 1, Assertions: 1, Failures: 1.

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class ContainsTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertContains('foo', 'FooBar');
        }

        public function testOK()
        {
            $this->assertContains('foo', 'FooBar', '', true);
        }
    }
    ?>
```

    $ phpunit ContainsTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F.

    Time: 0 seconds, Memory: 2.75Mb

    There was 1 failure:

    1) ContainsTest::testFailure
    Failed asserting that 'FooBar' contains "foo".

    /home/sb/ContainsTest.php:6

    FAILURES!
    Tests: 2, Assertions: 2, Failures: 1.


### assertContainsOnly()

``assertContainsOnly(string $type, Iterator|array $haystack[, boolean $isNativeType = null, string $message = ''])``

当 ``$haystack`` 并非仅包含类型为 ``$type`` 的变量时报告错误，错误讯息由 ``$message`` 指定。

``$isNativeType`` 是一个标志，用来表明 ``$type`` 是否是原生 PHP 类型。

``assertNotContainsOnly()`` 是与之相反的断言，并接受相同的参数。

``assertAttributeContainsOnly()`` 和 ``assertAttributeNotContainsOnly()`` 是便捷包装(convenience wrapper)，以某个类或对象的 ``public``、``protected`` 或 ``private`` 属性为搜索范围。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class ContainsOnlyTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertContainsOnly('string', ['1', '2', 3]);
        }
    }
    ?>
```

    $ phpunit ContainsOnlyTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 5.00Mb

    There was 1 failure:

    1) ContainsOnlyTest::testFailure
    Failed asserting that Array (
        0 => '1'
        1 => '2'
        2 => 3
    ) contains only values of type "string".

    /home/sb/ContainsOnlyTest.php:6

    FAILURES!
    Tests: 1, Assertions: 1, Failures: 1.


### assertContainsOnlyInstancesOf()

``assertContainsOnlyInstancesOf(string $classname, Traversable|array $haystack[, string $message = ''])``

当 ``$haystack`` 并非仅包含类 ``$classname`` 的实例时报告错误，错误讯息由 ``$message`` 指定。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class ContainsOnlyInstancesOfTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertContainsOnlyInstancesOf(
                Foo::class,
                [new Foo, new Bar, new Foo]
            );
        }
    }
    ?>
```

    $ phpunit ContainsOnlyInstancesOfTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 5.00Mb

    There was 1 failure:

    1) ContainsOnlyInstancesOfTest::testFailure
    Failed asserting that Array ([0]=> Bar Object(...)) is an instance of class "Foo".

    /home/sb/ContainsOnlyInstancesOfTest.php:6

    FAILURES!
    Tests: 1, Assertions: 1, Failures: 1.


### assertCount()

``assertCount($expectedCount, $haystack[, string $message = ''])``

当 ``$haystack`` 中的元素数量不是 ``$expectedCount`` 时报告错误，错误讯息由 ``$message`` 指定。

``assertNotCount()`` 是与之相反的断言，接受相同的参数。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class CountTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertCount(0, ['foo']);
        }
    }
    ?>
```

    $ phpunit CountTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 4.75Mb

    There was 1 failure:

    1) CountTest::testFailure
    Failed asserting that actual size 1 matches expected size 0.

    /home/sb/CountTest.php:6

    FAILURES!
    Tests: 1, Assertions: 1, Failures: 1.


### assertDirectoryExists()

``assertDirectoryExists(string $directory[, string $message = ''])``

当 ``$directory`` 所指定的目录不存在时报告错误，错误讯息由 ``$message`` 指定。

``assertDirectoryNotExists()`` 是与之相反的断言，并接受相同的参数。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class DirectoryExistsTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertDirectoryExists('/path/to/directory');
        }
    }
    ?>
```

    $ phpunit DirectoryExistsTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 4.75Mb

    There was 1 failure:

    1) DirectoryExistsTest::testFailure
    Failed asserting that directory "/path/to/directory" exists.

    /home/sb/DirectoryExistsTest.php:6

    FAILURES!
    Tests: 1, Assertions: 1, Failures: 1.


### assertDirectoryIsReadable()

``assertDirectoryIsReadable(string $directory[, string $message = ''])``

当 ``$directory`` 所指定的目录不是个目录或不可读时报告错误，错误讯息由 ``$message`` 指定。

``assertDirectoryNotIsReadable()`` 是与之相反的断言，并接受相同的参数。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class DirectoryIsReadableTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertDirectoryIsReadable('/path/to/directory');
        }
    }
    ?>
```

    $ phpunit DirectoryIsReadableTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 4.75Mb

    There was 1 failure:

    1) DirectoryIsReadableTest::testFailure
    Failed asserting that "/path/to/directory" is readable.

    /home/sb/DirectoryIsReadableTest.php:6

    FAILURES!
    Tests: 1, Assertions: 1, Failures: 1.


### assertDirectoryIsWritable()

``assertDirectoryIsWritable(string $directory[, string $message = ''])``

当 ``$directory`` 所指定的目录不是个目录或不可写时报告错误，错误讯息由 ``$message`` 指定。

``assertDirectoryNotIsWritable()`` 是与之相反的断言，并接受相同的参数。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class DirectoryIsWritableTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertDirectoryIsWritable('/path/to/directory');
        }
    }
    ?>
```

    $ phpunit DirectoryIsWritableTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 4.75Mb

    There was 1 failure:

    1) DirectoryIsWritableTest::testFailure
    Failed asserting that "/path/to/directory" is writable.

    /home/sb/DirectoryIsWritableTest.php:6

    FAILURES!
    Tests: 1, Assertions: 1, Failures: 1.


### assertEmpty()

``assertEmpty(mixed $actual[, string $message = ''])``

当 ``$actual`` 非空时报告错误，错误讯息由 ``$message`` 指定。

``assertNotEmpty()`` 是与之相反的断言，接受相同的参数。

``assertAttributeEmpty()`` 和 ``assertAttributeNotEmpty()`` 是便捷包装(convenience wrapper)，可以应用于某个类或对象的某个 ``public``、``protected`` 或 ``private`` 属性。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class EmptyTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertEmpty(['foo']);
        }
    }
    ?>
```

    $ phpunit EmptyTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 4.75Mb

    There was 1 failure:

    1) EmptyTest::testFailure
    Failed asserting that an array is empty.

    /home/sb/EmptyTest.php:6

    FAILURES!
    Tests: 1, Assertions: 1, Failures: 1.


### assertEqualXMLStructure()

``assertEqualXMLStructure(DOMElement $expectedElement, DOMElement $actualElement[, boolean $checkAttributes = false, string $message = ''])``

当 ``$actualElement`` 中 DOMElement 的 XML 结构与 ``$expectedElement`` 中 DOMElement的 XML 结构不相同时报告错误，错误讯息由 ``$message`` 指定。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class EqualXMLStructureTest extends TestCase
    {
        public function testFailureWithDifferentNodeNames()
        {
            $expected = new DOMElement('foo');
            $actual = new DOMElement('bar');

            $this->assertEqualXMLStructure($expected, $actual);
        }

        public function testFailureWithDifferentNodeAttributes()
        {
            $expected = new DOMDocument;
            $expected->loadXML('<foo bar="true" />');

            $actual = new DOMDocument;
            $actual->loadXML('<foo/>');

            $this->assertEqualXMLStructure(
              $expected->firstChild, $actual->firstChild, true
            );
        }

        public function testFailureWithDifferentChildrenCount()
        {
            $expected = new DOMDocument;
            $expected->loadXML('<foo><bar/><bar/><bar/></foo>');

            $actual = new DOMDocument;
            $actual->loadXML('<foo><bar/></foo>');

            $this->assertEqualXMLStructure(
              $expected->firstChild, $actual->firstChild
            );
        }

        public function testFailureWithDifferentChildren()
        {
            $expected = new DOMDocument;
            $expected->loadXML('<foo><bar/><bar/><bar/></foo>');

            $actual = new DOMDocument;
            $actual->loadXML('<foo><baz/><baz/><baz/></foo>');

            $this->assertEqualXMLStructure(
              $expected->firstChild, $actual->firstChild
            );
        }
    }
    ?>
```

    $ phpunit EqualXMLStructureTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    FFFF

    Time: 0 seconds, Memory: 5.75Mb

    There were 4 failures:

    1) EqualXMLStructureTest::testFailureWithDifferentNodeNames
    Failed asserting that two strings are equal.
    --- Expected
    +++ Actual
    @@ @@
    -'foo'
    +'bar'

    /home/sb/EqualXMLStructureTest.php:9

    2) EqualXMLStructureTest::testFailureWithDifferentNodeAttributes
    Number of attributes on node "foo" does not match
    Failed asserting that 0 matches expected 1.

    /home/sb/EqualXMLStructureTest.php:22

    3) EqualXMLStructureTest::testFailureWithDifferentChildrenCount
    Number of child nodes of "foo" differs
    Failed asserting that 1 matches expected 3.

    /home/sb/EqualXMLStructureTest.php:35

    4) EqualXMLStructureTest::testFailureWithDifferentChildren
    Failed asserting that two strings are equal.
    --- Expected
    +++ Actual
    @@ @@
    -'bar'
    +'baz'

    /home/sb/EqualXMLStructureTest.php:48

    FAILURES!
    Tests: 4, Assertions: 8, Failures: 4.


### assertEquals()

``assertEquals(mixed $expected, mixed $actual[, string $message = ''])``

当两个变量 ``$expected`` 和 ``$actual`` 不相等时报告错误，错误讯息由 ``$message`` 指定。

``assertNotEquals()`` 是与之相反的断言，接受相同的参数。

``assertAttributeEquals()`` 和 ``assertAttributeNotEquals()`` 是便捷包装(convenience wrapper)，以某个类或对象的某个 ``public``、``protected`` 或 ``private`` 属性作为实际值来进行比较。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class EqualsTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertEquals(1, 0);
        }

        public function testFailure2()
        {
            $this->assertEquals('bar', 'baz');
        }

        public function testFailure3()
        {
            $this->assertEquals("foo\nbar\nbaz\n", "foo\nbah\nbaz\n");
        }
    }
    ?>
```

    $ phpunit EqualsTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    FFF

    Time: 0 seconds, Memory: 5.25Mb

    There were 3 failures:

    1) EqualsTest::testFailure
    Failed asserting that 0 matches expected 1.

    /home/sb/EqualsTest.php:6

    2) EqualsTest::testFailure2
    Failed asserting that two strings are equal.
    --- Expected
    +++ Actual
    @@ @@
    -'bar'
    +'baz'

    /home/sb/EqualsTest.php:11

    3) EqualsTest::testFailure3
    Failed asserting that two strings are equal.
    --- Expected
    +++ Actual
    @@ @@
     'foo
    -bar
    +bah
     baz
     '

    /home/sb/EqualsTest.php:16

    FAILURES!
    Tests: 3, Assertions: 3, Failures: 3.

如果 ``$expected`` 和 ``$actual`` 是某些特定的类型，将使用更加专门的比较方式，参阅下文。

``assertEquals(float $expected, float $actual[, string $message = '', float $delta = 0])``

当两个浮点数 ``$expected`` 和 ``$actual`` 之间的差值（的绝对值）大于 ``$delta`` 时报告错误，错误讯息由 ``$message`` 指定。

关于为什么 ``$delta`` 参数是必须的，请阅读《`关于浮点运算，每一位计算机科学从业人员都应该知道的事实 <http://docs.oracle.com/cd/E19957-01/806-3568/ncg_goldberg.html>`_》。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class EqualsTest extends TestCase
    {
        public function testSuccess()
        {
            $this->assertEquals(1.0, 1.1, '', 0.2);
        }

        public function testFailure()
        {
            $this->assertEquals(1.0, 1.1);
        }
    }
    ?>
```

    $ phpunit EqualsTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    .F

    Time: 0 seconds, Memory: 5.75Mb

    There was 1 failure:

    1) EqualsTest::testFailure
    Failed asserting that 1.1 matches expected 1.0.

    /home/sb/EqualsTest.php:11

    FAILURES!
    Tests: 2, Assertions: 2, Failures: 1.

``assertEquals(DOMDocument $expected, DOMDocument $actual[, string $message = ''])``

当 ``$expected`` 和 ``$actual`` 这两个 DOMDocument 对象所表示的 XML 文档对应的无注释规范形式不相同时报告错误，错误讯息由 ``$message`` 指定。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class EqualsTest extends TestCase
    {
        public function testFailure()
        {
            $expected = new DOMDocument;
            $expected->loadXML('<foo><bar/></foo>');

            $actual = new DOMDocument;
            $actual->loadXML('<bar><foo/></bar>');

            $this->assertEquals($expected, $actual);
        }
    }
    ?>
```

    $ phpunit EqualsTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 5.00Mb

    There was 1 failure:

    1) EqualsTest::testFailure
    Failed asserting that two DOM documents are equal.
    --- Expected
    +++ Actual
    @@ @@
     <?xml version="1.0"?>
    -<foo>
    -  <bar/>
    -</foo>
    +<bar>
    +  <foo/>
    +</bar>

    /home/sb/EqualsTest.php:12

    FAILURES!
    Tests: 1, Assertions: 1, Failures: 1.

``assertEquals(object $expected, object $actual[, string $message = ''])``

当 ``$expected`` 和 ``$actual`` 这两个对象的属性值不相等时报告错误，错误讯息由 ``$message`` 指定。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class EqualsTest extends TestCase
    {
        public function testFailure()
        {
            $expected = new stdClass;
            $expected->foo = 'foo';
            $expected->bar = 'bar';

            $actual = new stdClass;
            $actual->foo = 'bar';
            $actual->baz = 'bar';

            $this->assertEquals($expected, $actual);
        }
    }
    ?>
```

    $ phpunit EqualsTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 5.25Mb

    There was 1 failure:

    1) EqualsTest::testFailure
    Failed asserting that two objects are equal.
    --- Expected
    +++ Actual
    @@ @@
     stdClass Object (
    -    'foo' => 'foo'
    -    'bar' => 'bar'
    +    'foo' => 'bar'
    +    'baz' => 'bar'
     )

    /home/sb/EqualsTest.php:14

    FAILURES!
    Tests: 1, Assertions: 1, Failures: 1.

``assertEquals(array $expected, array $actual[, string $message = ''])``

当 ``$expected`` 和 ``$actual`` 这两个数组不相等时报告错误，错误讯息由 ``$message`` 指定。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class EqualsTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertEquals(['a', 'b', 'c'], ['a', 'c', 'd']);
        }
    }
    ?>
```

    $ phpunit EqualsTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 5.25Mb

    There was 1 failure:

    1) EqualsTest::testFailure
    Failed asserting that two arrays are equal.
    --- Expected
    +++ Actual
    @@ @@
     Array (
         0 => 'a'
    -    1 => 'b'
    -    2 => 'c'
    +    1 => 'c'
    +    2 => 'd'
     )

    /home/sb/EqualsTest.php:6

    FAILURES!
    Tests: 1, Assertions: 1, Failures: 1.


### assertFalse()

``assertFalse(bool $condition[, string $message = ''])``

当 ``$condition`` 为 ``true`` 时报告错误，错误讯息由 ``$message`` 指定。

``assertNotFalse()`` 是与之相反的断言，接受相同的参数。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class FalseTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertFalse(true);
        }
    }
    ?>
```

    $ phpunit FalseTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 5.00Mb

    There was 1 failure:

    1) FalseTest::testFailure
    Failed asserting that true is false.

    /home/sb/FalseTest.php:6

    FAILURES!
    Tests: 1, Assertions: 1, Failures: 1.


### assertFileEquals()

``assertFileEquals(string $expected, string $actual[, string $message = ''])``

当 ``$expected`` 所指定的文件与 ``$actual`` 所指定的文件内容不同时报告错误，错误讯息由 ``$message`` 指定。

``assertFileNotEquals()`` 是与之相反的断言，接受相同的参数。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class FileEqualsTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertFileEquals('/home/sb/expected', '/home/sb/actual');
        }
    }
    ?>
```

    $ phpunit FileEqualsTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 5.25Mb

    There was 1 failure:

    1) FileEqualsTest::testFailure
    Failed asserting that two strings are equal.
    --- Expected
    +++ Actual
    @@ @@
    -'expected
    +'actual
     '

    /home/sb/FileEqualsTest.php:6

    FAILURES!
    Tests: 1, Assertions: 3, Failures: 1.


### assertFileExists()

``assertFileExists(string $filename[, string $message = ''])``

当 ``$filename`` 所指定的文件不存在时报告错误，错误讯息由 ``$message`` 指定。

``assertFileNotExists()`` 是与之相反的断言，接受相同的参数。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class FileExistsTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertFileExists('/path/to/file');
        }
    }
    ?>
```

    $ phpunit FileExistsTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 4.75Mb

    There was 1 failure:

    1) FileExistsTest::testFailure
    Failed asserting that file "/path/to/file" exists.

    /home/sb/FileExistsTest.php:6

    FAILURES!
    Tests: 1, Assertions: 1, Failures: 1.


### assertFileIsReadable()

``assertFileIsReadable(string $filename[, string $message = ''])``

当 ``$filename`` 所指定的文件不是个文件或不可读时报告错误，错误讯息由 ``$message`` 指定。

``assertFileNotIsReadable()`` 是与之相反的断言，并接受相同的参数。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class FileIsReadableTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertFileIsReadable('/path/to/file');
        }
    }
    ?>
```

    $ phpunit FileIsReadableTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 4.75Mb

    There was 1 failure:

    1) FileIsReadableTest::testFailure
    Failed asserting that "/path/to/file" is readable.

    /home/sb/FileIsReadableTest.php:6

    FAILURES!
    Tests: 1, Assertions: 1, Failures: 1.


### assertFileIsWritable()

``assertFileIsWritable(string $filename[, string $message = ''])``

当 ``$filename`` 所指定的文件不是个文件或不可写时报告错误，错误讯息由 ``$message`` 指定。

``assertFileNotIsWritable()`` 是与之相反的断言，并接受相同的参数。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class FileIsWritableTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertFileIsWritable('/path/to/file');
        }
    }
    ?>
```

    $ phpunit FileIsWritableTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 4.75Mb

    There was 1 failure:

    1) FileIsWritableTest::testFailure
    Failed asserting that "/path/to/file" is writable.

    /home/sb/FileIsWritableTest.php:6

    FAILURES!
    Tests: 1, Assertions: 1, Failures: 1.


### assertGreaterThan()

``assertGreaterThan(mixed $expected, mixed $actual[, string $message = ''])``

当 ``$actual`` 的值不大于 ``$expected`` 的值时报告错误，错误讯息由 ``$message`` 指定。

``assertAttributeGreaterThan()`` 是便捷包装(convenience wrapper)，以某个类或对象的某个 ``public``、``protected`` 或 ``private`` 属性作为实际值来进行比较。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class GreaterThanTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertGreaterThan(2, 1);
        }
    }
    ?>
```

    $ phpunit GreaterThanTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 5.00Mb

    There was 1 failure:

    1) GreaterThanTest::testFailure
    Failed asserting that 1 is greater than 2.

    /home/sb/GreaterThanTest.php:6

    FAILURES!
    Tests: 1, Assertions: 1, Failures: 1.


### assertGreaterThanOrEqual()

``assertGreaterThanOrEqual(mixed $expected, mixed $actual[, string $message = ''])``

当 ``$actual`` 的值不大于且不等于 ``$expected`` 的值时报告错误，错误讯息由 ``$message`` 指定。

``assertAttributeGreaterThanOrEqual()`` 是便捷包装(convenience wrapper)，以某个类或对象的某个 ``public``、``protected`` 或 ``private`` 属性作为实际值来进行比较。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class GreatThanOrEqualTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertGreaterThanOrEqual(2, 1);
        }
    }
    ?>
```

    $ phpunit GreaterThanOrEqualTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 5.25Mb

    There was 1 failure:

    1) GreatThanOrEqualTest::testFailure
    Failed asserting that 1 is equal to 2 or is greater than 2.

    /home/sb/GreaterThanOrEqualTest.php:6

    FAILURES!
    Tests: 1, Assertions: 2, Failures: 1.


### assertInfinite()

``assertInfinite(mixed $variable[, string $message = ''])``

当 ``$actual`` 不是  ``INF`` 时报告错误，错误讯息由 ``$message`` 指定。

``assertFinite()`` 是与之相反的断言，接受相同的参数。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class InfiniteTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertInfinite(1);
        }
    }
    ?>
```

    $ phpunit InfiniteTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 5.00Mb

    There was 1 failure:

    1) InfiniteTest::testFailure
    Failed asserting that 1 is infinite.

    /home/sb/InfiniteTest.php:6

    FAILURES!
    Tests: 1, Assertions: 1, Failures: 1.


### assertInstanceOf()

``assertInstanceOf($expected, $actual[, $message = ''])``

当 ``$actual`` 不是 ``$expected`` 的实例时报告错误，错误讯息由 ``$message`` 指定。

``assertNotInstanceOf()`` 是与之相反的断言，接受相同的参数。

``assertAttributeInstanceOf()`` 和 ``assertAttributeNotInstanceOf()`` 是便捷包装(convenience wrapper)，可以应用于某个类或对象的某个 ``public``、``protected`` 或 ``private`` 属性。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class InstanceOfTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertInstanceOf(RuntimeException::class, new Exception);
        }
    }
    ?>
```

    $ phpunit InstanceOfTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 5.00Mb

    There was 1 failure:

    1) InstanceOfTest::testFailure
    Failed asserting that Exception Object (...) is an instance of class "RuntimeException".

    /home/sb/InstanceOfTest.php:6

    FAILURES!
    Tests: 1, Assertions: 1, Failures: 1.


### assertInternalType()

``assertInternalType($expected, $actual[, $message = ''])``

当 ``$actual`` 不是 ``$expected`` 所指明的类型时报告错误，错误讯息由 ``$message`` 指定。

``assertNotInternalType()`` 是与之相反的断言，接受相同的参数。

``assertAttributeInternalType()`` 和 ``assertAttributeNotInternalType()`` 是便捷包装(convenience wrapper)，可以应用于某个类或对象的某个 ``public``、``protected`` 或 ``private`` 属性。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class InternalTypeTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertInternalType('string', 42);
        }
    }
    ?>
```

    $ phpunit InternalTypeTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 5.00Mb

    There was 1 failure:

    1) InternalTypeTest::testFailure
    Failed asserting that 42 is of type "string".

    /home/sb/InternalTypeTest.php:6

    FAILURES!
    Tests: 1, Assertions: 1, Failures: 1.


### assertIsReadable()

``assertIsReadable(string $filename[, string $message = ''])``

当 ``$filename`` 所指定的文件或目录不可读时报告错误，错误讯息由 ``$message`` 指定。

``assertNotIsReadable()`` 是与之相反的断言，并接受相同的参数。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class IsReadableTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertIsReadable('/path/to/unreadable');
        }
    }
    ?>
```

    $ phpunit IsReadableTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 4.75Mb

    There was 1 failure:

    1) IsReadableTest::testFailure
    Failed asserting that "/path/to/unreadable" is readable.

    /home/sb/IsReadableTest.php:6

    FAILURES!
    Tests: 1, Assertions: 1, Failures: 1.


### assertIsWritable()

``assertIsWritable(string $filename[, string $message = ''])``

当 ``$filename`` 所指定的文件或目录不可写时报告错误，错误讯息由 ``$message`` 指定。

``assertNotIsWritable()`` 是与之相反的断言，并接受相同的参数。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class IsWritableTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertIsWritable('/path/to/unwritable');
        }
    }
    ?>
```

    $ phpunit IsWritableTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 4.75Mb

    There was 1 failure:

    1) IsWritableTest::testFailure
    Failed asserting that "/path/to/unwritable" is writable.

    /home/sb/IsWritableTest.php:6

    FAILURES!
    Tests: 1, Assertions: 1, Failures: 1.


### assertJsonFileEqualsJsonFile()

``assertJsonFileEqualsJsonFile(mixed $expectedFile, mixed $actualFile[, string $message = ''])``

当 ``$actualFile`` 对应的值与 ``$expectedFile`` 对应的值不匹配时报告错误，错误讯息由 ``$message`` 指定。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class JsonFileEqualsJsonFileTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertJsonFileEqualsJsonFile(
              'path/to/fixture/file', 'path/to/actual/file');
        }
    }
    ?>
```

    $ phpunit JsonFileEqualsJsonFileTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 5.00Mb

    There was 1 failure:

    1) JsonFileEqualsJsonFile::testFailure
    Failed asserting that '{"Mascot":"Tux"}' matches JSON string "["Mascott", "Tux", "OS", "Linux"]".

    /home/sb/JsonFileEqualsJsonFileTest.php:5

    FAILURES!
    Tests: 1, Assertions: 3, Failures: 1.


### assertJsonStringEqualsJsonFile()

``assertJsonStringEqualsJsonFile(mixed $expectedFile, mixed $actualJson[, string $message = ''])``

当 ``$actualJson`` 对应的值与 ``$expectedFile`` 对应的值不匹配时报告错误，错误讯息由 ``$message`` 指定。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class JsonStringEqualsJsonFileTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertJsonStringEqualsJsonFile(
                'path/to/fixture/file', json_encode(['Mascot' => 'ux'])
            );
        }
    }
    ?>
```

    $ phpunit JsonStringEqualsJsonFileTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 5.00Mb

    There was 1 failure:

    1) JsonStringEqualsJsonFile::testFailure
    Failed asserting that '{"Mascot":"ux"}' matches JSON string "{"Mascott":"Tux"}".

    /home/sb/JsonStringEqualsJsonFileTest.php:5

    FAILURES!
    Tests: 1, Assertions: 3, Failures: 1.


### assertJsonStringEqualsJsonString()

``assertJsonStringEqualsJsonString(mixed $expectedJson, mixed $actualJson[, string $message = ''])``

当 ``$actualJson`` 对应的值与 ``$expectedJson`` 对应的值不匹配时报告错误，错误讯息由 ``$message`` 指定。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class JsonStringEqualsJsonStringTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertJsonStringEqualsJsonString(
                json_encode(['Mascot' => 'Tux']),
                json_encode(['Mascot' => 'ux'])
            );
        }
    }
    ?>
```

    $ phpunit JsonStringEqualsJsonStringTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 5.00Mb

    There was 1 failure:

    1) JsonStringEqualsJsonStringTest::testFailure
    Failed asserting that two objects are equal.
    --- Expected
    +++ Actual
    @@ @@
     stdClass Object (
     -    'Mascot' => 'Tux'
     +    'Mascot' => 'ux'
    )

    /home/sb/JsonStringEqualsJsonStringTest.php:5

    FAILURES!
    Tests: 1, Assertions: 3, Failures: 1.


### assertLessThan()

``assertLessThan(mixed $expected, mixed $actual[, string $message = ''])``

当 ``$actual`` 的值不小于 ``$expected`` 的值时报告错误，错误讯息由 ``$message`` 指定。

``assertAttributeLessThan()`` 是便捷包装(convenience wrapper)，以某个类或对象的某个 ``public``、``protected`` 或 ``private`` 属性作为实际值来进行比较。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class LessThanTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertLessThan(1, 2);
        }
    }
    ?>
```

    $ phpunit LessThanTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 5.00Mb

    There was 1 failure:

    1) LessThanTest::testFailure
    Failed asserting that 2 is less than 1.

    /home/sb/LessThanTest.php:6

    FAILURES!
    Tests: 1, Assertions: 1, Failures: 1.


### assertLessThanOrEqual()

``assertLessThanOrEqual(mixed $expected, mixed $actual[, string $message = ''])``

当 ``$actual`` 的值不小于且不等于 ``$expected`` 的值时报告错误，错误讯息由 ``$message`` 指定。

``assertAttributeLessThanOrEqual()`` 是便捷包装(convenience wrapper)，以某个类或对象的某个 ``public``、``protected`` 或 ``private`` 属性作为实际值来进行比较。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class LessThanOrEqualTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertLessThanOrEqual(1, 2);
        }
    }
    ?>
```

    $ phpunit LessThanOrEqualTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 5.25Mb

    There was 1 failure:

    1) LessThanOrEqualTest::testFailure
    Failed asserting that 2 is equal to 1 or is less than 1.

    /home/sb/LessThanOrEqualTest.php:6

    FAILURES!
    Tests: 1, Assertions: 2, Failures: 1.


### assertNan()

``assertNan(mixed $variable[, string $message = ''])``

当 ``$variable`` 不是  ``NAN`` 时报告错误，错误讯息由 ``$message`` 指定。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class NanTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertNan(1);
        }
    }
    ?>
```

    $ phpunit NanTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 5.00Mb

    There was 1 failure:

    1) NanTest::testFailure
    Failed asserting that 1 is nan.

    /home/sb/NanTest.php:6

    FAILURES!
    Tests: 1, Assertions: 1, Failures: 1.


### assertNull()

``assertNull(mixed $variable[, string $message = ''])``

当 ``$actual`` 不是  ``null`` 时报告错误，错误讯息由 ``$message`` 指定。

``assertNotNull()`` 是与之相反的断言，接受相同的参数。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class NullTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertNull('foo');
        }
    }
    ?>
```

    $ phpunit NotNullTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 5.00Mb

    There was 1 failure:

    1) NullTest::testFailure
    Failed asserting that 'foo' is null.

    /home/sb/NotNullTest.php:6

    FAILURES!
    Tests: 1, Assertions: 1, Failures: 1.


### assertObjectHasAttribute()

``assertObjectHasAttribute(string $attributeName, object $object[, string $message = ''])``

当 ``$object->attributeName`` 不存在时报告错误，错误讯息由 ``$message`` 指定。

``assertObjectNotHasAttribute()`` 是与之相反的断言，接受相同的参数。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class ObjectHasAttributeTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertObjectHasAttribute('foo', new stdClass);
        }
    }
    ?>
```

    $ phpunit ObjectHasAttributeTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 4.75Mb

    There was 1 failure:

    1) ObjectHasAttributeTest::testFailure
    Failed asserting that object of class "stdClass" has attribute "foo".

    /home/sb/ObjectHasAttributeTest.php:6

    FAILURES!
    Tests: 1, Assertions: 1, Failures: 1.


### assertRegExp()

``assertRegExp(string $pattern, string $string[, string $message = ''])``

当 ``$string`` 不匹配于正则表达式 ``$pattern`` 时报告错误，错误讯息由 ``$message`` 指定。

``assertNotRegExp()`` 是与之相反的断言，接受相同的参数。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class RegExpTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertRegExp('/foo/', 'bar');
        }
    }
    ?>
```

    $ phpunit RegExpTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 5.00Mb

    There was 1 failure:

    1) RegExpTest::testFailure
    Failed asserting that 'bar' matches PCRE pattern "/foo/".

    /home/sb/RegExpTest.php:6

    FAILURES!
    Tests: 1, Assertions: 1, Failures: 1.


### assertStringMatchesFormat()

``assertStringMatchesFormat(string $format, string $string[, string $message = ''])``

当 ``$string`` 不匹配于 ``$format`` 定义的格式时报告错误，错误讯息由 ``$message`` 指定。

``assertStringNotMatchesFormat()`` 是与之相反的断言，接受相同的参数。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class StringMatchesFormatTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertStringMatchesFormat('%i', 'foo');
        }
    }
    ?>
```

    $ phpunit StringMatchesFormatTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 5.00Mb

    There was 1 failure:

    1) StringMatchesFormatTest::testFailure
    Failed asserting that 'foo' matches PCRE pattern "/^[+-]?\d+$/s".

    /home/sb/StringMatchesFormatTest.php:6

    FAILURES!
    Tests: 1, Assertions: 1, Failures: 1.

格式定义字符串中可以使用如下占位符：

-

  ``%e``：表示目录分隔符，例如在 Linux 系统中是 ``/``。

-

  ``%s``：一个或多个除了换行符以外的任意字符（非空白字符或者空白字符）。

-

  ``%S``：零个或多个除了换行符以外的任意字符（非空白字符或者空白字符）。

-

  ``%a``：一个或多个包括换行符在内的任意字符（非空白字符或者空白字符）。

-

  ``%A``：零个或多个包括换行符在内的任意字符（非空白字符或者空白字符）。

-

  ``%w``：零个或多个空白字符。

-

  ``%i``：带符号整数值，例如 ``+3142``、``-3142``。

-

  ``%d``：无符号整数值，例如 ``123456``。

-

  ``%x``：一个或多个十六进制字符。所谓十六进制字符，指的是在以下范围内的字符：``0-9``、``a-f``、``A-F``。

-

  ``%f``：浮点数，例如 ``3.142``、``-3.142``、``3.142E-10``、``3.142e+10``。

-

  ``%c``：单个任意字符。


### assertStringMatchesFormatFile()

``assertStringMatchesFormatFile(string $formatFile, string $string[, string $message = ''])``

当 ``$string`` 不匹配于 ``$formatFile`` 的内容所定义的格式时报告错误，错误讯息由 ``$message`` 指定。

``assertStringNotMatchesFormatFile()`` 是与之相反的断言，接受相同的参数。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class StringMatchesFormatFileTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertStringMatchesFormatFile('/path/to/expected.txt', 'foo');
        }
    }
    ?>
```

    $ phpunit StringMatchesFormatFileTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 5.00Mb

    There was 1 failure:

    1) StringMatchesFormatFileTest::testFailure
    Failed asserting that 'foo' matches PCRE pattern "/^[+-]?\d+
    $/s".

    /home/sb/StringMatchesFormatFileTest.php:6

    FAILURES!
    Tests: 1, Assertions: 2, Failures: 1.


### assertSame()

``assertSame(mixed $expected, mixed $actual[, string $message = ''])``

当两个变量 ``$expected`` 和 ``$actual`` 的值与类型不完全相同时报告错误，错误讯息由 ``$message`` 指定。

``assertNotSame()`` 是与之相反的断言，接受相同的参数。

``assertAttributeSame()`` 和 ``assertAttributeNotSame()`` 是便捷包装(convenience wrapper)，以某个类或对象的某个 ``public``、``protected`` 或 ``private`` 属性作为实际值来进行比较。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class SameTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertSame('2204', 2204);
        }
    }
    ?>
```

    $ phpunit SameTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 5.00Mb

    There was 1 failure:

    1) SameTest::testFailure
    Failed asserting that 2204 is identical to '2204'.

    /home/sb/SameTest.php:6

    FAILURES!
    Tests: 1, Assertions: 1, Failures: 1.

``assertSame(object $expected, object $actual[, string $message = ''])``

当两个变量 ``$expected`` 和 ``$actual`` 不是指向同一个对象的引用时报告错误，错误讯息由 ``$message`` 指定。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class SameTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertSame(new stdClass, new stdClass);
        }
    }
    ?>
```

    $ phpunit SameTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 4.75Mb

    There was 1 failure:

    1) SameTest::testFailure
    Failed asserting that two variables reference the same object.

    /home/sb/SameTest.php:6

    FAILURES!
    Tests: 1, Assertions: 1, Failures: 1.


### assertStringEndsWith()

``assertStringEndsWith(string $suffix, string $string[, string $message = ''])``

当 ``$string`` 不以 ``$suffix`` 结尾时报告错误，错误讯息由 ``$message`` 指定。

``assertStringEndsNotWith()`` 是与之相反的断言，接受相同的参数。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class StringEndsWithTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertStringEndsWith('suffix', 'foo');
        }
    }
    ?>
```

    $ phpunit StringEndsWithTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 1 second, Memory: 5.00Mb

    There was 1 failure:

    1) StringEndsWithTest::testFailure
    Failed asserting that 'foo' ends with "suffix".

    /home/sb/StringEndsWithTest.php:6

    FAILURES!
    Tests: 1, Assertions: 1, Failures: 1.


### assertStringEqualsFile()

``assertStringEqualsFile(string $expectedFile, string $actualString[, string $message = ''])``

当 ``$expectedFile`` 所指定的文件其内容不是 ``$actualString`` 时报告错误，错误讯息由 ``$message`` 指定。

``assertStringNotEqualsFile()`` 是与之相反的断言，接受相同的参数。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class StringEqualsFileTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertStringEqualsFile('/home/sb/expected', 'actual');
        }
    }
    ?>
```

    $ phpunit StringEqualsFileTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 5.25Mb

    There was 1 failure:

    1) StringEqualsFileTest::testFailure
    Failed asserting that two strings are equal.
    --- Expected
    +++ Actual
    @@ @@
    -'expected
    -'
    +'actual'

    /home/sb/StringEqualsFileTest.php:6

    FAILURES!
    Tests: 1, Assertions: 2, Failures: 1.


### assertStringStartsWith()

``assertStringStartsWith(string $prefix, string $string[, string $message = ''])``

当 ``$string`` 不以 ``$prefix`` 开头时报告错误，错误讯息由 ``$message`` 指定。

``assertStringStartsNotWith()`` 是与之相反的断言，并接受相同的参数。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class StringStartsWithTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertStringStartsWith('prefix', 'foo');
        }
    }
    ?>
```

    $ phpunit StringStartsWithTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 5.00Mb

    There was 1 failure:

    1) StringStartsWithTest::testFailure
    Failed asserting that 'foo' starts with "prefix".

    /home/sb/StringStartsWithTest.php:6

    FAILURES!
    Tests: 1, Assertions: 1, Failures: 1.


### assertThat()

可以用 ``PHPUnit_Framework_Constraint`` 类来订立更加复杂的断言。随后可以用 ``assertThat()`` 方法来评定这些断言。:numref:`appendixes.assertions.assertThat.example` 展示了如何用 ``logicalNot()`` 和 ``equalTo()`` 约束条件来表达与 ``assertNotEquals()`` 等价的断言。

``assertThat(mixed $value, PHPUnit_Framework_Constraint $constraint[, $message = ''])``

当 ``$value`` 不符合约束条件 ``$constraint`` 时报告错误，错误讯息由 ``$message`` 指定。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class BiscuitTest extends TestCase
    {
        public function testEquals()
        {
            $theBiscuit = new Biscuit('Ginger');
            $myBiscuit  = new Biscuit('Ginger');

            $this->assertThat(
              $theBiscuit,
              $this->logicalNot(
                $this->equalTo($myBiscuit)
              )
            );
        }
    }
    ?>
```

列举了所有可用的 `PHPUnit_Framework_Constraint` 类。


    * - 约束条件
      - 含义
    * - ``PHPUnit_Framework_Constraint_Attribute attribute(PHPUnit_Framework_Constraint $constraint, $attributeName)``
      - 此约束将另外一个约束应用于某个类或对象的某个属性。
    * - ``PHPUnit_Framework_Constraint_IsAnything anything()``
      - 此约束接受任意输入值。
    * - ``PHPUnit_Framework_Constraint_ArrayHasKey arrayHasKey(mixed $key)``
      - 此约束断言所评定的数组拥有指定键名。
    * - ``PHPUnit_Framework_Constraint_TraversableContains contains(mixed $value)``
      - 此约束断言所评定的 ``array`` 或实现了 ``Iterator`` 接口的对象包含有给定值。
    * - ``PHPUnit_Framework_Constraint_TraversableContainsOnly containsOnly(string $type)``
      - 此约束断言所评定的 ``array`` 或实现了 ``Iterator`` 接口的对象仅包含给定类型的值。
    * - ``PHPUnit_Framework_Constraint_TraversableContainsOnly containsOnlyInstancesOf(string $classname)``
      - 此约束断言所评定的 ``array`` 或实现了 ``Iterator`` 接口的对象仅包含给定类名的类的实例。
    * - ``PHPUnit_Framework_Constraint_IsEqual equalTo($value, $delta = 0, $maxDepth = 10)``
      - 此约束检验一个值是否等于另外一个。
    * - ``PHPUnit_Framework_Constraint_Attribute attributeEqualTo($attributeName, $value, $delta = 0, $maxDepth = 10)``
      - 此约束检验一个值是否等于某个类或对象的某个属性。
    * - ``PHPUnit_Framework_Constraint_DirectoryExists directoryExists()``
      - 此约束检验所评定的目录是否存在。
    * - ``PHPUnit_Framework_Constraint_FileExists fileExists()``
      - 此约束检验所评定的文件名对应的文件是否存在。
    * - ``PHPUnit_Framework_Constraint_IsReadable isReadable()``
      - 此约束检验所评定的文件名对应的文件是否可读。
    * - ``PHPUnit_Framework_Constraint_IsWritable isWritable()``
      - 此约束检验所评定的文件名对应的文件是否可写。
    * - ``PHPUnit_Framework_Constraint_GreaterThan greaterThan(mixed $value)``
      - 此约束断言所评定的值大于给定值。
    * - ``PHPUnit_Framework_Constraint_Or greaterThanOrEqual(mixed $value)``
      - 此约束断言所评定的值大于或等于给定值。
    * - ``PHPUnit_Framework_Constraint_ClassHasAttribute classHasAttribute(string $attributeName)``
      - 此约束断言所评定的类具有给定属性。
    * - ``PHPUnit_Framework_Constraint_ClassHasStaticAttribute classHasStaticAttribute(string $attributeName)``
      - 此约束断言所评定的类具有给定静态属性。
    * - ``PHPUnit_Framework_Constraint_ObjectHasAttribute hasAttribute(string $attributeName)``
      - 此约束断言所评定的对象具有给定属性。
    * - ``PHPUnit_Framework_Constraint_IsIdentical identicalTo(mixed $value)``
      - 此约束断言所评定的值与另外一个值全等。
    * - ``PHPUnit_Framework_Constraint_IsFalse isFalse()``
      - 此约束断言所评定的值为 ``false``。
    * - ``PHPUnit_Framework_Constraint_IsInstanceOf isInstanceOf(string $className)``
      - 此约束断言所评定的对象是给定类的实例。
    * - ``PHPUnit_Framework_Constraint_IsNull isNull()``
      - 此约束断言所评定的值为 ``null``。
    * - ``PHPUnit_Framework_Constraint_IsTrue isTrue()``
      - 此约束断言所评定的值为 ``true``。
    * - ``PHPUnit_Framework_Constraint_IsType isType(string $type)``
      - 此约束断言所评定的值是指定类型的。
    * - ``PHPUnit_Framework_Constraint_LessThan lessThan(mixed $value)``
      - 此约束断言所评定的值小于给定值。
    * - ``PHPUnit_Framework_Constraint_Or lessThanOrEqual(mixed $value)``
      - 此约束断言所评定的值小于或等于给定值。
    * - ``logicalAnd()``
      - 逻辑与(AND)。
    * - ``logicalNot(PHPUnit_Framework_Constraint $constraint)``
      - 逻辑非(NOT)。
    * - ``logicalOr()``
      - 逻辑或(OR)。
    * - ``logicalXor()``
      - 逻辑异或(XOR)。
    * - ``PHPUnit_Framework_Constraint_PCREMatch matchesRegularExpression(string $pattern)``
      - 此约束断言所评定的字符串匹配于正则表达式。
    * - ``PHPUnit_Framework_Constraint_StringContains stringContains(string $string, bool $case)``
      - 此约束断言所评定的字符串包含指定字符串。
    * - ``PHPUnit_Framework_Constraint_StringEndsWith stringEndsWith(string $suffix)``
      - 此约束断言所评定的字符串以给定后缀结尾。
    * - ``PHPUnit_Framework_Constraint_StringStartsWith stringStartsWith(string $prefix)``
      - 此约束断言所评定的字符串以给定前缀开头。


### assertTrue()

``assertTrue(bool $condition[, string $message = ''])``

当 ``$condition`` 为 ``false`` 时报告错误，错误讯息由 ``$message`` 指定。

``assertNotTrue()`` 是与之相反的断言，接受相同的参数。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class TrueTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertTrue(false);
        }
    }
    ?>
```

    $ phpunit TrueTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 5.00Mb

    There was 1 failure:

    1) TrueTest::testFailure
    Failed asserting that false is true.

    /home/sb/TrueTest.php:6

    FAILURES!
    Tests: 1, Assertions: 1, Failures: 1.


### assertXmlFileEqualsXmlFile()

``assertXmlFileEqualsXmlFile(string $expectedFile, string $actualFile[, string $message = ''])``

当 ``$actualFile`` 对应的 XML 文档与 ``$expectedFile`` 对应的 XML 文档不相同时报告错误，错误讯息由 ``$message`` 指定。

``assertXmlFileNotEqualsXmlFile()`` 是与之相反的断言，接受相同的参数。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class XmlFileEqualsXmlFileTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertXmlFileEqualsXmlFile(
              '/home/sb/expected.xml', '/home/sb/actual.xml');
        }
    }
    ?>
```

    $ phpunit XmlFileEqualsXmlFileTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 5.25Mb

    There was 1 failure:

    1) XmlFileEqualsXmlFileTest::testFailure
    Failed asserting that two DOM documents are equal.
    --- Expected
    +++ Actual
    @@ @@
     <?xml version="1.0"?>
     <foo>
    -  <bar/>
    +  <baz/>
     </foo>

    /home/sb/XmlFileEqualsXmlFileTest.php:7

    FAILURES!
    Tests: 1, Assertions: 3, Failures: 1.


### assertXmlStringEqualsXmlFile()

``assertXmlStringEqualsXmlFile(string $expectedFile, string $actualXml[, string $message = ''])``

当 ``$actualXml`` 对应的 XML 文档与 ``$expectedFile`` 对应的 XML 文档不相同时报告错误，错误讯息由 ``$message`` 指定。

``assertXmlStringNotEqualsXmlFile()`` 是与之相反的断言，并接受相同的参数。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class XmlStringEqualsXmlFileTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertXmlStringEqualsXmlFile(
              '/home/sb/expected.xml', '<foo><baz/></foo>');
        }
    }
    ?>
```

    $ phpunit XmlStringEqualsXmlFileTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 5.25Mb

    There was 1 failure:

    1) XmlStringEqualsXmlFileTest::testFailure
    Failed asserting that two DOM documents are equal.
    --- Expected
    +++ Actual
    @@ @@
     <?xml version="1.0"?>
     <foo>
    -  <bar/>
    +  <baz/>
     </foo>

    /home/sb/XmlStringEqualsXmlFileTest.php:7

    FAILURES!
    Tests: 1, Assertions: 2, Failures: 1.


### assertXmlStringEqualsXmlString()

``assertXmlStringEqualsXmlString(string $expectedXml, string $actualXml[, string $message = ''])``

当 ``$actualXml`` 对应的 XML 文档与 ``$expectedXml`` 对应的 XML 文档不相同时报告错误，错误讯息由 ``$message`` 指定。

``assertXmlStringNotEqualsXmlString()`` 是与之相反的断言，接受相同的参数。

```php
    <?php
    use PHPUnit\Framework\TestCase;

    class XmlStringEqualsXmlStringTest extends TestCase
    {
        public function testFailure()
        {
            $this->assertXmlStringEqualsXmlString(
              '<foo><bar/></foo>', '<foo><baz/></foo>');
        }
    }
    ?>
```

    $ phpunit XmlStringEqualsXmlStringTest
    PHPUnit 7.0.0 by Sebastian Bergmann and contributors.

    F

    Time: 0 seconds, Memory: 5.00Mb

    There was 1 failure:

    1) XmlStringEqualsXmlStringTest::testFailure
    Failed asserting that two DOM documents are equal.
    --- Expected
    +++ Actual
    @@ @@
     <?xml version="1.0"?>
     <foo>
    -  <bar/>
    +  <baz/>
     </foo>

    /home/sb/XmlStringEqualsXmlStringTest.php:7

    FAILURES!
    Tests: 1, Assertions: 1, Failures: 1.


## 用 XML 配置来编排测试套件

PHPUnit的 XML 配置文件（[附录 C][0]）也可以用于编排测试套件。[例 5.1][1]展示了一个最小化的 phpunit.xml 例子，它将在递归遍历 tests 时添加所有在 *Test.php 文件中找到的 *Test 类。

**例 5.1: 用 XML 配置来编排测试套件**

```xml
    <phpunit bootstrap="src/autoload.php">
      <testsuites>
        <testsuite name="money">
          <directory>tests</directory>
        </testsuite>
      </testsuites>
    </phpunit>
```
  


如果 `phpunit.xml` 或 `phpunit.xml.dist` （按此顺序）存在于当前工作目录并且 _未_ 使用 --configuration，将自动从此文件中读取配置。

可以明确指定测试的执行顺序：

**例 5.2: 用 XML 配置来编排测试套件**

```xml
    <phpunit bootstrap="src/autoload.php">
      <testsuites>
        <testsuite name="money">
          <file>tests/IntlFormatterTest.php</file>
          <file>tests/MoneyTest.php</file>
          <file>tests/CurrencyTest.php</file>
        </testsuite>
      </testsuites>
    </phpunit>
```

[0]: appendixes.configuration.html
[1]: organizing-tests.html#organizing-tests.xml-configuration.examples.phpunit.xml



# XML 配置文件

## PHPUnit

`<phpunit>` 元素的属性用于配置 `PHPUnit` 的核心功能。

```xml
    <phpunit
             xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
             xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/6.3/phpunit.xsd"
             backupGlobals="true"
             backupStaticAttributes="false"
             <!--bootstrap="/path/to/bootstrap.php"-->
             cacheTokens="false"
             colors="false"
             convertErrorsToExceptions="true"
             convertNoticesToExceptions="true"
             convertWarningsToExceptions="true"
             forceCoversAnnotation="false"
             mapTestClassNameToCoveredClassName="false"
             printerClass="PHPUnit_TextUI_ResultPrinter"
             <!--printerFile="/path/to/ResultPrinter.php"-->
             processIsolation="false"
             stopOnError="false"
             stopOnFailure="false"
             stopOnIncomplete="false"
             stopOnSkipped="false"
             stopOnRisky="false"
             testSuiteLoaderClass="PHPUnit_Runner_StandardTestSuiteLoader"
             <!--testSuiteLoaderFile="/path/to/StandardTestSuiteLoader.php"-->
             timeoutForSmallTests="1"
             timeoutForMediumTests="10"
             timeoutForLargeTests="60"
             verbose="false">
      <!-- ... -->
    </phpunit>
```

以上 XML 配置对应于在“命令行选项”一节描述过的 TextUI 测试执行器的默认行为。

其他那些不能用命令行选项来配置的选项有：

- convertErrorsToExceptions

默认情况下，PHPUnit 将会安插一个错误处理函数来将以下错误转换为异常：


  * E_WARNING
  * E_NOTICE
  * E_USER_ERROR
  * E_USER_WARNING
  * E_USER_NOTICE
  * E_STRICT
  * E_RECOVERABLE_ERROR
  * E_DEPRECATED
  * E_USER_DEPRECATED

将 `convertErrorsToExceptions` 设为 `false` 可以禁用此功能。

- convertNoticesToExceptions

此选项设置为 `false` 时，由 `convertErrorsToExceptions` 安插的错误处理函数不会将 E_NOTICE、E_USER_NOTICE、E_STRICT 错误转换为异常。

- convertWarningsToExceptions

此选项设置为 false 时，由 convertErrorsToExceptions 安插的错误处理函数不会将 E_WARNING 或 E_USER_WARNING 错误转换为异常。

- forceCoversAnnotation

只记录使用了 @covers 标注（文档参见“@covers”一节）的测试的代码覆盖率。

- timeoutForLargeTests

如果实行了基于测试规模的时间限制，那么此属性为所有标记为 @large 的测试设定超时限制。在配置的时间限制内未执行完毕的测试将视为失败。

- timeoutForMediumTests

如果实行了基于测试规模的时间限制，那么此属性为所有标记为 @medium 的测试设定超时限制。在配置的时间限制内未执行完毕的测试将视为失败。

- timeoutForSmallTests

如果实行了基于测试规模的时间限制，那么此属性为所有未标记为 @medium 或 @large 的测试设定超时限制。在配置的时间限制内未执行完毕的测试将视为失败。

## 测试套件

带有一个或多个 `<testsuite>` 子元素的 `<testsuites>` 元素用于将测试套件及测试用例组合出新的测试套件。

```xml
    <testsuites>
      <testsuite name="My Test Suite">
        <directory>/path/to/*Test.php files</directory>
        <file>/path/to/MyTest.php</file>
        <exclude>/path/to/exclude</exclude>
      </testsuite>
    </testsuites>
```

可以用 phpVersion 和 phpVersionOperator 属性来指定 PHP 版本需求。在以下例子中，仅当 PHP 版本至少为 5.3.0 时才会将 `/path/to/*Test.php` 文件与 `/path/to/MyTest.php` 文件添加到测试套件中。

```xml
      <testsuites>
        <testsuite name="My Test Suite">
          <directory suffix="Test.php" phpVersion="5.3.0" phpVersionOperator=">=">/path/to/files</directory>
          <file phpVersion="5.3.0" phpVersionOperator=">=">/path/to/MyTest.php</file>
        </testsuite>
      </testsuites>
```

phpVersionOperator 属性是可选的，其默认值为 >=。

## 分组

`<groups>` 元素及其 `<include>`、`<exclude>`、`<group>` 子元素用于从带有 @group 标注（相关文档参见 “@group”一节）的测试中选择需要运行（或不运行）的分组。

```xml
    <groups>
      <include>
        <group>name</group>
      </include>
      <exclude>
        <group>name</group>
      </exclude>
    </groups>
```

以上 XML 配置对应于以如下选项调用 TextUI 测试执行器：

* --group name
* --exclude-group name

## Whitelisting Files for Code Coverage

`<filter>` 元素及其子元素用于配置代码覆盖率报告所使用的白名单。

```xml
    <filter>
      <whitelist processUncoveredFilesFromWhitelist="true">
        <directory suffix=".php">/path/to/files</directory>
        <file>/path/to/file</file>
        <exclude>
          <directory suffix=".php">/path/to/files</directory>
          <file>/path/to/file</file>
        </exclude>
      </whitelist>
    </filter>
```
## Logging （日志记录）

`<logging>` 元素及其 `<log>` 子元素用于配置测试执行期间的日志记录。

```xml
    <logging>
      <log type="coverage-html" target="/tmp/report" lowUpperBound="35"
           highLowerBound="70"></log>
      <log type="coverage-clover" target="/tmp/coverage.xml"></log>
      <log type="coverage-php" target="/tmp/coverage.serialized"></log>
      <log type="coverage-text" target="php://stdout" showUncoveredFiles="false"></log>
      <log type="junit" target="/tmp/logfile.xml" logIncompleteSkipped="false"></log>
      <log type="testdox-html" target="/tmp/testdox.html"></log>
      <log type="testdox-text" target="/tmp/testdox.txt"></log>
    </logging>
```

以上 XML 配置对应于以如下选项调用 TextUI 测试执行器：

* --coverage-html /tmp/report
* --coverage-clover /tmp/coverage.xml
* --coverage-php /tmp/coverage.serialized
* --coverage-text
    * /tmp/logfile.txt
* --log-junit /tmp/logfile.xml
* --testdox-html /tmp/testdox.html
* --testdox-text /tmp/testdox.txt

lowUpperBound、highLowerBound、logIncompleteSkipped 及 showUncoveredFiles 属性没有等价的 TextUI 测试执行器选项。

* lowUpperBound：视为“低”覆盖率的最大覆盖率百分比。
* highLowerBound：视为“高”覆盖率的最小覆盖率百分比。
* showUncoveredFiles：在 --coverage-text 输出中显示所有符合白名单的文件，不仅限于有覆盖率信息的那些。
* showOnlySummary：在 --coverage-text 输出中只显示摘要。

## 测试监听器

`<listeners>` 元素及其 `<listener>` 子元素用于在测试执行期间附加额外的测试监听器。

```xml
    <listeners>
      <listener class="MyListener" file="/optional/path/to/MyListener.php">
        <arguments>
          <array>
            <element key="0">
              <string>Sebastian</string>
            </element>
          </array>
          <integer>22</integer>
          <string>April</string>
          <double>19.78</double>
          <null></null>
          <object class="stdClass"></object>
        </arguments>
      </listener>
    </listeners>
```
以上 XML 配置对应于将 `$listener` 对象（见下文）附到测试执行过程上。

```php
    $listener = new MyListener(
        ['Sebastian'],
        22,
        'April',
        19.78,
        null,
        new stdClass
    );
```
## 设定 PHP INI 设置、常量、全局变量

`<php>` 元素及其子元素用于配置 PHP 设置、常量以及全局变量。同时也可用于向 `include_path` 前部置入内容。

```xml
    <php>
      <includePath>.</includePath>
      <ini name="foo" value="bar"></ini>
      <const name="foo" value="bar"></const>
      <var name="foo" value="bar"></var>
      <env name="foo" value="bar"></env>
      <post name="foo" value="bar"></post>
      <get name="foo" value="bar"></get>
      <cookie name="foo" value="bar"></cookie>
      <server name="foo" value="bar"></server>
      <files name="foo" value="bar"></files>
      <request name="foo" value="bar"></request>
    </php>
```
以上 XML 配置对应于如下 PHP 代码：

    ini_set('foo', 'bar');
    define('foo', 'bar');
    $GLOBALS['foo'] = 'bar';
    $_ENV['foo'] = 'bar';
    $_POST['foo'] = 'bar';
    $_GET['foo'] = 'bar';
    $_COOKIE['foo'] = 'bar';
    $_SERVER['foo'] = 'bar';
    $_FILES['foo'] = 'bar';
    $_REQUEST['foo'] = 'bar';


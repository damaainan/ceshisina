## php7 使用 phpunit 部分错误和解决方案

来源：[https://segmentfault.com/a/1190000013765217](https://segmentfault.com/a/1190000013765217)


##### 预先准备(brew 安装的情况下)



* php7

* php7-xdebug

* runkit7

 **`报错信息1：`** 

```
Error:No code coverage driver is available
```

问题和解决：

```
# 没有成功安装xdebug
  brew search php70-xdebug
  brew install php70-xdebug
  brew services restart php70
```

```
# 查看php -v 如果信息如下则安装成功
PHP 7.0.25 (cli) (built: Oct 27 2017 12:56:53) ( NTS )
Copyright (c) 1997-2017 The PHP Group
Zend Engine v3.0.0, Copyright (c) 1998-2017 Zend Technologies with 
Xdebug v2.5.5, Copyright (c) 2002-2017, by Derick Rethans
```
 **`报错信息2：`** 

```
Error: No whitelist configured, no code coverage will be generated
```

问题和解决：

```xml
# 因为我需要测试覆盖率，而这里没有设置白名单，可以在项目目录下增加 phpunit.xml,xml中增加下面这写代码，
可以增加多个目录。
    <filter>
        <whitelist  processUncoveredFilesFromWhitelist="true">
            <directory suffix=".php">./Api1</directory>
        </whitelist>
        <whitelist  processUncoveredFilesFromWhitelist="true">
            <directory suffix=".php">./Api2</directory>
        </whitelist>
    </filter>
  
```
 **`报错信息3`** ：

```
. 1 / 1 (100%)

Time: 340 ms, Memory: 10.00MB

OK (1 test, 0 assertions)
```

问题和解决：

```
# 测试其实已经通过了，但 0 assertions，代表没有任何断言被执行。

增加（或修改） processIsolation="false"  这行到 phpunit.xml 的中

<phpunit
        bootstrap="./tests/autoload.php"
        colors="true"
        convertErrorsToExceptions="true"
        convertNoticesToExceptions="true"
        convertWarningsToExceptions="true"
        processIsolation="false"
        stopOnFailure="false"
        stopOnError="false"
        stopOnIncoplete="false"
        stopOnSkipped="false"
>
```

```
--process-isolation
每个测试都在独立的PHP进程中运行。
```

下面贴上完整的phpunit.xml，配置项详见：

[https://phpunit.de/manual/cur...][0]
```xml
<?xml version="1.0" encoding="UTF-8"?>
<!-- bootstrap 可以使用项目的autoload文件 -->
<phpunit
        bootstrap="./tests/autoload.php"
        colors="true"
        convertErrorsToExceptions="true"
        convertNoticesToExceptions="true"
        convertWarningsToExceptions="true"
        processIsolation="false"
        stopOnFailure="false"
        stopOnError="false"
        stopOnIncoplete="false"
        stopOnSkipped="false"
>
    <!-- testsuites 指定测试目录集-->
    <testsuites>
        <testsuite name="Api Tests">
            <directory suffix="Test.php">./tests/Api</directory>
        </testsuite>
        <testsuite name="Util Tests">
            <directory suffix="Test.php">./tests/Util</directory>
        </testsuite>
    </testsuites>
    <!-- 覆盖率的测试文件，blacklist 黑名单(不需要统计覆盖率的文件)，whitelist 白名单(统计覆盖率的测试文件) 当黑名单与白名单文件重复时，白名单起作用 -->
    <filter>
        <whitelist  processUncoveredFilesFromWhitelist="true">
            <directory suffix=".php">./Util</directory>
        </whitelist>
        <whitelist  processUncoveredFilesFromWhitelist="true">
            <directory suffix=".php">./Api</directory>
        </whitelist>
    </filter>
    <!-- 生成单元测试覆盖率 html 文件的目录-->
    <logging>
        <log type="coverage-html" target="./tmp/cover" />
        <log type="junit" target="./tmp/result.xml" />
    </logging>
   <!-- 错误日志-->
<env name="LOCAL_ENV" value="test"/>
        <env name="APP_ENV" value="test"/>
        <ini name="error_log" value="/data/service/phpunit.log"/>
    </php>
</phpunit>
```

[0]: https://phpunit.de/manual/current/zh_cn/appendixes.configuration.html
## 写 Laravel 测试代码 (六)

来源：[https://segmentfault.com/a/1190000011717487](https://segmentfault.com/a/1190000011717487)

写测试代码时，有时候需要利用phpunit来生成测试代码覆盖率报告，方便调试和检查。本文主要聊聊如何在PHPStorm中配置phpunit。

假设`phpunit.xml`如下:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit backupGlobals="false"
         backupStaticAttributes="false"
         bootstrap="../vendor/autoload.php"
         colors="true"
         convertErrorsToExceptions="true"
         convertNoticesToExceptions="true"
         convertWarningsToExceptions="true"
         processIsolation="false"
         stopOnFailure="false">
    <testsuites>
        <testsuite name="firstclearing">
            <directory suffix="Test.php">./Integrations/FirstClearing</directory>
        </testsuite>
    </testsuites>
    <filter>
        <whitelist>
            <directory suffix=".php">./Integrations/FirstClearing/</directory>
            <directory suffix=".php">../app/Integrations/FirstClearing</directory>
        </whitelist>
    </filter>
<env name="APP_DEBUG" value="true"/>
        <env name="APP_ENV" value="testing"/>
        <env name="APP_KEY" value="XMSJBjsA0J8pDjxraJZo0kbswS6D69Qn"/>
        <env name="APP_URL" value="https://test.rightcapital.com"/>
        <env name="CACHE_DRIVER" value="redis"/>
        <env name="DB_DATABASE" value="rightcapital"/>
        <env name="DB_PASSWORD" value="testing"/>
        <env name="DB_USERNAME" value="testing"/>
        <env name="FILESYSTEM_CLOUD" value="local"/>
        <env name="MAIL_DRIVER" value="log"/>
        <env name="QUEUE_DRIVER" value="sync"/>
        <env name="SESSION_DRIVER" value="extended"/>
        <env name="TEST_AS_OF_DATE" value="2015-01-05"/>
        <env name="TEST_DATA_SET" value="simple"/>
    </php>
</phpunit>

```

然后点击`Run/Debug Configurations`按钮，设置`phpunit.xml`配置文件路径，测试报告存放位置`--coverage-html /Applications/MAMP/htdocs/WebService/API/storage/logs/tests/first_clearing`，同时还有xdebug.so的配置路径`-d zend_extension=/usr/local/opt/php71-xdebug/xdebug.so`，如图：

![][0]

记得本地php环境得安装xdebug扩展，但由于composer安装包时有xdebug扩展会减缓速度，可以这么设置避免这个问题：

![][1]

OK，当点击`Run`按钮运行测试时，会生成测试覆盖率报告：

![][2]

![][3]
`phpunit-firstclearing.xml`中配置了`whitelist`只显示这两个目录`./Integrations/FirstClearing/, ../app/Integrations/FirstClearing`的测试覆盖率报告。测试报告存放在`/Applications/MAMP/htdocs/WebService/API/storage/logs/tests/first_clearing`，是html文件，可在浏览器中打开。当然，要生成测试报告，测试运行速度就会大大降低。

![][4]

OK，有了测试报告可作为调试和检查代码的补充手段，能从整体层面查看代码的质量。为了修改出高质量代码，这个技巧也是必要的。

[0]: ./img/img/bVXkmc.png
[1]: ./img/img/bVXkm4.png
[2]: ./img/img/bVXknx.png
[3]: ./img/img/bVXknD.png
[4]: ./img/img/bVXkoQ.png
# Yaf中PHPUnit单元测试最佳实践

 时间 2015-09-10 00:01:59  X-Space

原文[http://blog.phpdr.net/yaf中phpunit单元测试最佳实践.html][1]


public同级目录建立tests目录

tests/phpunit.xml

```xml
    <phpunit bootstrap="./index.php">
        <testsuites>
            <testsuite name="controllers">
                <file>./controllers/TestIndex.php</file>
            </testsuite>
        </testsuites>
    </phpunit>
```

tests/index.php

```php
    <?php
    define ( 'APP_PATH', dirname ( __DIR__ ) . '/app', true );
    (new Yaf_Application ( APP_PATH . '/conf/app.ini' ))->bootstrap ();
```

tests/controllers/TestIndex.php

> 命令行测试

```php
    class TestIndex extends PHPUnit_Framework_TestCase {
        public function testIndex() {
            $request=new Yaf_Request_Simple('CLI','','Index','test');
            $res=Yaf_Application::app()->getDispatcher()->returnResponse(true)->dispatch($request);
            $valid='test string';
            $this->assertEquals ( $valid, $res->getBody());
        }
    }
```

APP_PATH.'/controllers/Index.php'

```php
    class IndexController extends Yaf_Controller_Abstract {
        function testAction(){
            $this->getResponse()->setBody('test string');
        }
    }
```

运行

> **在测试目录直接执行即可**

    [root@ares tests]# phpunit 
    PHPUnit 4.8.6 by Sebastian Bergmann and contributors.
    
    .
    
    Time: 105 ms, Memory: 4.00Mb
    
    OK (1 test, 1 assertion)

参考：

[http://www.crackedzone.com/phpunit-yaf-controller.html][5]


[1]: http://blog.phpdr.net/yaf中phpunit单元测试最佳实践.html
[5]: http://www.crackedzone.com/phpunit-yaf-controller.html
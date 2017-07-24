# [PHP SOAP实践][0]

# 1. 前言

学习一个知识点，最正确的方法应该是看它的官方手册！

PHP SOAP：http://php.net/manual/zh/book.soap.php

讲过简单了解，我决定使用 non-WSDL 模式。（比较方便）

# 2. 示例
 
```php
    /**
     * server.php
     */
    $opt = array(
        //命名空间
        'uri' => 'name',
    );
    $server = new SoapServer(null, $opt);
    //添加function
    function t($x) {
        return $x;
    }
    
    $server->addFunction('t');
    //执行
    $server->handle();
    
    /**
     * client.php
     */
    $opt = array(
        //命名空间
        'uri' => 'name',
        //地址
        'location' => 'http://localhost/test/server.php',
    );
    try {
        $client = new SoapClient(null, $opt);
        //调用function
        $res = $client->__soapCall('t', array(time()));
        print_r($res);
    } catch (Exception $e) {
        //输出错误
        echo $e->getMessage();
    }
```

提示：可以添加多个addFunction();或者添加所有addFunction(SOAP_FUNCTIONS_ALL);

# 3. class

```php
    //添加class
    class test {
        function run() {
            return __CLASS__;
        }
    }
    $server->setClass('test');
    
    //调用class
    $res = $client->run();
    print_r($res);
```


提示：setClass();只允许使用一次！addFunction(); 和 setClass(); 互相排斥不能同时使用。

# 4. 调试错误

getMessage();无法捕捉server端的PHP报错，这样开发调试起来十分麻烦。


```php
    $opt = array(
        //调试
        'trace' => true,
        //命名空间
        'uri' => 'name',
        //地址
        'location' => 'http://localhost/test/server.php',
    );
    
    //输出返回(必须启用trace选项)
    echo $client->__getLastResponse();
```


# 5. PHP兼容

在PHP5.6以上使用soap会报错：

    Deprecated: Automatically populating $HTTP_RAW_POST_DATA is deprecated and will be removed in a future version. To avoid this warning set 'always_populate_raw_post_data' to '-1' in php.ini and use the php://input stream instead. in Unknown on line 0

根据提示需要设置 always_populate_raw_post_data = -1，重启后正常

# 6. 完整代码

client.php

 

```php
    <?php
    $opt = array(
        //调试
        'trace' => true,
        //命名空间
        'uri' => 'name',
        //地址
        'location' => 'http://localhost/test/server.php',
        //账号
        'login' => 'root',
        //密码
        'password' => '123',
    );
    try {
        $client = new SoapClient(null, $opt);
        //调用function
        $res = $client->__soapCall('t', array(time()));
        //调用class
        //$res = $client->run();
        print_r($res);
    } catch (Exception $e) {
        //输出错误
        echo $e->getMessage();
        //输出返回(必须启用trace选项)
        echo $client->__getLastResponse();
    }
```


server.php

```php
    <?php
    function t($x) {
        return $x;
    }
    
    class test {
        function run() {
            return __CLASS__;
        }
    }
    
    //验证权限
    if ($_SERVER['PHP_AUTH_USER'] != 'root' || $_SERVER['PHP_AUTH_PW'] != '123') {
        throw new Exception('Access denied!');
    }
    
    $opt = array('uri' => 'name');
    $server = new SoapServer(null, $opt);
    //添加function
    $server->addFunction('t');
    //添加class
    //$server->setClass('test');
    //执行
    $server->handle();
```

[0]: http://www.cnblogs.com/xiejixing/p/5403592.html
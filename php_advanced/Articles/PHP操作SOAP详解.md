# PHP操作SOAP详解

 时间 2017-12-19 18:06:22  

原文[https://www.congcong.us/post/php_soap_server_client.html][1]


主要记录一下PHP如何操作SOAP，关于基础知识可以通过《SOAP与WSDL详解》了解，这里使用soap官方扩展来进行实现：

## 准备工作 

php.ini 开启扩展

    extension=soap.so
    extension=openssl.so
    extension=curl.so
     
    

配置soap相关参数

    [soap]
    ; Enables or disables WSDL caching feature. 开启或禁用WSDL缓存特性
    soap.wsdl_cache_enabled=1
    ; Sets the directory name where SOAP extension will put cache files.设置SOAP扩展放置缓存文件的目录
    soap.wsdl_cache_dir="/tmp"
    ; (time to live) Sets the number of second while cached file will be used 设置缓存生效时间（秒）
    ; instead of original one.
    soap.wsdl_cache_ttl=86400
     
    

## 基础类介绍 

### SoapClient类 

作为给定Web Services的客户端，两种操作形式

* WSDL模式 构造器可以使用WSDL文件名作为参数，并从WSDL中提取服务所使用的信息。

* Non-WSDL模式 使用参数来传递要使用的信息。

### SOAPServer类 

用来提供Web services，两种操作形式

* WSDL模式 服务实现了WSDL提供的接口

* non-WSDL模式 参数被用来管理服务的行为。

## 服务端示例 

    <?php
    Class RobotInfo
    {
        public function sayHello(){
            return "Say Hello!";
        }
    }
     
    //wsdl方式
    //$s = new SoapServer('RobotInfo.wsdl');
     
    //在non-wsdl方式中option服务端的location是选择性的，可以不提供  
    $s = new SoapServer(, array("location" => "http://localhost/server.php", "uri" => "server.php"));
    $s->setClass("RobotInfo");
    $s->handle();
     
    

## 客户端示例 

    <?php
    try {
        //wsdl方式
        //$soap = new SoapClient("http://localhost/RobotInfo.wsdl");
     
        //在non-wsdl方式中option location系必须提供的
        $soap = new SoapClient(, array('location' => "http://localhost/server.php", 'uri' => 'server.php'));
     
        //两种调用方式:直接调用方法和用__soapCall简接调用
        $result = $soap->sayHello(); // $soap->__soapCall("sayHello", array());
        echo $result;
    } catch (SoapFault $e) {
        echo $e->getMessage();
    } catch (Exception $e) {
        echo $e->getMessage();
    }


[1]: https://www.congcong.us/post/php_soap_server_client.html

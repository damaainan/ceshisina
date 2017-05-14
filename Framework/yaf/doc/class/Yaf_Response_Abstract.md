## The Yaf_Response_Abstract class

### 简介

响应对象和请求对象相对应, 是发送给请求端的响应的载体

在PHP5.3之后, 打开yaf.use_namespace的情况下, 也可以使用 Yaf\Response_Abstract


```php
abstract Yaf_Response_Abstract {
protected array _body ;
protected array _header ;
public boolean setBody ( string $body ,
string $name = NULL );
public boolean prependBody ( string $body ,
string $name = NULL );
public boolean appendBody ( string $body ,
string $name = NULL );
public boolean clearBody ( void );
public string getBody ( void );
public boolean response ( void );
public boolean setRedirect ( string $url );
public string __toString ( void );
}
```


属性说明

- _body  
响应给请求的Body内容

- _header  
响应给请求的Header, 目前是保留属性

## The Yaf_Response_Http class

### 简介

Yaf_Response_Http是在Yaf作为Web应用的时候默认响应载体

```php
final Yaf_Response_Http extends Yaf_Response_Abstract {
protected array _code = 200 ;
}
```


属性说明

- _code  
响应给请求端的HTTP状态码

## The Yaf_Response_Cli class

### 简介
Yaf_Response_Cli是在Yaf作为命令行应用的时候默认响应载体

```php
final Yaf_Response_Cli extends Yaf_Response_Abstract {
}
```


## 名称

##### Yaf_Response_Abstract::setBody

(Since Yaf 1.0.0.0)

    public boolean Yaf_Response_Abstract::setBody( string  $body ,
                                                    string  $name = NULL );
设置响应的Body, $name参数是保留参数, 目前没有特殊效果, 留空即可

> 参数

$body  
要响应的字符串, 一般是一段HTML, 或者是一段JSON(返回给Ajax请求)

$name  
要响应的字符串的key, 一般的你可以通过指定不同的key, 给一个response对象设置很多响应字符串, 可以在所有的请求结束后做layout, 如果你不做特殊处理, 交给Yaf去发送响应的话, 所有你设置的响应字符串, 按照被设置的先后顺序被输出给客户端.

> 返回值

成功返回Yaf_Response_Abstract, 失败返回FALSE

> **Yaf_Response_Abstract::setBody 的例子**

```php
     <?php
     class IndexController extends Yaf_Controller_Abstract {
        public funciton init() {
            $this->getResponse()->setBody("Hello World");
        }
     }
     ?>
```

## 名称

##### Yaf_Response_Abstract::appendBody

(Since Yaf 1.0.0.0)

    public boolean Yaf_Response_Abstract::appendBody( string  $body ,
                                                    string  $name = NULL );
往已有的响应body后附加新的内容, $name参数是保留参数, 目前没有特殊效果, 留空即可

> 参数

$body  
要附加的字符串, 一般是一段HTML, 或者是一段JSON(返回给Ajax请求)

$name  
保留参数, 目前没有特殊效果

> 返回值

成功返回Yaf_Response_Abstract, 失败返回FALSE

> **Yaf_Response_Abstract::appendBody 的例子**

```php
     <?php
     class IndexController extends Yaf_Controller_Abstract {
        public funciton init() {
            $this->getResponse()->appendBody("Hello World");
        }
     }
     ?>
```




## 名称

##### Yaf_Response_Abstract::prependBody

(Since Yaf 1.0.0.0)

    public boolean Yaf_Response_Abstract::prependBody( string  $body ,
                                                        string  $name = NULL );
往已有的响应body前插入新的内容, $name参数是保留参数, 目前没有特殊效果, 留空即可

> 参数

$body  
要插入的字符串, 一般是一段HTML, 或者是一段JSON(返回给Ajax请求)

$name  
保留参数, 目前没有特殊效果

> 返回值

成功返回Yaf_Response_Abstract, 失败返回FALSE

> **Yaf_Response_Abstract::prependBody 的例子**

```php
     <?php
     class IndexController extends Yaf_Controller_Abstract {
        public funciton init() {
            $this->getResponse()->prependBody("Hello World");
        }
     }
     ?>    
```


## 名称

##### Yaf_Response_Abstract::getBody

(Since Yaf 1.0.0.0)

    public string Yaf_Response_Abstract::getBody( void  );
获取已经设置的响应body

> 参数

void  
本方法不需要参数(起码暂时不需要)

> 返回值

成功返回已设置的body值, 失败返回NULL

> **Yaf_Response_Abstract::getBody 的例子**

```php
     <?php
     class IndexController extends Yaf_Controller_Abstract {
        public funciton init() {
            echo $this->getResponse()->getBody();
        }
     }
     ?>
```



## 名称

##### Yaf_Response_Abstract::clearBody

(Since Yaf 1.0.0.0)

    public boolean Yaf_Response_Abstract::clearBody( void  );
清除已经设置的响应body

> 参数

void  
本方法不需要参数(起码暂时不需要)

> 返回值

成功返回Yaf_Response_Abstract, 失败返回FALSE

> **Yaf_Response_Abstract::clearBody 的例子**

```php
     <?php
     class IndexController extends Yaf_Controller_Abstract {
        public funciton init() {
            $this->getResponse()->clearBody();
        }
     }
     ?>
```

## 名称

##### Yaf_Response_Abstract::response

(Since Yaf 1.0.0.0)

    public boolean Yaf_Response_Abstract::response( void  );
发送响应给请求端

> 参数

void  
本方法不需要参数(起码暂时不需要)

> 返回值

成功返回TRUE, 失败返回FALSE

> **Yaf_Response_Abstract::response 的例子**

```php
     <?php
     class IndexController extends Yaf_Controller_Abstract {
        public funciton init() {
            $this->getResponse()->response();
        }
     }
     ?>
```



## 名称

##### Yaf_Response_Abstract::setRedirect

(Since Yaf 1.0.0.0)

    public boolean Yaf_Response_Abstract::setRedirect( string  $url );
重定向请求到新的路径

> 注意
和Yaf_Controller_Abstract::forward不同, 这个重定向是HTTP 301重定向   


> 参数

$url  
要重定向到的URL

> 返回值

成功返回Yaf_Response_Abstract, 失败返回FALSE

> **Yaf_Response_Abstract::setRedirect 的例子**

```php
     <?php
     class IndexController extends Yaf_Controller_Abstract {
        public funciton init() {
            $this->getResponse()->setRedirect("http://domain.com/");
        }
     }
     ?>
```




## 名称

##### Yaf_Response_Abstract::__toString

(Since Yaf 1.0.0.0)

    public string Yaf_Response_Abstract::__toString( void  );
魔术方法

> 参数

void  
本方法不需要参数

> 返回值

Yaf_Response_Abstract中的body值













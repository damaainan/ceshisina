##The Yaf_Route class

### 简介

Yaf_Route_Interface是Yaf路由协议的标准接口, 它的存在使得用户可以自定义路由协议

在PHP5.3之后, 打开yaf.use_namespace的情况下, 也可以使用 Yaf\Route_Interface

```php
Interface Yaf_Route {
abstract public boolean route ( Yaf_Request_Abstract $request );
}
```
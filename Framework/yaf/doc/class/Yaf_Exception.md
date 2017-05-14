## The Yaf_Exception class

### 简介

Yaf_Exception是Yaf使用的异常类型, 它继承自Exception, 并实现了异常链.

在PHP5.3之后, 打开yaf.use_namespace的情况下, 也可以使用 Yaf\Exception

> 注意
只有在yaf.throw_exception(php.ini)或者yaf.throwException(配置文件)开启的情况下, Yaf才会抛出异常, 否则Yaf在出错的时候将trigger_error, 这种情况下, 可以使用Yaf_Dispatcher::setErrorHandler来捕获错误.

```php
Yaf_Exception {
protected string message ;
protected string code ;
private Exception _previous ;
public void __construct ( string $message ,
int $code = 0 ,
Exception $previous = NULL );
final public string Exception::getMessage ( void );
final public int Exception::getCode ( void );
public final Exception getPrevious ( void );
final public string Exception::getFile ( void );
final public int Exception::getLine ( void );
}
```

属性说明

- message  
异常信息

- code  
异常代码

- _previous  
此异常之前的异常

## Yaf_Exception_StartupError

### 简介

继承自Yaf_Exception, 在Yaf启动失败的时候抛出.

在PHP5.3之后, 打开yaf.use_namespace的情况下, 也可以使用 Yaf\Exception\StartupError


```php
Yaf_Exception_StartupError extends Yaf_Exception {
protected string code = YAF_ERR_STARTUP_FAILED ;
}
```



## Yaf_Exception_RouterFailed

### 简介

继承自Yaf_Exception, 在路由失败的时候抛出.

在PHP5.3之后, 打开yaf.use_namespace的情况下, 也可以使用 Yaf\Exception\RouterFailed


```php
Yaf_Exception_RouterFailed extends Yaf_Exception {
protected string code = YAF_ERR_ROUTER_FAILED ;
}
```


## Yaf_Exception_DispatchFailed

### 简介

继承自Yaf_Exception, 在分发失败的时候抛出.

在PHP5.3之后, 打开yaf.use_namespace的情况下, 也可以使用 Yaf\Exception\DispatchFailed

```php
Yaf_Exception_DispatchFailed extends Yaf_Exception {
protected string code = YAF_ERR_DISPATCH_FAILED ;
}
```


## Yaf_Exception_LoadFailed

### 简介

继承自Yaf_Exception, 在加载需要类失败的时候抛出.

在PHP5.3之后, 打开yaf.use_namespace的情况下, 也可以使用 Yaf\Exception\LoadFailed

```php
Yaf_Exception_LoadFailed extends Yaf_Exception {
protected string code = YAF_ERR_AUTOLOAD_FAILED ;
}
```


## Yaf_Exception_LoadFailed_Module

### 简介

继承自Yaf_Exception_LoadFailed, 在找不到路由指定的模块时抛出

在PHP5.3之后, 打开yaf.use_namespace的情况下, 也可以使用 Yaf\Exception\LoadFailed\Module


```php
Yaf_Exception_LoadFailed_Module extends Yaf_Exception_LoadFailed {
protected string code = YAF_ERR_NOTFOUND_MODULE ;
}
```



## Yaf_Exception_LoadFailed_Controller

### 简介

继承自Yaf_Exception_LoadFailed, 在找不到路由指定的控制器时抛出

在PHP5.3之后, 打开yaf.use_namespace的情况下, 也可以使用 Yaf\Exception\LoadFailed\Controller


```php
Yaf_Exception_LoadFailed_Controller extends Yaf_Exception_LoadFailed {
protected string code = YAF_ERR_NOTFOUND_CONTROLLER ;
}
```

## Yaf_Exception_LoadFailed_Action

### 简介

继承自Yaf_Exception_LoadFailed, 在找不到路由指定的动作时抛出

在PHP5.3之后, 打开yaf.use_namespace的情况下, 也可以使用 Yaf\Exception\LoadFailed\Action


```php
Yaf_Exception_LoadFailed_Action extends Yaf_Exception_LoadFailed {
protected string code = YAF_ERR_NOTFOUND_ACTION ;
}
```


## Yaf_Exception_LoadFailed_View

### 简介

继承自Yaf_Exception_LoadFailed, 在找不到指定的视图模板文件时抛出

在PHP5.3之后, 打开yaf.use_namespace的情况下, 也可以使用 Yaf\Exception\LoadFailed\View


```php
Yaf_Exception_LoadFailed_View extends Yaf_Exception_LoadFailed {
protected string code = YAF_ERR_NOTFOUND_VIEW ;
}
```

## Yaf_Exception_TypeError

### 简介

继承自Yaf_Exception, 在关键逻辑参数出错的时候抛出

在PHP5.3之后, 打开yaf.use_namespace的情况下, 也可以使用 Yaf\Exception\TypeError


```php
Yaf_Exception_TypeError extends Yaf_Exception {
protected string code = YAF_ERR_TYPE_ERROR ;
}
```
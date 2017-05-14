## The Yaf_Plugin_Abstract class

## 简介
Yaf_Plugin_Abstract是Yaf的插件基类, 所有应用在Yaf的插件都需要继承实现这个类, 这个类定义了7个方法, 依次在7个时机的时候被调用.

在PHP5.3之后, 打开yaf.use_namespace的情况下, 也可以使用 Yaf\Plugin_Abstract

```php
abstract Yaf_Plugin_Abstract {
public void routerStartup( Yaf_Request_Abstract $request ,
Yaf_Response_Abstarct $response );
public void routerShutdown( Yaf_Request_Abstract $request ,
Yaf_Response_Abstarct $response );
public void dispatchLoopStartup( Yaf_Request_Abstract $request ,
Yaf_Response_Abstarct $response );
public void preDispatch( Yaf_Request_Abstract $request ,
Yaf_Response_Abstarct $response );
public void postDispatch( Yaf_Request_Abstract $request ,
Yaf_Response_Abstarct $response );
public void dispatchLoopShutdown( Yaf_Request_Abstract $request ,
Yaf_Response_Abstarct $response );
}

```


> 注意

插件有两种部署方式, 一种是部署在plugins目录下, 通过名称中的后缀(可通过ap.name_suffix和ap.name_separator来改变具体命名形式)来使得自动加载器可以正确加载. 另外一种是放置在类库, 由普通加载规则加载, 但无论哪种方式, 用户定义的插件都需要继承自Yaf_Plugin_Abstract.
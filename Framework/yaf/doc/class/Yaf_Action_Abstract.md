## The Yaf_Action_Abstract class

### 简介
Yaf_Action_Abstract是MVC中C的动作, 一般而言动作都是定义在Yaf_Controller_Abstract的派生类中的, 但是有的时候, 为了使得代码清晰, 分离一些大的控制器, 则可以采用单独定义Yaf_Action_Abstract来实现.

Yaf_Action_Abstract体系具有可扩展性, 可以通过继承已有的类, 来实现这个抽象类, 从而添加应用自己的应用逻辑.

在PHP5.3之后, 打开yaf.use_namespace的情况下, 也可以使用 Yaf\Action_Abstract.


```php
abstract Yaf_Action_Abstract extends Yaf_Action_Controller {
public abstract void execute ( void );
}
```
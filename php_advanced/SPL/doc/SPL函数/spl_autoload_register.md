 spl_autoload_register — 注册给定的函数作为 __autoload 的实现

### 说明

> bool  **spl_autoload_register** ([ callable $autoload_function [, bool $throw = true [, bool $prepend = false ]]] )

将函数注册到SPL __autoload函数队列中。如果该队列中的函数尚未激活，则激活它们。 

如果在你的程序中已经实现了 __autoload() 函数，它必须显式注册到 __autoload() 队列中。因为 **spl_autoload_register()** 函数会将Zend Engine中的 __autoload() 函数取代为 spl_autoload() 或 spl_autoload_call() 。 

如果需要多条 autoload 函数， **spl_autoload_register()** 满足了此类需求。 它实际上创建了 autoload 函数的队列，按定义时的顺序逐个执行。相比之下， __autoload() 只可以定义一次。 

### 参数

- autoload_function   
欲注册的自动装载函数。如果没有提供任何参数，则自动注册 autoload 的默认实现函数 spl_autoload() 。 

- throw  
此参数设置了 autoload_function 无法成功注册时， **spl_autoload_register()** 是否抛出异常。 

- prepend  
如果是 true， **spl_autoload_register()** 会添加函数到队列之首，而不是队列尾部。 

### 返回值

成功时返回 **TRUE**， 或者在失败时返回 **FALSE**。 

### 更新日志

版本 说明 5.3.0 引入了命名空间的支持。 5.3.0 添加了 prepend 参数。 

### 范例

**Example #1 **spl_autoload_register()**作为 __autoload() 函数的替代**
```
 <?php  
 // function __autoload($class) {  
// include 'classes/' . $class . '.class.php';  
// }  
function my_autoloader ( $class ) {  
    include 'classes/' . $class . '.class.php' ;  
}  
spl_autoload_register ( 'my_autoloader' );  
 // 或者，自 PHP 5.3.0 起可以使用一个匿名函数 
spl_autoload_register (function ( $class ) {  
    include 'classes/' . $class . '.class.php' ;  
});  
 ?>  
```

 **Example #2 class 未能加载的 **spl_autoload_register()**例子**
```
 <?php  
 namespace Foobar ;  
  
class Foo {  
static public function test ( $name ) {  
print '[[' . $name . ']]' ;  
}  
}  
 spl_autoload_register ( __NAMESPACE__ . '\Foo::test' ); // 自 PHP 5.3.0 起  
 new InexistentClass ;  
 ?>  
```
 以上例程的输出类似于：

    [[Foobar\InexistentClass]]
    Fatal error: Class 'Foobar\InexistentClass' not found in ...


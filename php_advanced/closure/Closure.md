# Closure 类

(PHP 5 >= 5.3.0, PHP 7)

## 简介

用于代表 匿名函数 的类. 

匿名函数（在 PHP 5.3 中被引入）会产生这个类型的对象。在过去，这个类被认为是一个实现细节，但现在可以依赖它做一些事情。自 PHP 5.4 起，这个类带有一些方法，允许在匿名函数创建后对其进行更多的控制。 

除了此处列出的方法，还有一个 ___invoke_ 方法。这是为了与其他实现了 __invoke()魔术方法 的对象保持一致性，但调用匿名函数的过程与它无关。 

## 类摘要
```
 Closure {

/* 方法 */

 __construct ( void )

 public  static  Closure  bind ( Closure $closure , object $newthis , mixed $newscope = 'static' )

 public  Closure  bindTo ( object $newthis , mixed $newscope = 'static'  )

}
```
## Table of Contents

* Closure::__construct — 用于禁止实例化的构造函数
* Closure::bind — 复制一个闭包，绑定指定的$this对象和类作用域。
* Closure::bindTo — 复制当前闭包对象，绑定指定的$this对象和类作用域。

----

# Closure::__construct

(PHP 5 >= 5.3.0, PHP 7)

 Closure::__construct — 用于禁止实例化的构造函数

### 说明

**Closure::__construct** ( void )

这个方法仅用于禁止实例化一个 Closure 类的对象。这个类的对象的创建方法写在 匿名函数 页。 

### 参数

此函数没有参数。

### 返回值

没有返回值，它只是简单的触发一个错误 （类型是 **E_RECOVERABLE_ERROR**）。

-----

# Closure::bind

(PHP 5 >= 5.4.0, PHP 7)

 Closure::bind — 复制一个闭包，绑定指定的$this对象和类作用域。

### 说明

 public  static  Closure  **Closure::bind** ( Closure $closure , object $newthis , mixed $newscope = 'static' )

这个方法是 Closure::bindTo() 的静态版本。查看它的文档获取更多信息。 

### 参数

- closure
需要绑定的匿名函数。 

- newthis
需要绑定到匿名函数的对象，或者 **NULL** 创建未绑定的闭包。 

- newscope
想要绑定给闭包的类作用域，或者 'static' 表示不改变。如果传入一个对象，则使用这个对象的类型名。 类作用域用来决定在闭包中 $this 对象的 私有、保护方法 的可见性。 The class scope to which associate the closure is to be associated, or 'static' to keep the current one. If an object is given, the type of the object will be used instead. This determines the visibility of protected and private methods of the bound object. 

### 返回值

返回一个新的 Closure 对象 或者在失败时返回 **FALSE**


-----

# Closure::bindTo

(PHP 5 >= 5.4.0, PHP 7)

 Closure::bindTo — 复制当前闭包对象，绑定指定的$this对象和类作用域。

### 说明

 public  Closure  **Closure::bindTo** ( object $newthis , mixed $newscope = 'static' )

创建并返回一个 匿名函数， 它与当前对象的函数体相同、绑定了同样变量，但可以绑定不同的对象，也可以绑定新的类作用域。 

“绑定的对象”决定了函数体中的 _$this_ 的取值，“类作用域”代表一个类型、决定在这个匿名函数中能够调用哪些 私有 和 保护 的方法。 也就是说，此时 $this 可以调用的方法，与 newscope 类的成员函数是相同的。 

静态闭包不能有绑定的对象（ newthis 参数的值应该设为 **NULL**）不过仍然可以用 bubdTo 方法来改变它们的类作用域。 

This function will ensure that for a non-static closure, having a bound instance will imply being scoped and vice-versa. To this end, non-static closures that are given a scope but a **NULL** instance are made static and non-static non-scoped closures that are given a non-null instance are scoped to an unspecified class. 

> **Note**: 

> 如果你只是想要复制一个匿名函数，可以用 cloning 代替。 

### 参数

- newthis
绑定给匿名函数的一个对象，或者 **NULL** 来取消绑定。 

- newscope
关联到匿名函数的类作用域，或者 'static' 保持当前状态。如果是一个对象，则使用这个对象的类型为心得类作用域。 这会决定绑定的对象的 保护、私有成员 方法的可见性。 

### 返回值

返回新创建的 Closure 对象 或者在失败时返回 **FALSE**


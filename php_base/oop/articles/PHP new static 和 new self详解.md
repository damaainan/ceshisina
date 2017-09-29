# PHP new static 和 new self详解

最近在一个视频的评论被问到一个小问题：这里选择用static 而不是self有特殊的考虑么？或者我们可以这样转换一下问题：

PHP 的 `new static` 和 `new self` 具体有什么?

其实这个来看一个例子应该就很清晰了：

```php
<?php
class Father {
 
     public static function getSelf() {
          return new self();
     }
     
     public static function getStatic() {
          return new static();
     }
}
 
class Son extends Father {}
 
echo get_class(Son::getSelf()); // Father
echo get_class(Son::getStatic()); // Son
echo get_class(Father::getSelf()); // Father
echo get_class(Father::getStatic()); // Father

```

这里面注意这一行 `get_class(Son::getStatic());` 返回的是 Son 这个 class,可以总结如下：

new self

1.`self`返回的是 `new self` 中关键字 `new` 所在的类中，比如这里例子的 ：

```php
    public static function getSelf() {
      return new self(); // new 关键字在 Father 这里
    }
```
始终返回 Father。

`new static`

2.`static` 则上面的基础上，更聪明一点点：`static` 会返回执行 `new static()` 的类，比如 Son 执行 `get_class(Son::getStatic())` 返回的是 Son, Father 执行 `get_class(Father::getStatic())` 返回的是 Father而在没有继承的情况下，可以认为 `new self` 和 `new static`是返回相同的结果。


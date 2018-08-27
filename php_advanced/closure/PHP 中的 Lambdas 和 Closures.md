# PHP 中的 Lambdas 和 Closures 

   发表于 2016-09-30   |   分类于  [PHP][0]

PHP 5.3 开始，添加了 Lambdas（匿名函数）和 CLosures（闭包）这俩个新的特性。函数式编程也越来越能够得到程序员的追捧。其他语言，比如 Javascript 或 Ruby 中也经常能看到匿名函数的实现。

那么什，么是 Lambda 函数？

一个匿名函数可以简单理解为一个没有被定义的函数。

没有名字函数，允许临时创建一个没有指定命名的函数。最经常用作回调函数（callback）参数的值。当然，也有其它应用的情况。

比如我们定义一个正式的函数定义，可能是下面这个样子：

    
```php
<?php
function greeting()
{
     return "Hello world.";
}
// call function
echo greeting();
```
一个 lambda 函数

    
```php
<?php
function() {
     return "Hello world.";
}
```
因为 lambda 函数没有命名，不能像正式的函数那样调用。需要把它赋值给一个变量。

    
```php
<?php
$greeting = function() {
     return "Hello world.";
}
// call function
echo $greeting();
```
或者把 lambda 函数作为另一个函数的参数。

    
```php
<?php
function shout ($message)
{
     echo $message();
}
shout(function(){
     return 'Hello world.';
});
```
为什么要使用 lambda 函数？

通常使用匿名函数可以让我们摆脱那些在程序中只会调用一次的函数。很多时候，我们需要定义一个函数来执行一项任务，但并不意味着我们需要一直需要该函数，也不需要它之后还能被调用。这种时候，我们就可以使用匿名函数。

在函数式编程中，lambdas 被称为一流的函数，因为它们能够：

* 作为参数来传递。
* 可在运行过程中创建并使用。
* 从函数返回。
* 分配给变量。

可以使用内置函数 create_function ，作用是一样的。

    
```php
<?php
$greeting = create_function('', 'echo "Hello world."');
$greeting();
```
但是 create_function 的语法很繁琐。一方面，该代码是在一个大引证字符串中。因此，不要期望从 PHP 编辑器获得帮助，而且不要忘了尽量避免使用该代码串。此外，create_function 的代码是在执行时进行编译，而且结果函数无法被缓存。

什么是 Closure ？

如果 Lambdas 是没有名字的函数，那么 Closures 仅比 Lambdas 多一个上下文。就是说，Closure 是个匿名函数，在其创建时，将来自创建该函数的代码范围内得变量值附加到它本身。

    
```php
<?php
// Create a user
$user = "Philip";
// Create a Closure
$greeting = function() use ($user) {
     echo "Hello $user";
};
// Greet the user
$greeting(); // Returns "Hello Philip"
```
从上面的例子能看到，在 Closure 定义中，通过 `use` 语句传递了 `$user` 变量, 所以 Closure 内部能够访问到 $user 。但是如果在 Closure 作用域内修改变量 $user 的值，并不会修改父作用域的 $user 值。如果希望修改父作用域的 $user，可以传递变量的引用 &$user 给 Closure 。

    
```php
<?php
// Set counter

$i = 0;

// Increase counter within the scope

// of the function

$closure = function () use ($i){ $i++; };

// Run the function

$closure();

// The global count hasn't changed

echo $i; // Returns 0

// Reset count

$i = 0;

// Increase counter within the scope

// of the function but pass it as a reference

$closure = function () use (&$i){ $i++; };

// Run the function

$closure();

// The global count has increased

echo $i; // Returns 1
```
Closure 在作为回调函数的参数时，非常有用，比如 array_map、array_filter、array_reduce、array_walk等。

    
```php
<?php
// An array of names

$users = array("John", "Jane", "Sally", "Philip");

// Pass the array to array_walk

array_walk($users, function ($name) {
  echo "Hello $name<br>";
});

// Returns
// -> Hello John
// -> Hello Jane
// -> ..
```
同样的，可通过 use 语句把父作用变量传递到 Closure 中。

    
```php
<?php
// Set a multiplier

$multiplier = 3;

// Create a list of numbers

$numbers = array(1,2,3,4);

// Use array_walk to iterate

// through the list and multiply

array_walk($numbers, function($number) use($multiplier){
  echo $number * $multiplier;
});
```

在目前流行的 laravel 框架中，能看到如下处理路由的方法。
   
```php
<?php
Route::get('user/(:any)', function($name){
  return "Hello " . $name;
});
```
有没有觉得非常的简洁和清晰。利用这俩个新的特性，可以尝试重构之前的老代码，使得代码更简洁、更直观、更容易理解。也可以进一步学习函数式编程，感受新的开发模式和打破原有编码习惯。

[0]: /categories/PHP/
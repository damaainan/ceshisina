## 其实你并不懂如何定义一个 PHP 函数

来源：[http://www.cnblogs.com/summerblue/p/8795463.html](http://www.cnblogs.com/summerblue/p/8795463.html)

时间 2018-04-11 14:23:00

![][0]
 
```php
<?php
function divide($dividend, $divisor){
    return $dividend / $divisor;
}
echo divide(12, 4);
echo divide('sa', 'sd');
```
 
这段代码乍一看没毛病，但是如果你向它传递「任意」参数，它就会出问题。
 
写出好的代码是一个学无止境的过程。让我们来改进我们编写 PHP 函数的方式。 看下上面的代码，想想第二个函数调用那里会发生什么情况？ 它会接受你输入的任何参数，并且尝试执行然后抛出一个数学错误。 但是我们怎么才能让一个函数严格接收能使其正确执行的参数呢？ 现代的 PHP 解决了这个问题，并且有更多妙法能让你的代码质量更进一层，没有 bug。
 
#### 函数参数与它们的数据类型
 
你可以严格控制你的函数，使其只接收让它正确运行的参数。让我们改变上面的函数定义：
 
```php
<?php
function divide(int $dividend, int $divisor){
    return $dividend / $divisor;
}
echo divide(12, 4);
echo divide('sa', 'sd');
```
 
现在，第二次调用这个函数将会抛出一个致命错误，指出其需要的参数必须是整数类型。你可以用不同的方式处理参数。
 
```php
<?php
// 可选参数
function getName(string $firstName, string $lastName = ''){
    return $firstName . ' ' . $lastName;
}
echo getName('Muhammad', 'Nauman'); // Muhammad Nauman
echo getName('Adam'); // Adam
function divide(int $dividend, int $divisor = 2){
      return $dividend / $divisor;
}
echo divide(12, 4); // 3
echo divide(12); // 6
// 仅接收 Request 类的实例参数 $request
function getReuestParams(Request $request){
    return $request->only('name', 'email');
}
```
 
在定义的时候，将可选参数或带默认值的参数作为最后一个参数。
 
PHP 7.1 也给可迭代数据介绍了一种伪类型。它能接收任何可迭代的数据。
 
![][1]
 
上图是使用了`iterable`数据类型的函数。
 
现在通过代码，我们可以控制的更多了，不是吗？没错，确实如此！
 
#### 函数与它们的返回值
 
正如你可以控制传递给指定函数的参数类型一样，你也可以控制函数的返回值类型。它能确保你的函数总是返回同一个数据类型，并且不会崩溃。我们改变一下上面的代码：
 
```php
<?php
// 可选参数
function getName(string $firstName, string $lastName = '') : string {
    return $firstName . ' ' . $lastName;
}
echo getName('Muhammad', 'Nauman'); // Muhammad Nauman
echo getName('Adam'); // Adam
function divide(int $dividend, int $divisor = 2) : int {
      return $dividend / $divisor;
}
echo divide(12, 4); // 3
echo divide(12); // 6
// 仅接收 Request 类的实例 $request 作为参数
function getReuestParams(Request $request) : array {
    return $request->only('name', 'email');
}
// 返回 void 类型
$attribute = 2;
function changeAttribute(string &$param, $value) : void {
    $param = $value;
}
changeAttribute($attribute, 5);
echo $attribute; // 5
```
 
PHP 逐渐引入了这些功能，如：5.1 版引入的数组类型作为参数，5.4 版引入的可调用类型（callable type），以及 7.1 版引入的 void 返回类型等。
 
#### 可选参数 VS 可空参数
 
除了可选参数外，你还可以定义可空（nullable）参数，这意味着你可以定义一种可空参数类型。我们来看个例子：
 
```php
<?php
function nullableParameter(?string $name)
{
    return $name;
}
echo nullableParameter(null); // 不会返回任何东西
echo nullableParameter('Nauman'); // Nauman
echo nullableParameter(); // 致命错误
function nullableParameterWithReturnType(?string $name) : string
{
    return $name;
}
echo nullableParameter(null); // 致命错误，必须返回 string 类型
echo nullableParameter('Nauman'); // Nauman
function nullableReturnType(string $name) : ?string
{
    return $name;
}
echo nullableParameter(null); // 致命错误，$name 应该是 string 类型
echo nullableParameter('Nauman'); // Nauman
```
 
显然，可空参数不是可选参数，你必须传递一个值或者是`null`。我个人喜欢使用空值作为可选参数，但这取决于你的任务需求。
 
### 总结
 
从我开启职业生涯的时候我就使用 PHP 了，我真的很爱这门语言。在过去很长一段时间里，它都是开发 web 应用的不二之选。现在 7.x 版本又填补了许多高级特性和现代化应用的需求，并且提高了开发者的效率。这门语言正不断的发生改变，找出这些变化，并停止过去的写法，放弃你原来的习惯并自豪的使用这些新特性，让你的代码更易读易懂。 Happy coding :)
 
更多现代化 PHP 知识，请前往 [Laravel / PHP 知识社区][2]
 


[2]: https://laravel-china.org/topics/9661
[0]: ../img/Vfu2uaZ.jpg 
[1]: ../img/BnqIva2.png 
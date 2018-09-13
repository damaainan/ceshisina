# Generator 的异常处理

 时间 2017-08-19 11:46:16  SegmentFault

原文[https://segmentfault.com/a/1190000010743480][1]


本文是我在研究 PHP 异步编程时的总结。对于相当多的 PHPer 来说，可能都不知道 `Generator`，或者对 `Generaotr` 的流程不是很熟悉。因为 `Generator` 使得程序不再是顺序的。鉴于本人的水平有限，如果有不同意见，还望指点一二，不胜感激！

## PHP 中的异常处理

从 PHP 5 开始，PHP 为我们提供了 `try catch` 来进行异常处理。当我们使用 `catch` 将异常捕获，那么一场后续的代码就会执行。我们看看下面的例子。

```php
try {
    throw new Exception('e');
} catch (Exception $e) {
    echo $e->getMessage(); // output: e
}

echo 2; // output: 2
```

如果我们没有将异常捕获，那么后面的代码就不会执行了。

    throw new Exception('e'); // throw an exception
    
    echo 2; // not execute

## Generator 的 throw 方法

在 PHP 中，Generator 提供了 `throw` 方法来抛出异常。用法和普通的异常一样，只不过把 `throw` 关键字改成了方法调用。 

```php
function gen()
{
    yield 0;
    yield 1;
    yield 2;
    yield 3;
}

$gen = gen();

$gen->throw(new Exception('e')); // throw an exception

var_dump($gen->valid()); // output: false

echo 2; // not execute
```

同样的，我们可以这个异常捕获，通过 try catch 来进行。

```php
try {
    $gen->throw(new Exception('e'));
} catch (Exception $e) {
    echo $e->getMessage(); // output: e
}

var_dump($gen->valid()); // output: false

echo 2; // output: 2
```

我们可以看到，当我们使用 `throw` 抛出异常后，当前的生成器的 valid 变成了 false。但是考虑下面一种情况，当我们在外面调用 `throw` 方法后，在生成器函数中捕获异常，会发生什么呢？我们来看下面的例子。

```php
function gen()
{
    yield 0;
    try {
        yield;
    } catch (Exception $e) {
        echo $e->getMessage(); // output: e
    }
    yield 2;
    yield 3;
}

$gen = gen();
$gen->next(); // reach the point of catching exception
$gen->throw(new Exception('e'));

var_dump($gen->valid()); // output: true

echo 2; // output: 2
```

当我们在生成器函数捕获来自 `throw` 方法抛出的异常后，生成器依然是 valid 的。但是如果像刚才一样只是在调用 `throw` 方法，那么生成器就结束了。

### 在生成器函数中抛出异常

```php
function gen()
{
    yield 0;
    throw new Exception('e');
    yield 2;
    yield 3;
}

$gen = gen();

$gen->next();

$gen->current(); // throw an exception

var_dump($gen->valid()); // output: false

echo 2; // not execute
```

之前我们看到的是调用 `throw` 方法来抛出异常。那么在生成器函数中，抛出一个异常而没有在生成器函数中捕获，结果也都是一样的。同样的，如果在生成器函数中捕获了异常，那么就和之前的例子一样了。

在理解了上面的例子之后，我们就要考虑一下，如果有嵌套的生成器，会发生什么了。

## 嵌套生成器

当我们在一个生成器函数中， `yield` 了另外一个生成器函数之后，就会变成嵌套生成器。我们来看下面的例子。 

```php
function subGen()
{
    yield 1;
    throw new Exception('e');
    yield 4;
}

function gen()
{
    yield 0;
    yield subGen();
    yield 2;
    yield 3;
}

$gen = gen();

$gen->next();
$gen->current()->next(); // throw an exception

echo 2; // not execute
```

对于嵌套的生成器来说，如果子生成器中抛出了异常，那么在没有捕获这个异常的情况下，会一级一级向上抛出，直到结束。

刚才我们尝试了，在抛出异常之后，valid 的返回值变成了 false。那么在嵌套生成器中，是不是也是这样呢？我们把异常捕获，使程序能够继续执行下去，来看下面这个例子。

```php
function subGen()
{
    yield 1;
    throw new Exception('e');
    yield 4;
}

function gen()
{
    yield 0;
    yield subGen();
    yield 2;
    yield 3;
}

$gen = gen();

$gen->next();
try {
    $gen->current()->next();
} catch (Exceprion $e) {
    echo $e->getMessage(); //output: e
}

var_dump($gen->valid()); // output: true

echo 2; // output: 2
```
所以，当子生成器抛出异常后在迭代的过程中被正常地捕获，那么，父生成器便不会受到影响，valid 的返回值依然是 true 。 

## 总结

关于生成器的异常处理，这里来进行一下总结。

* 在生成器中抛出一个异常，或者使用 `throw` 方法抛出一个异常，那么，生成器的迭代便会结束，`valid` 变成 `false` ；
* 在生成器中抛出一个异常，迭代过程中对异常进行捕获，生成器的迭代依然会结束，`valid` 依然会变成 `false` ；
* 在生成器中抛出一个异常，在生成器中将其捕获处理，生成器的迭代不会结束，`valid` 会返回 `true` ；
* 在嵌套的生成器中，如果子生成器抛出了异常，只会对子生成器产生影响，不会对父生成器产生影响。

## 后记

`yield` 为我们提供了使用 PHP 实现半协程的工具。最近在研究使用 `yield` 实现半协程，而这个过程中，对异常的处理，是非常重要的。但是 `yield` 的运行方式决定了异常处理比较难以理解。于是我花了几天的时间，尝试了各种可能，得出来的这些结论。当然由于本人水平有限，如有错误，还望指点一二，不胜感激。


[1]: https://segmentfault.com/a/1190000010743480

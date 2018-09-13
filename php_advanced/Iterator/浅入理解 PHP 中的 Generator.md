## 浅入理解 PHP 中的 Generator

## 何为 Generator

从 PHP 5.5 开始，PHP 加入了一个新的特性，那就是 Generator，中文译为生成器。生成器可以简单地用来实现对象的迭代，让我们先从官方的一个小例子说起。

### xrange

在 PHP 中，我们都知道，有一个函数叫做 range，用来生成一个等差数列的数组，然后我们可以用这个数组进行 foreach 的迭代。具体就想这样。

```php
foreach (range(1, 100, 2) as $num) {
    echo $num . PHP_EOL;
}
```
这一段代码就会输出首项为 1，末项为 100，公差为 2 的等差数列。它的执行顺序是这样的。首先，`range(1, 100, 2)` 会生成一个数组，里面存了上面那样的一个等差数列，之后在 foreach 中对这个数组进行迭代。

那么，这样就会出现一个问题，如果我要生成 100 万个数字呢？那我们就要占用上百兆内存。虽然现在内存很便宜，但是我们也不能这么浪费内存嘛。那么这时，我们的生成器就可以排上用场了。考虑下面的代码。

```php
function xrange($start, $limit, $step = 1) {
    while ($start <= $limit) {
        yield $start;
        $start += $step;
    }
}

foreach (xrange(1, 100, 2) as $num) {
    echo $num . PHP_EOL;
}
```

这段代码所的出来的结果，和前面的那段代码一模一样，但是，它内部的原理是天翻地覆了。

我们刚才说了，前面的代码，`range` 会生成一个数组，然后 `foreach` 来迭代这个数组，从而取出某一个值。但是这段代码呢，我们重新定义了一个 `xrange` 函数，在函数中，我们用了一个关键字 **`yield`**。我们都知道定义一个函数，希望它返回一个值得时候，用 `return` 来返回。那么这个 `yield` 呢，也可以返回一个值，但是，它和 `return` 是截然不同的。

使用 `yield` 关键字，可以让函数在运行的时候，中断，同时会保存整个函数的上下文，返回一个 `Generator` 类型的对象。在执行对象的 next 方法时，会重新加载中断时的上下文，继续运行，直到出现下一个 `yield` 为止，如果后面没有再出现 `yield`，那么就认为整个生成器结束了。

这样，我们上面的函数调用可以等价地写成这样。

```php
$nums = xrange(1, 100, 2);
while ($nums->valid()) {
    echo $nums->current() . "\n";
    $nums->next();
}
```

在这里，`$num` 是一个 `Generator` 的对象。我们在这里看到三个方法，valid、current 和 next。当我们函数执行完了，后面没有 `yield` 中断了，那么我们在 `xrange` 函数就执行完了，那么 `valid` 方法就会变成 `false`。而 `current` 呢，会返回当前 `yield` 后面的值，这是，生成器的函数会中断。那么在调用 `next` 方法之后，函数会继续执行，直到下一个 `yield` 出现，或者函数结束。

好了，到这里，我们看到了通过 `yield` 来“生成”一个值并返回。其实，`yield` 其实也可以这么写 `$ret = yield`;。同返回值一样，这里是将一个值在继续执行函数的时候，传值进函数，可以通过 `Generator::send($value)` 来使用。例如。

```php
function sum()
{
    $ret = yield;
    echo $ret . PHP_EOL;
}

$sum = sum();
$sum->send('I am from outside.');
```

这样，程序就会打印出 `send` 方法传进去的字符串了。在 `yield` 的两边可以同时有调用。

```php
function xrange($start, $limit, $step = 1) {
    while ($start <= $limit) {
        $ret = yield $start;
        $start += $step;
        echo $ret . PHP_EOL;
    }
}

$nums = xrange(1, 100, 2);
while ($nums->valid()) {
    echo $nums->current() . "\n";
    $nums->send($nums->current() + 1);
}
```

而像这样的使用，`send()` 可以返回下一个 `yield` 的返回。

## 其它的 Generator 方法

### Generator::key()

对于 `yield`，我们可以这样使用 `yield $id => $value`，这是，我们可以通过 `key` 方法来获取 `$id`，而 `current` 方法返回的是 `$value`。

### Generator::rewind()

这个方法，可以帮我们让生成器重新开始执行并保存上下文，同时呢，会返回第一个 `yield` 返回的内容。在第一次执行 `send` 方法的时候，`rewind` 会被隐式调用。

### Generator::throw()

这个方法，向生成器中，抛送一个异常。

## 后记

`yield` 作为 PHP 5.5 的新特性，让我们用了新的方法来高效地迭代数据。同时，我们还可以使用 `yield` 来实现协程。


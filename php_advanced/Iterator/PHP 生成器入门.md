## PHP 生成器入门

来源：[https://segmentfault.com/a/1190000015644298](https://segmentfault.com/a/1190000015644298)

本文首发于 [入门 PHP 生成器][0]，转载请注明出处。
PHP 在 5.5 版本中引入了「生成器（Generator）」特性，不过这个特性并没有引起人们的注意。在官方的 [从 PHP 5.4.x 迁移到 PHP 5.5.x][1] 中介绍说它能以一种简单的方式实现迭代器（Iterator）。

生成器实现通过 yield 关键字完成。生成器提供一种简单的方式实现迭代器，几乎无任何额外开销或需要通过实现迭代器接口的类这种复杂方式实现迭代。
文档提供了一个简单的实例演示这个简单的迭代器，请看下面的代码：

```php
function xrange($start, $limit, $step = 1) {
    for ($i = $start; $i <= $limit; $i += $step) {
        yield $i;
    }
}
```

让我们将它与无迭代器支持的数组进行比较：

```php
foreach xrange($start, $limit, $step = 1) {
    $elements = [];
    
    for ($i = $start; $i <= $limit; $i += $step) {
        $elements[] = $i;
    }

    return $elements;
}
```

这两个版本的函数都支持 **`foreach`**  迭代获取所有元素：

```php
foreach (xrange(1, 100) as $i) {
    print $i . PHP_EOL;
}
```

所以除了一个更短的函数定义，我们还能获取什么呢？ **`yield`**  到底做了什么？为什么在第一个函数定义时依然可以返回数据，即使没有 **`return`**  语句？

先从返回值说起。生成器是 PHP 中的一个很特别的函数。当一个函数包含 **`yield`** ，那么这个函数即不再是一个普通函数，它永远返回一个「[Generator(生成器)][2]」实例。生成器实现了 **`[Iterator][3]`**  接口，这就是为何它能够进行 **`foreach`**   遍历的原因。

接下来我使用 **`Iterator`**  接口中的方法，对之前的 **`foreach`**  循环进行重写。你可以在 [3v4l.org][4] 查看结果。

```php
$generator = xrange(1, 100);

while($generator->valid()) {
    print $generator->current() . PHP_EOL;

    $generator->next();
}
```

我们可以清楚的看到生成器是更高级的技术，现在让我们编写一个新的生成器示例来更好的理解到底在生成器内部是如何进行处理的吧。

```php
function foobar() {
    print 'foobar - start' . PHP_EOL;

    for ($i = 0; $i < 5; $i++) {
        print 'foobar - yielding...' . PHP_EOL;
        yield $i;
        print 'foobar - continued...' . PHP_EOL;
    }

    print 'foobar - end' . PHP_EOL;
}

$generator = foobar();

print 'Generator created' . PHP_EOL;

while ($generator->valid()) {
    print "Getting current value from the generator..." . PHP_EOL;

    print $generator->current() . PHP_EOL;

    $generator->next();
}
```

```
Generator created
foobar - start
foobar - yielding...
Getting current value from the generator...
1
foobar - continued
foobar - yielding...
Getting current value from the generator...
2
foobar - continued
foobar - yielding...
Getting current value from the generator...
3
foobar - continued
foobar - yielding...
Getting current value from the generator...
4
foobar - continued
foobar - yielding...
Getting current value from the generator...
5
foobar - continued
foobar - end
```

嗯？为什么 **`Generator created`**  最先打印出来？这是因为生成器在被使用之前不会执行任何操作。在上例中就是 **`$generator->valid()** 这句代码才开始执行生成器。我们看到生成器一直运行到了第一个 **yield** 时，将控制流程交还给调用者 **$generator->valid()`** 。 **`$generator->next()`**  调用时则恢复生成器执行，到下一个 **`yield`**  再次停止运行，如此反复直到没有更多的 **`yield`**  为止。我们现在拥有了可以在任何 **`yield`**  执行暂停和回复的终端函数。这个特性允许编写客户端所需的延迟函数。

你可以创建一个从 GitHub API 读取所有用户的功能。支持分页处理，但是你可以隐藏这些细节并且仅当需要时再去获取下一页数据。你可以使用 **`yield`**  从当前页面获取每个用户数据，直到当前页所有用户获取完成，你就可以再去获取下一页数据。

```php

class GitHubClient {
    function getUsers(): Iterator {
        $uri = '/users';

        do {
            $response = $this->get($uri);
            foreach ($response->items as $user) {
                yield $user;
            }

            $uri = $response->nextUri;
        } while($uri !== null);
    }
}
```

客户端可以迭代出所有用户或者在任何时候停止遍历。
## 把生成器当迭代器使用真是无聊

是的，你的想法是对的。以上我给出的所有讲解任何人都可以从 PHP 文档中获取到。但是作为迭代器这些使用，连它强大功能的一半都没用到。生成器还提供了不属于 **`Iterator`**  接口的 **`send()`**  和 **`throw()`**  功能。我们前面谈到了暂停和恢复生成器执行功能。当需要恢复生成器时，不仅可以功过 **`Generator::next()`**  方法，还可以使用 **`Generator::send()`**  和 **`Generator::throw()`** 方法。
 **`Generator::send()`**  允许你指定 **`yield`**  的返回值，而 **`Generator::throw()`**  允许向 **`yield`**  抛出异常。通过这些方法我们不仅可以从生成器中获取数据，还能向生成器中发送新数据。

让我们看一个从 [Cooperative multitasking using coroutines][5]（强烈推荐阅读本文）摘取的 **`Logger`**  日志示例。

```php
function logger($filename) {
    $fileHandle = fopen($filename, 'a');

    while (true) {
        fwrite($fileHandle, yield . "\n");
    }
}

$logger = logger(__DIR__ . '/log');
$logger->send('Foo');
$logger->send('Bar');
```
 **`yield`**  在这里是作为表达式使用的。当我们发送数据时，从 **`yield`**  返回数据然后作为参数传入到 **`fwrite()`** 。

讲真，这个示例在实际项目中没毛用。它仅仅用于演示 **`Generator::send()`**  的使用原理，但是仅仅能够发送数据并没有太大作用。如果有一个类和普通函数支持的话就不一样了。

使用生成器的乐趣来自于通过 **`yield`**  创建数据，然后由「生成器执行程序（generator runner）」依据这个数据来处理业务，然后再继续执行生成器。这就是「[协程（coroutines）][6]」和「[状态流解析器（stateful streaming parsers）][7]」实例。在讲解协程和状态流解析器之前，我们快速浏览一下如何在生成器中返回数据，我们还没有将接触这方面的知识。从 PHP 5.5 开始我们可以在生成器内部使用 **`return;`**  语句，但是不能返回任何值。执行 **`return;`**  语句的唯一目的是结束生成器执行。

不过从 PHP 7.0 起支持返回值。这个功能在用于迭代时可能有些奇怪，但是在其他使用场景如协程时将非常有用，例如，当我们在执行一个生成器时我们可以依据返回值处理，而无需直接对生成器进行操作。下一节我们将讲解 **`return`**  语句在协程中的使用。
## 异步生成器

[Amp][8] 是一款 PHP 异步编程的框架。支持异步协程功能，本质上是等待处理结果的占位符。「生成器执行程序」为 **`Coroutine`** 类。它会订阅异步生成器（yielded promise），当有执行结果可用时则继续生成器处理。如果处理失败，则会抛出异常给生成器。你可以到 [amphp/amp][9] 版本库查看实现细节。在 Amp 中的 **`Coroutine`**  本身就是一个 **`Promise`** 。如果这个协程抛出未经捕获的异常，这个协程就执行失败了。如果解析成功，那么就返回一个值。这个值看起来和普通函数的返回值并无二致，只不过它处于异步执行环境中。这就是需要生成器需要有返回值的意义，这也是为何我们将这个特性加入到 PHP 7.0 中的原因，我们会将最后执行的yield 值作为返回值，但这不是一个好的解决方案。

Amp 可以像编写阻塞代码一样编写非阻塞代码，同时允许在同一进程中执行其它非阻塞事件。一个使用场景是，同时对一个或多个第三方 API 并行的创建多个 HTTP 请求，但不限于此。得益于事件循环，可以同时处理多个 I/O 处理，而不仅仅是只能处理多个 HTTP请求这类操作。

```php
Loop::run(function() {
    $uris = [
        "https://google.com/",
        "https://github.com/",
        "https://stackoverflow.com/",
    ];

    $client = new Amp\Artax\DefaultClient;
    $promises = [];

    foreach ($uris as $uri) {
        $promises[$uri] = $client->request($uri);
    }

    $responses = yield $promises;

    foreach ($responses as $uri => $response) {
        print $uri . " - " . $response->getStatus() . PHP_EOL;
    }
});
```

但是，拥有异步功能的协程并非只能够在 **`yield`**  右侧出现变量，还可以在它的左侧。这就是我们前面提到的解析器。

```php
$parse = new Parser((function(){
    while (true) {
        $line = yield "\r\n";

        if (trim($line) === "") {
            continue;
        }

        print "New item: {$line}" . PHP_EOL;
    }
})());

for ($i = 0; $i < 100; $i++) {
    $parser->push("bar\r");
    $parser->push("\nfoo");
}
```

解析器会缓存所有输入直到接收的是 **`rn`** 。这类生成器解析器并不能简化简单协议处理（如换行分隔符协议），但是对于复杂的解析器，如在服务器解析 HTTP 请求的 [Aerys][10]。
## 小结

生成器的功能远超多数人的认知范围。对于一些朋友来说可能是首次接触生成器相关知识，一些朋友可能已经将它作为迭代器来使用，仅有很少一部分朋友使用生成器处理更多的事情。获取你有一些很赞的想法？我很乐意进一步探讨这些项目，并且希望你能从中学习到一些知识。:)

如果你需要更多资料，我推荐你阅读 nikic 写的 [使用生成器处理多任务][11]。
## 原文

[An Introduction to Generators in PHP][12]

[0]: http://blog.phpzendo.com/?p=404
[1]: https://secure.php.net/manual/zh/migration55.new-features.php
[2]: https://secure.php.net/manual/zh/class.generator.php
[3]: https://secure.php.net/manual/zh/class.iterator.php
[4]: https://3v4l.org/5uF7I
[5]: https://nikic.github.io/2012/12/22/Cooperative-multitasking-using-coroutines-in-PHP.html
[6]: https://amphp.org/amp/coroutines/
[7]: https://amphp.org/parser/
[8]: https://amphp.org/
[9]: https://github.com/amphp/amp/blob/05491a57c98bffdd0728477819c2211c0802daeb/lib/Coroutine.php#L69-L75
[10]: https://github.com/amphp/aerys
[11]: https://nikic.github.io/2012/12/22/Cooperative-multitasking-using-coroutines-in-PHP.html
[12]: https://blog.kelunik.com/2017/09/14/an-introduction-to-generators-in-php.html
# PSR规范总结：

 时间 2017-04-20 03:59:00  William的后花园

_原文_[http://www.jwlchina.cn/2017/04/19/PSR规范总结/][1]

 主题  PHP 数据库

* **必须 (MUST)** ：绝对，严格遵循，请照做，无条件遵守；
* **一定不可 (MUST NOT)** ：禁令，严令禁止；
* **应该 (SHOULD)** ：强烈建议这样做，但是不强求；
* **不该 (SHOULD NOT)** ：强烈不建议这样做，但是不强求；
* **可以 (MAY) 和 可选 (OPTIONAL)** ：选择性高一点，在这个文档内，此词语使用较少；

## PSR-1（基础编码规范） 

* PHP代码文件 **必须** 以 `<?php` 或 `<?=` 标签开始；
* PHP代码文件 **必须** 以 不带 BOM 的 UTF-8 编码；
* PHP代码中 **应该** 只定义类、函数、常量等声明，或其他会产生 副作用 的操作（如：生成文件输出以及修改 .ini 配置文件等），二者只能选其一；
* 命名空间以及类 **必须** 符合 PSR 的自动加载规范：PSR-4 中的一个；
* 类的命名 **必须** 遵循 StudlyCaps 大写开头的驼峰命名规范；
* 类中的常量所有字母都 **必须** 大写，单词间用下划线分隔；
* 方法名称 **必须** 符合 camelCase 式的小写开头驼峰命名规范。（这个类似于Java的命名方式）

## PSR-2（编码风格规范） 

* 代码 **必须** 遵循 PSR-1 中的编码规范 。
* 代码 **必须** 使用4个空格符而不是「Tab 键」进行缩进。
* 每行的字符数 **应该** 软性保持在 80 个之内，理论上 **一定不可** 多于 120 个，但 **一定不可** 有硬性限制。
* 每个 namespace 命名空间声明语句和 use 声明语句块后面，必须 插入一个空白行。
* 类的开始花括号（`{`） **必须** 写在函数声明后自成一行，结束花括号（`}`）也 **必须** 写在函数主体后自成一行。
* 方法的开始花括号（`{`） **必须** 写在函数声明后自成一行，结束花括号（}`）也 **必须** 写在函数主体后自成一行。
* 类的属性和方法 **必须** 添加访问修饰符（private、protected 以及 public），abstract 以及 final **必须** 声明在访问修饰符之前，而 static 必须 声明在访问修饰符之后。
* 控制结构的关键字后 **必须** 要有一个空格符，而调用方法或函数时则 **一定不可** 有。
* 控制结构的开始花括号（`{`） **必须** 写在声明的同一行，而结束花括号（`}`） **必须** 写在主体后自成一行。
* 控制结构的开始左括号后和结束右括号前，都 **一定不可** 有空格符。

## PSR-3（日志接口规范） 

* LoggerInterface 接口对外定义了八个方法，分别用来记录 RFC 5424 中定义的八个等级的日志： `debug` 、 `info` 、 `notice` 、 `warning` 、 `error` 、 `critical` 、 `alert` 以及 `emergency` ，以及还有第九个方法 `log` （用于写入或者记录日志）。
* 参数 **可以** 携带占位符，并且占位符 **必须** 与上下文的数组中键名保持一致。 **必须** :u6709:️一个左花括号 { 和右花括号 } 组成。占位符的名称 **应该** 只由 A-Z、a-z、0-9、下划线 _、以及英文的句号 . 组成，其它字符作为将来占位符规范的保留。
* 每个记录函数都接受一个上下文数组参数，用来装载字符串类型无法表示的信息。它 **可以** 装载任何信息，所以实现者 **必须** 确保能正确处理其装载的信息，对于其装载的数据， **一定不可** 抛出异常，或产生PHP出错、警告或提醒信息（`error`、`warning`、`notice`）
* `Psr\Log\AbstractLogger` 类使得只需继承它和实现其中的 `log` 方法，就能够很轻易地实现 `LoggerInterface` 接口，而另外八个方法就能够把记录信息和上下文信息传给它。
* 同样地，使用 `Psr\Log\LoggerTrait` 也只需实现其中的 log 方法。不过，需要特别注意的是，在 `traits` 可复用代码块还不能实现接口前，还需要 `implement LoggerInterface`。
* 在没有可用的日志记录器时， `Psr\Log\NullLogger` 接口 可以 为使用者提供一个备用的日志「黑洞」。不过，当上下文的构建非常消耗资源时，带条件检查的日志记录或许是更好的办法。
* `Psr\Log\LoggerAwareInterface` 接口仅包括一个 `setLogger(LoggerInterface $logger)` 方法，框架可以使用它实现自动连接任意的日志记录实例。
* `Psr\Log\LoggerAwareTrait trait`可复用代码块可以在任何的类里面使用，只需通过它提供的 `$this->logger`，就可以轻松地实现等同的接口。
* `Psr\Log\LogLevel` 类装载了八个记录等级常量。

LoggerInterface.php 

```php
<?php
namespace Psr\Log;
/**
 * Describes a logger instance.
 *
 * The message MUST be a string or object implementing __toString().
 *
 * The message MAY contain placeholders in the form: {foo} where foo
 * will be replaced by the context data in key "foo".
 *
 * The context array can contain arbitrary data. The only assumption that
 * can be made by implementors is that if an Exception instance is given
 * to produce a stack trace, it MUST be in a key named "exception".
 *
 * See https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md
 * for the full interface specification.
 */
interfaceLoggerInterface
{
    /**
     * System is unusable.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public functionemergency($message, array $context = array());
    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public functionalert($message, array $context = array());
    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public functioncritical($message, array $context = array());
    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public functionerror($message, array $context = array());
    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public functionwarning($message, array $context = array());
    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public functionnotice($message, array $context = array());
    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public functioninfo($message, array $context = array());
    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public functiondebug($message, array $context = array());
    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public functionlog($level, $message, array $context = array());
}
```

再看看AbstractLogger.php 

```php
<?php
namespace Psr\Log;
/**
 * This is a simple Logger implementation that other Loggers can inherit from.
 *
 * It simply delegates all log-level-specific methods to the `log` method to
 * reduce boilerplate code that a simple Logger that does the same thing with
 * messages regardless of the error level has to implement.
 */
abstract classAbstractLoggerimplementsLoggerInterface
{
    /**
     * System is unusable.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public functionemergency($message, array $context = array())
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }
    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public functionalert($message, array $context = array())
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }
    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public functioncritical($message, array $context = array())
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }
    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public functionerror($message, array $context = array())
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }
    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public functionwarning($message, array $context = array())
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }
    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public functionnotice($message, array $context = array())
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }
    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public functioninfo($message, array $context = array())
    {
        $this->log(LogLevel::INFO, $message, $context);
    }
    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public functiondebug($message, array $context = array())
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }
}
```

可以看到，如果我们继承了 `AbstractLogger` ，只需要实现我们自己的log方法就可以了 

## PSR-4（自动加载规范） 

一个完整的类名需具有以下结构: 

    \<命名空间>(\<子命名空间>)*\<类名>
    

* 完整的类名 **必须** 要有一个顶级命名空间，被称为 “vendor namespace”；
* 完整的类名 **可以** 有一个或多个子命名空间；
* 完整的类名 **必须** 有一个最终的类名；
* 完整的类名中任意一部分中的下滑线都是没有特殊含义的；
* 完整的类名 **可以 由任意大小写字母组成；
* 所有类名都 **必须** 是大小写敏感的。
* 完整的类名中，去掉最前面的命名空间分隔符，前面连续的一个或多个命名空间和子命名空间，作为「命名空间前缀」，其必须与至少一个「文件基目录」相对应；
* 紧接命名空间前缀后的子命名空间 **必须** 与相应的「文件基目录」相匹配，其中的命名空间分隔符将作为目录分隔符。
* 末尾的类名 **必须** 与对应的以 .php 为后缀的文件同名。
* 自动加载器（autoloader）的实现 **一定不可** 抛出异常、一定不可 触发任一级别的错误信息以及 不应该 有返回值。

## Examples 

完整类名 | 命名空间前缀 | 文件基目录 | 文件路径    
-|-|-|-
\Acme\Log\Writer\File_Writer | Acme\Log\Writer\ | ./acme-log-writer/lib/ | ./acme-log-writer/lib/File_Writer.php 
\Aura\Web\Response\Status | Aura\Web | /path/to/aura-web/src/ | /path/to/aura-web/src/Response/Status.php 
\Symfony\Core\Request | Symfony\Core | ./vendor/Symfony/Core/ | ./vendor/Symfony/Core/Request.php 
\Zend\Acl | Zend | /usr/includes/Zend/ | /usr/includes/Zend/Acl.php    

## PSR-6（缓存接口规范） 

## 定义 

* 调用类库 (Calling Library) - 调用者，使用缓存服务的类库，这个类库调用缓存服务，调用的 是此缓存接口规范的具体「实现类库」，调用者不需要知道任何「缓存服务」的具体实现。
* 实现类库 (Implementing Library) - 此类库是对「缓存接口规范」的具体实现，封装起来的缓存服务，供「调用类库」使用。实现类库 **必须** 提供 PHP 类来实现 `Cache\CacheItemPoolInterface` 和` Cache\CacheItemInterface` 接口。 实现类库 必须 支持最小的如下描述的 TTL 功能，秒级别的精准度。
* 生存时间值 (TTL - Time To Live) - 定义了缓存可以存活的时间，以秒为单位的整数值。
* 过期时间 (Expiration) - 定义准确的过期时间点，一般为缓存存储发生的时间点加上 TTL 时 间值，也可以指定一个 DateTime 对象。
* 假如一个缓存项的 TTL 设置为 300 秒，保存于 1:30:00 ，那么缓存项的过期时间为 1:35:00。
* 实现类库 **可以** 让缓存项提前过期，但是 **必须** 在到达过期时间时立即把缓存项标示为 过期。如果调用类库在保存一个缓存项的时候未设置「过期时间」、或者设置了 null 作为过期 时间（或者 TTL 设置为 null），实现类库 可以 使用默认自行配置的一个时间。如果没 有默认时间，实现类库 必须把存储时间当做 永久性 存储，或者按照底层驱动能支持的 最长时间作为保持时间。
* 键 (KEY) - 长度大于 1 的字串，用作缓存项在缓存系统里的唯一标识符。实现类库 必须 支持「键」规则 `A-Z`, `a-z`, `0-9`, `_` 和 `.` 任何顺序的 UTF-8 编码，长度 小于 64 位。实现类库 可以 支持更多的编码或者更长的长度，不过 必须 支持至少以上指定 的编码和长度。实现类库可自行实现对「键」的转义，但是 **必须** 保证能够无损的返回「键」字串。以下 的字串作为系统保留: `{}()/\@:`， **一定不可** 作为「键」的命名支持。
* 命中 (Hit) - 一个缓存的命中，指的是当调用类库使用「键」在请求一个缓存项的时候，在缓存 池里能找到对应的缓存项，并且此缓存项还未过期，并且此数据不会因为任何原因出现错误。调用类 库 **应该** 确保先验证下 isHit() 有命中后才调用 get() 获取数据。
* 未命中 (Miss) - 一个缓存未命中，是完全的上面描述的「命中」的相反。指的是当调用类库使用「键」在请求一个缓存项的时候，在缓存池里未能找到对应的缓存项，或者此缓存项已经过期，或者此数据因为任何原因出现错误。一个过期的缓存项，必须 被当做 未命中 来对待。
* 延迟 (Deferred) - 一个延迟的缓存，指的是这个缓存项可能不会立刻被存储到物理缓存池里。一个 缓存池对象 **可以** 对一个指定延迟的缓存项进行延迟存储，这样做的好处是可以利用一些缓存服务器提供 的批量插入功能。缓存池 必须 能对所有延迟缓存最终能持久化，并且不会丢失。可以 在调用类库还未发起保存请求之前就做持久化。当调用类库调用 commit() 方法时，所有的延迟缓存都 必须 做持久化。实现类库 **可以** 自行决定使用什么逻辑来触发数据持久化，如对象的 析构方法 (destructor) 内、调用 save() 时持久化、倒计时保存或者触及最大数量时保存等。当请求一个延迟 缓存项时，必须 返回一个延迟，未持久化的缓存项对象。

## 数据 

* 实现类库 **必须** 支持所有的可序列化的 PHP 数据类型
* 所有存进实现类库的数据，都 **必须** 能做到原封不动的取出。连类型也 必须 是完全一致，如果 存进缓存的是字符串 5，取出来的却是整数值 5 的话，可以算作严重的错误。实现类库 **可以** 使用 PHP 的「serialize()/unserialize() 方法」作为底层实现，不过不强迫这样做。对于他们的兼容性，以能支持所有数据类型作为基准线。
* 实在无法「完整取出」存入的数据的话，实现类库 **必须** 把「缓存丢失」标示作为返回，而不是损坏了的数据。


[1]: http://www.jwlchina.cn/2017/04/19/PSR规范总结/
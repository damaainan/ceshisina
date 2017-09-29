# 你真的了解现在的PHP吗？（2）

 时间 2017-01-29 21:52:21  

原文[http://www.jianshu.com/p/2b6342817dff][1]


良好实践，这次主要挑了一些开发PHP应用时应该运用上的良好实践进行详细记录，特别是良好实践部分中密码和流两个点。关于代码风格、（我个人）常用或者常见的做法会简单带过。

## 二、标准
如果你了解PHP-FIG和PSR可以跳过这部分PHP组件和框架的数量很多，随之产生的问题就是：单独开发的框架没有考虑到与其他框架的通信。这样对开发者和框架本身都是不利的。

打破旧局面的PHP-FIG 

多位PHP框架的开发者认识到了这个问题，在2009年的 [php|tek][3] （一个受欢迎的PHP会议）上谈论了这个问题。经过讨论后得出： **我们需要一个标准** ，用来提高框架的互操作性。于是这几位在php|tek意外碰头的PHP框架开发者组织了PHP Framework Interop Group，简称PHP-FIG。 

PHP-FIG是框架代表自发组织的，其成员不是选举产生的，任何人都可以申请加入PHP-FIG，并且能对处于提议阶段的推荐规范提交反馈。另外，PHP-FIG发布的是推荐规范，而不是强制规定。

## 1.PSR是什么？

PSR是PHP Standards Recommendation（PHP推荐标准）的简称。截至今日，PHP-FIG发布了五个推荐规范：

* [PSR-1：基本的代码风格][4]
* [PSR-2：严格的代码风格][5]
* [PSR-3：日志记录器接口][6]
* [PSR-4：自动加载][7]

你会发现只有四个，没错，因为第一份推荐规范 [PSR-0][8] 废弃了，新发布的PSR-4替代了。 

## 2.PSR-1：基本的代码风格

如果想编写符合社区标准的PHP代码，首先要遵守PSR-1。遵守这个标准非常简单，可能你已经再使用了。标准的细节就不写啦，点链接就能看。

## 3.PSR-2：严格的代码风格

PSR-2是在PSR-1的基础上更进一步的定义PHP代码规范。这个标准解决了很多世纪问题哈，比如缩进，大括号等等。细节也不多记录啦。

另外，现在很多IDE（比如，PHPStorm）会有代码格式化功能，设置代码格式化的标准，编写完代码，然后全部格式化，可以帮助你遵循推荐规范，修复一些换行、缩进、大括号等细节。

![][9]

设置标准

## 4.PSR-3：日志记录器接口

这个推荐规范与前两个不同，这是一个接口，规定PHP日志记录器组件可以实现的方法。符合PSR-3推荐规范的PHP日志记录器组件，必须包含一个实现`Psr\Log\LoggerInterface`接口的PHP类。PSR-3接口复用了 [RFC 5424系统日志协议][10] ，规定要实现的九个方法： 

```php
    <?php
    namespace Psr\Log;
    
    interface LoggerInterface
    {
      public function emergency($message, array $context = array());
      public function alert($message, array $context = array());
      public function critical($message, array $context = array());
      public function error($message, array $context = array());
      public function warning($message, array $context = array());
      public function notice($message, array $context = array());
      public function info($message, array $context = array());
      public function debug($message, array $context = array());
      public function log($level, $message, array $context = array());
    }
```

每个方法对应RFC 5424协议的一个日志级别。

使用PRS-3日志记录器 

如果你正在编写自己的PSR-3日志记录器，可以停下来了。因为已经有一些十分出色的日志记录器组件。比如： [monolog/monolog][11] ，直接用就可以了。如果不能满足要求，也建议在此基础上做扩展。 

## 5.PSR-4：自动加载器

这个推荐规范描述了一个标准的自动加载器策略。自动加载器策略是指，在运行时按需查找PHP类，接口或性状，并将其载入PHP解释器。

为什么自动加载很重要 

在PHP文件的顶部你是不是经常看到类似下面的代码？

```php
    <?php
    include 'path/to/file1.php';
    include 'path/to/file2.php';
    include 'path/to/file3.php';
```

如果只需载入几个PHP脚本，使用这些函数（`include()`、`include_once()`、`require()`、`require_once()`）能很好的完成工作。可是如果你要引入一千个PHP脚本呢？

在PSR-4推荐规范之前，PHP组件和框架的作者使用`__autoload()`和`spl_autoload_register()`函数注册自定义的自动加载器策略。可是，每个PHP组件和框架的自动加载器都使用独特的自动加载器。因此，使用的组件多的时候，也是很麻烦的事情。

推荐使用PSR-4自动加载器规范，就是解决这个问题，促进组件实现互操作性。

PSR-4自动加载器策略 

PSR-4推荐规范不要求改变代码的实现方式，只建议如何使用文件系统目录结构和PHP命名空间组织代码。 PSR-4的精髓是把命名空间的前缀和文件系统中的目录对应起来 。比如，我可以告诉PHP，\Oreilly\ModernPHP命名空间中的类、接口和性状在物理文件系统的src/目录中，这样PHP就知道，前缀为\Oreilly\ModernPHP的命名空间中的类、接口和性状对应的src/目录里的目录和文件。 

如何编写PSR-4自动加载器 

如果你在写自己的PSR-4自动加载器，请停下来。我们可以使用依赖管理器Composer自动生成的PSR-4自动加载器。

## 三、良好实践

## 1.过滤、验证和转义

### 过滤HTML

使用`htmlentities()`函数过滤输入。

```php
    <?php
    $input = '<p><script>alert("You won the Nigerian lottery!");</script></p>';
    echo htmlentities($input, ENT_QUOTES, 'UTF-8');
```

需要注意的是：默认情况下，`htmlentities()`函数不会转义单引号，而且也检测不出输入字符串的字符集。正确的使用方式是： 第一个参数输入字符串；第二个参数设为`ENT_QUOTES`常量，转移单引号；第三个参数设为输入字符串的字符集 。 

更多过滤HTML输入的方式，可以使用 [HTML Purifier][12] 库。这个库强健且安全，缺点：慢，且可能难以配置。 

### SQL查询

构建SQL查询不好的方式：

```php
    $sql = sprintf(
      'UPDATE users SET password = "%s" WHERE id = %s',
      $_POST['password'],
      $_GET['id']
    );
```

如果 psasword=abc";-- ，则导致修改了整个users表的记录password都未abc。 如果需要在SQL查询中使用输入数据，要使用PDO预处理语句 。 

### 用户资料信息

A.过滤用户资料中的电子邮件地址

这里会删除除字符、数字和`!#$%&'*+-/=?^_{|}~@.[]` `之外的所有其他符号。

```php
    <?php
    $email = 'beckjiang@meijiabang.cn';
    $emailSafe = filter_var($email, FILTER_SANITIZE_EMAIL);
```

B.过滤用户资料中的外国字符

```php
    <?php
    $string = "外国字符";
    $safeString = filter_var(
      $string,
      FILTER_SANITIZE_STRING,
      FILTER_FLAG_STRIP_LOW|FILTER_FLAG_ENCODE_HIGH
    );
```

### 验证数据

验证数据与过滤不同，验证不会从输入数据中删除信息，而是只确认输入数据是否符合预期。

验证电子邮件地址 

我们可以把某个`FILTER_VALIDATE_*`标志传给`filter_var()`函数，除了电子邮件地址，还可以验证布尔值、浮点数、整数、IP地址、正则表达式和URL。

```php
    <?php
    $input = 'beckjiang@meijiabang.cn';
    $isEmail = filter_var($input, FILTER_VALIDAE_EMAIL);
    if ($isEmail !== false) {
      echo "Success";
    } else {
      echo "Fail";
    }
```

## 2.密码

哈希算法有很多种，例如：MD5、SHA1、bcrypt和scrypt。有些算法的速度很快，用于验证数据完整性；有些算法速度则很慢，旨在提高安全性。生成密码和存储密码时需要使用速度慢、安全性高的算法。

目前，经同行审查，最安全的哈希算法是`bcrypt`。与MD5和SHA1不同，bcrypt是故意设计的很慢。bcrypt算法会自动加盐，防止潜在的彩虹表攻击。`bcrypt`算法永不过时，如果计算机的运算速度变快了，我们只需提高工作因子的值。

重新计算密码的哈希值 

下面是登录用户的脚本：

```php
    <?php
    session_start();
    try {
      // 从请求主体中获取电子邮件地址
      $email = filter_input(INPUT_POST, 'email');
    
      // 从请求主体中获取密码
      $password = filter_input(INPUT_POST, 'password');
    
      // 使用电子邮件地址获取用户（注意，这是虚构代码）
      $user = User::findByEmail($email);
    
      // 验证密码和账户的密码哈希值是否匹配
      if (password_verify($password, $user->password_hash) === false) {
        throw new Exception('Invalid password');
      }
    
      // 如果需要，重新计算密码的哈希值
      $currentHashAlgorithm = PASSWORD_DEFAULT;
      $currentHashOptions = array('cost' => 15);
      $passwordNeedRehash = password_needs_rehash(
        $user->password_hash,
        $currentHashAlgorithm,
        $currentHashOptions
      );
      if ($passwordNeedsRehash === true) {
        // 保存新计算得出的密码哈希值（注意，这是虚构代码）
        $user->password_hash = password_hash(
          $password,
          $currentHashAlgorithm,
          $currentHashOptions
        );
        $user->save();
      }
      // 把登录状态保存到回话中
      ...
      // 重定向到个人资料页面
      ...
    
    } catch (Exception $e) {
      //异常处理
      ...
    }
```

值得注意的是： 在登录前，一定要使用`password_needs_rehash()`函数检查用户记录中现有的密码哈希值是否过期。如果过期了，要重新计算密码哈希值 。 

PHP5.5.0之前的密码哈希API 

如果无法使用PHP5.5.0或以上版本，可以使用安东尼·费拉拉开发的 [ircmaxell/password-compat][13] 组件。这个组件实现了PHP密码哈希API中的所有函数： 

* `password_hash()`
* `password_get_info()`
* `password_needs_rehash()`
* `password_verify()`

## 3.日期、时间和时区

### DateTime类

DateTime类提供一个面向对象接口，用于管理日期和时间。

没有参数，创建的是一个表示当前日期和时间的实例：

```php
    <?php
    $datetime = new DateTime();
```

传入参数创建实例：

```php
    <?php
    $datetime = new DateTime('2017-01-28 15:27');
```

指定格式，静态构造：

```php
    <?php
    $datetime = DateTime::createFromFormat('M j, Y H:i:s', 'Jan 2, 2017 15:27:30');
```

### DateInterval类

`DateInterval`实例表示长度固定的时间段（比如，“两天”），或者相对而言的时间段（比如，“昨天”）。`DateInterval`实例用于修改DateTime实例。

使用DateInterval类：

```php
    <?php
    // 创建DateTime实例
    $datetime = new DateTime();
    
    // 创建长度为两周的间隔
    $interval = new DateInterval('P2W');
    
    // 修改DateTime实例
    $datetime->add($interval);
    echo $datetime->format('Y-m-d H:i:s');
```

创建反向的DateInterval实例：

```php
    <?php
    // 过去一天
    $interval = new DateInterval('-1 day');
```

### DateTimeZone类

如果应用要迎合国际客户，可能要和时区斗争。

创建、使用时区：

```php
    <?php
    $timezone = new DateTimeZone('America/New_York');
    $datetime = new DateTime('2017-01-28', $timezone);
```

实例化之后，也可以使用 `setTimeZone()` 函数设置市区： 

    $datetime->setTimeZone(new DateTimeZone('Asia/Hong_Kong'));

### DatePeriod类

有时我们需要迭代处理一段时间内反复出现的一系列日期和时间，DatePeriod类可以解决这种问题。DatePeriod类的构造方法接受三个参数，而且都必须提供：

* 一个Datetime实例，表示迭代开始时的日期和时间。
* 一个DateInterval实例，表示到下个日期和时间的间隔。
* 一个整数，表示迭代的总次数。

#### DatePeriod实例是迭代器，每次迭代时都会产出一个DateTime实例。

使用DatePeriod类：

```php
    <?php
    $start = new DateTime();
    $interval = new DateInterval('P2W');
    $period = new DatePeriod($start, $interval, 3);
    
    foreach ($period as $nextDateTime) {
      echo $nextDateTime->format('Y-m-d H:i:s'), PHP_EOL;
    }
```

## 4.数据库

PHP应用可以在很多种数据库中持久保存信息，比如：MySQL、SQLite、Oracle等。如果在项目中使用多种数据库，需要安装并学习多种PHP数据库扩展和接口，这增加了认知和技术负担。

正是基于这个原因，PHP原生提供了PDO扩展（PHP Data Objects，意思是PHP数据对象），PDO是一系列PHP类，抽象了不同数据库的具体实现。PDO的介绍和使用就不写了，比较常用。

## 5.流

在现代的PHP特性中，流或许是最出色但最少使用的。虽然PHP4.3.0就引入了流，但很多开发者不知道流的存在，因为很少人提及流，而且流的文档也匮乏。官方的解释比较难理解，一句话说就是： **流的作用是在出发地和目的地之间传输数据** 。 

我把流理解为管道，相当于把水从一个地方引到另一个地方。在水从出发地流到目的地的过程中，我们可以过滤水，可以改变水质，可以添加水，也可以排出水（提示：水是数据的隐喻）。

### 流封装协议

流式数据的种类各异，每种类型需要独特的协议，以便读写数据。我们称这些协议为 [流封装协议][14] 。比如，我们可以读写文件系统，可以通过HTTP、HTTPS或SSH与远程Web服务器通信，还可以打开并读写ZIP、RAR或PHAR压缩文件。这些通信方式都包含下述相同的过程： 

1. 开始通信。
1. 读取数据。
1. 写入数据。
1. 结束通信。

虽然过程一样的，但是读写文件系统中文件的方式与手法HTTP消息的方式有所不同。流封装协议的作用是使用通用的几口封装这些差异。

每个流都有一个协议和一个目标。格式如下：

    <scheme>://<target>

说这么多有点懵，先看例子， **使用HTTP流封装协议与Flickr API通信：**

```php
    <?php
    $json = file_get_contents(
      'http://api.flickr.com/services/feeds/photos_public.gne?format=json'
    );
```

不要误以为这是普通的网页URL，`file_get_contents()`函数的字符串参数其实是一个流标识符。http协议会让PHP使用HTTP流封装协议。看起来像是普通的网页URL，是因为HTTP流封装协议就是这样规定的:)。其他流封装协议可能不是这样。 

### file://流封装协议

我们使用 `file_get_contents()` ， `fopen()` ， `fwrite()` 和 `fclose()` 函数读写文件系统。因为PHP默认使用的流封装协议是 `file://` ，所以我们很少认为这些函数使用的是PHP流。 

隐式使用 `file://` 流封装协议： 

```php
    <?php
    $handle = fopen('/etc/hosts', 'rb');
    while (feof($handle) !== true) {
      echo fgets($handle);
    }
    fclose($handle);
```

显式使用 `file://` 流封装协议： 

```php
    <?php
    $handle = fopen('file:///etc/hosts', 'rb');
    while (feof($handle) !== true) {
      echo fgets($handle);
    }
    fclose($handle);
```

### 流上下文

有些PHP流能接受一些列可选的参数，这些参数叫流上下文，用于定制流的行为。流上下文使用 `stream_context_create()` 函数创建。 

比如，你知道可以使用 `file_get_contents()` 函数发送HTTP POST请求吗？如果想这么做，可以使用一个流上下文对象： 

```php
    <?php
    $requestBody = '{"username": "beck"}';
    $context = stream_context_create(array(
      'http' => array(
        'method' => 'POST',
        'header' => "Content-Type: application/json;charset=utf-8;\r\n" . 
                    "Content-Length: " . mb_strlen($requestBody),
        "content" => $requestBody
      )
    ));
    $response = file_get_contents('https://my-api.com/users', false, $context);
```

### 流过滤器

关于PHP的流，其实 真正强大的地方在于过滤、转换、添加或删除流中传输的数据 。 

注意：PHP内置了几个流过滤器：`string.rot13`、`string.toupper`、`string.tolower`和`string.strp_tags`。这些过滤器没什么用，我们要使用自定义的过滤器。

若想把过滤器附加到现有的流上，要使用 `stream_filter_append()` 函数。比如，想要把文件中的内容转换成大写字母，可以使用`string.touppe`r过滤器。书中不建议使用这个过滤器，这里只是演示如何把过滤器附加到流上： 

```php
    <?php
    $handle = fopen('data.txt', 'rb');
    stream_filter_append($handle, 'string.toupper');
    while (feof($handle) !== true) {
      echo fgets($handle); // <-- 输出的全是大写字母
    }
    fclose($handle);
```

使用` php://filter` 流封装协议把过滤器附加到流上： 

```php
    <?php
    $handle = fopen('php://filter/read=string.toupper/resource=data.txt', 'rb');
    while (feof($handle) !== true) {
      echo fgets($handle); // <-- 输出的全是大写字母
    }
    fclose($handle);
```

来看个更实际的流过滤器示例，假如我们nginx访问日志保存在`rsync.net`，一天的访问情况保存在一个日志文件中，而且会使用bzip2压缩每个日志文件，名称格式为：YYYY-MM-DD.log.bz2。某天，领导让我提取过去30天某个域名的访问数据。 **使用DateTime类和流过滤器迭代bzip压缩的日志文件** ： 

```php
    <?php
    $dateStart = new \DateTime();
    $dateInterval = \DateInterval::createFromDateString('-1 day');
    $datePeriod = new \DatePeriod($dateStart, $dateInterval, 30);//创建迭代器
    foreach ($datePeriod as $date) {
      $file = 'sftp://USER:PASS@rsync.net/' . $date->format('Y-m-d') . 'log.bz2';
      if (file_exists($file)) {
        $handle = fopen($file, 'rb');
        stream_filter_append($handle, 'bzip2.decompress');
        while (feof($handle) !== true) {
          $line = fgets($handle);
          if (strpos($line, 'www.example.com') !== false) {
            fwrite(STDOUT, $line);
          }
        }
        fclose($handle);
      }
    }
```

计算日期范围，确定日志文件的名称，通过FTP连接rsync.net，下载文件，解压缩文件，逐行迭代每个文件，把相应的行提取出来，然后把访问数据写入一个输出目标。使用PHP流，不到20行代码就能做完所有这些事情。

### 自定义流过滤器

其实大多数情况下都要使用自定义的流过滤器。自定义的流过滤器是个PHP类，继承内置的 [php_user_filter][15] 类。这个类必须实现 `filter()` 、 `onCreate()` 和 `onClose()` 方法。而且，必须使用 `stream_filter_register()` 函数注册自定义的流过滤器。 

PHP流会把数据分成按次序排列的桶，一个桶中盛放的流数据量是固定的。一定时间内过滤器接收到的桶叫做 **桶队列** 。桶队列中的每个桶对象都有两个公开属性：data和datalen，分别是桶中的内容和内容的长度。 

#### 下面定义一个处理脏字的流过滤器：

```php
    <?php
    class DirtyWordsFilter extends php_user_filter
    {
      /**
       * @param resource $in         流来的桶队列
       * @param resource $out        流走的桶队列
       * @param resource $consumed   处理的字节数
       * @param resource $closing    是流中最后一个桶队列吗？
       */
      public function filter()
      {
        $words = array('grime', 'dirt', 'grease');
        $wordData = array();
        foreach ($words as $word) {
          $replacement = array_fill(0, mb_strlen($word), '*');
          $wordData[$word] = implode(' ', $replacement);
        }
        $bad = array_keys($wordData);
        $goods = array_values($wordData);
    
        // 迭代流来的桶队列中的每个桶
        while ($bucket = stream_bucket_make_writeable($in)) {
          // 审查桶数据中的脏字
          $bucket->data = str_replace($bad, $goods, $bucket->data);
    
          // 增加已处理的数据量
          $consumed += $bucket->datalen;
    
          // 把桶放入流向下游的队列中
          stream_bucket_append($out, $bucket);
        }
    
        return PSFS_PASS_ON;
      }
    }
```

`filter()` 方法的作用是接受、处理再转运桶中的流数据。这个方法的返回值是 `PSFS_PASS_ON` 常量，表示操作成功。 

注册流过滤器 

接着，我们必须使用 `stream_filter_register()` 函数注册这个自定义的DirtWordsFilter流过滤器： 

```php
    <?php
    stream_filter_register('dirty_words_filter', 'DirtWordsFilter');
```

第一个参数是用于识别这个自定义过滤器的过滤器名，第二个参数是自定义过滤器的类名。

#### 使用DirtWordsFilter流过滤器

```php
    <?php
    $handle = fopen('data.txt', 'rb');
    stream_filter_append($handle, 'dirty_words_filter');
    while (feof($handle) !== true) {
      echo fgets($handle); // <-- 输出审查后的文本
    }
    fclose($handle);
```

## 6.错误与异常

对错误和异常的处理，一定要遵守四个规则：

* 一定要让PHP报告错误。
* 在开发环境中要显示错误。
* 在生产环境中不能显示错误。
* 在开发环境和生产环境中都要记录错误。

错误与异常在日常使用的比较多，就不记录啦。


[1]: http://www.jianshu.com/p/2b6342817dff
[3]: http://tek.phparch.com
[4]: http://www.php-fig.org/psr/psr-1/
[5]: http://www.php-fig.org/psr/psr-2/
[6]: http://www.php-fig.org/psr/psr-3/
[7]: http://www.php-fig.org/psr/psr-4/
[8]: http://www.php-fig.org/psr/psr-0/
[9]: http://img1.tuicool.com/I3EZNbm.png
[10]: http://tools.ietf.org/html/rfc5424
[11]: https://packagist.org/packages/monolog/monolog
[12]: http://htmlpurifier.org/
[13]: https://packagist.org/packages/ircmaxell/password-compat
[14]: http://php.net/manual/wrappers.php
[15]: http://php.net/manual/en/class.php-user-filter.php
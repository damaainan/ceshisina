## Carbon中文使用手册

来源：[http://www.cnblogs.com/x-x-j/p/9377338.html](http://www.cnblogs.com/x-x-j/p/9377338.html)

时间 2018-07-27 14:19:00

 
 
* ## Introduction 
  
 
  Carbon   继承了PHP的  Datetime   类和 JsonSerialiable  。所以  Carbon   中没有涉及到的，但在  Datetime   和 JsonSerializable  中已经实现的方法都是可以使用的。
 
```php
class Carbon extends DateTime implements JsonSerializable
{
    //code here
}
```
 
Carbon 类声明在 Carbon 命名空间下，可以通过引入命名空间的方式来代替每次输入完整的类名。
 
```php
<?php 
use Carbon\Carbon;
```
 
要特别留意是否使用了正确的时区，比如的所有差异比较都使用或者系统设定的时区
 
```php
$dtToronto = Carbon::create(2012, 1, 1, 0, 0, 0, 'America/Toronto');
$dtVancouver = Carbon::create(2012, 1, 1, 0, 0, 0, 'America/Vancouver');

echo $dtVancouver->diffInHours($dtToronto); // 3
```
 
以上进行的时间比较是在提供的 Carbon  实例所在的时区下完成的。例如作者所在的时区为 东京时间减13 小时，因此在下午一点后。 Carbon::now(‘Asia/Tokyo’)->isToday()  将会返回 false ，如果在调用  now()  时设置时区为东京时区，接下来的操作都使用东京时区是说不过去的。所以在与  now()   创建的实例进行比较时，默认是在当前时区下完成的。
 
 
* ## Instantiation 
  
 
 
有几种不同的方法可以创建一个新的 Carbon  实例。首先是构造函数。它覆盖父构造函数，您最好阅读 PHP  手册中的第一个参数，并了解它所接受的日期/时间字符串格式。您可能会发现自己很少使用构造函数，而是依赖于显式静态方法来提高可读性
 
```php
$carbon = new Carbon();                  // 等同于 Carbon::now()
$carbon = new Carbon('first day of January 2008', 'America/Vancouver');
echo get_class($carbon);                 // 'Carbon\Carbon'
$carbon = Carbon::now(-5);//1表示英国伦敦，2表示法国巴黎
```
 
您将在上面注意到， timezone(2nd)  参数是作为字符串和整数而不是 \DateTimeZone  实例传递的。所有 DateTimeZone  参数都已被增强，因此您可以将一个DateTimeZone实例、字符串或整型偏移量传递给 GMT  ，并为您创建时区。在下一个示例中再次显示了这一点，该示例还介绍了 now()  函数。
 
```php
$nowInLondonTz = Carbon::now(new \DateTimeZone('Europe/London'));
// 或者以字符串形式只传时区
$nowInLondonTz = Carbon::now('Europe/London');

// 或者在DST期间创建一个时区为+1到GMT的日期，然后传递一个整数
echo Carbon::now(1)->tzName;             // Europe/London
```
 
如果您真的喜欢您的动态方法调用，并且对使用构造函数时所需的额外的行或难看的括号感到失望，那么您将喜欢 parse  方法。
 
```php
echo (new Carbon('first day of December 2008'))->addWeeks(2);     // 2008-12-15 00:00:00
echo Carbon::parse('first day of December 2008')->addWeeks(2);    // 2008-12-15 00:00:00
```
  NOTE  :  在  PHP 5.4  之前  (new MyClass())->method()     会报语法错误  ,  如果你使用  PHP 5.3,  你需要创建一个变量然后再调用方法： 
 
```php
$date = new Carbon('first day of December 2008'); 

echo $date->addWeeks(2);
```
 
传递给 Carbon:::parse  或 new Carbon  的字符串可以表示相对时间( next sunday, tomorrow, first day of next month, last year  )或绝对时间( first day of December 2008, 2017-01-06  )。您可以用 Carbon::hasRelativeKeywords()  测试一个字符串是否会产生一个相对或绝对日期。
 
```php
$string = 'first day of next month';
if (strtotime($string) === false) {
    echo "'$string' is not a valid date/time string.";
} elseif (Carbon::hasRelativeKeywords($string)) {
    echo "'$string' is a relative valid date/time string, it will returns different dates depending on the current date.";
} else {
    echo "'$string' is an absolute date/time string, it will always returns the same date.";
}
```
 
为了配合 now()  ，还存在一些静态的实例化助手来创建广为人知的实例。这里唯一需要注意的是， today()  、  tomorrow()  和 yesterday()  除了按照预期的行为，都接受一个时区参数，每个参数的时间值都设置为00:00:00。
 
```php
$now = Carbon::now();
echo $now;                               // 2018-07-26 16:25:49
$today = Carbon::today();
echo $today;                             // 2018-07-26 00:00:00
$tomorrow = Carbon::tomorrow('Europe/London');
echo $tomorrow;                          // 2018-07-27 00:00:00
$yesterday = Carbon::yesterday();
echo $yesterday;                         // 2018-07-25 00:00:00
```
 
下一组静态助手是createXXX() 函数。大多数静态create函数允许您提供许多个或少量的参数，并为所有其他参数提供默认值。通常默认值是当前日期、时间或时区。更高的值将适当地包装，但无效的值将抛出一个 InvalidArgumentException  ，并附带一条信息。错误消息从 DateTime:::getLastErrors()  调用中获取。
 
```php
Carbon::createFromDate($year, $month, $day, $tz);
Carbon::createFromTime($hour, $minute, $second, $tz);
Carbon::createFromTimeString("$hour:$minute:$second", $tz);
Carbon::create($year, $month, $day, $hour, $minute, $second, $tz);
```
  createFromDate()  的默认值是当前时间.  createFromTime()  默认值是今天.  create()  如果不传参数也是当前时间. 与前面一样，$tz默认设置为当前时区，否则可以是 DateTimeZone  实例，也可以是字符串时区值。默认值(模拟底层PHP库)的唯一特殊情况发生在指定了小时值但没有分钟或秒时，它们将默认为0。
  注：  **` createFromTime()  `**  **`  will default the date to today  `**  **` 。小编经实战代码打印出来发现  `**  **` createFromTime()  `**  **`    `**  **` 的默认值也是当前时间，不是今天（时分秒并不是  `**  **` 00:00:00  `**  **` ）。  `** 
 
```php
$xmasThisYear = Carbon::createFromDate(null, 12, 25);  // Year默认值是今年
$Y2K = Carbon::create(2000, 1, 1, 0, 0, 0); // 等价于Carbon::createMidnightDate(2000, 1, 1)
$alsoY2K = Carbon::create(1999, 12, 31, 24);
$noonLondonTz = Carbon::createFromTime(12, 0, 0, 'Europe/London');
$teaTime = Carbon::createFromTimeString('17:00:00', 'Europe/London');

// A two digit minute could not be found
try { Carbon::create(1975, 5, 21, 22, -2, 0); } catch(\InvalidArgumentException $x) { echo $x->getMessage()
```
 
创建异常发生在使用负值上，而不是在溢出上，要获取溢出上的异常，请使用 createSafe  ()
 
```php
echo Carbon::create(2000, 1, 35, 13, 0, 0);// 2000-02-04 13:00:00
//(1月有31天，4天自动加上去转换成了2月4号)

try {
    Carbon::createSafe(2000, 1, 35, 13, 0, 0);
} catch (\Carbon\Exceptions\InvalidDateException $exp) {
    echo $exp->getMessage();
}
// 会报错:day : 35 is not a valid value.
```
  NOTE1  :2018-02-29会产生一个异常，而2020-02-29不会产生异常，因为2020年是闰年。
  NOTE2  : Carbon::createSafe(2014,3,30,1,30,0,'Europe/London');  从PHP 5.4开始也会产生一个异常，因为在夏令时跳过一个小时，但是在PHP 5.4之前，它只会创建这个无效的日期。
 
```php
Carbon::createFromFormat($format, $time, $tz);
```
  createFromFormat()  是最基本的php函数 DateTime:::createFromFormat  的包装器。不同的是，$tz参数可以是 DateTimeZone  实例或字符串时区值。此外，如果格式有错误，这个函数将调用 DateTime::getLastErrors()  方法，然后抛出一个 InvalidArgumentException  ，错误作为消息。如果您查看上面的 createXX()  函数的源代码，它们都会调用 createFromFor  mat()。
 
```php
echo Carbon::createFromFormat('Y-m-d H', '1975-05-21 22')->toDateTimeString(); // 1975-05-21 22:00:00
```
 
最后三个create函数用于使用unix时间戳。第一个将创建一个与给定的时间戳相等的Carbon实例，并将设置时区或默认为当前时区。第二个 createFromTimestampUTC()  是不同的，因为时区将保持UTC(GMT)。第二种方法与 Carbon: createFromFormat('@'.$timestamp)  的作用相同，但我只是让它更明确了一点。第三个是 createFromTimestampMs()  ，它接受以毫秒而不是秒为单位的时间戳。也允许使用负时间戳。
 
```php
echo Carbon::createFromTimestamp(-1)->toDateTimeString();                                  // 1969-12-31 18:59:59
echo Carbon::createFromTimestamp(-1, 'Europe/London')->toDateTimeString();                 // 1970-01-01 00:59:59
echo Carbon::createFromTimestampUTC(-1)->toDateTimeString();                               // 1969-12-31 23:59:59
echo Carbon::createFromTimestampMs(1)->format('Y-m-d\TH:i:s.uP T');                        // 1969-12-31T19:00:00.001000-05:00 EST
echo Carbon::createFromTimestampMs(1, 'Europe/London')->format('Y-m-d\TH:i:s.uP T');       // 1970-01-01T01:00:00.001000+01:00 BST
```
 
您还可以copy()在现有Carbon实例上创建。如预期的那样，日期、时间和时区值都被复制到新实例。
 
```php
$dt = Carbon::now();
echo $dt->diffInYears($dt->copy()->addYear());  // 1
// $dt 实例没有改变，任然是Carbon:now()
```
 
您可以在现有的Carbon实例上使用nowWithSameTz()来在相同的时区中获取一个新的实例。
 
```php
$meeting = Carbon::createFromTime(19, 15, 00, 'Africa/Johannesburg');

// 19:15 in Johannesburg
echo 'Meeting starts at '.$meeting->format('H:i').' in Johannesburg.';                  // Meeting starts at 19:15 in Johannesburg.
// now in Johannesburg
echo "It's ".$meeting->nowWithSameTz()->format('H:i').' right now in Johannesburg.';    // It's 09:37 right now in Johannesburg.
```
 
最后，如果您发现自己从另一个库继承了\DateTime实例，不要害怕!您可以通过友好的 instance  ()方法创建一个Carbon实例。或者使用更灵活的方法 make  ()，它可以从 DateTime  、 Carbon  或 string  返回一个新的Carbon实例，否则它只返回null。
 
```php
$dt = new \DateTime('first day of January 2008'); // <== instance from another API
$carbon = Carbon::instance($dt);
echo get_class($carbon);                               // 'Carbon\Carbon'
echo $carbon->toDateTimeString();                      // 2008-01-01 00:00:00
```
 
关于微秒的简要说明。PHP DateTime  对象允许您设置一个微秒值，但是忽略它的所有日期数学。现在，1.12.0的Carbon在实例化或复制操作过程中支持微秒，并在默认情况下使用 format()  方法。
 
```php
$dt = Carbon::parse('1975-05-21 22:23:00.123456');
echo $dt->micro;                                       // 123456
echo $dt->copy()->micro;                               // 123456
```
 
在PHP 7.1之前 DateTime微秒未添加到“now”实例，并且之后不能更改，这意味着:
 
```php
$date = new DateTime('now');
echo $date->format('u');
// display current microtime in PHP >= 7.1 (expect a bug in PHP 7.1.3 only)
// display 000000 before PHP 7.1

$date = new DateTime('2001-01-01T00:00:00.123456Z');
echo $date->format('u');
// display 123456 in all PHP versions

$date->modify('00:00:00.987654');
echo $date->format('u');
// display 987654 in PHP >= 7.1
// display 123456 before PHP 7.1
```
 
为了解决这个限制，我们在PHP < 7.1中调用了microseconds，但是这个特性在需要时可以被禁用(PHP >= 7.1):
 
```php
Carbon::useMicrosecondsFallback(false);
var_dump(Carbon::isMicrosecondsFallbackEnabled()); // false

echo Carbon::now()->micro; // 0 in PHP < 7.1, microtime in PHP >= 7.1

Carbon::useMicrosecondsFallback(true); // default value
var_dump(Carbon::isMicrosecondsFallbackEnabled()); // true

echo Carbon::now()->micro; // microtime in all PHP version
```
 
是否需要遍历一些日期以找到最早或最近的日期?不知道如何设置初始最大值/最小值?现在有两个助手可以帮助你做出简单的决定:
 
```php
echo Carbon::maxValue();                               // '9999-12-31 23:59:59'
echo Carbon::minValue();                               // '0001-01-01 00:00:00'
```
 
最小和最大值主要取决于系统(32位或64位)。
 
使用32位OS系统或32位版本的PHP(您可以在PHP中使用PHP_INT_SIZE == 4来检查它)，最小值是0-unix-timestamp(1970-01-01 00:00:00)，最大值是常量PHP_INT_MAX给出的时间戳。
 
使用64位OS系统和64位PHP版本，最小值为01-01 00:00，最大值为9999-12-31 23:59:59。
 
 
* ## Localization 
  
 
 
不幸的是，基类DateTime没有任何本地化支持。为了开始本地化支持，还添加了一个formatLocalized($format)方法。实现使用当前实例时间戳对strftime进行调用。如果您首先使用PHP函数setlocale()设置当前的语言环境，那么返回的字符串将被格式化为正确的语言环境。
 
```php
$newLocale = setlocale(LC_TIME, 'German');
if ($newLocale === false) {
    echo '"German" locale is not installed on your machine, it may have a different name a different name on your machine or you may need to install it.';
}
echo $dt->formatLocalized('%A %d %B %Y');          // Mittwoch 21 Mai 1975
setlocale(LC_TIME, 'English');
echo $dt->formatLocalized('%A %d %B %Y');          // Wednesday 21 May 1975
setlocale(LC_TIME, ''); // reset locale
```
 
diffForHumans()也被定位。您可以通过使用静态Carbon::setLocale()函数来设置Carbon locale()，并使用Carbon::getLocale()获取当前的设置。
 
```php
Carbon::setLocale('de');
echo Carbon::getLocale();                          // de
echo Carbon::now()->addYear()->diffForHumans();    // in 1 Jahr

Carbon::setLocale('en');
echo Carbon::getLocale();                          // en
```
 
或者，您可以将一些代码与给定的语言环境隔离:
 
```php
Carbon::executeWithLocale('de', function ($newLocale) {
    // You can optionally get $newLocale as the first argument of the closure
    // It will be set to the new locale or false if the locale was not found.

    echo Carbon::now()->addYear()->diffForHumans();
}); // in 1 Jahr

// outside the function the locale did not change
echo Carbon::getLocale();                          // en

// or same using a return statement
$french = Carbon::executeWithLocale('fr', function () {
    return Carbon::now()->addYear()->diffForHumans();
});
echo $french; // dans 1 an
```
 
有些语言需要打印utf8编码(主要以. utf8结尾的语言环境包)。在本例中，您可以使用静态方法Carbon::setUtf8()对对utf8字符集的formatlocalized()调用的结果进行编码。
 
```php
setlocale(LC_TIME, 'Spanish');
$dt = Carbon::create(2016, 01, 06, 00, 00, 00);
Carbon::setUtf8(false);
echo $dt->formatLocalized('%A %d %B %Y');          // mi�rcoles 06 enero 2016
Carbon::setUtf8(true);
echo $dt->formatLocalized('%A %d %B %Y');          // miércoles 06 enero 2016
Carbon::setUtf8(false);
setlocale(LC_TIME, '');
```
 
在Linux上
 
如果您在翻译方面有问题，请检查系统中安装的地区(本地和生产)。
 
区域设置-列出已启用的区域设置。
 
sudo locale-gen fr_FR。UTF-8安装一个新的语言环境。
 
sudo dpkg-reconfigure locale来发布所有启用的locale。
 
并重启系统。
 
您可以通过以下方式自定义现有语言:
 
```php
Carbon::setLocale('en');
$translator = Carbon::getTranslator();
$translator->setMessages('en', array(
    'day' => ':count boring day|:count boring days',
));

$date1 = Carbon::create(2018, 1, 1, 0, 0, 0);
$date2 = Carbon::create(2018, 1, 4, 4, 0, 0);

echo $date1->diffForHumans($date2, true, false, 2); // 3 boring days 4 hours

$translator->resetMessages('en'); // reset language customizations for en language
```
 
请注意，您还可以使用另一个转换器Carbon::setTranslator($custom)，只要给定的转换器继承了Symfony\Component\Translation\TranslatorInterface。 因此，对格式本地化、getter(如localeMonth、localedayayofweek和短变体)的语言环境支持是由安装在操作系统中的语言环境驱动的。对于其他翻译，由于碳社区的支持，它在内部得到了支持。您可以使用以下方法检查支持的内容:
 
```php
echo implode(', ', array_slice(Carbon::getAvailableLocales(), 0, 3)).'...';      // af, ar, ar_Shakl...

// Support diff syntax (before, after, from now, ago)
var_dump(Carbon::localeHasDiffSyntax('en'));                                     // bool(true)
var_dump(Carbon::localeHasDiffSyntax('zh_TW'));                                  // bool(true)
// Support 1-day diff words (just now, yesterday, tomorrow)
var_dump(Carbon::localeHasDiffOneDayWords('en'));                                // bool(true)
var_dump(Carbon::localeHasDiffOneDayWords('zh_TW'));                             // bool(false)
// Support 2-days diff words (before yesterday, after tomorrow)
var_dump(Carbon::localeHasDiffTwoDayWords('en'));                                // bool(true)
var_dump(Carbon::localeHasDiffTwoDayWords('zh_TW'));                             // bool(false)
// Support short units (1y = 1 year, 1mo = 1 month, etc.)
var_dump(Carbon::localeHasShortUnits('en'));                                     // bool(true)
var_dump(Carbon::localeHasShortUnits('zh_TW'));                                  // bool(false)
// Support period syntax (X times, every X, from X, to X)
var_dump(Carbon::localeHasPeriodSyntax('en'));                                   // bool(true)
var_dump(Carbon::localeHasPeriodSyntax('zh_TW'));                                // bool(false)
```
 
以下是最后一个碳版本支持的73个地区的概述:
 
![][0]
 
![][1]
 
![][2]
 
注意，如果您使用Laravel 5.5+，语言环境将根据当前的最后一个App:setLocale execution自动设置。所以扩散人类将是透明的。您可能仍然需要在某些中间件中运行setlocale以使formatlocalizedworking正确。
 
 
* ## Testing Aids 
  
 
 
测试方法允许您在创建“现在”实例时设置要返回的Carbon实例(real或mock)。所提供的实例将在以下条件下具体返回:
 
对static now()方法的调用，例如:Carbon::now()
 
当一个空(或空字符串)被传递给构造函数或parse()时，ex.new Carbon(空)
 
当字符串“now”传递给构造函数或parse()时，ex. new Carbon('now')
 
给定的实例也将作为diff方法的默认相对时间。
 
```php
$knownDate = Carbon::create(2001, 5, 21, 12);          // create testing date
Carbon::setTestNow($knownDate);                        // set the mock (of course this could be a real mock object)
echo Carbon::getTestNow();                             // 2001-05-21 12:00:00
echo Carbon::now();                                    // 2001-05-21 12:00:00
echo new Carbon();                                     // 2001-05-21 12:00:00
echo Carbon::parse();                                  // 2001-05-21 12:00:00
echo new Carbon('now');                                // 2001-05-21 12:00:00
echo Carbon::parse('now');                             // 2001-05-21 12:00:00
echo Carbon::create(2001, 4, 21, 12)->diffForHumans(); // 1 month ago
var_dump(Carbon::hasTestNow());                        // bool(true)
Carbon::setTestNow();                                  // clear the mock
var_dump(Carbon::hasTestNow());                        // bool(false)
echo Carbon::now();                                    // 2018-07-05 03:37:12
```
 
一个更有意义的完整例子:
 
```php
class SeasonalProduct
{
    protected $price;

    public function __construct($price)
    {
        $this->price = $price;
    }

    public function getPrice() {
        $multiplier = 1;
        if (Carbon::now()->month == 12) {
            $multiplier = 2;
        }

        return $this->price * $multiplier;
    }
}

$product = new SeasonalProduct(100);
Carbon::setTestNow(Carbon::parse('first day of March 2000'));
echo $product->getPrice();                                             // 100
Carbon::setTestNow(Carbon::parse('first day of December 2000'));
echo $product->getPrice();                                             // 200
Carbon::setTestNow(Carbon::parse('first day of May 2000'));
echo $product->getPrice();                                             // 100
Carbon::setTestNow();
```
 
根据给定的“now”实例，还可以对相关短语进行嘲笑。
 
```php
$knownDate = Carbon::create(2001, 5, 21, 12);          // create testing date
Carbon::setTestNow($knownDate);                        // set the mock
echo new Carbon('tomorrow');                           // 2001-05-22 00:00:00  ... notice the time !
echo new Carbon('yesterday');                          // 2001-05-20 00:00:00
echo new Carbon('next wednesday');                     // 2001-05-23 00:00:00
echo new Carbon('last friday');                        // 2001-05-18 00:00:00
echo new Carbon('this thursday');                      // 2001-05-24 00:00:00
Carbon::setTestNow();                                  // always clear it !
```
 
被认为是相对修饰语的单词列表如下:
 
 
* ago 
* first 
* next 
* last 
* this 
* today 
* tomorrow 
* yesterday 
 
 
请注意，与next()、previous()和modify()方法类似，这些相对修饰符中的一些将把时间设置为00:00。
 
Carbon: parse($time， $tz)和new Carbon($time， $tz)都可以将时区作为第二个参数。
 
```php
echo Carbon::parse('2012-9-5 23:26:11.223', 'Europe/Paris')->timezone->getName(); // Europe/Paris
```
 
 
* ## Getters 
  
 
 
getter方法是通过PHP的__get()方法实现的。这使您能够像访问属性而不是函数调用那样访问值。
 
```php
$dt = Carbon::parse('2012-10-5 23:26:11.123789');

// 这些getter方法都将返回int类型
var_dump($dt->year);                                         // int(2012)
var_dump($dt->month);                                        // int(10)
var_dump($dt->day);                                          // int(5)
var_dump($dt->hour);                                         // int(23)
var_dump($dt->minute);                                       // int(26)
var_dump($dt->second);                                       // int(11)
var_dump($dt->micro);                                        // int(123789)
// dayOfWeek 返回一个数值 0 (sunday) 到 6 (saturday)
var_dump($dt->dayOfWeek);                                    // int(5)
// dayOfWeekIso 返回一个数值 1 (monday) 到 7 (sunday)
var_dump($dt->dayOfWeekIso);                                 // int(5)
setlocale(LC_TIME, 'German');
var_dump($dt->englishDayOfWeek);                             // string(6) "Friday"
var_dump($dt->shortEnglishDayOfWeek);                        // string(3) "Fri"
var_dump($dt->localeDayOfWeek);                              // string(7) "Freitag"
var_dump($dt->shortLocaleDayOfWeek);                         // string(2) "Fr"
var_dump($dt->englishMonth);                                 // string(7) "October"
var_dump($dt->shortEnglishMonth);                            // string(3) "Oct"
var_dump($dt->localeMonth);                                  // string(7) "Oktober"
var_dump($dt->shortLocaleMonth);                             // string(3) "Okt"
setlocale(LC_TIME, '');
var_dump($dt->dayOfYear);                                    // int(278)
var_dump($dt->weekNumberInMonth);    
// weekNumberInMonth consider weeks from monday to sunday, so the week 1 will
// contain 1 day if the month start with a sunday, and up to 7 if it starts with a monday
var_dump($dt->weekOfMonth);                                  // int(1)
// weekOfMonth will returns 1 for the 7 first days of the month, then 2 from the 8th to
// the 14th, 3 from the 15th to the 21st, 4 from 22nd to 28th and 5 above
var_dump($dt->weekOfYear);                                   // int(40)
var_dump($dt->daysInMonth);                                  // int(31)
var_dump($dt->timestamp);                                    // int(1349493971)
var_dump(Carbon::createFromDate(1975, 5, 21)->age);          // int(43) calculated vs now in the same tz
var_dump($dt->quarter);                                      // int(4)

// Returns an int of seconds difference from UTC (+/- sign included)
var_dump(Carbon::createFromTimestampUTC(0)->offset);         // int(0)
var_dump(Carbon::createFromTimestamp(0)->offset);            // int(-18000)

// Returns an int of hours difference from UTC (+/- sign included)
var_dump(Carbon::createFromTimestamp(0)->offsetHours);       // int(-5)

// Indicates if day light savings time is on
var_dump(Carbon::createFromDate(2012, 1, 1)->dst);           // bool(false)
var_dump(Carbon::createFromDate(2012, 9, 1)->dst);           // bool(true)

// Indicates if the instance is in the same timezone as the local timezone
var_dump(Carbon::now()->local);                              // bool(true)
var_dump(Carbon::now('America/Vancouver')->local);           // bool(false)

// Indicates if the instance is in the UTC timezone
var_dump(Carbon::now()->utc);                                // bool(false)
var_dump(Carbon::now('Europe/London')->utc);                 // bool(false)
var_dump(Carbon::createFromTimestampUTC(0)->utc);            // bool(true)

// Gets the DateTimeZone instance
echo get_class(Carbon::now()->timezone);                     // DateTimeZone
echo get_class(Carbon::now()->tz);                           // DateTimeZone

// Gets the DateTimeZone instance name, shortcut for ->timezone->getName()
echo Carbon::now()->timezoneName;                            // America/Toronto
echo Carbon::now()->tzName;                                  // America/Toronto
```
 
 
* ## Setters 
  
 
 
下面的setter是通过PHP的__set()方法实现的。值得注意的是，除了显式地设置时区之外，任何设置程序都不会更改实例的时区。具体地说，设置时间戳不会将相应的时区设置为UTC。
 
```php
$dt = Carbon::now();

$dt->year = 1975;
$dt->month = 13;             //强制 year++ 然后 month = 1
$dt->month = 5;
$dt->day = 21;
$dt->hour = 22;
$dt->minute = 32;
$dt->second = 5;

$dt->timestamp = 169957925;  // 这不会改变时区

// 通过DateTimeZone实例或字符串设置时区
$dt->timezone = new DateTimeZone('Europe/London');
$dt->timezone = 'Europe/London';
$dt->tz = 'Europe/London';
```
 
 
* ## Fluent Setters 
  
 
 
对于setter没有可选参数，但是函数定义中有足够的多样性，因此无论如何都不需要它们。值得注意的是，除了显式地设置时区之外，任何设置程序都不会更改实例的时区。具体地说，设置时间戳不会将相应的时区设置为UTC。
 
```php
$dt = Carbon::now();

$dt->year(1975)->month(5)->day(21)->hour(22)->minute(32)->second(5)->toDateTimeString();
$dt->setDate(1975, 5, 21)->setTime(22, 32, 5)->toDateTimeString();
$dt->setDate(1975, 5, 21)->setTimeFromTimeString('22:32:05')->toDateTimeString();
$dt->setDateTime(1975, 5, 21, 22, 32, 5)->toDateTimeString();

$dt->timestamp(169957925)->timezone('Europe/London');

$dt->tz('America/Toronto')->setTimezone('America/Vancouver');
```
 
您还可以将日期和时间与其他DateTime/Carbon对象分开设置:
 
```php
$source1 = new Carbon('2010-05-16 22:40:10');

$dt = new Carbon('2001-01-01 01:01:01');
$dt->setTimeFrom($source1);

echo $dt; // 2001-01-01 22:40:10

$source2 = new DateTime('2013-09-01 09:22:56');

$dt->setDateFrom($source2);

echo $dt; // 2013-09-01 22:40:10
```
 
 
* ## IsSet 
  
 
 
实现了PHP函数__isset()。这是在一些外部系统(例如Twig)在使用属性之前验证属性的存在时完成的。这是使用isset()或empty()方法完成的。在PHP站点:__isset()、isset()、empty()上，您可以阅读更多关于这些内容的信息。
 
```php
var_dump(isset(Carbon::now()->iDoNotExist));       // bool(false)
var_dump(isset(Carbon::now()->hour));              // bool(true)
var_dump(empty(Carbon::now()->iDoNotExist));       // bool(true)
var_dump(empty(Carbon::now()->year));              // bool(false)
```
 
 
* ## String Formatting 
  
 
 
所有可用的toXXXString()方法都依赖于基类方法DateTime: format()。您将注意到__toString()方法的定义，它允许在字符串上下文中使用时将一个Carbon实例打印为一个漂亮的日期时间字符串。
 
```php
$dt = Carbon::create(1975, 12, 25, 14, 15, 16);

var_dump($dt->toDateTimeString() == $dt);          // bool(true) => uses __toString()
echo $dt->toDateString();                          // 1975-12-25
echo $dt->toFormattedDateString();                 // Dec 25, 1975
echo $dt->toTimeString();                          // 14:15:16
echo $dt->toDateTimeString();                      // 1975-12-25 14:15:16
echo $dt->toDayDateTimeString();                   // Thu, Dec 25, 1975 2:15 PM

// ... of course format() is still available
echo $dt->format('l jS \\of F Y h:i:s A');         // Thursday 25th of December 1975 02:15:16 PM

// The reverse hasFormat method allows you to test if a string looks like a given format
var_dump($dt->hasFormat('Thursday 25th December 1975 02:15:16 PM', 'l jS F Y h:i:s A')); // bool(true)
```
 
您还可以设置默认的__toString()格式(默认为Y-m-d H:i:s)，这是在发生类型杂耍时使用的格式。
 
```php
Carbon::setToStringFormat('jS \o\f F, Y g:i:s a');
echo $dt;                                          // 25th of December, 1975 2:15:16 pm
Carbon::resetToStringFormat();
echo $dt;                                          // 1975-12-25 14:15:16
```
 
NOTE:对于本地化支持，请参阅本地化部分。
 
 
* ## Common Formats 
  
 
 
下面是DateTime类中提供的公共格式的包装器。
 
```php
$dt = Carbon::createFromFormat('Y-m-d H:i:s.u', '2019-02-01 03:45:27.612584');

// $dt->toAtomString() is the same as $dt->format(DateTime::ATOM);
echo $dt->toAtomString();        // 2019-02-01T03:45:27-05:00
echo $dt->toCookieString();      // Friday, 01-Feb-2019 03:45:27 EST

echo $dt->toIso8601String();     // 2019-02-01T03:45:27-05:00
// Be aware we chose to use the full-extended format of the ISO 8601 norm
// Natively, DateTime::ISO8601 format is not compatible with ISO-8601 as it
// is explained here in the PHP documentation:
// https://php.net/manual/class.datetime.php#datetime.constants.iso8601
// We consider it as a PHP mistake and chose not to provide method for this
// format, but you still can use it this way:
echo $dt->format(DateTime::ISO8601); // 2019-02-01T03:45:27-0500

echo $dt->toIso8601ZuluString(); // 2019-02-01T08:45:27Z
echo $dt->toRfc822String();      // Fri, 01 Feb 19 03:45:27 -0500
echo $dt->toRfc850String();      // Friday, 01-Feb-19 03:45:27 EST
echo $dt->toRfc1036String();     // Fri, 01 Feb 19 03:45:27 -0500
echo $dt->toRfc1123String();     // Fri, 01 Feb 2019 03:45:27 -0500
echo $dt->toRfc2822String();     // Fri, 01 Feb 2019 03:45:27 -0500
echo $dt->toRfc3339String();     // 2019-02-01T03:45:27-05:00
echo $dt->toRfc7231String();     // Fri, 01 Feb 2019 08:45:27 GMT
echo $dt->toRssString();         // Fri, 01 Feb 2019 03:45:27 -0500
echo $dt->toW3cString();         // 2019-02-01T03:45:27-05:00

var_dump($dt->toArray());
/*
array(12) {
  ["year"]=>
  int(2019)
  ["month"]=>
  int(2)
  ["day"]=>
  int(1)
  ["dayOfWeek"]=>
  int(5)
  ["dayOfYear"]=>
  int(31)
  ["hour"]=>
  int(3)
  ["minute"]=>
  int(45)
  ["second"]=>
  int(27)
  ["micro"]=>
  int(612584)
  ["timestamp"]=>
  int(1549010727)
  ["formatted"]=>
  string(19) "2019-02-01 03:45:27"
  ["timezone"]=>
  object(DateTimeZone)#118 (2) {
    ["timezone_type"]=>
    int(3)
    ["timezone"]=>
    string(15) "America/Toronto"
  }
}
*/
```
 
 
* ## Comparison 
  
 
 
通过以下函数提供了简单的比较。请记住，比较是在UTC时区进行的，所以事情并不总是像看上去的那样。
 
```php
echo Carbon::now()->tzName;                        // America/Toronto
$first = Carbon::create(2012, 9, 5, 23, 26, 11);
$second = Carbon::create(2012, 9, 5, 20, 26, 11, 'America/Vancouver');

echo $first->toDateTimeString();                   // 2012-09-05 23:26:11
echo $first->tzName;                               // America/Toronto
echo $second->toDateTimeString();                  // 2012-09-05 20:26:11
echo $second->tzName;                              // America/Vancouver

var_dump($first->eq($second));                     // bool(true)
var_dump($first->ne($second));                     // bool(false)
var_dump($first->gt($second));                     // bool(false)
var_dump($first->gte($second));                    // bool(true)
var_dump($first->lt($second));                     // bool(false)
var_dump($first->lte($second));                    // bool(true)

$first->setDateTime(2012, 1, 1, 0, 0, 0);
$second->setDateTime(2012, 1, 1, 0, 0, 0);         // Remember tz is 'America/Vancouver'

var_dump($first->eq($second));                     // bool(false)
var_dump($first->ne($second));                     // bool(true)
var_dump($first->gt($second));                     // bool(false)
var_dump($first->gte($second));                    // bool(false)
var_dump($first->lt($second));                     // bool(true)
var_dump($first->lte($second));                    // bool(true)

// All have verbose aliases and PHP equivalent code:

var_dump($first->eq($second));                     // bool(false)
var_dump($first->equalTo($second));                // bool(false)
var_dump($first == $second);                       // bool(false)

var_dump($first->ne($second));                     // bool(true)
var_dump($first->notEqualTo($second));             // bool(true)
var_dump($first != $second);                       // bool(true)

var_dump($first->gt($second));                     // bool(false)
var_dump($first->greaterThan($second));            // bool(false)
var_dump($first > $second);                        // bool(false)

var_dump($first->gte($second));                    // bool(false)
var_dump($first->greaterThanOrEqualTo($second));   // bool(false)
var_dump($first >= $second);                       // bool(false)

var_dump($first->lt($second));                     // bool(true)
var_dump($first->lessThan($second));               // bool(true)
var_dump($first < $second);                        // bool(true)

var_dump($first->lte($second));                    // bool(true)
var_dump($first->lessThanOrEqualTo($second));      // bool(true)
var_dump($first <= $second);                       // bool(true)
```
 
这些方法使用PHP $date1 == $date2提供的自然比较，因此在PHP 7.1之前，所有方法都将忽略milli/micro-seconds，然后从7.1开始考虑它们。
 
要确定当前实例是否在其他两个实例之间，可以使用恰当命名的between()方法。第三个参数表示是否应该进行相等的比较。默认值是true，它决定了它的中间值还是等于边界。
 
```php
$first = Carbon::create(2012, 9, 5, 1);
$second = Carbon::create(2012, 9, 5, 5);
var_dump(Carbon::create(2012, 9, 5, 3)->between($first, $second));          // bool(true)
var_dump(Carbon::create(2012, 9, 5, 5)->between($first, $second));          // bool(true)
var_dump(Carbon::create(2012, 9, 5, 5)->between($first, $second, false));   // bool(false)
```
 
哇!你忘记了min()和max()了吗?不。这也被适当命名的min()和max()方法或minimum()和maximum()别名所覆盖。与往常一样，如果指定为null，则默认参数现在为。
 
```php
$dt1 = Carbon::createMidnightDate(2012, 1, 1);
$dt2 = Carbon::createMidnightDate(2014, 1, 30);
echo $dt1->min($dt2);                              // 2012-01-01 00:00:00
echo $dt1->minimum($dt2);                          // 2012-01-01 00:00:00

$dt1 = Carbon::createMidnightDate(2012, 1, 1);
$dt2 = Carbon::createMidnightDate(2014, 1, 30);
echo $dt1->max($dt2);                              // 2014-01-30 00:00:00
echo $dt1->maximum($dt2);                          // 2014-01-30 00:00:00

// now is the default param
$dt1 = Carbon::createMidnightDate(2000, 1, 1);
echo $dt1->max();                                  // 2018-07-05 03:37:12
echo $dt1->maximum();                              // 2018-07-05 03:37:12

$dt1 = Carbon::createMidnightDate(2010, 4, 1);
$dt2 = Carbon::createMidnightDate(2010, 3, 28);
$dt3 = Carbon::createMidnightDate(2010, 4, 16);

// returns the closest of two date (no matter before or after)
echo $dt1->closest($dt2, $dt3);                    // 2010-03-28 00:00:00
echo $dt2->closest($dt1, $dt3);                    // 2010-04-01 00:00:00
echo $dt3->closest($dt2, $dt1);                    // 2010-04-01 00:00:00

// returns the farthest of two date (no matter before or after)
echo $dt1->farthest($dt2, $dt3);                   // 2010-04-16 00:00:00
echo $dt2->farthest($dt1, $dt3);                   // 2010-04-16 00:00:00
echo $dt3->farthest($dt2, $dt1);                   // 2010-03-28 00:00:00
```
 
为了处理最常用的情况，这里有一些简单的帮助函数，希望它们的名称能很明显地反映出来。对于以某种方式与now() (ex.istoday()))进行比较的方法，now()是在与实例相同的时区创建的。
 
```php
$dt = Carbon::now();
$dt2 = Carbon::createFromDate(1987, 4, 23);

$dt->isSameAs('w', $dt2); // w is the date of the week, so this will return true if $dt and $dt2
// the same day of week (both monday or both sunday, etc.)
// you can use any format and combine as much as you want.
$dt->isFuture();
$dt->isPast();

$dt->isSameYear($dt2);
$dt->isCurrentYear();
$dt->isNextYear();
$dt->isLastYear();
$dt->isLongYear(); // see https://en.wikipedia.org/wiki/ISO_8601#Week_dates
$dt->isLeapYear();

$dt->isSameQuarter($dt2); // same quarter (3 months) no matter the year of the given date
$dt->isSameQuarter($dt2, true); // same quarter of the same year of the given date
/*
    Alternatively, you can run Carbon::compareYearWithMonth() to compare both quarter and year by default,
    In this case you can use $dt->isSameQuarter($dt2, false) to compare ignoring the year
    Run Carbon::compareYearWithMonth(false) to reset to the default behavior
    Run Carbon::shouldCompareYearWithMonth() to get the current setting
*/
$dt->isCurrentQuarter();
$dt->isNextQuarter(); // date is in the next quarter
$dt->isLastQuarter(); // in previous quarter

$dt->isSameMonth($dt2); // same month no matter the year of the given date
$dt->isSameMonth($dt2, true); // same month of the same year of the given date
/*
    As for isSameQuarter, you can run Carbon::compareYearWithMonth() to compare both month and year by default,
    In this case you can use $dt->isSameMonth($dt2, false) to compare ignoring the year
    Run Carbon::compareYearWithMonth(false) to reset to the default behavior
    Run Carbon::shouldCompareYearWithMonth() to get the current setting
*/
$dt->isCurrentMonth();
$dt->isNextMonth();
$dt->isLastMonth();

$dt->isWeekday();
$dt->isWeekend();
$dt->isMonday();
$dt->isTuesday();
$dt->isWednesday();
$dt->isThursday();
$dt->isFriday();
$dt->isSaturday();
$dt->isSunday();
$dt->isDayOfWeek(Carbon::SATURDAY); // is a saturday
$dt->isLastOfMonth(); // is the last day of the month

$dt->isSameDay($dt2); // Same day of same month of same year
$dt->isCurrentDay();
$dt->isYesterday();
$dt->isToday();
$dt->isTomorrow();
$dt->isNextWeek();
$dt->isLastWeek();

$dt->isSameHour($dt2);
$dt->isCurrentHour();
$dt->isSameMinute($dt2);
$dt->isCurrentMinute();
$dt->isSameSecond($dt2);
$dt->isCurrentSecond();

$dt->isStartOfDay(); // check if hour is 00:00:00
$dt->isMidnight(); // check if hour is 00:00:00 (isStartOfDay alias)
$dt->isEndOfDay(); // check if hour is 23:59:59
$dt->isMidday(); // check if hour is 12:00:00 (or other midday hour set with Carbon::setMidDayAt())
$born = Carbon::createFromDate(1987, 4, 23);
$noCake = Carbon::createFromDate(2014, 9, 26);
$yesCake = Carbon::createFromDate(2014, 4, 23);
$overTheHill = Carbon::now()->subYears(50);
var_dump($born->isBirthday($noCake));              // bool(false)
var_dump($born->isBirthday($yesCake));             // bool(true)
var_dump($overTheHill->isBirthday());              // bool(true) -> default compare it to today!
```
 
 
* ## Addition and Subtraction 
  
 
 
默认的DateTime提供了几种不同的方法来方便地添加和减少时间。有modify()、add()和sub()。modify()使用一个魔术date/time格式字符串“last day of next month”，它解析并应用修改，而add()和sub()则期望一个不那么明显的日期间隔实例(例如新的\日期间隔('P6YT5M')将意味着6年5分钟)。希望使用这些流畅的函数将会更加清晰，并且在几个星期内没有看到您的代码后更容易阅读。当然，我不会让您选择，因为基类函数仍然可用。
 
```php
$dt = Carbon::create(2012, 1, 31, 0);

echo $dt->toDateTimeString();            // 2012-01-31 00:00:00

echo $dt->addCenturies(5);               // 2512-01-31 00:00:00
echo $dt->addCentury();                  // 2612-01-31 00:00:00
echo $dt->subCentury();                  // 2512-01-31 00:00:00
echo $dt->subCenturies(5);               // 2012-01-31 00:00:00

echo $dt->addYears(5);                   // 2017-01-31 00:00:00
echo $dt->addYear();                     // 2018-01-31 00:00:00
echo $dt->subYear();                     // 2017-01-31 00:00:00
echo $dt->subYears(5);                   // 2012-01-31 00:00:00

echo $dt->addQuarters(2);                // 2012-07-31 00:00:00
echo $dt->addQuarter();                  // 2012-10-31 00:00:00
echo $dt->subQuarter();                  // 2012-07-31 00:00:00
echo $dt->subQuarters(2);                // 2012-01-31 00:00:00

echo $dt->addMonths(60);                 // 2017-01-31 00:00:00
echo $dt->addMonth();                    // 2017-03-03 00:00:00 equivalent of $dt->month($dt->month + 1); so it wraps
echo $dt->subMonth();                    // 2017-02-03 00:00:00
echo $dt->subMonths(60);                 // 2012-02-03 00:00:00

echo $dt->addDays(29);                   // 2012-03-03 00:00:00
echo $dt->addDay();                      // 2012-03-04 00:00:00
echo $dt->subDay();                      // 2012-03-03 00:00:00
echo $dt->subDays(29);                   // 2012-02-03 00:00:00

echo $dt->addWeekdays(4);                // 2012-02-09 00:00:00
echo $dt->addWeekday();                  // 2012-02-10 00:00:00
echo $dt->subWeekday();                  // 2012-02-09 00:00:00
echo $dt->subWeekdays(4);                // 2012-02-03 00:00:00

echo $dt->addWeeks(3);                   // 2012-02-24 00:00:00
echo $dt->addWeek();                     // 2012-03-02 00:00:00
echo $dt->subWeek();                     // 2012-02-24 00:00:00
echo $dt->subWeeks(3);                   // 2012-02-03 00:00:00

echo $dt->addHours(24);                  // 2012-02-04 00:00:00
echo $dt->addHour();                     // 2012-02-04 01:00:00
echo $dt->subHour();                     // 2012-02-04 00:00:00
echo $dt->subHours(24);                  // 2012-02-03 00:00:00

echo $dt->addMinutes(61);                // 2012-02-03 01:01:00
echo $dt->addMinute();                   // 2012-02-03 01:02:00
echo $dt->subMinute();                   // 2012-02-03 01:01:00
echo $dt->subMinutes(61);                // 2012-02-03 00:00:00

echo $dt->addSeconds(61);                // 2012-02-03 00:01:01
echo $dt->addSecond();                   // 2012-02-03 00:01:02
echo $dt->subSecond();                   // 2012-02-03 00:01:01
echo $dt->subSeconds(61);                // 2012-02-03 00:00:00
```
 
为了好玩，您还可以将负值传递给addXXX()，实际上这就是subXXX()实现的方式。 附注:如果你忘记并使用addDay(5) 或subYear(3)，我支持你; 默认情况下，Carbon依赖于底层父类PHP DateTime行为。因此，增加或减少月份可能会溢出，例如:
 
```php
$dt = Carbon::create(2017, 1, 31, 0);

echo $dt->copy()->addMonth();            // 2017-03-03 00:00:00
echo $dt->copy()->subMonths(2);          // 2016-12-01 00:00:00
```
 
为了防止溢出Carbon:usemonthverflow (false)
 
```php
Carbon::useMonthsOverflow(false);

$dt = Carbon::createMidnightDate(2017, 1, 31);

echo $dt->copy()->addMonth();            // 2017-02-28 00:00:00
echo $dt->copy()->subMonths(2);          // 2016-11-30 00:00:00

// Call the method with true to allow overflow again
Carbon::resetMonthsOverflow(); // same as Carbon::useMonthsOverflow(true);
```
 
方法Carbon:::shouldOverflowMonths()允许您知道当前是否启用了溢出。您也可以使用->addMonthsNoOverflow， ->subMonthsNoOverflow， ->addMonthsWithOverflow， -> submonth withoverflow(或单数方法，不含s至“month”)，显式add/sub，无论当前模式如何，都可以添加或不添加溢出。
 
```php
Carbon::useMonthsOverflow(false);

$dt = Carbon::createMidnightDate(2017, 1, 31);

echo $dt->copy()->addMonthWithOverflow();          // 2017-03-03 00:00:00
// plural addMonthsWithOverflow() method is also available
echo $dt->copy()->subMonthsWithOverflow(2);        // 2016-12-01 00:00:00
// singular subMonthWithOverflow() method is also available
echo $dt->copy()->addMonthNoOverflow();            // 2017-02-28 00:00:00
// plural addMonthsNoOverflow() method is also available
echo $dt->copy()->subMonthsNoOverflow(2);          // 2016-11-30 00:00:00
// singular subMonthNoOverflow() method is also available

echo $dt->copy()->addMonth();                      // 2017-02-28 00:00:00
echo $dt->copy()->subMonths(2);                    // 2016-11-30 00:00:00

Carbon::useMonthsOverflow(true);

$dt = Carbon::createMidnightDate(2017, 1, 31);

echo $dt->copy()->addMonthWithOverflow();          // 2017-03-03 00:00:00
echo $dt->copy()->subMonthsWithOverflow(2);        // 2016-12-01 00:00:00
echo $dt->copy()->addMonthNoOverflow();            // 2017-02-28 00:00:00
echo $dt->copy()->subMonthsNoOverflow(2);          // 2016-11-30 00:00:00

echo $dt->copy()->addMonth();                      // 2017-03-03 00:00:00
echo $dt->copy()->subMonths(2);                    // 2016-12-01 00:00:00

Carbon::resetMonthsOverflow();
```
 
从1.23.0版本开始，在以下年份也可以使用溢出控制:
 
```php
Carbon::useYearsOverflow(false);

$dt = Carbon::createMidnightDate(2020, 2, 29);

var_dump(Carbon::shouldOverflowYears());           // bool(false)

echo $dt->copy()->addYearWithOverflow();           // 2021-03-01 00:00:00
// plural addYearsWithOverflow() method is also available
echo $dt->copy()->subYearsWithOverflow(2);         // 2018-03-01 00:00:00
// singular subYearWithOverflow() method is also available
echo $dt->copy()->addYearNoOverflow();             // 2021-02-28 00:00:00
// plural addYearsNoOverflow() method is also available
echo $dt->copy()->subYearsNoOverflow(2);           // 2018-02-28 00:00:00
// singular subYearNoOverflow() method is also available

echo $dt->copy()->addYear();                       // 2021-02-28 00:00:00
echo $dt->copy()->subYears(2);                     // 2018-02-28 00:00:00

Carbon::useYearsOverflow(true);

$dt = Carbon::createMidnightDate(2020, 2, 29);

var_dump(Carbon::shouldOverflowYears());           // bool(true)

echo $dt->copy()->addYearWithOverflow();           // 2021-03-01 00:00:00
echo $dt->copy()->subYearsWithOverflow(2);         // 2018-03-01 00:00:00
echo $dt->copy()->addYearNoOverflow();             // 2021-02-28 00:00:00
echo $dt->copy()->subYearsNoOverflow(2);           // 2018-02-28 00:00:00

echo $dt->copy()->addYear();                       // 2021-03-01 00:00:00
echo $dt->copy()->subYears(2);                     // 2018-03-01 00:00:00

Carbon::resetYearsOverflow();
```
 
 
* ## Difference 
  
 
 
由于Carbon继承了DateTime，它继承了它的方法，如diff()，它将第二个date对象作为参数，并返回一个DateInterval实例。
 
我们还提供了diffAsCarbonInterval()，类似于diff()，但返回一个CarbonInterval实例。检查CarbonInterval 章节了解更多信息。每个单元的Carbon添加了diff方法，如diffInYears()、diffInMonths()等。diffAsCarbonInterval()和diffIn*()方法都可以使用两个可选参数:date to compare with(如果缺失，now是默认值)，以及一个绝对布尔选项(默认为true)，无论哪个日期大于另一个，该方法都返回一个绝对值。如果设置为false，则在调用方法的实例大于比较日期(第一个参数或now)时返回负值。注意，diff()原型是不同的:它的第一个参数(date)是强制性的，第二个参数(绝对选项)默认为false。
 
这些函数总是返回在指定的时间内表示的总差异。这与基类diff()函数不同，该函数的时间间隔为122秒，通过DateInterval实例返回2分零2秒。diffInMinutes()函数只返回2，而diffInSeconds()将返回122。所有的值都被截断而不是四舍五入。下面的每个函数都有一个默认的第一个参数，该参数是要比较的Carbon实例，如果您想使用now()，则为null。第二个参数也是可选的，如果您希望返回值是绝对值，或者如果传递的日期小于当前实例，则返回值可能具有-(负)符号的相对值。这将默认为true，返回绝对值。
 
```php
echo Carbon::now('America/Vancouver')->diffInSeconds(Carbon::now('Europe/London')); // 0

$dtOttawa = Carbon::createMidnightDate(2000, 1, 1, 'America/Toronto');
$dtVancouver = Carbon::createMidnightDate(2000, 1, 1, 'America/Vancouver');
echo $dtOttawa->diffInHours($dtVancouver);                             // 3
echo $dtVancouver->diffInHours($dtOttawa);                             // 3

echo $dtOttawa->diffInHours($dtVancouver, false);                      // 3
echo $dtVancouver->diffInHours($dtOttawa, false);                      // -3

$dt = Carbon::createMidnightDate(2012, 1, 31);
echo $dt->diffInDays($dt->copy()->addMonth());                         // 31
echo $dt->diffInDays($dt->copy()->subMonth(), false);                  // -31

$dt = Carbon::createMidnightDate(2012, 4, 30);
echo $dt->diffInDays($dt->copy()->addMonth());                         // 30
echo $dt->diffInDays($dt->copy()->addWeek());                          // 7

$dt = Carbon::createMidnightDate(2012, 1, 1);
echo $dt->diffInMinutes($dt->copy()->addSeconds(59));                  // 0
echo $dt->diffInMinutes($dt->copy()->addSeconds(60));                  // 1
echo $dt->diffInMinutes($dt->copy()->addSeconds(119));                 // 1
echo $dt->diffInMinutes($dt->copy()->addSeconds(120));                 // 2

echo $dt->addSeconds(120)->secondsSinceMidnight();                     // 120

$interval = $dt->diffAsCarbonInterval($dt->copy()->subYears(3), false);
echo ($interval->invert ? 'minus ' : 'plus ') . $interval->years;      // minus 3
```
 
关于夏令时(DST)的重要注意，默认情况下，PHP DateTime不考虑DST，这意味着，像2014年3月30日这样只有23小时的一天在伦敦将被计算为24小时。
 
```php
$date = new DateTime('2014-03-30 00:00:00', new DateTimeZone('Europe/London')); // DST off
echo $date->modify('+25 hours')->format('H:i');                   // 01:00 (DST on, 24 hours only have been actually added)
```
 
Carbon也遵循这种行为，增加/减少/降低秒/分钟/小时。但是我们提供了使用时间戳进行实时工作的方法:
 
```php
$date = new Carbon('2014-03-30 00:00:00', 'Europe/London');    // DST off
echo $date->addRealHours(25)->format('H:i');             // 02:00 (DST on)
echo $date->diffInRealHours('2014-03-30 00:00:00');               // 25
echo $date->diffInHours('2014-03-30 00:00:00');                   // 26
echo $date->diffInRealMinutes('2014-03-30 00:00:00');             // 1500
echo $date->diffInMinutes('2014-03-30 00:00:00');                 // 1560
echo $date->diffInRealSeconds('2014-03-30 00:00:00');             // 90000
echo $date->diffInSeconds('2014-03-30 00:00:00');                 // 93600
echo $date->subRealHours(25)->format('H:i');             // 00:00 (DST off)
```
 
同样的方法可以使用addRealMinutes()、subRealMinutes()、addRealSeconds()、subRealSeconds()和所有它们的唯一快捷方式:addRealHour()、subRealHour()、addrealmin()、subRealMinute()、addRealSecond()、subRealSecond()。
 
还有特殊的过滤器函数diffindaysfilter()、diffinhoursfilter()和difffilter()，以帮助您按天数、小时或自定义间隔过滤差异。例如，计算两个实例之间的周末天数:
 
```php
$dt = Carbon::create(2014, 1, 1);
$dt2 = Carbon::create(2014, 12, 31);
$daysForExtraCoding = $dt->diffInDaysFiltered(function(Carbon $date) {
    return $date->isWeekend();
}, $dt2);

echo $daysForExtraCoding;      // 104

$dt = Carbon::create(2014, 1, 1)->endOfDay();
$dt2 = $dt->copy()->startOfDay();
$littleHandRotations = $dt->diffFiltered(CarbonInterval::minute(), function(Carbon $date) {
    return $date->minute === 0;
}, $dt2, true); // true as last parameter returns absolute value

echo $littleHandRotations;     // 24

$date = Carbon::now()->addSeconds(3666);

echo $date->diffInSeconds();                       // 3666
echo $date->diffInMinutes();                       // 61
echo $date->diffInHours();                         // 1
echo $date->diffInDays();                          // 0

$date = Carbon::create(2016, 1, 5, 22, 40, 32);

echo $date->secondsSinceMidnight();                // 81632
echo $date->secondsUntilEndOfDay();                // 4767

$date1 = Carbon::createMidnightDate(2016, 1, 5);
$date2 = Carbon::createMidnightDate(2017, 3, 15);

echo $date1->diffInDays($date2);                   // 435
echo $date1->diffInWeekdays($date2);               // 311
echo $date1->diffInWeekendDays($date2);            // 124
echo $date1->diffInWeeks($date2);                  // 62
echo $date1->diffInMonths($date2);                 // 14
echo $date1->diffInYears($date2);                  // 1
```
 
所有的diffIn*滤波方法都采用1个可调用滤波器作为必要参数，一个date对象作为可选的第二个参数，如果缺失，使用now。您也可以将true作为第三个参数传递，以获得绝对值。
 
对于周/周末的高级处理，使用以下工具:
 
```php
echo implode(', ', Carbon::getDays());                 // Sunday, Monday, Tuesday, Wednesday, Thursday, Friday, Saturday

$saturday = new Carbon('first saturday of 2019');
$sunday = new Carbon('first sunday of 2019');
$monday = new Carbon('first monday of 2019');

echo implode(', ', Carbon::getWeekendDays());                // 6, 0
var_dump($saturday->isWeekend());                            // bool(true)
var_dump($sunday->isWeekend());                              // bool(true)
var_dump($monday->isWeekend());                              // bool(false)

Carbon::setWeekendDays(array(
    Carbon::SUNDAY,
    Carbon::MONDAY,
));                                                          //自定义设置“周末”

echo implode(', ', Carbon::getWeekendDays());           // 0, 1
var_dump($saturday->isWeekend());                            // bool(false),周六返回false
var_dump($sunday->isWeekend());                              // bool(true)
var_dump($monday->isWeekend());                              // bool(true)，周一返回true

Carbon::setWeekendDays(array(
    Carbon::SATURDAY,
    Carbon::SUNDAY,
));
// weekend days and start/end of week or not linked
Carbon::setWeekStartsAt(Carbon::FRIDAY);
Carbon::setWeekEndsAt(Carbon::WEDNESDAY); // and it does not need neither to precede the start

var_dump(Carbon::getWeekStartsAt() === Carbon::FRIDAY);      // bool(true)
var_dump(Carbon::getWeekEndsAt() === Carbon::WEDNESDAY);     // bool(true)
echo $saturday->copy()->startOfWeek()->toRfc850String();     // Friday, 06-Jul-18 00:00:00 EDT
echo $saturday->copy()->endOfWeek()->toRfc850String();       // Wednesday, 11-Jul-18 23:59:59 EDT

Carbon::setWeekStartsAt(Carbon::MONDAY);
Carbon::setWeekEndsAt(Carbon::SUNDAY);

echo $saturday->copy()->startOfWeek()->toRfc850String();     // Monday, 02-Jul-18 00:00:00 EDT
echo $saturday->copy()->endOfWeek()->toRfc850String();       // Sunday, 08-Jul-18 23:59:59 EDT
```
 
 
* ## Difference for Humans 
  
 
 
对人类来说，一个月前比30天前更容易阅读。这是在大多数日期库中常见的函数，所以我也想在这里添加它。函数的唯一参数是另一个要对其进行diff的Carbon实例，当然，如果没有指定，它默认为now()。
 
此方法将在相对于实例的差值和传入实例的差值之后添加短语。有4个可能性:
 
当将过去的值与现在的默认值进行比较时:
 
1小时前
 
5个月前
 
当将未来的值与现在的默认值进行比较时:
 
从现在开始的1小时
 
从现在开始的5个月
 
当比较一个过去的值与另一个值时:
 
前1小时
 
5个月前
 
当比较未来的价值与另一个价值时:
 
1小时后
 
5个月后
 
您还可以将true作为第二个参数传递，以便从现在开始删除修饰符，等等:diffforhuman ($other, true)。
 
如果在所使用的语言环境:diffforhuman ($other, false, true)中可用，您可以将true作为第三个参数传递给它，以使用简短语法。
 
您可以将1和6之间的数字作为第4个参数传递给diffforhuman ($other, false, false, 4)。
 
$other实例可以是DateTime、Carbon实例或任何实现DateTimeInterface的对象，如果传递了一个字符串，它将被解析为获取一个Carbon实例，如果传递了null，那么将使用Carbon: now()。
 
```php
// The most typical usage is for comments
// The instance is the date the comment was created and its being compared to default now()
echo Carbon::now()->subDays(5)->diffForHumans();               // 5 days ago

echo Carbon::now()->diffForHumans(Carbon::now()->subYear());   // 1 year after

$dt = Carbon::createFromDate(2011, 8, 1);

echo $dt->diffForHumans($dt->copy()->addMonth());                        // 1 month before
echo $dt->diffForHumans($dt->copy()->subMonth());                        // 1 month after

echo Carbon::now()->addSeconds(5)->diffForHumans();                      // 5 seconds from now

echo Carbon::now()->subDays(24)->diffForHumans();                        // 3 weeks ago（21-27都返回这个，一个周的单位是7天，小于7直接舍去）
echo Carbon::now()->subDays(24)->diffForHumans(null, true);    // 3 weeks（21-27都返回这个，一个周的单位是7天，小于7直接舍去）
echo Carbon::parse('2019-08-03')->diffForHumans('2019-08-13');           // 1 week before(时间间隔7-13天都是返回这个，一个周的单位是7天，小于7直接舍去）
echo Carbon::parse('2000-01-01 00:50:32')->diffForHumans('@946684800');  // 5 hours after(同理，都是舍去的)

echo Carbon::create(2018, 2, 26, 4, 29, 43)->diffForHumans(Carbon::create(2016, 6, 21, 0, 0, 0), false, false, 6); // 1 year 8 months 5 days 4 hours 29 minutes 43 seconds after
```
 
您还可以在调用diffforhuman()之前使用Carbon::setLocale('fr')更改字符串的locale。有关更多细节，请参见本地化部分。
 
可以通过以下方式启用/禁用diffforhuman()选项:
 
```php
Carbon::enableHumanDiffOption(Carbon::NO_ZERO_DIFF);
var_dump((bool) (Carbon::getHumanDiffOptions() & Carbon::NO_ZERO_DIFF)); // bool(true)
Carbon::disableHumanDiffOption(Carbon::NO_ZERO_DIFF);
var_dump((bool) (Carbon::getHumanDiffOptions() & Carbon::NO_ZERO_DIFF)); // bool(false)
```
 
可用的选项是:
 
Carbon::NO_ZERO_DIFF(默认启用):将空diff变为1秒
 
Carbon::JUST_NOW在默认情况下是禁用的):从现在开始变为“刚才”
 
Carbon:ONE_DAY_WORDS(默认禁用):将“从现在/之前1天”变为“昨天/明天”
 
Carbon::TWO_DAY_WORDS(默认禁用):将“从现在/之前2天”变为“昨天/之后”
 
Carbon::JUST_NOW，Carbon::ONE_DAY_WORDS和Carbon::TWO_DAY_WORDS现在只能使用en和fr语言，其他语言将会恢复到以前的行为，直到添加缺失的翻译。
 
使用管道操作符一次启用/禁用多个选项，例如:Carbon::ONE_DAY_WORDS | Carbon::TWO_DAY_WORDS
 
您还可以使用setHumanDiffOptions($options)禁用所有选项，然后只激活作为参数传递的选项。
 
 
* ## Modifiers 
  
 
 
这些方法组对当前实例进行了有益的修改。他们中的大多数方法的名字都是不言自明的……或者至少应该是这样。您还会注意到startOfXXX()、next()和previous()方法将时间设置为00:00,endOfXXX()方法将时间设置为23:59:59。
 
唯一稍有不同的是average()函数。它将实例移动到其本身和提供的碳参数之间的中间日期。
 
```php
$dt = Carbon::create(2012, 1, 31, 15, 32, 45);
echo $dt->startOfMinute();                         // 2012-01-31 15:32:00

$dt = Carbon::create(2012, 1, 31, 15, 32, 45);
echo $dt->endOfMinute();                           // 2012-01-31 15:32:59

$dt = Carbon::create(2012, 1, 31, 15, 32, 45);
echo $dt->startOfHour();                           // 2012-01-31 15:00:00

$dt = Carbon::create(2012, 1, 31, 15, 32, 45);
echo $dt->endOfHour();                             // 2012-01-31 15:59:59

$dt = Carbon::create(2012, 1, 31, 15, 32, 45);
echo Carbon::getMidDayAt();                        // 12获取正午时间
echo $dt->midDay();                                // 2012-01-31 12:00:00
Carbon::setMidDayAt(13);                     //设置正午时间为13点
echo Carbon::getMidDayAt();                        // 13
echo $dt->midDay();                                // 2012-01-31 13:00:00
Carbon::setMidDayAt(12);

$dt = Carbon::create(2012, 1, 31, 12, 0, 0);
echo $dt->startOfDay();                            // 2012-01-31 00:00:00

$dt = Carbon::create(2012, 1, 31, 12, 0, 0);
echo $dt->endOfDay();                              // 2012-01-31 23:59:59

$dt = Carbon::create(2012, 1, 31, 12, 0, 0);
echo $dt->startOfMonth();                          // 2012-01-01 00:00:00

$dt = Carbon::create(2012, 1, 31, 12, 0, 0);
echo $dt->endOfMonth();                            // 2012-01-31 23:59:59

$dt = Carbon::create(2012, 1, 31, 12, 0, 0);
echo $dt->startOfYear();                           // 2012-01-01 00:00:00

$dt = Carbon::create(2012, 1, 31, 12, 0, 0);
echo $dt->endOfYear();                             // 2012-12-31 23:59:59

$dt = Carbon::create(2012, 1, 31, 12, 0, 0);
echo $dt->startOfDecade();                         // 2010-01-01 00:00:00 十年（“年代？”）的开始1990，2000，2010，2010

$dt = Carbon::create(2012, 1, 31, 12, 0, 0);
echo $dt->endOfDecade();                           // 2019-12-31 23:59:59

$dt = Carbon::create(2012, 1, 31, 12, 0, 0);
echo $dt->startOfCentury();                        // 2001-01-01 00:00:00 世纪的开始？为什么不是2000-01-01 00:00:00？？？可能老外就是这个定义的吧……

$dt = Carbon::create(2012, 1, 31, 12, 0, 0);
echo $dt->endOfCentury();                          // 2100-12-31 23:59:59 世纪的结束？为什么不是2099-12-31 23:59:59？？？

$dt = Carbon::create(2012, 1, 31, 12, 0, 0);
echo $dt->startOfWeek();                           // 2012-01-30 00:00:00
var_dump($dt->dayOfWeek == Carbon::MONDAY);        // bool(true) : ISO8601 week starts on Monday ISO8601标准每周的开始是周一，老外好像认为每周的开始是周日……

$dt = Carbon::create(2012, 1, 31, 12, 0, 0);
echo $dt->endOfWeek();                             // 2012-02-05 23:59:59
var_dump($dt->dayOfWeek == Carbon::SUNDAY);        // bool(true) : ISO8601 week ends on Sunday

$dt = Carbon::create(2012, 1, 31, 12, 0, 0);
echo $dt->next(Carbon::WEDNESDAY);                 // 2012-02-01 00:00:00 传了参数表示“下一个周三”，不传表示“明天”
var_dump($dt->dayOfWeek == Carbon::WEDNESDAY);     // bool(true)

$dt = Carbon::create(2012, 1, 1, 12, 0, 0);
echo $dt->next();                                  // 2012-01-08 00:00:00

$dt = Carbon::create(2012, 1, 31, 12, 0, 0);
echo $dt->previous(Carbon::WEDNESDAY);             // 2012-01-25 00:00:00 传了参数表示“上一个周三”，不传表示“昨天”
var_dump($dt->dayOfWeek == Carbon::WEDNESDAY);     // bool(true)

$dt = Carbon::create(2012, 1, 1, 12, 0, 0);
echo $dt->previous();                              // 2011-12-25 00:00:00

$start = Carbon::create(2014, 1, 1, 0, 0, 0);
$end = Carbon::create(2014, 1, 30, 0, 0, 0);
echo $start->average($end);                        // 2014-01-15 12:00:00 (1+30)/2 = 15 int运算

echo Carbon::create(2014, 5, 30, 0, 0, 0)->firstOfMonth();                       // 2014-05-01 00:00:00 这个月的第一天
echo Carbon::create(2014, 5, 30, 0, 0, 0)->firstOfMonth(Carbon::MONDAY);         // 2014-05-05 00:00:00 这个月的第一个周一
echo Carbon::create(2014, 5, 30, 0, 0, 0)->lastOfMonth();                        // 2014-05-31 00:00:00 这个月的最后一天
echo Carbon::create(2014, 5, 30, 0, 0, 0)->lastOfMonth(Carbon::TUESDAY);         // 2014-05-27 00:00:00 这个月的最后一个周二
echo Carbon::create(2014, 5, 30, 0, 0, 0)->nthOfMonth(2, Carbon::SATURDAY);      // 2014-05-10 00:00:00 这个月的第“2”个“周六”，2和周六是参数

echo Carbon::create(2014, 5, 30, 0, 0, 0)->firstOfQuarter();                     // 2014-04-01 00:00:00 这个季度的第一天（5月是第二个季度，所以是4月1号）
echo Carbon::create(2014, 5, 30, 0, 0, 0)->firstOfQuarter(Carbon::MONDAY);       // 2014-04-07 00:00:00 这个季度的第一个周一
echo Carbon::create(2014, 5, 30, 0, 0, 0)->lastOfQuarter();                      // 2014-06-30 00:00:00 这个季度的最后一天
echo Carbon::create(2014, 5, 30, 0, 0, 0)->lastOfQuarter(Carbon::TUESDAY);       // 2014-06-24 00:00:00 这个季度的最后一个周二
echo Carbon::create(2014, 5, 30, 0, 0, 0)->nthOfQuarter(2, Carbon::SATURDAY);    // 2014-04-12 00:00:00 这个季度的第“2”个“周六”，2和周六是参数
echo Carbon::create(2014, 5, 30, 0, 0, 0)->startOfQuarter();                     // 2014-04-01 00:00:00 这个季度的开始
echo Carbon::create(2014, 5, 30, 0, 0, 0)->endOfQuarter();                       // 2014-06-30 23:59:59 这个季度的结束

echo Carbon::create(2014, 5, 30, 0, 0, 0)->firstOfYear();                        // 2014-01-01 00:00:00 同上……
echo Carbon::create(2014, 5, 30, 0, 0, 0)->firstOfYear(Carbon::MONDAY);          // 2014-01-06 00:00:00
echo Carbon::create(2014, 5, 30, 0, 0, 0)->lastOfYear();                         // 2014-12-31 00:00:00
echo Carbon::create(2014, 5, 30, 0, 0, 0)->lastOfYear(Carbon::TUESDAY);          // 2014-12-30 00:00:00
echo Carbon::create(2014, 5, 30, 0, 0, 0)->nthOfYear(2, Carbon::SATURDAY);       // 2014-01-11 00:00:00

echo Carbon::create(2018, 2, 23, 0, 0, 0)->nextWeekday();                        // 2018-02-26 00:00:00 下周一
echo Carbon::create(2018, 2, 23, 0, 0, 0)->previousWeekday();                    // 2018-02-22 00:00:00 这周的上一个工作日
echo Carbon::create(2018, 2, 21, 0, 0, 0)->nextWeekendDay();                     // 2018-02-24 00:00:00 即将要过的周末的第一天（即这星期的周六，如果今天是周六，则结果是周日）
echo Carbon::create(2018, 2, 21, 0, 0, 0)->previousWeekendDay();                 // 2018-02-18 00:00:00 刚过完的周末的最后一个（即上一周的周日，如果今天是周日，则结果是周六）
```
 
 
* ## Constants 
  
 
 
下面的常数是在Carbon中定义的。
 
```php
// These getters specifically return integers, ie intval()
var_dump(Carbon::SUNDAY);                          // int(0)
var_dump(Carbon::MONDAY);                          // int(1)
var_dump(Carbon::TUESDAY);                         // int(2)
var_dump(Carbon::WEDNESDAY);                       // int(3)
var_dump(Carbon::THURSDAY);                        // int(4)
var_dump(Carbon::FRIDAY);                          // int(5)
var_dump(Carbon::SATURDAY);                        // int(6)

var_dump(Carbon::YEARS_PER_CENTURY);               // int(100)
var_dump(Carbon::YEARS_PER_DECADE);                // int(10)
var_dump(Carbon::MONTHS_PER_YEAR);                 // int(12)
var_dump(Carbon::WEEKS_PER_YEAR);                  // int(52)
var_dump(Carbon::DAYS_PER_WEEK);                   // int(7)
var_dump(Carbon::HOURS_PER_DAY);                   // int(24)
var_dump(Carbon::MINUTES_PER_HOUR);                // int(60)
var_dump(Carbon::SECONDS_PER_MINUTE);              // int(60)

$dt = Carbon::createFromDate(2012, 10, 6);
if ($dt->dayOfWeek === Carbon::SATURDAY) {
    echo 'Place bets on Ottawa Senators Winning!';
}
```
 
 
* ## Serialization 
  
 
 
Carbon实例能被序列化的。
 
```php
$dt = Carbon::create(2012, 12, 25, 20, 30, 00, 'Europe/Moscow');

echo serialize($dt);                                              // O:13:"Carbon\Carbon":3:{s:4:"date";s:26:"2012-12-25 20:30:00.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:13:"Europe/Moscow";}
// 等同于:
echo $dt->serialize();                                            // O:13:"Carbon\Carbon":3:{s:4:"date";s:26:"2012-12-25 20:30:00.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:13:"Europe/Moscow";}

$dt = 'O:13:"Carbon\Carbon":3:{s:4:"date";s:26:"2012-12-25 20:30:00.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:13:"Europe/Moscow";}';

echo unserialize($dt)->format('Y-m-d\TH:i:s.uP T');               // 2012-12-25T20:30:00.000000+04:00 MSK
// 等同于:
echo Carbon::fromSerialized($dt)->format('Y-m-d\TH:i:s.uP T');    // 2012-12-25T20:30:00.000000+04:00 MSK
```
 
 
* ## JSON 
  
 
 
Carbon实例可以从JSON编码和解码(这些特性只能从PHP 5.4+中获得，参见下面关于PHP 5.3的注释)。
 
```php
$dt = Carbon::create(2012, 12, 25, 20, 30, 00, 'Europe/Moscow');
echo json_encode($dt);
// {"date":"2012-12-25 20:30:00.000000","timezone_type":3,"timezone":"Europe\/Moscow"}

$json = '{"date":"2012-12-25 20:30:00.000000","timezone_type":3,"timezone":"Europe\/Moscow"}';
$dt = Carbon::__set_state(json_decode($json, true));
echo $dt->format('Y-m-d\TH:i:s.uP T');
// 2012-12-25T20:30:00.000000+04:00 MSK
```
 
您可以使用serializeUsing()自定义序列化。
 
```php
$dt = Carbon::create(2012, 12, 25, 20, 30, 00, 'Europe/Moscow');
Carbon::serializeUsing(function ($date) {
    return $date->getTimestamp();
});
echo json_encode($dt);
/*
1356453000
*/

// Call serializeUsing with null to reset the serializer:
Carbon::serializeUsing(null);
```
 
jsonSerialize()方法返回中间通过“json_encode”将其转换为字符串，它还允许您使用PHP 5.3兼容性。
 
```php
$dt = Carbon::create(2012, 12, 25, 20, 30, 00, 'Europe/Moscow');
echo json_encode($dt->jsonSerialize());
// {"date":"2012-12-25 20:30:00.000000","timezone_type":3,"timezone":"Europe\/Moscow"}
// This is equivalent to the first json_encode example but works with PHP 5.3.

// And it can be used separately:
var_dump($dt->jsonSerialize());
// array(3) {
["date"]=>
  string(26) "2012-12-25 20:30:00.000000"
["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/Moscow"
}
```
 
 
* ## Macro 
  
 
 
如果您习惯于使用Laravel和对象(如响应或集合)，您可能熟悉这个宏概念。Carbon macro()的工作方式与Laravel宏特性相同，它将方法名作为第一个参数，闭包作为第二个参数。这使得闭包操作可以作为一个具有给定名称的方法在所有Carbon实例(也可以作为Carbon static方法)上使用。
 
在PHP 5.4中，$this可用于闭包中引用当前实例。对于PHP 5.3的兼容性，我们还向闭包添加了一个“$self”属性。例子:
 
```php
Carbon::macro('diffFromYear', function ($year, $self = null) {
    // 这个块是为了在独立的Carbon上与PHP版本< 5.4和Laravel兼容
    if (!isset($self) && isset($this)) {
        $self = $this;
    }
    //兼容性块的结束。

    return $self->diffForHumans(Carbon::create($year, 1, 1, 0, 0, 0), false, false, 3);
});
echo Carbon::parse('2020-01-12 12:00:00')->diffFromYear(2019);                 // 1 year 1 week 4 days after
```
 
兼容性块允许您确保宏的完全兼容性。一个关于Illuminate\Support\Carbon (Laravel包装类)的宏将不会被定义，正如上面在PHP 5.3 $this中提到的，这个不会被定义。要使宏在任何地方都能工作，只需粘贴这个if语句测试如果它是定义的，而不是$self然后复制它，然后在函数体中使用$self。
 
不管您是否省略了一些可选参数，只要$self有这个名称，并且是最后一个参数:
 
```php
Carbon::macro('diffFromYear', function ($year, $absolute = false, $short = false, $parts = 1, $self = null) {
    // compatibility chunk
    if (!isset($self) && isset($this)) {
        $self = $this;
    }

    return $self->diffForHumans(Carbon::create($year, 1, 1, 0, 0, 0), $absolute, $short, $parts);
});

echo Carbon::parse('2020-01-12 12:00:00')->diffFromYear(2019);                 // 1 year after
echo Carbon::parse('2020-01-12 12:00:00')->diffFromYear(2019, true);           // 1 year
echo Carbon::parse('2020-01-12 12:00:00')->diffFromYear(2019, true, true);     // 1yr
echo Carbon::parse('2020-01-12 12:00:00')->diffFromYear(2019, true, true, 5);  // 1yr 1w 4d 12h
```
 
还可以将宏分组到类中，并与mixin()一起应用
 
```php
Class BeerDayCarbonMixin
{
    public function nextBeerDay()
    {
        return function ($self = null) {
            // compatibility chunk
            if (!isset($self) && isset($this)) {
                $self = $this;
            }

            return $self->modify('next wednesday');
        };
    }

    public function previousBeerDay()
    {
        return function ($self = null) {
            // compatibility chunk
            if (!isset($self) && isset($this)) {
                $self = $this;
            }

            return $self->modify('previous wednesday');
        };
    }
}

Carbon::mixin(new BeerDayCarbonMixin());

$date = Carbon::parse('First saturday of December 2018');

echo $date->previousBeerDay();                                                 // 2018-11-28 00:00:00
echo $date->nextBeerDay();                                                     // 2018-12-05 00:00:00
```
 
您可以用hasMacro()检查是否可用宏(包括mixin)
 
```php
var_dump(Carbon::hasMacro('previousBeerDay'));                                 // bool(true)
var_dump(Carbon::hasMacro('diffFromYear'));                                    // bool(true)
var_dump(Carbon::hasMacro('dontKnowWhat'));                                    // bool(false)
```
 
你猜怎么着?在CarbonInterval和CarbonPeriod类上也可以使用所有的宏方法。
 
```php
CarbonInterval::macro('twice', function ($self = null) {
    return $self->times(2);
});
echo CarbonInterval::day()->twice()->forHumans();                        // 2 days
echo CarbonInterval::hours(2)->minutes(15)->twice()->forHumans(true);    // 4h 30m

CarbonPeriod::macro('countWeekdays', function ($self = null) {
    return $self->filter('isWeekday')->count();
});
echo CarbonPeriod::create('2017-11-01', '2017-11-30')->countWeekdays();  // 22
echo CarbonPeriod::create('2017-12-01', '2017-12-31')->countWeekdays();  // 21
```
 
以下是社区提出的一些有用的宏:
 
```php
Carbon::macro('isHoliday', function ($self = null) {
    // compatibility chunk
    if (!isset($self) && isset($this)) {
        $self = $this;
    }

    return in_array($self->format('d/m'), [
        '25/12', // Christmas
        '01/01', // New Year
        // ...
    ]);
});
var_dump(Carbon::createMidnightDate(2012, 12, 25)->isHoliday());  // bool(true)
var_dump(Carbon::createMidnightDate(2017, 6, 25)->isHoliday());   // bool(false)
var_dump(Carbon::createMidnightDate(2021, 1, 1)->isHoliday());    // bool(true)
```
 
Credit:kylekatarnls(# 116)。
 
检查cmixin/业务日以获得更完整的业务日处理程序。
 
```php
Class CurrentDaysCarbonMixin
{
    /**
     * Get the all dates of week
     *
     * @return array
     */
    public static function getCurrentWeekDays()
    {
        return function ($self = null) {
            // compatibility chunk
            if (!isset($self) && isset($this)) {
                $self = $this;
            }

            $startOfWeek = ($self ?: static::now())->startOfWeek()->subDay();
            $weekDays = array();

            for ($i = 0; $i < static::DAYS_PER_WEEK; $i++) {
                $weekDays[] = $startOfWeek->addDay()->startOfDay()->copy();
            }

            return $weekDays;
        };
    }

    /**
     * Get the all dates of month
     *
     * @return array
     */
    public static function getCurrentMonthDays()
    {
        return function ($self = null) {
            // compatibility chunk
            if (!isset($self) && isset($this)) {
                $self = $this;
            }

            $startOfMonth = ($self ?: static::now())->startOfMonth()->subDay();
            $endOfMonth = ($self ?: static::now())->endOfMonth()->format('d');
            $monthDays = array();

            for ($i = 0; $i < $endOfMonth; $i++)
            {
                $monthDays[] = $startOfMonth->addDay()->startOfDay()->copy();
            }

            return $monthDays;
        };
    }
}

Carbon::mixin(new CurrentDaysCarbonMixin());

function dumpDateList($dates) {
    echo substr(implode(', ', $dates), 0, 100).'...';
}

dumpDateList(Carbon::getCurrentWeekDays());                       // 2018-07-02 00:00:00, 2018-07-03 00:00:00, 2018-07-04 00:00:00, 2018-07-05 00:00:00, 2018-07-06 00:00...
dumpDateList(Carbon::getCurrentMonthDays());                      // 2018-07-01 00:00:00, 2018-07-02 00:00:00, 2018-07-03 00:00:00, 2018-07-04 00:00:00, 2018-07-05 00:00...
dumpDateList(Carbon::now()->subMonth()->getCurrentWeekDays());    // 2018-06-04 00:00:00, 2018-06-05 00:00:00, 2018-06-06 00:00:00, 2018-06-07 00:00:00, 2018-06-08 00:00...
dumpDateList(Carbon::now()->subMonth()->getCurrentMonthDays());   // 2018-06-01 00:00:00, 2018-06-02 00:00:00, 2018-06-03 00:00:00, 2018-06-04 00:00:00, 2018-06-05 00:00...
```
  Credit:   [ meteguerlek ][3]  (  [ #1191 ][4] ). 
 
```php
Carbon::macro('toAtomStringWithNoTimezone', function ($self = null) {
    // compatibility chunk
    if (!isset($self) && isset($this)) {
        $self = $this;
    }

    return $self->format('Y-m-d\TH:i:s');
});
echo Carbon::parse('2021-06-16 20:08:34')->toAtomStringWithNoTimezone(); // 2021-06-16T20:08:34


```
 
```php
 Credit: afrojuju1 (#1063). 
```
 
```php
Carbon::macro('easterDate', function ($year) {    
    return Carbon::createMidnightDate($year, 3, 21)->addDays(easter_days($year));
});
    echo Carbon::easterDate(2015)->format('d/m'); // 05/04
    echo Carbon::easterDate(2016)->format('d/m'); // 27/03
    echo Carbon::easterDate(2017)->format('d/m'); // 16/04
    echo Carbon::easterDate(2018)->format('d/m'); // 01/04
    echo Carbon::easterDate(2019)->format('d/m'); // 21/04
```
  Credit:   [ andreisena ][5] ,   [ 36864 ][6]  (  [ #1052 ][7] ). 
 
查看cmixin/工作日以获得更完整的假日处理程序。
 
```php
Carbon::macro('range', function ($startDate, $endDate) {
    return new DatePeriod($startDate, new DateInterval('P1D'), $endDate);
});
foreach (Carbon::range(Carbon::createMidnightDate(2019, 3, 28), Carbon::createMidnightDate(2019, 4, 3)) as $date) {
    echo "$date\n";
}
/*
2019-03-28 00:00:00
2019-03-29 00:00:00
2019-03-30 00:00:00
2019-03-31 00:00:00
2019-04-01 00:00:00
2019-04-02 00:00:00
*/
```
  Credit:   [ reinink ][8]  (  [ #132 ][9] ). 
 
```php
class UserTimezoneCarbonMixin
{
    public $userTimeZone;

    /**
     * Set user timezone, will be used before format function to apply current user timezone
     *
     * @param $timezone
     */
    public function setUserTimezone()
    {
        $mixin = $this;

        return function ($timezone) use ($mixin) {
            $mixin->userTimeZone = $timezone;
        };
    }

    /**
     * Returns date formatted according to given format.
     *
     * @param string $format
     *
     * @return string
     *
     * @link http://php.net/manual/en/datetime.format.php
     */
    public function tzFormat()
    {
        $mixin = $this;

        return function ($format, $self = null) use ($mixin) {
            // compatibility chunk
            if (!isset($self) && isset($this)) {
                $self = $this;
            }

            if (!is_null($mixin->userTimeZone)) {
                $self->timezone($mixin->userTimeZone);
            }

            return $self->format($format);
        };
    }
}

Carbon::mixin(new UserTimezoneCarbonMixin());

Carbon::setUserTimezone('Europe/Berlin');
echo Carbon::createFromTime(12, 0, 0, 'UTC')->tzFormat('H:i'); // 14:00
echo Carbon::createFromTime(15, 0, 0, 'UTC')->tzFormat('H:i'); // 17:00
Carbon::setUserTimezone('America/Toronto');
echo Carbon::createFromTime(12, 0, 0, 'UTC')->tzFormat('H:i'); // 08:00
echo Carbon::createFromTime(15, 0, 0, 'UTC')->tzFormat('H:i'); // 11:00
```
  Credit:   [ thiagocordeiro ][10]  (  [ #927 ][11] ). 
 
 
* ## CarbonInterval 
  
 
 
CarbonInterval类继承了PHP DateInterval类。
 
```php
<?php
class CarbonInterval extends \DateInterval
{    
    // code here
}
```
 
你可以通过以下方式创建实例
 
```php
echo CarbonInterval::year();                           // 1 year
echo CarbonInterval::months(3);                        // 3 months
echo CarbonInterval::days(3)->seconds(32);             // 3 days 32 seconds
echo CarbonInterval::weeks(3);                         // 3 weeks
echo CarbonInterval::days(23);                         // 3 weeks 2 days
echo CarbonInterval::create(2, 0, 5, 1, 1, 2, 7);      // 2 years 5 weeks 1 day 1 hour 2 minutes 7 seconds
```
 
如果您发现自己从另一个库继承了\DateInterval实例，不要害怕!您可以通过一个友好的instance()函数创建一个CarbonInterval实例。
 
```php
$di = new \DateInterval('P1Y2M'); // <== instance from another API
$ci = CarbonInterval::instance($di);
echo get_class($ci);                                   // 'Carbon\CarbonInterval'
echo $ci;                                              // 1 year 2 months
```
 
其他的帮助程序，但是要注意实现提供了帮助程序来处理几周，但是只节省了几天。数周是根据当前实例的总天数计算的。
 
```php
echo CarbonInterval::year()->years;                    // 1
echo CarbonInterval::year()->dayz;                     // 0
echo CarbonInterval::days(24)->dayz;                   // 24
echo CarbonInterval::days(24)->daysExcludeWeeks;       // 3
echo CarbonInterval::weeks(3)->days(14)->weeks;        // 2  <-- days setter overwrites the current value
echo CarbonInterval::weeks(3)->weeks;                  // 3
echo CarbonInterval::minutes(3)->weeksAndDays(2, 5);   // 2 weeks 5 days 3 minutes
```
 
CarbonInterval扩展DateInterval，您可以使用ISO-8601的持续时间格式创建这两种格式:
 
```php
$ci = CarbonInterval::create('P1Y2M3D');
$ci = new CarbonInterval('PT0S');
```
 
借助fromString()方法，可以从友好的字符串创建Carbon intervals。
 
```php
CarbonInterval::fromString('2 minutes 15 seconds');

CarbonInterval::fromString('2m 15s'); // or abbreviated
```
 
注意这个月缩写为“mo”以区别于分钟和整个语法不区分大小写。
 
它还有一个方便的for human()，它被映射为__toString()实现，用于为人类打印间隔。
 
```php
CarbonInterval::setLocale('fr');
echo CarbonInterval::create(2, 1)->forHumans();        // 2 ans 1 mois
echo CarbonInterval::hour()->seconds(3);               // 1 heure 3 secondes
CarbonInterval::setLocale('en');
```
 
如您所见，您可以使用CarbonInterval::setLocale('fr')更改字符串的语言环境。
 
至于Carbon，您可以使用make方法从其他区间或字符串返回一个新的CarbonInterval实例:
 
```php
$dateInterval = new DateInterval('P2D');
$carbonInterval = CarbonInterval::month();
echo CarbonInterval::make($dateInterval)->forHumans();       // 2 days
echo CarbonInterval::make($carbonInterval)->forHumans();     // 1 month
echo CarbonInterval::make('PT3H')->forHumans();              // 3 hours
echo CarbonInterval::make('1h 15m')->forHumans();            // 1 hour 15 minutes
// Pass true to get short format
echo CarbonInterval::make('1h 15m')->forHumans(true);        // 1h 15m
```
 
本机DateInterval分别添加和相乘，因此:
 
```php
$interval = CarbonInterval::make('7h 55m');
$interval->add(CarbonInterval::make('17h 35m'));
$interval->times(3);
echo $interval->forHumans(); // 72 hours 270 minutes
```
 
从单位到单位的输入中得到纯计算。将分钟级联成小时、小时级联成天等。使用级联方法:
 
```php
echo $interval->forHumans();             // 72 hours 270 minutes
echo $interval->cascade()->forHumans();  // 3 days 4 hours 30 minutes
```
 
默认的因素有:
 
1分钟= 60秒
 
1小时=60分钟
 
1天=24小时
 
1周= 7天
 
1个月= 4周
 
1年= 12个月
 
CarbonIntervals 没有上下文，所以它们不能更精确(没有DST、没有闰年、没有实际的月长或年长)。但是你可以完全定制这些因素。例如处理工作时间日志:
 
```php
$cascades = CarbonInterval::getCascadeFactors(); // save initial factors

CarbonInterval::setCascadeFactors(array(
    'minute' => array(60, 'seconds'),
    'hour' => array(60, 'minutes'),
    'day' => array(8, 'hours'),
    'week' => array(5, 'days'),
    // in this example the cascade won't go farther than week unit
));

echo CarbonInterval::fromString('20h')->cascade()->forHumans();              // 2 days 4 hours
echo CarbonInterval::fromString('10d')->cascade()->forHumans();              // 2 weeks
echo CarbonInterval::fromString('3w 18d 53h 159m')->cascade()->forHumans();  // 7 weeks 4 days 7 hours 39 minutes

// You can see currently set factors with getFactor:
echo CarbonInterval::getFactor('minutes', /* per */ 'hour');                 // 60
echo CarbonInterval::getFactor('days', 'week');                              // 5

// And common factors can be get with short-cut methods:
echo CarbonInterval::getDaysPerWeek();                                       // 5
echo CarbonInterval::getHoursPerDay();                                       // 8
echo CarbonInterval::getMinutesPerHours();                                   // 60
echo CarbonInterval::getSecondsPerMinutes();                                 // 60

CarbonInterval::setCascadeFactors($cascades); // restore original factors
```
 
是否可能将间隔转换为给定的单元(使用提供的级联因子)。
 
```php
echo CarbonInterval::days(3)->hours(5)->total('hours');    // 77
echo CarbonInterval::days(3)->hours(5)->totalHours;        // 77
echo CarbonInterval::months(6)->totalWeeks;                // 24
echo CarbonInterval::year()->totalDays;                    // 336
```
 
您还可以使用spec()获得inverval的ISO 8601规范
 
```php
echo CarbonInterval::days(3)->hours(5)->spec(); // P3DT5H
```
 
也可以从DateInterval对象获取它，因为它是静态助手:
 
```php
echo CarbonInterval::getDateIntervalSpec(new DateInterval('P3DT6M10S')); // P3DT6M10S
```
 
使用compare()和comparedateinterval()方法可以对日期间隔列表进行排序:
 
```php
$halfDay = CarbonInterval::hours(12);
$oneDay = CarbonInterval::day();
$twoDay = CarbonInterval::days(2);

echo CarbonInterval::compareDateIntervals($oneDay, $oneDay);   // 0
echo $oneDay->compare($oneDay);                                // 0
echo CarbonInterval::compareDateIntervals($oneDay, $halfDay);  // 1
echo $oneDay->compare($halfDay);                               // 1
echo CarbonInterval::compareDateIntervals($oneDay, $twoDay);   // -1
echo $oneDay->compare($twoDay);                                // -1

$list = array($twoDay, $halfDay, $oneDay);
usort($list, array('Carbon\CarbonInterval', 'compareDateIntervals'));

echo implode(', ', $list);                                     // 12 hours, 1 day, 2 days
```
 
最后，通过使用互补参数调用toPeriod()，可以将一个CarbonInterval实例转换为一个CarbonPeriod实例。
 
我听到你问什么是CarbonPeriod 实例。哦!完美过渡到下一章。
 
 
* ## CarbonPeriod 
  
 
 
CarbonPeriod是一个友好的DatePeriod版本，具有许多快捷方式。
 
```php
// Create a new instance:
$period = new CarbonPeriod('2018-04-21', '3 days', '2018-04-27');
// Use static constructor:
$period = CarbonPeriod::create('2018-04-21', '3 days', '2018-04-27');
// Use the fluent setters:
$period = CarbonPeriod::since('2018-04-21')->days(3)->until('2018-04-27');
// Start from a CarbonInterval:
$period = CarbonInterval::days(3)->toPeriod('2018-04-21', '2018-04-27');
```
 
CarbonPeriod可以通过多种方式构建:
 
开始日期、结束日期和可选间隔(默认为1天)，
 
起始日期，递归次数和可选区间，
 
ISO 8601间隔规范。
 
日期可以是DateTime/Carbon实例，绝对字符串如“2007-10-15 15:00”或相对字符串，例如“next monday”。Interval可以作为DateInterval/CarbonInterval实例、ISO 8601的Interval规范(如“P4D”)或人类可读字符串(如“4 days”)给出。
 
默认构造函数和create()方法在参数类型和顺序方面都很容易理解，所以如果您想要更精确，建议使用fluent语法。另一方面，您可以将动态值数组传递给createFromArray()，它将使用给定的数组作为参数列表构造一个新实例。
 
CarbonPeriod实现迭代器接口。它意味着它可以直接传递给foreach循环:
 
```php
$period = CarbonPeriod::create('2018-04-21', '3 days', '2018-04-27');
foreach ($period as $key => $date) {
    if ($key) {
        echo ', ';
    }
    echo $date->format('m-d');
}
// 04-21, 04-24, 04-27

// Here is what happens under the hood:
$period->rewind(); // restart the iteration
while ($period->valid()) { // check if current item is valid
    if ($period->key()) { // echo comma if current key is greater than 0
        echo ', ';
    }
    echo $period->current()->format('m-d'); // echo current date
    $period->next(); // move to the next item
}
// 04-21, 04-24, 04-27
```
 
参数可以在迭代过程中进行修改:
 
```php
$period = CarbonPeriod::create('2018-04-29', 7);
$dates = array();
foreach ($period as $key => $date) {
    if ($key === 3) {
        $period->invert()->start($date); // invert() is an alias for invertDateInterval()
    }
    $dates[] = $date->format('m-d');
}

echo implode(', ', $dates); // 04-29, 04-30, 05-01, 05-02, 05-01, 04-30, 04-29
```
 
和DatePeriod一样，CarbonPeriod也支持ISO 8601时间间隔规范。
 
请注意，本机日期周期将递归处理为多次重复间隔。因此，在排除开始日期时，它将减少一个结果。CarbonPeriod的自定义过滤器的引入使得知道结果的数量变得更加困难。由于这个原因，我们稍微改变了实现，递归被视为返回日期的总体限制。
 
```php
// Possible options are: CarbonPeriod::EXCLUDE_START_DATE | CarbonPeriod::EXCLUDE_END_DATE
// Default value is 0 which will have the same effect as when no options are given.
$period = CarbonPeriod::createFromIso('R4/2012-07-01T00:00:00Z/P7D', CarbonPeriod::EXCLUDE_START_DATE);
$dates = array();
foreach ($period as $date) {
    $dates[] = $date->format('m-d');
}

echo implode(', ', $dates); // 07-08, 07-15, 07-22, 07-29
```
 
您可以从不同的getter中检索数据:
 
```php
$period = CarbonPeriod::create('2010-05-06', '2010-05-25', CarbonPeriod::EXCLUDE_START_DATE);

$exclude = $period->getOptions() & CarbonPeriod::EXCLUDE_START_DATE;

echo $period->getStartDate();            // 2010-05-06 00:00:00
echo $period->getEndDate();              // 2010-05-25 00:00:00
echo $period->getDateInterval();         // 1 day
echo $exclude ? 'exclude' : 'include';   // exclude

var_dump($period->isStartExcluded());    // bool(true)
var_dump($period->isEndExcluded());      // bool(false)

echo $period->toString();                // Every 1 day from 2010-05-06 to 2010-05-25
echo $period; /*implicit toString*/      // Every 1 day from 2010-05-06 to 2010-05-25
```
 
附加的getter允许您以数组的形式访问结果:
 
```php
$period = CarbonPeriod::create('2010-05-11', '2010-05-13');

echo $period->count();                   // 3, equivalent to count($period)
echo implode(', ', $period->toArray());  // 2010-05-11 00:00:00, 2010-05-12 00:00:00, 2010-05-13 00:00:00
echo $period->first();                   // 2010-05-11 00:00:00
echo $period->last();                    // 2010-05-13 00:00:00
```
 
注意，如果您打算使用上述函数，将toArray()调用的结果存储为变量并使用它是一个好主意，因为每个调用在内部执行一个完整的迭代。
 
想要更改参数，可以使用setter方法:
 
```php
$period = CarbonPeriod::create('2010-05-01', '2010-05-14', CarbonPeriod::EXCLUDE_END_DATE);

$period->setStartDate('2010-05-11');
echo implode(', ', $period->toArray());  // 2010-05-11 00:00:00, 2010-05-12 00:00:00, 2010-05-13 00:00:00

// Second argument can be optionally used to exclude the date from the results.
$period->setStartDate('2010-05-11', false);
$period->setEndDate('2010-05-14', true);
echo implode(', ', $period->toArray());  // 2010-05-12 00:00:00, 2010-05-13 00:00:00, 2010-05-14 00:00:00

$period->setRecurrences(2);
echo implode(', ', $period->toArray());  // 2010-05-12 00:00:00, 2010-05-13 00:00:00

$period->setDateInterval('PT12H');
echo implode(', ', $period->toArray());  // 2010-05-11 12:00:00, 2010-05-12 00:00:00
```
 
您可以使用setOptions()更改选项以替换所有选项，但也可以分别更改:
 
```php
$period = CarbonPeriod::create('2010-05-06', '2010-05-25');

var_dump($period->isStartExcluded());    // bool(false)
var_dump($period->isEndExcluded());      // bool(false)

$period->toggleOptions(CarbonPeriod::EXCLUDE_START_DATE, true); // true, false or nothing to invert the option
var_dump($period->isStartExcluded());    // bool(true)
var_dump($period->isEndExcluded());      // bool(false) (unchanged)

$period->excludeEndDate();               // specify false to include, true or omit to exclude
var_dump($period->isStartExcluded());    // bool(true) (unchanged)
var_dump($period->isEndExcluded());      // bool(true)

$period->excludeStartDate(false);        // specify false to include, true or omit to exclude
var_dump($period->isStartExcluded());    // bool(false)
var_dump($period->isEndExcluded());      // bool(true)
```
 
如前所述，根据ISO 8601规范，递归是重复间隔的数倍。因此，本机DatePeriod将根据开始日期的排除而改变返回日期的数量。与此同时，CarbonPeriod在输入和允许自定义过滤器方面更加宽容，将递归作为返回日期的总体限制:
 
```php
$period = CarbonPeriod::createFromIso('R4/2012-07-01T00:00:00Z/P7D');
$days = array();
foreach ($period as $date) {
    $days[] = $date->format('d');
}

echo $period->getRecurrences();          // 4
echo implode(', ', $days);               // 01, 08, 15, 22

$days = array();
$period->setRecurrences(3)->excludeStartDate();
foreach ($period as $date) {
    $days[] = $date->format('d');
}

echo $period->getRecurrences();          // 3
echo implode(', ', $days);               // 08, 15, 22

$days = array();
$period = CarbonPeriod::recurrences(3)->sinceNow();
foreach ($period as $date) {
    $days[] = $date->format('Y-m-d');
}

echo implode(', ', $days);               // 2018-07-05, 2018-07-06, 2018-07-07
```
 
DatePeriod返回的日期可以很容易地过滤。例如，过滤器可以用于跳过某些日期或只在工作日或周末迭代。筛选函数应该返回true以接受日期，返回false以跳过日期，但继续搜索或CarbonPeriod::END_ITERATION以结束迭代。
 
```php
$period = CarbonPeriod::between('2000-01-01', '2000-01-15');
$weekendFilter = function ($date) {
    return $date->isWeekend();
};
$period->filter($weekendFilter);

$days = array();
foreach ($period as $date) {
    $days[] = $date->format('m-d');
}
echo implode(', ', $days);                         // 01-01, 01-02, 01-08, 01-09, 01-15
```
 
您还可以跳过循环中的一个或多个值。
 
```php
$period = CarbonPeriod::between('2000-01-01', '2000-01-10');
$days = array();
foreach ($period as $date) {
    $day = $date->format('m-d');
    $days[] = $day;
    if ($day === '01-04') {
        $period->skip(3);
    }
}
echo implode(', ', $days);                         // 01-01, 01-02, 01-03, 01-04, 01-08, 01-09, 01-10
```
 
getFilters()允许您在一个时间段内检索所有存储的过滤器。但是要注意递归限制和结束日期将出现在返回的数组中，因为它们作为过滤器存储在内部。
 
```php
$period = CarbonPeriod::end('2000-01-01')->recurrences(3);
var_export($period->getFilters());
/*
array (
  0 => 
  array (
    0 => 'Carbon\\CarbonPeriod::filterEndDate',
    1 => NULL,
  ),
  1 => 
  array (
    0 => 'Carbon\\CarbonPeriod::filterRecurrences',
    1 => NULL,
  ),
)
*/
```
 
过滤器存储在堆栈中，可以使用一组特殊的方法进行管理:
 
```php
$period = CarbonPeriod::between('2000-01-01', '2000-01-15');
$weekendFilter = function ($date) {
    return $date->isWeekend();
};

var_dump($period->hasFilter($weekendFilter));      // bool(false)
$period->addFilter($weekendFilter);
var_dump($period->hasFilter($weekendFilter));      // bool(true)
$period->removeFilter($weekendFilter);
var_dump($period->hasFilter($weekendFilter));      // bool(false)

// To avoid storing filters as variables you can name your filters:
$period->prependFilter(function ($date) {
    return $date->isWeekend();
}, 'weekend');

var_dump($period->hasFilter('weekend'));           // bool(true)
$period->removeFilter('weekend');
var_dump($period->hasFilter('weekend'));           // bool(false)
```
 
添加过滤器的顺序会对性能和结果产生影响，因此您可以使用addFilter()在堆栈末尾添加过滤器;您可以使用prependFilter()在开始时添加一个。甚至可以使用setfilter()替换所有的过滤器。请注意，您必须保持堆栈的正确格式，并记住关于递归限制和结束日期的内部过滤器。或者，您可以使用resetFilters()方法，然后逐个添加新的过滤器。
 
例如，当您添加一个限制尝试日期数量的自定义过滤器时，如果您在工作日过滤器之前或之后添加它，那么结果将是不同的。
 
```php
// Note that you can pass a name of any Carbon method starting with "is", including macros
$period = CarbonPeriod::between('2018-05-03', '2018-05-25')->filter('isWeekday');

$attempts = 0;
$attemptsFilter = function () use (&$attempts) {
    return ++$attempts <= 5 ? true : CarbonPeriod::END_ITERATION;
};

$period->prependFilter($attemptsFilter, 'attempts');
$days = array();
foreach ($period as $date) {
    $days[] = $date->format('m-d');
}
echo implode(', ', $days);                         // 05-03, 05-04, 05-07

$attempts = 0;

$period->removeFilter($attemptsFilter)->addFilter($attemptsFilter, 'attempts');
$days = array();
foreach ($period as $date) {
    $days[] = $date->format('m-d');
}
echo implode(', ', $days);                         // 05-03, 05-04, 05-07, 05-08, 05-09
```
 
注意，内置的递归过滤器不是这样工作的。相反，它基于当前键，每个条目只增加一次，无论在找到有效日期之前需要检查多少个日期。如果您将它放在堆栈的开头或末尾，那么这个技巧将使它的工作方式相同。
 
为了简化CarbonPeriod的构建，添加了一些别名:
 
```php
// "start", "since", "sinceNow":
CarbonPeriod::start('2017-03-10') == CarbonPeriod::create()->setStartDate('2017-03-10');
// Same with optional boolean argument $inclusive to change the option about include/exclude start date:
CarbonPeriod::start('2017-03-10', true) == CarbonPeriod::create()->setStartDate('2017-03-10', true);
// "end", "until", "untilNow":
CarbonPeriod::end('2017-03-20') == CarbonPeriod::create()->setEndDate('2017-03-20');
// Same with optional boolean argument $inclusive to change the option about include/exclude end date:
CarbonPeriod::end('2017-03-20', true) == CarbonPeriod::create()->setEndDate('2017-03-20', true);
// "dates", "between":
CarbonPeriod::dates(..., ...) == CarbonPeriod::create()->setDates(..., ...);
// "recurrences", "times":
CarbonPeriod::recurrences(5) == CarbonPeriod::create()->setRecurrences(5);
// "options":
CarbonPeriod::options(...) == CarbonPeriod::create()->setOptions(...);
// "toggle":
CarbonPeriod::toggle(..., true) == CarbonPeriod::create()->toggleOptions(..., true);
// "filter", "push":
CarbonPeriod::filter(...) == CarbonPeriod::create()->addFilter(...);
// "prepend":
CarbonPeriod::prepend(...) == CarbonPeriod::create()->prependFilter(...);
// "filters":
CarbonPeriod::filters(...) == CarbonPeriod::create()->setFilters(...);
// "interval", "each", "every", "step", "stepBy":
CarbonPeriod::interval(...) == CarbonPeriod::create()->setDateInterval(...);
// "invert":
CarbonPeriod::invert() == CarbonPeriod::create()->invertDateInterval();
// "year", "months", "month", "weeks", "week", "days", "dayz", "day",
// "hours", "hour", "minutes", "minute", "seconds", "second":
CarbonPeriod::hours(5) == CarbonPeriod::create()->setDateInterval(new CarbonInterval::hours(5));
```
 
可以很容易地将CarbonPeriod转换为人类可读的字符串和ISO 8601规范:
 
```php
$period = CarbonPeriod::create('2000-01-01 12:00', '3 days 12 hours', '2000-01-15 12:00');
echo $period->toString();            // Every 3 days 12 hours from 2000-01-01 12:00:00 to 2000-01-15 12:00:00
echo $period->toIso8601String();     // 2000-01-01T12:00:00-05:00/P3DT12H/2000-01-15T12:00:00-05:00
```
 
​
  英文原文：  https://carbon.nesbot.com/docs/#api-interval 
  翻译有问题的地方还请斧正  ~ 
 
[3]: https://github.com/meteguerlek
[4]: https://github.com/briannesbitt/Carbon/pull/1191
[5]: https://github.com/andreisena
[6]: https://github.com/36864
[7]: https://github.com/briannesbitt/Carbon/pull/1052
[8]: https://github.com/reinink
[9]: https://github.com/briannesbitt/Carbon/pull/132
[10]: https://github.com/thiagocordeiro
[11]: https://github.com/briannesbitt/Carbon/pull/927
[0]: ./img/vQzmay2.png 
[1]: ./img/Ybemm22.png 
[2]: ./img/nYfuYf3.png 
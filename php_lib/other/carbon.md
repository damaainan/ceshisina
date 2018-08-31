### A simple PHP API extension for DateTime.

Carbon 使处理日期和时间更加简单。

* 处理时区
* 轻松获取时间
* 日期加减
* 使用英语短语处理时间
* ...

## 安装

* 使用 composer 安装，composer require nesbot/carbon。
* 在 composer.json 里添加
```json
    {
       "require": {
          "nesbot/carbon": "~1.18"
       }
    }
```
* 执行 composer install
* 没有使用 composer
```php
    <?php
    require 'path/to/Carbon.php';
    
    use Carbon\Carbon;
    
    printf("Now: %s", Carbon::now());
```
## 使用

> 获取时间

    echo Carbon::now();       //当前时间
    echo Carbon::yesterday(); //昨天
    echo Carbon::tomorrow();  //明天

以上的时间均是对象类型（使用 var_dump()）

object(Carbon\Carbon)#78 (3) { ["date"]=> string(26) "2017-11-15 00:00:00.000000" ["timezone_type"]=> int(3) ["timezone"]=> string(3) "PRC" }需要使用 toDateTimeString() 将其转为字符串格式

var_dump(Carbon::now()->toDateTimeString());使用一些短语获取时间

    $knownDate = Carbon::create(2001, 5, 21, 12);          // create testing date
    Carbon::setTestNow($knownDate);                        // set the mock
    echo new Carbon('tomorrow');                           // 2001-05-22 00:00:00  ... notice the time !
    echo new Carbon('yesterday');                          // 2001-05-20 00:00:00
    echo new Carbon('next wednesday');                     // 2001-05-23 00:00:00
    echo new Carbon('last friday');                        // 2001-05-18 00:00:00
    echo new Carbon('this thursday');                      // 2001-05-24 00:00:00

当使用 next(), previous() and modify() 等词语会将时分秒设置为 00:00:00

想要获取一个已知时间里的一个属性，可以这么做

    $dt = Carbon::parse('2012-9-5 23:26:11.123789');
    
    var_dump($dt->year);                                         // int(2012)
    var_dump($dt->month);                                        // int(9)
    var_dump($dt->day);                                          // int(5)
    var_dump($dt->hour);                                         // int(23)
    var_dump($dt->minute);                                       // int(26)
    var_dump($dt->second);                                       // int(11)
    var_dump($dt->micro);                                        // int(123789)
    var_dump($dt->dayOfWeek);                                    // int(3)
    var_dump($dt->dayOfYear);                                    // int(248)
    var_dump($dt->weekOfMonth);                                  // int(1)
    var_dump($dt->weekOfYear);                                   // int(36)
    var_dump($dt->daysInMonth);                                  // int(30)
    var_dump($dt->timestamp);                                    // int(1346901971)

> 常用时间格式

    $dt = Carbon::create(1975, 12, 25, 14, 15, 16);
    
    echo $dt->toDateString();                          // 1975-12-25
    echo $dt->toFormattedDateString();                 // Dec 25, 1975
    echo $dt->toTimeString();                          // 14:15:16
    echo $dt->toDateTimeString();                      // 1975-12-25 14:15:16
    echo $dt->toDayDateTimeString();                   // Thu, Dec 25, 1975 2:15 PM
    echo $dt->format('l jS \\of F Y h:i:s A');         // Thursday 25th of December 1975 02:15:16 PM

    $dt = Carbon::now();
    
    echo $dt->toAtomString();      // 1975-12-25T14:15:16-05:00
    echo $dt->toCookieString();    // Thursday, 25-Dec-1975 14:15:16 EST
    echo $dt->toIso8601String();   // 1975-12-25T14:15:16-0500
    echo $dt->toRfc822String();    // Thu, 25 Dec 75 14:15:16 -0500
    echo $dt->toRfc850String();    // Thursday, 25-Dec-75 14:15:16 EST
    echo $dt->toRfc1036String();   // Thu, 25 Dec 75 14:15:16 -0500
    echo $dt->toRfc1123String();   // Thu, 25 Dec 1975 14:15:16 -0500
    echo $dt->toRfc2822String();   // Thu, 25 Dec 1975 14:15:16 -0500
    echo $dt->toRfc3339String();   // 1975-12-25T14:15:16-05:00
    echo $dt->toRssString();       // Thu, 25 Dec 1975 14:15:16 -0500
    echo $dt->toW3cString();       // 1975-12-25T14:15:16-05:00

> 时间比较

    $first = Carbon::create(2012, 9, 5, 23, 26, 11);
    $second = Carbon::create(2012, 9, 5, 20, 26, 11);
    
    var_dump($first->eq($second));  //bool(false) 
    var_dump($first->ne($second));  //bool(true)
    var_dump($first->gt($second));  //bool(true)
    var_dump($first->gte($second)); //bool(true)
    var_dump($first->lte($second)); //bool(false)

还可以这样

    $dt1 = Carbon::create(2012, 1, 1, 0, 0, 0);
    $dt2 = Carbon::create(2014, 1, 30, 0, 0, 0);
    echo $dt1->min($dt2);                              // 2012-01-01 00:00:00
    
    $dt1 = Carbon::create(2012, 1, 1, 0, 0, 0);
    $dt2 = Carbon::create(2014, 1, 30, 0, 0, 0);
    echo $dt1->max($dt2);                              // 2014-01-30 00:00:00

> 时间加减

    $dt = Carbon::create(2012, 1, 31, 0);
    
    echo $dt->toDateTimeString();            // 2012-01-31 00:00:00
    
    echo $dt->addYears(5);                   // 2017-01-31 00:00:00
    echo $dt->addYear();                     // 2018-01-31 00:00:00
    echo $dt->subYear();                     // 2017-01-31 00:00:00
    echo $dt->subYears(5);                   // 2012-01-31 00:00:00
    echo $dt->addDays(29);                   // 2012-03-03 00:00:00
    echo $dt->addDay();                      // 2012-03-04 00:00:00
    echo $dt->subDay();                      // 2012-03-03 00:00:00
    echo $dt->subDays(29);                   // 2012-02-03 00:00:00
    echo $dt->addSeconds(61);                // 2012-02-03 00:01:01
    echo $dt->addSecond();                   // 2012-02-03 00:01:02
    echo $dt->subSecond();                   // 2012-02-03 00:01:01
    echo $dt->subSeconds(61);                // 2012-02-03 00:00:00

> 多久之前，时间差异

    $dt     = Carbon::now();
    $past   = $dt->subMonth();
    $future = $dt->addMonth();
    echo $dt->subDays(10)->diffForHumans();     // 10 days ago
    echo $dt->diffForHumans($past);             // 1 month ago
    echo $dt->diffForHumans($future);           // 1 month before

> 一些时间修改符

在使用这些修饰符的时候，部分时分秒会是 23:59:59,

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
    echo $dt->startOfDecade();                         // 2010-01-01 00:00:00
    $dt = Carbon::create(2012, 1, 31, 12, 0, 0);
    echo $dt->endOfDecade();                           // 2019-12-31 23:59:59

更多使用方式，参考[官网][0]

[0]: http://carbon.nesbot.com/
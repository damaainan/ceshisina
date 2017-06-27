### 适配器模式
适配器模式，正如其名，是为了做接口兼容用的。在阎宏博士的《JAVA与模式》一书中开头是这样描述适配器（Adapter）模式的：
>适配器模式把一个类的接口变换成客户端所期待的另一种接口，从而使原本因接口不匹配而无法在一起工作的两个类能够在一起工作。


现在有2种场景：
1、服务接口提供了2种方法，但目标客户端还需要额外一种方法才能正常运行；
2、接口类定义了3种方法，但子类只能实现其中1种方法，可是不实现又不能正常工作。

这就用到了适配器模式。适配器就是在中间做兼容用的。

场景1：适配器只需要实现源服务接口未实现的类即可，其它的直接调用源服务接口。

![](http://images2015.cnblogs.com/blog/663847/201706/663847-20170625142901007-1830337605.png)

示例代码需要有2个类：

源服务接口类：
``` php
<?php
namespace Yjc\Adapter;

class SimpleBook
{
    private $author;
    private $title;

    function __construct($author_in, $title_in) {
        $this->author = $author_in;
        $this->title  = $title_in;
    }

    function getAuthor() {
        return $this->author;
    }

    function getTitle() {
        return $this->title;
    }
}
```

适配器类：
``` php
<?php
namespace Yjc\Adapter;

class BookAdapter
{
    private $book;

    public function __construct(SimpleBook $book_in) {
        $this->book = $book_in;
    }

    //该方法是客户端需要的，适配器实现
    public function getAuthorAndTitle() {
        return $this->book->getTitle().' by '.$this->book->getAuthor();
    }

    //该方法源类已实现
    function getAuthor() {
        return $this->book->getAuthor();
    }

    //该方法源类已实现
    function getTitle() {
        return $this->book->getTitle();
    }
}
```

测试：
``` php
$book = new SimpleBook('yjc', 'PHP设计模式');
$target = new BookAdapter($book);
echo $target->getAuthorAndTitle();//调用了SimpleBook没有实现的类
$target->getTitle();//调用了SimpleBook已实现的类
```

场景2：既然子类无法实现接口所定义，那么由适配器去实现一些空的方法即可。

![](http://images2015.cnblogs.com/blog/663847/201706/663847-20170625143058054-1006410449.png)

以『MP4播放器』为例：假设MP4播放器正常需要实现听音乐、播放视频、听收音机，可实际情况有的MP4播放器不支持听收音机：
``` php
namespace Yjc\Adapter;

interface IMp4Plater
{
    public function playMusic();
    public function playVideo();
    public function receiveRadio();
}
```
我们可以在每个子类里面将不支持的方法返回空，但繁琐，还不如使用适配器做这件事情：
``` php
namespace Yjc\Adapter;

class Mp4PlayerAdapter implements IMp4Plater
{
    public function playMusic(){}
    public function playVideo(){}
    public function receiveRadio(){}
}
```
然后实现一个播放器，但不支持收音机：
``` php
namespace Yjc\Adapter;

class SimpleMp4Player extends Mp4PlayerAdapter
{
    public function playMusic(){
        echo '播放音乐';
    }

    public function playVideo(){
        echo '播放视频';
    }
}
```

测试：
``` php
$player = new SimpleMp4Player();
echo $player->playMusic();
```

适配器模式的优点：

- 更好的复用性
系统需要使用现有的类，而此类的接口不符合系统的需要。那么通过适配器模式就可以让这些功能得到更好的复用。
- 更好的扩展性
在实现适配器功能的时候，可以调用自己开发的功能，从而自然地扩展系统的功能。

适配器模式的缺点：

- 过多的使用适配器，会让系统非常零乱，不易整体进行把握。比如，明明看到调用的是A接口，其实内部被适配成了B接口的实现，一个系统如果太多出现这种情况，无异于一场灾难。因此如果不是很有必要，可以不使用适配器，而是直接对系统进行重构。

该小节参考：[《JAVA与模式》之适配器模式](http://www.cnblogs.com/java-my-life/archive/2012/04/13/2442795.html)。
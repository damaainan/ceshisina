## PHP图像处理组件：Intervention/image

来源：[https://www.helloweba.net/php/545.html](https://www.helloweba.net/php/545.html)

时间 2018-04-20 20:39:36


Intervention/image是一个PHP图像处理组件，是基于Imagick和GD，可以用于改变图片大小，剪裁，添加水印等等功能，此外还有图片缓存功能，在动态URL获取图片尺寸的应用非常有效。

[查看演示][0]
    [下载源码][1]

  
  
#### 前置条件

该组件需要满足以下条件才可以正常运行：

  

* PHP >= 5.4
* 需要支持Fileinfo扩展
* GD库 > 2.0 或者 Imagick扩展 >= 6.5.7
    

  
#### 安装

使用 composer 安装:

```
composer require intervention/image
```

  
#### 使用

在使用 Intervention Image 的时候, 你只需要给 ImageManager 传一个数组参数就可以完成 GD 或 Imagick 库之间的互相切换。

```php
// 引入 composer autoload
require 'vendor/autoload.php';

// 导入 Intervention Image Manager Class
use Intervention\Image\ImageManager;

// 通过指定 driver 来创建一个 image manager 实例
$manager = new ImageManager(array('driver' => 'imagick'));

// 最后创建 image 实例
$image = $manager->make('public/foo.jpg')->resize(300, 200);
```

另外你也可以使用 ImageManager 的静态版本, 如下所示:

```php
// 引入 composer autoload
require 'vendor/autoload.php';

// 导入 Intervention Image Manager Class
use Intervention\Image\ImageManagerStatic as Image;

// 通过指定 driver 来创建一个 image manager 实例 (默认使用 gd)
Image::configure(array('driver' => 'imagick'));

// 最后创建 image 实例
$image = Image::make('public/foo.jpg')->resize(300, 200);
```

  
#### 调整尺寸

当上传的图片尺寸不合适时，可以将图片重新调整尺寸。

1.调整图片为固定尺寸300x200像素：

```php
$img = Image::make('public/foo.jpg')

$img->resize(300, 200);
```

仅调整图片宽度为300像素：

```php
$img->resize(300, null);
```

仅调整图片高度为200像素：

```php
$img->resize(null, 200);
```

调整图片宽度为300像素，高度根据宽度等比例缩放：

```php
$img->resize(300, null, function ($constraint) {
    $constraint->aspectRatio();
});
```

调整图片高度为200像素，高度根据高度等比例缩放：

```php
$img->resize(null, 200, function ($constraint) {
    $constraint->aspectRatio();
});
```

  
#### 裁剪图片

使用方法`crop(int $width, int $height, [int $x, int $y])`可以将图片裁剪成合适的尺寸。

```php
$img = Image::make('public/foo.jpg');

$img->crop(100, 100, 25, 25);
```

以上代码将图片从坐标x:25，y:25开始裁剪成100x100像素大小的图片。

  
#### 图片水印

使用方法：`insert(mixed $source, [string $position, [integer $x, integer $y]])`可以给图片添加水印图片，方法中第一个参数是水印图片，第二个参数是水印的位置，支持9个位置，最后两个参数是水印的相对$position参数的位移。

要想给图片加个水印图标，可以参照以下代码：

```php
// 修改指定图片的大小
$img = Image::make('images/avatar.jpg')->resize(200, 200);

// 插入水印, 水印位置在原图片的右下角, 距离下边距 10 像素, 距离右边距 15 像素
$img->insert('images/watermark.png', 'bottom-right', 15, 10);

// 将处理后的图片重新保存到其他路径
$img->save('images/new_avatar.jpg');

/* 上面的逻辑可以使用链式表达式 */
$img = Image::make('images/avatar.jpg')->resize(200, 200)->insert('images/new_avatar.jpg', 'bottom-right', 15, 10);
```

这时你查看新生成的图片new_avatar.jpg的右下角会有水印图标。

  
#### 图片缓存

要想缓存图片，先得安装另外一个组件：imagecache。

```php
composer require intervention/imagecache
```

我们使用方法`cache( Closure $callback, [int $lifetime, [bool $returnObj]] )`，可以实现图片缓存功能。第2个参数`$lifetime`是缓存时间，默认为5，单位分钟。

```php
$img = Image::cache(function($image) {
    $image->make('public/foo.jpg')->resize(300, 200)->greyscale();
}, 10, true);
```

以上代码将图片foo.jpg重置大小为300x200，并且变成灰色，保存在缓冲区，缓存过期时间为10分钟。

  
#### 图片根据URL参数动态处理大小

当你上传一张图片后需要生成多种尺寸的图片，比如常见的头像尺寸就有多个尺寸以满足不同应用展示。那么我们的解决办法有：1.上传时就生成裁剪好多种相应的尺寸，2.根据请求带参数的URL来生成不同尺寸的图片。方法1有局限性，必须先设定尺寸，方法2比较靠谱，根据传递的参数，生成所需尺寸的图片，而且结合图片缓存功能，让生成的图片缓存起来，那么在缓存期限内，多次请求同一个URL是不会重复生成图片的。以下是个简单的示例：

```php
<?php

require 'vendor/autoload.php';

use Intervention\Image\ImageManagerStatic as Image;

$s = isset($_GET['s']) ? $_GET['s'] : 'medium';
switch ($s) {
    case 'small':  //60x60 px
        $imgName = 'public/60x60.jpg';
        $width = 60;
        $height = 60;
        break;

    case 'medium': //150x150
        $imgName = 'public/150x150.jpg';
        $width = 150;
        $height = 150;
        break;

    case 'large': //300x300
        $imgName = 'public/300x300.jpg';
        $width = 300;
        $height = 300;
        break;
    
    default:
        $imgName = 'public/150x150.jpg';
        $width = 150;
        $height = 150;
        break;
}

$img = Image::cache(function($image) use ($imgName, $width, $height) {
    $image->make('public/foo.jpg')->resize($width, $height)->save($imgName);
}, 600); //缓存：600min

echo $imgName;
```

根据传递的参数s，生成不同尺寸的图片，并且缓存600分钟。

更多应用如图片旋转、透明度、模糊设置、图片翻转等请参考项目官网API文档：      [http://image.intervention.io/api/backup][2]
。

  



[0]: https://www.helloweba.net/demo/2018/interImage/
[1]: https://www.helloweba.net/down/545.html
[2]: http://image.intervention.io/api/backup
## PHP-生成二维码（qr-code）

来源：[https://segmentfault.com/a/1190000013188314](https://segmentfault.com/a/1190000013188314)


## 1.为什么要写这篇文章

最近做项目要生成二维码让用户做跳转，搜索了一下发现网上都是用一个叫做`phpqrcode` 的扩展，在`github` 上搜索了一下发现这个项目作者在6年前就已经没有维护了，百度的文章也是千篇一律的你复制我的我复制你的，所以只好在`github` 上看看有没有更好的关于`PHP` 生成二维码的扩展，之后找到了一个项目名称为`qr-code` 的扩展，感觉不错，作者也一直在做维护，使用也是简单方便。所以在这里把这个扩展的安装使用说明一下，方便各位的开发。

`qr-code` 项目的`github` 地址为：[qr-code][5]
## 2.安装 qr-code

这里我们通过`composer` 来安装扩展，`composer` 也算是现在比较火的包管理工具了，如果对`composer` 不太了解的话，可以看下我以前的文章：

[《php-composer的安装与使用方法》][6]

我的环境为 linux，我们键入以下命令来进行该扩展的安装：

```
composer require endroid/qr-code

```


![][0]

当扩展安装完毕后，我们就可以开始下面的操作了。
## 3.生成二维码

首先我们需要在项目中引入`qr-code` 类文件，`composer` 现在基本上是通过`psr-4 "命名空间": "路径"` 的方式进行自动加载，它的位置位于扩展根目录的`composer.json` 文件中。

好了，现在我们引入`qr-code` 类文件，并尝试输出一个简单的二维码。

```php
use Endroid\QrCode\QrCode;

// $content 一般为url地址 当然也可以是文字内容
$content = 'http://www.baidu.com?rand=' . rand(1000, 9999);
$qrCode = new QrCode($content);
// 指定内容类型
header('Content-Type: '.$qrCode->getContentType());
// 输出二维码
echo $qrCode->writeString();

```

好了，当指定了内容类型后，会直接在页面输出二维码


![][1]

那这种直接输出的二维码怎么应用于项目中呢，一般都是直接写在`html` 中的`<img>` 标签中，例如：

```php
<img src="http://localhost:8080/projecttest/qrtest?id=1234"  alt="这是一个二维码" />

```


![][3]

这样，就可以把二维码显示在页面的任意位置了。当然，我们也可以把它存入文件中，生成一个任意格式的图片，比如说：

```php
$qrCode->writeFile(__DIR__ . '/qrcode.png');

```

这样我们就可以根据图片路径在页面上展示二维码了
## 4.简单的示例文件以及常用参数介绍

这里，我贴出一个简单的类处理文件，并介绍一下`qr-code` 常用的一些参数。

类文件：

```php
namespace '命名空间';

use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\LabelAlignment;
use Endroid\QrCode\QrCode;

class QrcodeComponent
{
    protected $_qr;
    protected $_encoding = 'UTF-8';
    protected $_size = 300;
    protected $_logo = false;
    protected $_logo_url = '';
    protected $_logo_size = 80;
    protected $_title = false;
    protected $_title_content = '';
    protected $_generate = 'display'; // display-直接显示 writefile-写入文件
    const MARGIN = 10;
    const WRITE_NAME = 'png';
    const FOREGROUND_COLOR = ['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0];
    const BACKGROUND_COLOR = ['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0];

    public function __construct($config) {
        isset($config['generate']) && $this->_generate = $config['generate'];
        isset($config['encoding']) && $this->_encoding = $config['encoding'];
        isset($config['size']) && $this->_size = $config['size'];
        isset($config['display']) && $this->_size = $config['size'];
        isset($config['logo']) && $this->_logo = $config['logo'];
        isset($config['logo_url']) && $this->_logo_url = $config['logo_url'];
        isset($config['logo_size']) && $this->_logo_size = $config['logo_size'];
        isset($config['title']) && $this->_title = $config['title'];
        isset($config['title_content']) && $this->_title_content = $config['title_content'];
    }

    /**
     * 生成二维码
     * @param $content 需要写入的内容
     * @return array | page input
     */
    public function create($content) {
        $this->_qr = new QrCode($content);
        $this->_qr->setSize($this->_size);
        $this->_qr->setWriterByName(self::WRITE_NAME);
        $this->_qr->setMargin(self::MARGIN);
        $this->_qr->setEncoding($this->_encoding);
        $this->_qr->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH);
        $this->_qr->setForegroundColor(self::FOREGROUND_COLOR);
        $this->_qr->setBackgroundColor(self::BACKGROUND_COLOR);
        if ($this->_title) {
            $this->_qr->setLabel($this->_title_content, 16, '字体地址', LabelAlignment::CENTER);
        }
        if ($this->_logo) {
            $this->_qr->setLogoPath($this->_logo_url);
            $this->_qr->setLogoWidth($this->_logo_size);
            $this->_qr->setRoundBlockSize(true);
        }
        $this->_qr->setValidateResult(false);

        if ($this->_generate == 'display') {
            // 前端调用 例：

![][4]
            header('Content-Type: ' . $this->_qr->getContentType());
            return $this->_qr->writeString();
        } else if ($this->_generate == 'writefile') {
            return $this->_qr->writeString();
        } else {
            return ['success' => false, 'message' => 'the generate type not found', 'data' => ''];
        }
    }

    /**
     * 生成文件
     * @param $file_name 目录文件 例: /tmp
     * @return array
     */
    public function generateImg($file_name) {
        $file_path = $file_name . DS . uniqid() . '.' . self::WRITE_NAME;

        if (!file_exists($file_name)) {
            mkdir($file_name, 0777, true);
        }

        try {
            $this->_qr->writeFile($file_path);
            $data = [
                'url' => $file_path,
                'ext' => self::WRITE_NAME,
            ];
            return ['success' => true, 'message' => 'write qrimg success', 'data' => $data];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage(), 'data' => ''];
        }
    }
}

```

使用方法：

```php
use '命名空间';

$qr_url = 'http://www.baidu.com?id=' . rand(1000, 9999);
$file_name = '/tmp';

// 直接输出
$qr_code = new QrcodeComponent();
$qr_img = qr_code->create($qr_url);
echo $qr_img;

// 生成文件
$config = [
    'generate' => 'writefile',
];
$qr_code = new QrcodeComponent($config);
$qr_img = $qr_code->create($qr_url);
$rs = $qr_code->generateImg($file_name);
print_r($rs);

```

常用参数解释：

`setSize` - 二维码大小 px
`setWriterByName` - 写入文件的后缀名
`setMargin` - 二维码内容相对于整张图片的外边距
`setEncoding` - 编码类型
`setErrorCorrectionLevel` - 容错等级，分为L、M、Q、H四级
`setForegroundColor` - 前景色
`setBackgroundColor` - 背景色
`setLabel` - 二维码标签
`setLogoPath` - 二维码logo路径
`setLogoWidth` - 二维码logo大小 px

[5]: https://github.com/endroid/qr-code
[6]: https://segmentfault.com/a/1190000012020479
[0]: https://segmentfault.com/img/bV3uN4
[1]: https://segmentfault.com/img/bV3uRl
[2]: https://segmentfault.com
[3]: https://segmentfault.com/img/bV3uTm
[4]: https://segmentfault.com
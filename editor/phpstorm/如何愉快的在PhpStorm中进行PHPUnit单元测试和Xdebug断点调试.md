## 如何愉快的在PhpStorm中进行PHPUnit单元测试和Xdebug断点调试？

来源：[https://segmentfault.com/a/1190000016323574](https://segmentfault.com/a/1190000016323574)


## 前言


* 如果你想做个接口测试，但并不想公开内部接口
* 如果你只是想对自己封装的某块代码做个小测试
* 如果你想要编写代码边调试，又不想操作`Postman`或前端的功能来调用`API`
* 本文暂时仅介绍在`Laravel`和`Comoposer Library`项目中如何配置`PHPUnit`


## 参考文档

如何愉快的在PhpStorm中进行PHPUnit单元测试和Xdebug断点调试？[https://segmentfault.com/a/11...][9]
如何愉快的在PhpStorm中进行PHPUnit单元测试和Xdebug断点调试？[https://blog.csdn.net/RobotYa...][10]
——
PHPUnit 手册：[http://www.phpunit.cn/manual/...][11]
phpunit assert断言分类整理 ：[https://www.cnblogs.com/nings...][12]
## 安装
### Xdebug

* 请参考以下文章的`PHP 安装 Xdebug`章节

如何愉快的在PhpStorm中进行Xdebug断点调试：[https://segmentfault.com/a/11...][13]
如何愉快的在PhpStorm中进行Xdebug断点调试：[https://blog.csdn.net/RobotYa...][14]


## 配置
### 配置 PhpStorm 的 PHP CLi


* 选择 File -> Setting

![][0] 
* 搜索`CLI`，左侧选择`PHP`，点击`+`新增一个 PHP 解释器。


* `Windows`

* 配置`php`执行程序
* 点击那个`同步的小图标`，如果看到`successfully`就说明配置有效
* 指定 Xdebug 模块

![][1] 



* `Ubuntu`
* 配置步骤同 Windows，稍后截图


### 配置 PHPUnit

* 选择 File -> Setting，搜索`test`，左侧选择`Test Framework`，点击`+`新增一个`PHPUnit Local`。

![][2]

* Composer Library 项目


* 选择第一项：使用`composer autoloader`导入配置
* 选择你项目下的`vendor/autoload.php`
* 点击那个`同步的小图标`，如果看到`successfully`就说明配置有效


```
    ![配置 composer autoloader](https://img-blog.csdn.net/20180908214108744?watermark/2/text/aHR0cHM6Ly9ibG9nLmNzZG4ubmV0L1JvYm90WWFuZzEyMw==/font/5a6L5L2T/fontsize/400/fill/I0JBQkFCMA==/dissolve/70)
- Laravel 项目
    - 选择第一项：使用 `composer autoloader` 导入配置
    - 选择你项目下的 `bootstrap/autoload.php`（或者选项目下的 `vendor/autoload.php`）
    - 点击那个 `同步的小图标`，如果看到 `successfully` 就说明配置有效
    ![配置 composer autoloader](https://img-blog.csdn.net/20180908233525792?watermark/2/text/aHR0cHM6Ly9ibG9nLmNzZG4ubmV0L1JvYm90WWFuZzEyMw==/font/5a6L5L2T/fontsize/400/fill/I0JBQkFCMA==/dissolve/70)

```
### 配置 phpunit.xml

* 在你的项目根目录下新建`phpunit.xml`文件（但奇怪的是我在`PhpStorm`删除这个文件，也可以执行单元测试，猜测可能是`phpunit.xml`配置文件是可选的）

```xml
<?xml version="1.0" encoding="UTF-8"?>
<!--bootstrap指定启动测试时, 先加载vendor/autoload.php-->
<phpunit backupGlobals="false"
         backupStaticAttributes="false"
         bootstrap="vendor/autoload.php"
         colors="true"
         convertErrorsToExceptions="true"
         convertNoticesToExceptions="true"
         convertWarningsToExceptions="true"
         processIsolation="false"
         stopOnFailure="false">

    <!--testsuite指定测试文件的目录-->
    <testsuite>
        <directory suffix="Test.php">./tests</directory>
    </testsuite>

    <!--filter过滤依赖文件的位置-->
    <filter>
        <whitelist processUncoveredFilesFromWhitelist="true">
            <directory suffix=".php">./src</directory>
        </whitelist>
    </filter>
</phpunit>
```

* 当然这个 phpunit.xml 只是基本配置，还有其他高级配置请自行百度

## 新增测试例


* 在你的项目根目录下新建`tests`目录
* 在`tests`目录下新建`phpunit`测试类，以`Test.php`为后缀

![][3] 

![][4] 
* 编写测试例方法，以`test`开头


```php
<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2018/9/8
 * Time: 23:54
 */

namespace HonorSdk\Tests;

use PHPUnit\Framework\TestCase;

class HelloTest extends TestCase
{
    public function testHello()
    {
        $expect = 'hello world'; //期待结果
        $result = $this->requestApi(); //请求api 或 调用 service 后返回的结果
        $this->assertEquals($expect, $result); //使用断言方法 比较结果值
    }

    //假装请求数据
    private function requestApi()
    {
        echo $date_1 = date('Y-m-d H:i:s');
        echo "<br/>";;
        echo $date_2 = date('Y-m-d H:i:s');
        echo "<br/>";;
        echo $date_3 = date('Y-m-d H:i:s');
        echo "<br/>";;
        echo $date_4 = date('Y-m-d H:i:s');
        echo "<br/>";;
        echo $date_5 = date('Y-m-d H:i:s');
        echo "<br/>";;
        $j = 0;
        for ($i = 0; $i < 10; $i++) {
            $j = $i * 2;
            $i = $i + 2;
            echo $i;
            echo "<br/>";
            echo $j;
            echo "<br/>";
        }
        return 'hello world';
    }
}
```


* 打上断点，然后在要测试的方法名上右键，选择`Debug`这个方法

![][5] 
* 第一次可能会弹出让你选择测试范围的配置界面，这里我们只测试指定方法，所以选择了`Method`

![][6] 
* 测试结果


* 测试例执行 Xdebug 的流程

![][7] 
* 测试例执行成功的返回信息

![][8] 



## 调试快捷键


* `F7`通过当前行，进入下一行，如果该行是方法，则进入方法体
* `F8`通过当前行，进入下一行，如果该行是方法，也直接进入下一行，不进入方法体
* `F9`通过整个流程，全部顺序执行，除非遇到下一个断点


## 要点总结


* 配置`PHP CLI`（`php.exe`和`xdebug.dll`）
* 配置`PHPUnit`（`autoload.php`）
* 配置`phpunit.xml`（可选）
* 新增`测试例`（测试类 和 测试方法）


[9]: https://segmentfault.com/a/1190000016323574
[10]: https://blog.csdn.net/RobotYang123/article/details/82533080
[11]: http://www.phpunit.cn/manual/current/zh_cn/index.html
[12]: https://www.cnblogs.com/ningskyer/articles/5744760.html
[13]: https://segmentfault.com/a/1190000014942730?_ea=4357076
[14]: https://blog.csdn.net/RobotYang123/article/details/80370030
[0]: ./img/1460000016323577.png
[1]: ./img/1460000016323578.png
[2]: ./img/1460000016323579.png
[3]: ./img/1460000016323580.png
[4]: ./img/1460000016323581.png
[5]: ./img/1460000016323582.png
[6]: ./img/1460000016323583.png
[7]: ./img/1460000016323584.png
[8]: ./img/1460000016323585.png
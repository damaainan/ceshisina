## [phpstorm-laravel-phpunit](https://segmentfault.com/a/1190000007432699)

### 一，新建composer.json文件

    {
        "require": {
            "yzalis/identicon": "^1.1",
            "phpunit/phpunit": "5.5.*"
        }
    }

### 二，运行composer install，得到如下目录结构

![][0]

### 三，复制yzalis/phpuit.xml.dist到项目的根目录下，并重命名为phpunit.xml### 四，配置phpstorm的php环境

![][1]

### 五，配置PHPUnit环境

![][2]

### 六，新建RunTest.php文件

    <?php
    
    namespace demo1;
    use Identicon\Identicon;
    use PHPUnit\Framework\TestCase;
    class RunTest extends TestCase{
        
        public function testDemo(){
            $identicon = new Identicon();
            $img = $identicon->getImageData('bar',512);
            file_put_contents('./a.png',$img);
        }
    }

### 七，执行

![][3]

### 八，输出

![][4]

[0]: ./img/bVFlGR.png
[1]: ./img/bVFmuw.png
[2]: ./img/bVFmCq.png
[3]: ./img/bVFlJf.png
[4]: ./img/bVFmCx.png
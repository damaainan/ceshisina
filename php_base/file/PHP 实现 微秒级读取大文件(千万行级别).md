# PHP 实现 微秒级读取大文件(千万行级别)

 时间 2017-12-19 17:57:57  苗启源的部落格

原文[http://www.miaoqiyuan.cn/p/php-bigfile][1]


一同行朋友遇到奇葩的需求，需要在 一个 1000万行级别的 文件，随机读取一行。

几千行很好处理，直接 file(文件名)，通过返回的数组 的索引既可获取。

尝试了 很多方法，超过50M的文件，打开速度都不理想。给朋友提供了 memcached 和 mysql 内存表的思路，因为后期维护比较麻烦，也都放弃了。

在 去 蹲坑时 灵光一闪，既然小文件打开很快，为什么不把数据分到多个文件呢？

最终解决了，为了解耦，直接写成了类。

```php
    <?php
    
    /**
      * 作者：苗启源 <miaoqiyuan.cn>
      * 原理：比如存在数据1000万行
      *       如果( 没有 程序运行时生成的配置文件 )｛
      *           自动把这1000万个行 分割成1000个文件，并记录总条数写入配置文件
      *       ｝否则 {
      *           读取配置文件，获取总行数
      *       }
      *       随机生成 1 到 总行数 的任意一个数字，比如 999万1000
      *       通过 分割规则，可以 计算出 在 文件999 中的 第1000行< file(x)[999] >
      *       直接通过 file 直接读取。
      */
    // 核心类
    class qiyuanBigFile {
    
        public $lineAll = 0;
        public $lineFile = 5000;
    
        public $filePath = './data/';
        public $filePrefix = 'qiyuan_big_file_';
        public $fileSuffix = '.txt';
    
        // 分割文件
        public function setup($input_file) {
            $fp = fopen($input_file, 'r');
            $file_no = 0; //首个文件的名称
            $line_no = 0; //第几行
            $file_texts = ''; //文件
            while (($line = fgets($fp)) !== false) {
                $file_texts .= $line;
                if ($line_no === $this->lineFile) {
                    file_put_contents($this->getFileName($file_no, $this->fileSuffix), $file_texts);
                    $file_no++;
                    $line_no = 0;
                    $file_texts += '';
                }
                $line_no++;
                $this->lineAll++;
            }
            fclose($fp);
            return $this->lineAll;
        }
    
        // 配置文件文件名
        public function configFile() {
            return $this->getFileName('config', '.php');
        }
    
        // 获取随机行数
        public function randomLine() {
            $random_line = (int) (rand(1, $this->lineAll)); //随机读取行数
            $line = $random_line % $this->lineFile; //在第几行
            $file_no = ($random_line - $line) / $this->lineFile; //在哪个文件
            $file_name = $this->getFileName($file_no, $this->fileSuffix);
            return $this->randomFile($file_name, $line);
        }
    
        // 根据文件名，返回某一行
        public function randomFile($file_name, $line) {
            $lines = file($file_name);
            $text = $lines[$line];
            unset($lines);
            return $text;
        }
    
        // 返回文件名
        private function getFileName($name, $suffix = '') {
            return $this->filePath . $this->filePrefix . $name . $suffix;
        }
    
    }
    ?>
```

为了方便调用，另外写了个助手函数：

```php
    <?php
    // 助手函数
    function random_line($file_name) {
        $qy_file = new qiyuanBigFile;
        $qy_file->lineFile = 10000; //5000个为一个文件
        $qy_file->filePath = './cache/';
    
        $conf_file = $qy_file->configFile();
        if (!is_file($conf_file)) {
            $line_all = $qy_file->setup($file_name);
            file_put_contents($conf_file, json_encode($line_all));
        } else {
            $qy_file->lineAll = (int) file_get_contents($conf_file);
        }
        $text = $qy_file->randomLine();
        unset($qy_file);
        return $text;
    }
    ?>
```
为了方便大家测试，在写个完整的测试文件：

```php
    <?php
    
    die(random_line('./rndkey.txt'));
    
    // 助手函数
    function random_line($file_name) {
        $qy_file = new qiyuanBigFile;
        $qy_file->lineFile = 10000; //5000个为一个文件
        $qy_file->filePath = './cache/';
    
        $conf_file = $qy_file->configFile();
        if (!is_file($conf_file)) {
            $line_all = $qy_file->setup($file_name);
            file_put_contents($conf_file, json_encode($line_all));
        } else {
            $qy_file->lineAll = (int) file_get_contents($conf_file);
        }
        $text = $qy_file->randomLine();
        unset($qy_file);
        return $text;
    }
    
    // 核心类
    class qiyuanBigFile {
    
        public $lineAll = 0;
        public $lineFile = 5000;
    
        public $filePath = './data/';
        public $filePrefix = 'qiyuan_big_file_';
        public $fileSuffix = '.txt';
    
        // 分割文件
        public function setup($input_file) {
            $fp = fopen($input_file, 'r');
            $file_no = 0; //首个文件的名称
            $line_no = 0; //第几行
            $file_texts = ''; //文件
            while (($line = fgets($fp)) !== false) {
                $file_texts .= $line;
                if ($line_no === $this->lineFile) {
                    file_put_contents($this->getFileName($file_no, $this->fileSuffix), $file_texts);
                    $file_no++;
                    $line_no = 0;
                    $file_texts += '';
                }
                $line_no++;
                $this->lineAll++;
            }
            fclose($fp);
            return $this->lineAll;
        }
    
        // 配置文件文件名
        public function configFile() {
            return $this->getFileName('config', '.php');
        }
    
        // 获取随机行数
        public function randomLine() {
            $random_line = (int) (rand(1, $this->lineAll)); //随机读取行数
            $line = $random_line % $this->lineFile; //在第几行
            $file_no = ($random_line - $line) / $this->lineFile; //在哪个文件
            $file_name = $this->getFileName($file_no, $this->fileSuffix);
            return $this->randomFile($file_name, $line);
        }
    
        // 根据文件名，返回某一行
        public function randomFile($file_name, $line) {
            $lines = file($file_name);
            $text = $lines[$line];
            unset($lines);
            return $text;
        }
    
        // 返回文件名
        private function getFileName($name, $suffix = '') {
            return $this->filePath . $this->filePrefix . $name . $suffix;
        }
    
    }
    
    ?>
```

测试数据 1480万行数据(文件360M左右)，第一次读取时速度大约在秒级左右(我的测试在 7-8秒)，之后都可以达到微秒级（我的测试在0.002-0.005秒）

看到 https://www.cnblogs.com/easirm/p/4199318.html 中提到 fseek 可以提高速度。因为文件不经常改变，暂时不测试了，现在已经满足需求了。

[1]: http://www.miaoqiyuan.cn/p/php-bigfile

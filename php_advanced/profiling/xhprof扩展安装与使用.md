## xhprof扩展安装与使用

来源：[http://www.cnblogs.com/renzhicai/p/9545614.html](http://www.cnblogs.com/renzhicai/p/9545614.html)

时间 2018-08-27 23:58:00

 
目录
 
### 一、xhprof扩展安装步骤
 
xhprof是PHP的一个扩展，最好也直接安装上graphviz图形绘制工具（用于xhprof分析结果以直观的图形方式显示），废话不多说，直奔正题。
 
#### 1、安装

 
* PHP5版本的安装 
 

```
wget http://pecl.php.net/get/xhprof-0.9.4.tgz
tar -zxvf xhprof-0.9.4.tgz 
cd xhprof-0.9.4
cd extension/
phpize
./configure
make
make install
```

 
* PHP7版本的安装 
 

```
unzip xhprof-php7.zip 
cd xhprof-php7/extension/
phpize 
./configure --with-php-config=/usr/local/php/bin/php-config 
make
make install
```
 
#### 2、修改php.ini配置文件
 
在php.ini配置文件中追加下面配置，并创建目录`/home/wwwroot/default/xhprof_data`

```
[xhprof]
extension = xhprof.so
// xhprof分析结果文件存放根目录
xhprof.output_dir = /home/wwwroot/default/xhprof_data
```
 
#### 3、添加一个环境变量`XHPROF_ROOT_PATH`为了后期每个项目都能使用xhprof来进行性能分析，建议给PHP加一个环境变量，这样之后，在任何项目代码里都可以很方便的调用xhprof来分析性能瓶颈，请执行如下操作：

```
vim /usr/local/php/etc/php-fpm.conf
env[XHPROF_ROOT_PATH]=/usr/local/php/include/xhprof/
```
 
#### 4、将xhprof核心源代码复制到上述`XHPROF_ROOT_PATH`环境变量所指定的目录下 

```
cp -r xhprof_lib /usr/local/php/include/xhprof/xhprof_lib
```
 
#### 5、将下面两个目录复制到`xhprof_data`的同级目录下（最好都放到web根目录下） 
 
查看分析结果文件有用，如下图所示：
 
![][0]

```
//执行
cp -r xhprof_html /home/wwwroot/default/xhprof_html
cp -r xhprof_lib /home/wwwroot/default/xhprof_lib

//改变xhprof_data目录拥有者，为了浏览器访问时能在xhprof_data目录下写入文件
chown -R www:www xhprof_data
```
 
#### 6、访问xhprof根目录
 
配置demo.com域名根目录为`/home/wwwroot/default/`，则可访问如下链接查看xhprof结果分析根目录`http://demo.com/xhprof_data/`，如下图所示：
 
![][1]
 
#### 7、形象化的查看分析结果
 
安装图形绘制工具，后面的分析结果可以通过该工具以图形显示,更直观 
 执行安装命令：`yum install graphviz`### 二、xhprof的使用
 
#### 1、xhprof性能分析小demo
 
下面写的三种方式实现阶乘的代码

```php
<?php

xhprof_enable(XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY);

$n = 5;

echo jc($n);
echo '<br/>';

echo jc($n);
echo '<br/>';

echo jc($n);
echo '<br/>';

function jc($n){
    if($n == 1){
        return 1;
    }
    
    return $n * jc($n-1);
}

function jc2($n){
    $m = 1;
    for($i=1; $i<=$n; $i++){
        $m = $m * $i;
    }
    
    return $m;
}

function jc3($n){
    $arr = [];
    $arr[1] = 1;
    
    for($i = 2; $i<=$n; $i++){
        $arr[$i] = $i * $arr[$i-1];
    }
    
    return $arr[$n];
}


$data = xhprof_disable();
//$_SERVER['XHPROF_ROOT_PATH'] 这就是第三步添加的那个环境变量
include_once $_SERVER['XHPROF_ROOT_PATH'] . "xhprof_lib/utils/xhprof_lib.php";
include_once $_SERVER['XHPROF_ROOT_PATH'] . "xhprof_lib/utils/xhprof_runs.php";
$x = new XHProfRuns_Default();

//拼接文件名
$xhprofFilename = date('Ymd_His');

//print_r($data);die;//此处的打印数据看起来非常不直观，所以需要安装yum install graphviz 图形化界面显示,更直观
$x->save_run($data, $xhprofFilename);
```
 
  
上述小demo执行后，会在xhprof_data目录下生成一个分析结果保存文件，网页端访问结果文件，如下图：

![][2]

 
![][3]
 
  
当我在点`[View Full Callgraph]`查看图形分析界面时，问题出现了，如下图：

![][4]
 
好在网上查到原因是，php配置文件中有个disable_functions禁用函数列表，把里面的`proc_open`去掉即可。

 
   图形分析结果显示如下 

![][5]

 
#### 2、实际项目中该如何引入xhprof
 
请参考如下截图所示引入思路（在项目控制器基类构造方法和析构方法里做手脚），思路技巧仅供学习参考，如下图：
 
![][6]
 **`下面是我在项目（以Yii2为框架）下引入xhprof代码一览`** 

```php
<?php
namespace backend\component;

use Yii;
use common\component\baseController;

class backendBaseController extends baseController
{
    public $layout = "/content";
    public $enableCsrfValidation = false;

    public static $profiling = 0;

    public function init(){
        parent::init();
        
        self::$profiling = 1;// !(mt_rand() % 9);
        if  (self::$profiling) {
            xhprof_enable(XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY);
        }
    }

    public function __destruct()
    {
        if(self::$profiling){
            $data = xhprof_disable();
            //$_SERVER['XHPROF_ROOT_PATH'] 该环境变量由第3步得来
            include_once $_SERVER['XHPROF_ROOT_PATH'] . "/xhprof_lib/utils/xhprof_lib.php";
            include_once $_SERVER['XHPROF_ROOT_PATH'] . "/xhprof_lib/utils/xhprof_runs.php";
            $x = new XHProfRuns_Default();

            //当前路由
            $routeName = Yii::$app->requestedRoute;
            //路由为空，则说明是首页
            if (empty($routeName)){
                $routeName = Yii::$app->defaultRoute;
            }

            //拼接xhprof分析结果保存文件名
            $xhprofFilename = str_replace('/', '_', $routeName).'_'.date('Ymd_His');
            $x->save_run($data, $xhprofFilename);
        }
    }
}
```
 
### 总结
 
##### xhprof是一个分析PHP代码性能瓶颈，提高PHP代码效率的有利工具,通过xhprof，可以看到代码慢在哪里，哪里还有优化的空间等等。
 
##### 最后分享一个关于`xhprof`不错的资料 [ipc2015-xhprof.pdf][7] 下载链接： [https://pan.baidu.com/s/1EPuKunXlI1gvmtLICHyCxw][8] 密码：11p0 
 
### 参考资料
 
[1.使用XHProf查找PHP性能瓶颈][9]
 
[2.PHP性能分析工具 xhprof][10]
 
[3.xhprof安装了graphviz还报错failed to execute cmd " dot -Tpng"][11]


[7]: https://pan.baidu.com/s/1EPuKunXlI1gvmtLICHyCxw
[8]: https://pan.baidu.com/s/1EPuKunXlI1gvmtLICHyCxw
[9]: https://segmentfault.com/a/1190000003509917
[10]: http://blog.sina.com.cn/s/blog_5f54f0be0102v995.html
[11]: https://www.04007.cn/article/340.html
[0]: ../img/eQruaui.png
[1]: ../img/BRBb2aQ.png
[2]: ../img/AZzumeR.png
[3]: ../img/yyaUZzI.png
[4]: ../img/jMbm22N.png
[5]: ../img/YvAzqy3.png
[6]: ../img/vIZnyiM.png
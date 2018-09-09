# [php调试利器：FirePHP的安装与使用][0]

 2016-08-25 14:48  496人阅读  

版权声明：本文为博主（木鱼大叔）原创文章，未经博主允许不得转载。
 做开发的人都知道，我们可以利用浏览器的控制台来调试[JavaScript][6]脚本，但是对于像[PHP][7]这种服务端的脚本，你知道如何调试吗？今天给大家推荐一个php调试利器，FirePHP！
 以 Chrome 浏览器为例，具体实施步骤如下：
** 1. 安装FirePHP插件**  
 在 > Chrome浏览器的应用商店中，搜索firephp关键词，在出来的插件列表中，选择第一个，将它添加到Chrome即可。如图：

![][8]
** 2. 获取FirePHP类库**  
 仅仅安装好FirePHP浏览器端的插件是不够的，我们还需要安装它的服务端，FirePHP类库下载地址：http://www.firephp.org/，如图：

 

![][9]
 下载完成后，将压缩包中的fb.php和FirePHP.class.php两个文件，拷贝到我们的项目中，如图：

 

![][10]
 由于我的开发环境是ThinkPHP，所以我将它拷贝到了Library的Vendor目录下，如图：

 

![][11]
** 3. 如何使用**  
 FirePHP的插件和类库都已经安装好了，下面我们来看下如何使用它。
 首先，我写了一个FirePHP的工具类，内容如下：


```php
<?php
namespace Common\Lib\Util;
if (!class_exists('FB')) {
    vendor('FirePHP.fb');
}

class FireBug {
    /**
     * 将php调试信息打印到控制台
     * @param mixes $object : 待输出的数据,类型可以是字符串、数组或者对象
     * @param string $label : 标题
     * @param boolean $showTrace : 是否显示调用跟踪信息
     */ 
    public static function console($object, $label=null, $showTrace=false){
        
        //开发与生产模式的开关标识，我们只在开发模式下调试脚本
        if (!DEBUG_PHP) {
            return;
        }
        try {
            $label = $label ? $label : time();
            \FB::log($object,$label);
            if (is_array($object) || is_object($object)) {
                $headers = array_keys(reset($object));
                if (is_array($headers)) {
                    array_unshift($object,$headers);
                    \FB::table($label,$object);
                }else{
                    \FB::table($label,array(array_keys($object),$object));
                }
            }else if(is_object($object)){
                \FB::table($label,$object);
            }
            if ($showTrace) {
                \FB::trace($label);
            }
        } catch (Exception $e) {
            echo '请开启输出缓冲函数ob_start()';
        }
    }
}

?>
```
> 然后，在需要调试的地方，调用它，如下：

 

![][13]

> 打开 Chrome 浏览器的控制台，我们将会看到如下输出：

 

![][14]

> 是不是非常方便，通过FirePHP，我们就不需要把调试信息用echo，print_r或者日志的形式输出了，这样，无形中，也加快了我们的开发进程。

[0]: https://blog.csdn.net/tdcqfyl/article/details/52314470
[1]: http://www.csdn.net/tag/firephp
[6]: http://lib.csdn.net/base/javascript
[7]: http://lib.csdn.net/base/php
[8]: http://img.blog.csdn.net/20160825145505313
[9]: http://img.blog.csdn.net/20160825150024585
[10]: http://img.blog.csdn.net/20160825150433185
[11]: http://img.blog.csdn.net/20160825150751080
[12]: #
[13]: http://img.blog.csdn.net/20160825151423711
[14]: http://img.blog.csdn.net/20160825151816040
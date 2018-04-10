## 如何让Composer的autoload支持自定义文件后缀名

来源：[http://www.cnblogs.com/x3d/p/6679351.html](http://www.cnblogs.com/x3d/p/6679351.html)

2017-04-07 17:46

PHP的Composer工具规范了我们对系统各种资源库的加载格式，借助于PHP的自动加载机制，可以很大程度上简化在应用开发过程中的类库文件引用场景。但到目前为止，它有个不是问题的问题，就是文件后缀名只支持.php，而基于某些框架开发的旧资产，类文件的后缀名是.class.php，想使用Composer的自动加载规范，就不太纯粹了，一般要两者混着用，或者修改其他框架下的加载规则。

有没有省事点的解决办法呢？

首先只要能产生这么一个疑问，就赢了。而答案呢，多半能找到的。

Composer实现自动加载机制的代码非常简练，稍微看一下就能看懂。

当看到`ClassLoader.php`文件中的`findFileWithExtension`方法时参数里出现了一个`$ext`，也就看到希望。只要在适当的时机，能覆盖这个`$ext`参数就搞定。

---

`findFileWithExtension` 可以修改成 只需要 `namd.***.php` 即可  



---


其原始代码如下：


```php
private function findFileWithExtension($class, $ext)
    {
        // PSR-4 lookup
        $logicalPathPsr4 = strtr($class, '\\', DIRECTORY_SEPARATOR) . $ext;

        $first = $class[0];
        if (isset($this->prefixLengthsPsr4[$first])) {
            foreach ($this->prefixLengthsPsr4[$first] as $prefix => $length) {
                if (0 === strpos($class, $prefix)) {
                    foreach ($this->prefixDirsPsr4[$prefix] as $dir) {
                        if (file_exists($file = $dir . DIRECTORY_SEPARATOR . substr($logicalPathPsr4, $length))) {
                            return $file;
                        }
                    }
                }
            }
        }

        // PSR-4 fallback dirs
        foreach ($this->fallbackDirsPsr4 as $dir) {
            if (file_exists($file = $dir . DIRECTORY_SEPARATOR . $logicalPathPsr4)) {
                return $file;
            }
        }

        // PSR-0 lookup
        if (false !== $pos = strrpos($class, '\\')) {
            // namespaced class name
            $logicalPathPsr0 = substr($logicalPathPsr4, 0, $pos + 1)
                . strtr(substr($logicalPathPsr4, $pos + 1), '_', DIRECTORY_SEPARATOR);
        } else {

        }
    }
```


稍微修改一下：

![](https://images2015.cnblogs.com/blog/5409/201704/5409-20170407191031144-1329025615.png)

autload_psr4.php 配置文件中，对应的格式变化：


```php
return array(
    'Qiniu\\' => array($vendorDir . '/qiniu/php-sdk/src/Qiniu'),
    // 字符串格式改为二维数组格式
    'Liniu\\' => array([$vendorDir . '/Liniu/php-sdk/src/Liniu', '.class.php']),
);
```


贴出代码：


```php
private function findFileWithExtension($class, $ext)
    {
        // PSR-4 lookup
        $logicalPathPsr4 = strtr($class, '\\', DIRECTORY_SEPARATOR);

        $first = $class[0];
        if (isset($this->prefixLengthsPsr4[$first])) {
            foreach ($this->prefixLengthsPsr4[$first] as $prefix => $length) {
                if (0 === strpos($class, $prefix)) {
                    foreach ($this->prefixDirsPsr4[$prefix] as $dir) {
                        $_ext = $ext;
                        $_dir = $dir;
                        if (is_array($dir) && count($dir) == 2) {
                            $_ext = $dir[1];
                            $_dir = $dir[0];
                        }
                        if (file_exists($file = $_dir . DIRECTORY_SEPARATOR . substr($logicalPathPsr4 . $_ext, $length))) {
                            return $file;
                        }
                    }
                }
            }
        }

        // PSR-4 fallback dirs
        foreach ($this->fallbackDirsPsr4 as $dir) {
            if (file_exists($file = $dir . DIRECTORY_SEPARATOR . $logicalPathPsr4 . $ext)) {
                return $file;
            }
        }

        // PSR-0 lookup
        if (false !== $pos = strrpos($class, '\\')) {
            // namespaced class name
            $logicalPathPsr0 = substr($logicalPathPsr4 . $ext, 0, $pos + 1)
                . strtr(substr($logicalPathPsr4 . $ext, $pos + 1), '_', DIRECTORY_SEPARATOR);
        } else {
        }
    }
```


编码，有一种纯粹的乐趣。

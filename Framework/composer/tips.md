#### 国内镜像


```
"repositories": {
        "packagist": {
            "type": "composer",
            "url": "https://packagist.phpcomposer.com"
        }
    }
```

### 全局配置

需要添加环境变量 `COMPOSER_HOME` 及 缓存目录 

看代码 只需要设置 `COMPOSER_HOME` 即可

引入 非 `www` 目录依赖文件 ，即全局安装的依赖包时，页面显示失效，命令行可以使用
例如
    
    composer global require symfony/var-dumper;
    require "D:/composer/vendor/autoload.php";

命令行显示，页面报错


php的命名空间是不支持`*`这种方式的，`c#`中支持`use app.models.*`就可以引入model下所有的类，但是php是不支持的（详情要去看 **`autoload`**方法，看完就明白了），必须要引入到具体的类
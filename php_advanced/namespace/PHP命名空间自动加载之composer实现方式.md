#  [PHP命名空间自动加载之composer实现方式][0]

 2016-11-20 14:34  290人阅读  

 本文章已收录于：

版权声明：本文为博主原创文章，未经博主允许不得转载。

必备条件：你已经安装了composer；

项目构建完成之后的文件结构：

![][4]

S1：

在项目根目录创建composer.json文件，写入代码



    {
        "type": "project",
        "autoload": {
            "psr-4": {
                "Admin\\": "admin/"
            }
        }
    }

  
S2：在项目根目录打开命令，写入命令



    composer update

等待执行完成。

安装成功后，会在项目根目录下新建一个"/vendor/"文件夹。

S3：

说明：使用之前需要require一下"/vendor/autoload.[PHP][6]"文件。

```php
    $autoLoadFilePath = dirname($_SERVER['DOCUMENT_ROOT']).DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';
    require_once $autoLoadFilePath;
```

  
说明：我的入口文件在根目录下的"\public\"文件夹下。S4：

在"/admin/"目录下新建test.[php][6]文件，文件内容如下

```php
    <?php
    
    namespace Admin;
    
    class test
    {
        public function sayHi()
        {
            echo 'hi';
        }
    }
```

  
在"/public/"目录下新建index.php文件，文件内容如下

```php
    <?php
    
    //装载自动加载函数
    $autoLoadFilePath = dirname($_SERVER['DOCUMENT_ROOT']).DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';
    require_once $autoLoadFilePath;
    
    $test = new \Admin\test();
    $test->sayHi();
```
    

S5：配置apache，访问路径，得到如下

![][7]

成功！

[0]: /izhengyang/article/details/53240792
[4]: ../img/20161120144922132
[5]: #
[6]: http://lib.csdn.net/base/php
[7]: ../img/20161120145414963
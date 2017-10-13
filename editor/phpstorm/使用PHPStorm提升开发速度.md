## [laravel使用手札——使用PHPStorm提升开发速度](https://segmentfault.com/a/1190000004980370)


# PHPStorm安装

PHPStorm 使用手札——安装[看这里][0]点击预览

## 代码自动提示支持

laravel引入`laravel-ide-helper`能为PHPStorm提供相应支持

    composer require barryvdh/laravel-ide-helper

添加以下代码到`config/app.php`的`providers`里

    Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class,

构建相关内容：

    php artisan ide-helper:generate

再把构建添加到composer.json配置

    "scripts":{
        "post-update-cmd": [
            "php artisan clear-compiled",
            "php artisan ide-helper:generate",
            "php artisan optimize"
        ]
    },

完成上面步骤即可于PHPStorm内快乐地使用代码自动提示了，其余配置请看[laravel-ide-helper][1]

## 使用Swagger提供API文档

使用Swagger能很好地提供一套文档自动生成方案，并有效解决前后台工作交付等沟通上的问题。

Laravel安装Swagger支持和`Swagger-ui`请移步到[laravel使用手札——Swagger][2]。

在PHPStorm安装支持

    菜单栏
    File -> Setting -> Plugins -> Browse repositories
    
    搜索 PHP Annotations Plugin 和 Symfony2 Plugin 安装

使用时可不用完全参照laravel插件zircote/swagger-php的备注方式，使用PHPStorm自动补全内容的格式便可以，即：

    ##Swagger-php建设的备注格式
    /**
     * @SWG\Info(title="My First API", version="0.1")
     */
    
    /**
     * @SWG\Get(
     *     path="/api/resource.json",
     *     @SWG\Response(response="200", description="An example resource")
     * )
     */
     
    ##在PHPStorm自动补全
    /**
     * @Info(title="My First API", version="0.1")
     */
    
    /**
     * @Get(
     *     path="/api/resource.json",
     *     @SWG\Response(response="200", description="An example resource")
     * )
     */

### Swagger小结

从[Swagger官方文档][3]能看出对于PHPStorm支持可选`PHP Annotations Plugin`和`Symfony2 Plugin`，经过试验后发觉必须安装`PHP Annotations Plugin`才能很好地使用备注补全功能。

[0]: https://segmentfault.com/n/1330000004978888
[1]: https://github.com/barryvdh/laravel-ide-helper
[2]: https://segmentfault.com/a/1190000004980342
[3]: http://doctrine-common.readthedocs.org/en/latest/reference/annotations.html#ide-support
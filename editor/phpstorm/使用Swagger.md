## [laravel使用手札——Swagger](https://segmentfault.com/a/1190000004980342)

> 本例子使用Laravel 5.2版本

这里记录的是较为灵活的方案，不考虑使用`swaggervel`，具体使用参考一下步骤：

安装依赖`swagger-php`
    
    composer require zircote/swagger-php

创建`SwaggerController`

    php artisan make:controller SwaggerController

在`SwaggerController`加上导出`SwaggerJSON`数据的处理

    /**
     * @Swagger(
     *     schemes={"http"},
     *     basePath="/",
     *     consumes={"application/json"},
     *     tags={
     *         @SWG\Tag(
     *             name="API",
     *             description="API接口"
     *         )
     *     }
     * )
     *
     * @Info(
     *  title="API文档",
     *  version="0.1"
     * )
     *
     * @return mixed
     */
    class SwaggerController extends Controller
    {
        public function doc()
        {
            $swagger = \Swagger\scan(realpath(__DIR__.'/../../'));
            return response()->json($swagger);
        }
    }

在`routes.php`加上路由

    Route::get('/swagger/doc', 'SwaggerController@doc');

接下来下载[swagger-ui][0]，将`swagger-ui/dist`目录内的文件拷贝于`/public/swagger-ui`目录下，可以尝试访问http://localhost/swagger-ui看看能否正常显示。

会发现文档地址还是例子的地址，可以修改`public/swagger-ui/index.html`文件下的默认地址

    var url = window.location.search.match(/url=([^&]+)/);
    if (url && url.length > 1) {
    url = decodeURIComponent(url[1]);
    } else {
    url = "http://petstore.swagger.io/v2/swagger.json" //改成你的路由地址，如：/swagger/doc;
    }

再刷新页面，慢慢享用！

[0]: https://github.com/swagger-api/swagger-ui
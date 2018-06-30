## swagger系列一：laravel中部署swagger ui

来源：[https://segmentfault.com/a/1190000014059501](https://segmentfault.com/a/1190000014059501)


##### 1. 部署swagger ui 到项目中：

可以Git下来` git clone https://github.com/swagger-api/swagger-uiv`
也可以下载zip文件。解压后把目录下的`dist`目录拷贝到 laravel下`public`下的文件夹中，如新建`docs`。访问`http://localhost/docs/`![][0]
##### 2. 修改为自己的项目文件。

打开`docs`（即dist下index.html）下`index.html`。找到`url: "http://petstore.swagger.io/v2/swagger.json",`，把URL修改为自己的，如`url: "swagger.json",`，再次访问即可。但是`swagger.json`并不存在，需要生成。
##### 3.`swagger-php`从代码和现有的`phpdoc注释`中提取信息，为您的RESTful API 生成交互式Swagger文档。与Swagger 2.0规范兼容。

在Laravel项目中安装swagger-php：
  $`composer require zircote/swagger-php`

##### 4. 安装完成后，可以用swagger-php测试示例测试。

如：放在`public`下的`docs`目录，用于存放swagger.json文件。执行命令：
$`php vendor/zircote/swagger-php/bin/swagger  vendor/zircote/swagger-php/Examples -o public/docs`
再次访问即可。界面或许看起来和swagger ui的默认一样，是因为用的同样的模板，但是以后用自己的注释生成的`swagger.json`文件重新覆盖即可。

[0]: https://segmentfault.com/img/bV69yd
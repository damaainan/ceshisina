安装 `swagger-php` 

    composer require zircote/swagger-php

获取`swagger-ui` 

    git clone https://github.com/swagger-api/swagger-ui
    cd swagger-ui
    git checkout -b 2.x origin/master # 切换到特定分支

将 `dist` 目录复制到项目目录 `public` 下，改名 `swagger`，新建 `swagger-doc` 目录
修改 `swagger/index.html` 中数据来源 

    url = "http://abc.com/app/Public/swagger-doc/swagger.json"; 

放开中文翻译的注释

---


### 示例使用
执行命令

     php vendor\zircote\swagger-php\bin\ swagger vendor\zircote\swagger-php\Examples  -o Public\swagger-doc\

在 `swagger-doc` 生成 `swagger.json`  文件

打开 地址 `http://abc.com/app/Public/swagger/index.html` 即出现接口界面


### 接口文档编写

示例中共有三种方式

#### 第一种

模块目录新建入口文件 `api.php`

```php
<?php

/**
 * @SWG\Swagger(
 *     basePath="/tp323/web/interface.php",    # 入口文件
 *     host="www.localhost2.com",              # 域名
 *     schemes={"http"},                       # 协议
 *     produces={"application/json"},          # 
 *     consumes={"application/json"},          # 
 *     @SWG\Info(
 *         version="1.0.0",
 *         title="第一种测试",
 *         description="描述文档",
 *         termsOfService="http://swagger.io/terms/",
 *         @SWG\Contact(name="Swagger API Team"),
 *         @SWG\License(name="MIT")
 *     ),
 *     @SWG\Definition(
 *         definition="ErrorModel",
 *         type="object",
 *         required={"code", "message"},
 *         @SWG\Property(
 *             property="code",
 *             type="integer",
 *             format="int32"
 *         ),
 *         @SWG\Property(
 *             property="message",
 *             type="string"
 *         )
 *     )
 * )
 */

```

`controller` 类文档编写，写于方法之上

```php
<?php

namespace Lib\Controller;

class SimplePetsController extends Controller 
{

 
    /**
     * @SWG\Get(
     *     path="/SimplePets/findPets",
     *     description="方法描述",
     *     operationId="findPets",
     *     produces={"application/json", "application/xml", "text/xml", "text/html"},
     *     @SWG\Parameter(
     *         name="tags",
     *         in="query",
     *         description="tags to filter by",
     *         required=false,
     *         type="array",
     *         @SWG\Items(type="string"),
     *         collectionFormat="csv"
     *     ),
     *     @SWG\Parameter(
     *         name="limit",
     *         in="query",
     *         description="maximum number of results to return",
     *         required=false,
     *         type="integer",
     *         format="int32"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="pet response",
     *         @SWG\Schema(
     *             type="array",
     *             @SWG\Items(ref="#/definitions/Pet")
     *         ),
     *     ),
     *     @SWG\Response(
     *         response="default",
     *         description="unexpected error",
     *         @SWG\Schema(
     *             ref="#/definitions/ErrorModel"
     *         )
     *     )
     * )
     */
    public function findPets()
    {
    }
}

```

执行命令

    php vendor\zircote\swagger-php\bin\swagger Interface\Lib -o Public\swagger-doc\

打开接口地址 `http://abc.com/app/Public/swagger/index.html`





#### 第二种




#### 第三种
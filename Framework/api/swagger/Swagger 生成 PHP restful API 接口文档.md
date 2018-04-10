## Swagger 生成 PHP restful API 接口文档

来源：[https://segmentfault.com/a/1190000014272580](https://segmentfault.com/a/1190000014272580)


## 需求和背景
 **`需求:`** 

为客户端同事写接口文档的各位后端同学,已经在各种场合回忆了使用自动化文档工具前手写文档的血泪史.
我的故事却又不同,因为首先来说,我在公司是 Android 组负责人,属于上述血泪史中催死人不偿命的客户端阵营.
但血泪史却是相通的,没有自动化文档的日子,对接口就是开发流程中最低效的环节.
因此决定使用 swagger 搭建由php注释生成文档的流程.
 **`背景:`** 

我们的 restful api 项目采用 phalcon 框架,整体结构很简单,我们只需要用 swagger 扫描 controller 目录即可.
下简称我们的 php api 项目为 php_api_project.
服务器采用 nginx.
## 搭建

先说下最终的文档生成流程会是什么样子,以便先有个整体的认识:
搭建完成后, 整个流程, 从文档生成到前端展现, 大体如下:


* 在php文件中写 swagger 格式的 / * 注释 /
* 用 swagger-php 内的 bin/swagger.phar 命令扫描 php controller 所在目录, 生成 swagger.json 文件
* 将 swagger.json 文件拷贝到 swagger-ui 中 index.html 指定的目录中
* 打开 swagger-ui 所在的 url, 就可以看到文档了. 文档中的各个 api 可以在该网址上直接访问得到数据.


实现此需求只需要 swagger 的如下两个项目:
 **`swagger-php:`**  扫描 php 注释的工具. 内含一个不错的例子.
 **`swagger-ui:`**  用以将扫描工具生成的 swagger.json 文件内容展示在网页上.

首先将这两个项目下载到本地:

```
$ git clone https://github.com/swagger-api/swagger-ui.git
$ git clone https://github.com/zircote/swagger-php.git
```
## 文档生成工具部署

说是部署,主要就是产生 bin/swagger 这个用来生成 swagger.json 文件的命令.
主要工作,就是用 composer 解决下依赖就可以了.
因为国内直接用 composer 比较蛋疼,所以最好设置下国内的那个 composer 源.
这样的话, 整个 文档生成工具的部署 就是下面三行命令:

```
$ cd swagger-php
$ composer config repo.packagist composer https://packagist.phpcomposer.com
$ composer update
```

只要中间不报错,就算部署完成了. 完成后可以生成一份文档试一下.
swagger-php 项目下的 Examples 目录下有一个示例php工程,里面已经用 swagger 格式写了各种接口注释, 我们来尝试生成一份文档.
执行下面命令:

```
$ cd swagger-php
$ mkdir json_docs
$ php ./bin/swagger ./Examples -o json_docs/
```

上面命令会扫描 Examples 目录中的php文件注释, 然后在 json_docs 目录下生成 swagger.json 文件.
这个 swagger.json 文件就是前端 swagger-ui 用来展示的我们的api文档文件.
 **`NOTE:`**  swagger-php 只是个工具,放在哪里都可以.
## 前端 swagger-ui 部署:

部署方法很简单,就三步:
### 1. 将 swagger-ui 项目中的 dist 文件夹拷贝到 php_rest_api 根目录下.
 **`NOTE1:`**  只需要拷贝dist这一个文件夹就可以了.最好重命名下,简单起见,这里不再重命名.
 **`NOTE2:`**  我们的项目根目录和 nginx 配置的 root 是同一个目录.其实不用放跟目录,只要放到一个不用跨域就跨域访问的目录就可以了. 为啥有跨域问题? 后面会讲.
### 2. 修改 dist 文件夹下的 index.html 文件,指定 swagger.json 所在目录

只改一行就可以.
简单起见,这里直接将 swagger.json 目录指定在 dist 目录下即可. 我们这里屡一下预设条件:
假设 php_api_project 项目的 host 是 api.my_project.com;
假设 php_api_project 项目在 nginx 中指定的 root 即为其根目录;
假设 swagger-ui 里的 dist 文件夹放在上述根目录中;
假设 swagger.json 文件就打算放在上述 dist 目录下 (php_api_project/dist/swagger.json) ;
那么 index.html 中把下面的片段改成这样:

```js
      var url = window.location.search.match(/url=([^&]+)/);
      if (url && url.length > 1) {
        url = decodeURIComponent(url[1]);
      } else {
        <!-- 就是这行,改成你生成的 swagger.json 可以被访问到的路径即可 -->
        url = "http://api.my_project.com/dist/swagger.json";
      }
```
### 3. 拷贝 swagger.json 到上述目录中.

```
# 把 swagger-php_dir 这个,换成你的 swagger-php 录即可
cp swagger-php_dir/json_docs/swagger.json php_api_project/dist/
```

上述步骤完成后, 访问 [http://api.my_project.com/dis...][0] 就可以看到 Examples 那个小项目的 api 文档了.
## 编写 PHP 注释

swagger-php 项目的 Example 中已经有了很多相关例子,照着复制粘贴就可以了.
更具体的相关注释规则的文档,看这里:[http://bfanger.nl/swagger-exp...][1]

假设我的项目 controller 所在目录为 php_api_project/controller/, 那么我只需要扫描这个目录就可以了,不用扫描整个 php 工程.

为了在 swagger.json 中生成某些统一的配置, 建立 php_api_project/controller/swagger 目录. 目录存放一个没有代码的php文件,里面只写注释.

我给这个文件取名叫 Swagger.php, 大体内容如下:

```php
<?php

/**
 * @SWG\Swagger(
 *   schemes={"http"},
 *   host="api.my_project.com",
 *   consumes={"multipart/form-data"},
 *   produces={"application/json"},
 *   @SWG\Info(
 *     version="2.3",
 *     title="my project doc",
 *     description="my project 接口文档, V2-3.

以后大家就在这里愉快的对接口把!

以后大家就在这里愉快的对接口把!

以后大家就在这里愉快的对接口把!

"
 *   ),
 *
 *   @SWG\Tag(
 *     name="User",
 *     description="用户操作",
 *   ),
 *
 *   @SWG\Tag(
 *     name="MainPage",
 *     description="首页模块",
 *   ),
 *
 *   @SWG\Tag(
 *     name="News",
 *     description="新闻资讯",
 *   ),
 *
 *   @SWG\Tag(
 *     name="Misc",
 *     description="其他接口",
 *   ),
 * )
 */
```

如上所示,我的这个php文件一行php代码也没有,就只有注释,为了定义一些全局的swagger设置:
`schemes`: 使用协议 (可以填多种协议)
`host`: 项目地址, 这个地址会作为每个接口的 url base ,拼接起来一期作为访问地址
`consumes`: 接口默认接收的MIME类型, 我的例子中的 formData 对应post表单类型. 注意这是项目默认值,在单个接口注释里可以复写这个值.
`produces`: 接口默认的回复MIME类型. api接口用的比较多的就是`application/json`和`application/xml`.
`@SWG\Info`: 这个里面填写的东西,会放在文档的最开头,用作文档说明.
`@SWG\Tag`: tag是用来给文档分类的,name字段必须唯一.某个接口可以指定多个tag,那它就会出现在多组分类中. tag也可以不用在这里预先定义就可以使用,但那样就没有描述了. 多说无益,稍微用用就啥都明白了.

然后就是给每个接口编写 swagger 格式的注释了.还是举个栗子吧:

```php
    /**
     * @SWG\Post(path="/user/login", tags={"User"},
     *   summary="登录接口(用户名+密码)",
     *   description="用户登录接口,账号可为 用户名 或 手机号. 参考(这个会在页面产生一个可跳转的链接: [用户登录注意事项](http://blog.csdn.net/liuxu0703/)",
     *   @SWG\Parameter(name="userName", type="string", required=true, in="formData",
     *     description="登录用户名/手机号"
     *   ),
     *   @SWG\Parameter(name="password", type="string", required=true, in="formData",
     *     description="登录密码"
     *   ),
     *   @SWG\Parameter(name="image_list", type="string", required=true, in="formData",
     *     @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Image")),
     *     description="用户相册. 好吧,没人会在登录时要求填一堆图片信息.这里是为了示例 带结构的数据, @SWG\Schema ,这个结构需要另行定义,下面会讲."
     *   ),
     *   @SWG\Parameter(name="video", type="string", required=true, in="formData",
     *     @SWG\Schema(ref="#/definitions/Video"),
     *     description="用户 呃... 视频? 同上,为了示例 @SWG\Schema ."
     *   ),
     *   @SWG\Parameter(name="client_type", type="integer", required=false, in="formData",
     *     description="调用此接口的客户端类型: 1-Android, 2-IOS. 非必填,所以 required 写了 false"
     *   ),
     *   @SWG\Parameter(name="gender", type="integer", required=false, in="formData",
     *     default="1",
     *     description="性别: 1-男; 2-女. 注意这个参数的default上写的不是参数默认值,而是默认会被填写在swagger页面上的值,为的是方便用swagger就地访问该接口."
     *   ),
     * )
     */
    public function loginAction() {
        // php code
    } 

    /**
     * @SWG\Get(path="/User/myWebPage", tags={"User"},
     *   produces={"text/html"},
     *   summary="用户的个人网页",
     *   description="这不是个api接口,这个返回一个页面,所以 produces 写了 text/html",
     *   @SWG\Parameter(name="userId", type="integer", required=true, in="query"),
     *   @SWG\Parameter(name="userToken", type="string", required=true, in="query",
     *     description="用户令牌",
     *   ),
     * )
     */
    public function myWebPageAction(){
        // php code
    }
```

规则简单明了,看着代码大家就都懂了.不懂的话,去看文档吧...

上面 login 接口中用到了两个有结构的数据, 一个是 image 类型的数组, 一个是 video 类型的结构.
(其实结构化的参数只能在`in="body"`时才可以用,但这并不妨碍我们为了简化问题,把结构化数据格式化为 json 当字符串传递. 我们只要将这种结构展现在文档里就可以了)

这种有结构的东西 swagger 也可以用 php 注释定义:

```php
<?php

/**
 * @SWG\Definition(type="object", @SWG\Xml(name="Image"))
 */
class Image {

    /**
     * @SWG\Property()
     * @var string
     */
    public $url;
    
    /**
     * @SWG\Property(format="int32")
     * @var int
     */
    public $height;
    
    /**
     * @SWG\Property(format="int32")
     * @var int
     */
    public $width;

}

<?php

/**
 * @SWG\Definition(type="object", @SWG\Xml(name="Video"))
 */
class Video {

    /**
     * @SWG\Property()
     * @var string
     */
    public $url;

    /**
     * @SWG\Property()
     * @var string
     */
    public $thumb_url;

    /**
     * @SWG\Property(format="int32")
     * @var int
     */
    public $length;

    /**
     * @SWG\Property(format="int64")
     * @var int
     */
    public $size;

}
```

这样当这两个类也被 swagger-php/bin/swagger 扫描到后,其他地方就可以正确引用到 Image 和 Video 为名字的这两个结构体了.

这样做的好处是,在接口参数文档中,这个结构会被展示出来,这样客户端同学就知道该传什么结构了.

我的接口栗子里都没有写 response 规则,是因为我们用 json 作为返回载体,返回错误码也是包含在这个 json 结构体里. 并且多数接口返回的json格式都很复杂,用 swagger 的 response 规则基本没法描述.

swagger 的 response 编写规则是按照 http 的 response code 来的 (404, 401 等), 总之对我们的接口来说,这套描述规则不好用.

因此我就直接舍弃了 response 描述, 直接用 swagger 就地请求接口看看返回了什么就是. 再不行就把接口的返回信息在 description 里大体描述一下.

文档写完后,就可以调用 swagger-php/bin/swagger 命令生成 swagger.json, 再拷贝到 swagger-ui 中你指定的那个目录中,就可以访问文档了.

 **`NOTE:`** 

大家应该已经看出来了,其实接口的注释不一定要写在接口上,凭空写注释,一样能生成文档.所以不必纠结各个注释放在什么地方. 比如 swagger 整体定义, tag 定义等,写在任意可以被扫描到的 php 文件中就可以了.
## 常用字段简要说明

这里只是自己理解加翻译的简要说明,更详细的字段说明,还是要去看文档.再次贴出文档:
[http://bfanger.nl/swagger-exp...][1]

接口描述 (@SWGGet, @SWGPost 等) 常用字段:

```
summary - string
接口的简要介绍,会显示在接口标头上,不能超过120个字符

description - string
接口的详细介绍

externalDocs - string
外部文档链接

operationId - string
全局唯一的接口标识

consumes - [string]
接口接收的MIME类型

produces - [string]
接口返回的MIME类型,如 application/json

schemes -    [string]
接口所支持的协议,取值仅限: "http", "https", "ws", "wss"

parameters -    [Parameter Object | Reference Object]
参数列表
```

参数描述 (@SWGParameter) 常用字段:

```
name - string
参数名. 通过路径传参(in 取值 "path")时有注意事项,没用到,懒得看了...

in - string
参数从何处来. 必填. 取值仅限: "query", "header", "path", "formData", "body"

description - string
参数描述. 最好别太长

type - string
参数类型. 取值仅限: "string", "number", "integer", "boolean", "array", "file"

required - boolean
参数是否必须. 通过路径传参(in 取值 "path")时必须为 true.

default - *
默认值. 在你打算把参数通过 path 传递时规矩挺多,我没用到.用到的同学自己看文档吧.
```
## 遇到的问题
## 跨域问题:

swagger 牛X的地方就是它可以在文档上就地访问接口并展示输出,对于调试和对接口非常的方便.但如果不想将 swagger-ui 部署在接口项目下,那么在 swagger-ui 就地访问接口时,就会因跨域问题而请求不到结果. (Response Headers: no response from server; Response Body: no content).
这里不讲跨域问题怎么解决,只是给遇到上面的问题的各位一个思路,知道错误是由跨域产生的,就好解决了.
## 登录鉴权:

如果要将文档放在公网,直接暴露自己的接口可不太合适. 因此要给文档的访问地址做鉴权.
因为安全性要求不高,并且公司较小开发组人员不多,我就直接用了 nginx 提供的 http basic auth 做了登录鉴权.
老规矩,先贴官方文档:
[https://www.nginx.com/resourc...][3]
这样做鉴权很简单,分分钟搞定. 不详述, 就两步:
### 1. 首先用 htpasswd 命令,为需要访问文档的同学生成帐号名和密码:

```
$ htpasswd -cb your/path/to/api_project_accounts.db admin password_for_admin
$ htpasswd -b your/path/to/api_project_accounts.db liuxu 123456
$ htpasswd your/path/to/api_project_accounts.db xiaoming
```
`-c`选项表示如果账号文件 ( api_project_accounts.db ) 不存在,则新建之. 因此创建第一个账号时一定要加 -c, 之后创建则一定不要再加 -c.
`-b`参数表示明文指定密码,就是上面第一条和第二条命令中最后一个输入 (password_for_admin, 123456) . 因此如果不想明文指定,可以不加 -b, 像上面的第三条命令,就会和sudo命令一样,让你输入一段看不见的密码.
上面命令创建了三个账号, admin, liuxu, xiaoming, 并将账号密码保存在 api_project_accounts.db 文件中.
### 2. 然后在 nginx 的项目配置中给自己的这个访问地址开启 http basic auth 就可以了.

```
location /dist {
    auth_basic              "my api project login";
    auth_basic_user_file    your/path/to/api_project_accounts.db;
}
```

记得改完了重启 nginx:

```
nginx -s reload
```

这样一个简单的登录鉴权就建立起来了.
 **`NOTE:`** 

如果系统里没有`htpass`这个命令, 可以安装 apache 的 **`httpd`**  服务器软件,`htpass`这个命令就包含在其中:

```
yum install httpd
```
## 参考:

1. [swagger 官网][4]  
2. [swagger 项目地址][5]  
3. [swagger ui 项目地址][6]  
4. [swagger php 项目地址][7]  
5. [swagger 文档参考地址][8]  

[0]: http://api.my_project.com/dist/index.html
[1]: http://bfanger.nl/swagger-explained/#/swaggerObject
[2]: http://bfanger.nl/swagger-explained/#/swaggerObject
[3]: https://www.nginx.com/resources/admin-guide/restricting-access-auth-basic/
[4]: http://swagger.io/
[5]: https://github.com/swagger-api
[6]: https://github.com/swagger-api/swagger-ui
[7]: https://github.com/zircote/swagger-php
[8]: http://bfanger.nl/swagger-explained/#/swaggerObject
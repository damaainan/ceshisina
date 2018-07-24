## php - Api 接口写法规范和要求

来源：[https://segmentfault.com/a/1190000012074276](https://segmentfault.com/a/1190000012074276)


## 前言

说明
[apidoc][0]是一个API文档生成工具, apidoc可以根据代码注释生成web api文档, apidoc从注释生成静态html网页文档，不仅支持项目版本号，还支持api版本号
## 安装

A). 系统需要安装nodejs(略)

B). 安装apidoc

```
# 有些系统需要sudo 权限来安装
$ npm install apidoc -g
```

C). 执行生成

```
# 这个文档的生成规则是 
# apidoc 
#      -i code_dir
#      -o output_dir
$ apidoc -i myapp/ -o apidoc/ 

# 对于项目中我们使用 laravel artisan 封装了一个函数
# 生成 api doc 文档
$ php artisan lemon:doc apidoc
```

注意: 分组名不支持中文，可修改
## 使用

A) 生成文档

```
$ apidoc -i myapp/ -o doc/api [-c ./] -f ".*\.js$" 
```

`-i`表示输入，后面是文件夹路径 <br/>
`-o`表示输出，后面是文件夹路径 <br/>
默认会带上`-c`，在当前路径下寻找配置文件`apidoc.json`，如果找不到则会在package.json中寻找`"apidoc": { }`<br/>
`-f`为文件过滤，后面是正则表达式，示例为只选着js文件 <br/>
`-e`的选项，表示要排除的文件/文件夹，也是使用正则表达式

B) 项目配置

```
{
    "name" : "项目名",
    "version": "1.0.0",
    "title": "mysails-浏览器标题",
    "description": "description"
}
```

我们的配置存放在根目录`package.json`文件中.
## 参数说明和示例
`apidoc`支持如下关键字：(下面 [ ] 中括号中表示是可选写的内容，使用时不用加 [ ] 中括号。)

```LANG
@api {method} path [title]
    只有使用@api标注的注释块才会在解析之后生成文档，title会被解析为导航菜单(@apiGroup)下的小菜单
    method可以有空格，如{POST GET}
  
@apiGroup name
    分组名称，被解析为导航栏菜单

@apiName name
    接口名称，在同一个@apiGroup下，名称相同的@api通过@apiVersion区分，否者后面@api会覆盖前面定义的@api

@apiDescription text
    接口描述，支持html语法

@apiParam [(group)] [{type}] [field=defaultValue] [description]
    详细介绍见: http://apidocjs.com/#param-api-param
    
@apiVersion verison
    接口版本，major.minor.patch的形式
    
@apiIgnore [hint]
    apidoc会忽略使用@apiIgnore标注的接口，hint为描述
    
@apiSampleRequest url
  接口测试地址以供测试，发送请求时，@api method必须为POST/GET等其中一种
  
@apiDefine name [title] [description]
    定义一个注释块(不包含@api)，配合@apiUse使用可以引入注释块
    在@apiDefine内部不可以使用@apiUse
@apiUse name
     引入一个@apiDefine的注释块

@apiHeader [(group)] [{type}] [field=defaultValue] [description]

@apiError [(group)] [{type}] field [description]

@apiSuccess [(group)] [{type}] field [description]
    用法基本类似，分别描述请求参数、头部，响应错误和成功
    group表示参数的分组，type表示类型(不能有空格)，入参可以定义默认值(不能有空格)，field上使用[]中扩号表示该参数是可选参数
    
@apiParamExample [{type}] [title] example

@apiHeaderExample [{type}] [title] example

@apiErrorExample [{type}] [title] example

@apiSuccessExample [{type}] [title] example
    用法完全一致，但是type表示的是example的语言类型
  example书写成什么样就会解析成什么样，所以最好是书写的时候注意格式化，(许多编辑器都有列模式，可以使用列模式快速对代码添加*号)
```
## 写法规范
### 参数对齐

```php
/**
 * @api                 {get} /api_prefix/check_verification [O]验证验证码
 * @apiVersion          1.0.0
 * @apiName             HomeCheckVerification
 * @apiGroup            Home
 * @apiParam   {String} mobile       手机号
 * @apiParam   {String} captcha      验证码
 */
public function checkVerification(){}
```
### apiName命名规范

apiName 的命名规范是 apiGroup + functionName; 

apiName 的写法规范是 首字母大写的驼峰模式 

例如上面的命名规范是

```
apiGroup : Home
apiName  : HomeCheckVerification
```
### 返回值约定


* 数字类型, 需要转换成int 类型(返回的json 串中不需要有引号包裹)

* 文字类型的, 需要转换成 string 类型

* 返回值中不允许存在`null`


### 返回值对齐

返回的类型值, 参数值, 说明必须对齐 

返回的参数值和真正返回的内容值必须填写完整

```php
/**
 * @api                 {get} /api_prefix/version [O]检测新版本(Android)
 * @apiVersion          1.0.0
 * @apiName             HomeVersion
 * @apiGroup            Home
 * @apiParam   {String}  version        版本号
 * @apiSuccess {String}  download_url   下载地址
 * @apiSuccess {String}  description    描述
 * @apiSuccess {String}  version        版本
 * @apiSuccessExample  data:
 * {
 *     "download_url": "http:\/\/domain.com\/1.1.1.apk",
 *     "description": "修改bug若干, 增加微信支付功能",
 *     "version": "1.1.1"
 * }
 */
public function version()
```
### 路由定义

api 路由文件存放在`app/Http/Routes/`文件夹下

```
Routes/
    api_dailian.php
    api_up.php
    ...
```
### 使用的PHP组件


* 系统使用 dinggo 作为 api的封装组件

* [dingo/api 中文文档][1]



## 其他说明

A). 接口命名

```
lists   => 列表
create  => 创建
edit    => 编辑
delete  => 删除
```

B). 参数命名

```
例如 A下的传递参数, 我们应当使用  title 而不能使用

```

C). 路由命名

```
路由的名称和坐在分组还有函数名进行匹配, 使用蛇形写法

```

```php
/**
 * @api                 {get} /dailian/bank/lists [O][B]银行账户列表
 * @apiVersion          1.0.0
 * @apiName             UserBankList
 * @apiGroup            User
 * @apiSuccess {String} id                  账号ID
 * @apiSuccess {String} bank_account        账号信息
 * @apiSuccess {String} bank_true_name      真实姓名
 * @apiSuccess {String} bank_type           账号类型 : 支付宝
 * @apiSuccess {String} note                备注
 * @apiSuccessExample   成功返回:
 *  [
 *      {
 *          "id": 2,
 *          "bank_account": "123123123",
 *          "bank_true_name": "二狗",
 *          "bank_type": "支付宝",
 *          "note": ""
 *      }
 *  ]
 */
public function lists()
```

这里的命名是`api_dailian.bank_lists`D). 自营的接口无特殊返回不需要填写说明

E). 接口中只能返回有意义的数据, 对app无用的数据不得返回

F). 列表为空也需要返回分页

[0]: http://apidocjs.com/
[1]: https://github.com/liyu001989/dingo-api-wiki-zh
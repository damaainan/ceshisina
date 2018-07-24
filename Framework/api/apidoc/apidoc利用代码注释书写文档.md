## apidoc利用代码注释书写文档

来源：[https://segmentfault.com/a/1190000015567561](https://segmentfault.com/a/1190000015567561)

个人博客同步文章 [https://mr-houzi.com/2018/07/...][1]

apidoc是一款利用源代码中注释来创建RESTful Web API文档的工具。apidoc可用于C＃，Go，Dart，Java，JavaScript，PHP，TypeScript和所有其他支持Javadoc的语言。
## 安装

```
npm install apidoc -g
```
## 运行

```
apidoc -i myapp/ -o apidoc/ -t mytemplate/
```
`myapp/`根据`myapp`文件夹下文件的注释进行创建文档
`apidoc/`文档的输出位置
`mytemplate/`使用的模板
## 命令行界面

查看帮助，用于显示命令行参数：

```
apidoc -h
```
## 配置（apidoc.json）

在apidoc.json配置项目的基本信息

```json
{
  "name": "example",
  "version": "0.1.0",
  "description": "apiDoc basic example",
  "title": "Custom apiDoc browser title",
  "url" : "https://api.github.com/v1"
}
```

apidoc也支持通过`package.json`进行设置，只需在`"apidoc":{}`下添加参数即可。

```json
{
  "name": "example",
  "version": "0.1.0",
  "description": "apiDoc basic example",
  "apidoc": {
    "title": "Custom apiDoc browser title",
    "url" : "https://api.github.com/v1"
  }
}
```

如果你想设置header和footer，把下面信息加入到`apidoc.json`中（别忘记创建markdown文件）。

```json
{
  "header": {
    "title": "My own header title",
    "filename": "header.md"
  },
  "footer": {
    "title": "My own footer title",
    "filename": "footer.md"
  }
}
```
## 使用

接下来给大家介绍一下常用的参数，完整介绍大家可以自己看一下[官方文档][2]，正常情况来说下面这些就够用。
### @api

```
@api {method} path [title]
```

声明一下请求方法、请求路径等。

| 名称 | 描述 |
|-|-|
| method | 请求方法：DELETE，GET，POST，PUT，... |
| path | 请求路径 |
| title | 一个简短的标题。（用于导航和文章标题） |


eg:

```
/**
 * @api {get} /user/:id
 */
```
### @apiDeprecated

```
@apiDeprecated [text]
```

将API方法标记为已弃用

| 名称 | 描述 |
|-|-|
| text | 文字描述 |


### apiDescription

```
@apiDescription text
```

API方法的详细描述。

| 名称 | 描述 |
|-|-|
| text | 文字描述 |


### @apiName

```
@apiName name
```

定义方法文档块的名称。名称将用于生成的输出中的子导航。结构定义不需要`@apiName`。

| 名称 | 描述 |
|-|-|
| name | 方法的唯一名称。 <br/> 格式：方法 + 路径（例如Get + User），建议以这种方式命名 |


eg:

```
/**
 * @api {get} /user/:id
 * @apiName GetUser
 */
```
### @apiGroup

```
@apiGroup name
```

定义方法文档块属于哪个组。组将用于生成的输出中的主导航。例如：`login`和`register`接口都可以划分到`User`组。

| 名称 | 描述 |
|-|-|
| name | 组的名称。也用作导航标题。 |


eg:

```
/**
 * @api {get} /user/:id
 * @apiGroup User
 */
```
### @apiHeader

```
@apiHeader [(group)] [{type}] [field=defaultValue] [description]
```

描述`API-Header`传递的参数，例如用于授权。

| 名称 | 描述 |
|-|-|
| group | 参数组别 |
| type | 参数类型 |
| field | 参数名 |
| description | 描述 |


eg:

```
/**
 * @api {get} /user/:id
 * @apiHeader {String} access-key Users unique access-key.
 */
```
### @apiParam

```
@apiParam [(group)] [{type}] [field=defaultValue] [description]
```

用来描述API传参值

| 名称 | 描述 |
|-|-|
| group | 参数组别 |
| type | 参数类型 |
| field | 参数名 |
| description | 描述 |


eg:

```
 /** @apiParam (params) {int} time 时间戳(用于判断请求是否超时)
   * @apiParam (params) {String} token 确认来访者身份
   * @apiParam (params) {String} user_name 手机号或者邮箱
   * @apiParam (params) {String} user_pwd MD5加密的用户密码
   */
```
### @apiSuccess

```
@apiSuccess [(group)] [{type}] field [description]
```

成功返回参数。用法和`@apiParam`类似。个人认为`@apiSuccess`有点多余，使用`@apiSuccessExample`就足够了。
### @apiSuccessExample

```
@apiSuccessExample [{type}] [title] example
```

成功返回消息的示例，作为预格式化代码输出。

eg:

```
/**
 * @api {get} /user/:id
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "firstname": "John",
 *       "lastname": "Doe"
 *     }
 */
```
### @apiError

错误返回参数。用法和`@apiSuccess`类似
### @apiErrorExample

错误返回消息的示例，作为预格式化代码输出。用法和`@apiSuccessExample`类似。
### @apiVersion

```
@apiVersion version
```

设置文档块的版本。版本也可用于`@apiDefine`。

eg:

```
/**
 * @api {get} /user/:id
 * @apiVersion 1.6.2
 */
```
### @apiIgnore

```
@apiIgnore [hint]
```
 **`将它放在一个块的顶部。`** 
`@apiIgnore`将无法解析块。如果您在源代码中留下过时或未完成的方法并且您不希望将其发布到文档中，那么它很有用。

| 名称 | 描述 |
|-|-|
| hint | 用于提示为什么忽略这个块。 |


eg:

```
/**
 * @apiIgnore Not finished Method
 * @api {get} /user/:id
 */
```
## 举个栗子

来一个完整的例子

```
/**
 * @api {post} /user/login 用户登录
 * @apiName login
 * @apiGroup User
 * @apiParam (params) {int} time 时间戳(用于判断请求是否超时)
 * @apiParam (params) {String} token 确认来访者身份
 * @apiParam (params) {String} user_name 手机号或者邮箱
 * @apiParam (params) {String} user_pwd MD5加密的用户密码
 * @apiSuccessExample Success-Response:
 *  {
 *      "code": 200,
 *      "msg": "登录成功！",
 *      "data": {
 *           'uid': 1, //用户ID
 *           'user_phone': '13011111111', //用户手机号
 *           'user_nickname': '小明', //用户昵称
 *           'user_email': '123456789@163.com', //用户邮箱
 *           'user_rtime': 1501414343 //用户注册时间
 *  }
 *
 */
```
 **`效果：`** 

![][0]

[1]: https://mr-houzi.com/2018/07/09/apidoc-generate-doc/
[2]: http://apidocjs.com/index.html#example-full
[0]: https://segmentfault.com/img/remote/1460000015567821
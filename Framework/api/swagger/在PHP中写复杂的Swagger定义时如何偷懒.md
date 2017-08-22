# 在PHP中写复杂的Swagger定义时如何偷懒（基于zircote/swagger-php）

 时间 2017-05-05 13:56:57  简书
<font face=微软雅黑>

原文[http://www.jianshu.com/p/b59d305acd60][1]


Swagger大大降低了接口提供者和接入者之间的沟通和维护成本，如果你还不了解Swagger的话，可以看我的另一篇文章 [《Laravel（PHP）使用Swagger生成API文档不完全指南 - 基本概念和环境搭建》][4]

在PHP中使用Swagger，大多都会用到 [zircote/swagger-php][5] 这个Composer库。以 [Laravel][6] 项目为例，我们通常会为每个Controller的返回写一个单独的 `Swagger Definition` 以方便管理，然后在Controller的 `Annotation` 中写下这样的规则： 

```php
    <?php 
    
    // ...
    
        /**
         * 假设是项目中的一个API
         *
         * @SWG\Get(path="/swagger/my-data",
         *   tags={"project"},
         *   summary="拿一些神秘的数据",
         *   description="请求该接口需要先登录。",
         *   operationId="getMyData",
         *   produces={"application/json"},
         *   @SWG\Parameter(
         *     in="formData",
         *     name="reason",
         *     type="string",
         *     description="拿数据的理由",
         *     required=true,
         *   ),
         *   @Swagger\Annotations\Schema(ref="#/definitions/MyDataResponse"),
         * )
         */
        public function getMyData()
        {
            //todo 待实现
        }
    
    // ...
```

而上面引用的 `MyDataResponse` 的定义看起来可能是这样： 

```php
    <?php
    
    /**
     * @SWG\Definition
     */
    class MyDataResponse
    {
        /**
         * @var string
         * @SWG\Property(example="Alan Jones")
         */
        public $data;
    }
```

注意， `$data` 字段定义中设置了 `example` 属性，这实际上是给 `$data` 字段举了一个返回值的例子，这样不光可以把 Swagger 定义导入工具中做接口 `Mock` （example即是 `Mock` 接口的返回值）、在 Swagger UI 返回格式同样也一目了然： 

![][7]

Property定义了example之后在Swagger UI中的显示效果

但有时这些简单的整型或者字符串example就无法满足项目需求了，例如你可能会需要返回这样一个数据结构：

```json
    {
        "data": {
            "current_level": 1,
            "machine_detail": {
                "sn": "77777777",
                "mode": "extreme"
            }
            "records": [
                {
                    "time": "2017-03-28 00:00:00",
                    "message": "machine started"
                }
            ]
        }
    }
```
正常来讲，我们应该针对例子中的 `data` 、 `machine_detail` 和 `record` （ `records` 中的每一个元素）分别建立 `Definition` ，然后在定义中去写 **引用**（ `ref=` ）。但有时我们就是突然感觉很懒啊！又或者这些数据结构只有这一个接口使用，实在不值当单独定义几个 `Definition` 去实现啊（还是懒）！ 

那么怎么办呢？我简单总结了三个方法。

### 1. 直接把复杂结构写在 example 中 

如下：

```php
    <?php
    //...
    
    /**
     * @SWG\Property(
     *     example={"current_level": 1, [省略] "records": { { "time" [继续省略] } } },
     * )
     * @var object
     */
    public $data;
    
    //...
```

这种做法最大的坏处就是在注释中排版实在很痛苦……另外要注意得把JSON的数组括号（方括号）写成花括号（这是 `zircote/swagger-php` 的限制）。 

### 2. 使用JSON文件定义

我们可以单独把这一个 `$data` 的 `Definition` 写在一个JSON文件中，如 `data.json` ，然后在注释（`Annotation`）中写一个引用： 

```php
    <?php
    //...
    
    /**
     * @SWG\Property(
     *     ref="data.json",
     * )
     */
    public $data;
    
    //...
```

data.json 内容为： 

```json
    {
        "current_level": 1,
        "machine_detail": {
            "sn": "77777777",
            "mode": "extreme"
        }
        "records": [
            {
                "time": "2017-03-28 00:00:00",
                "message": "machine started"
            }
        ]
    }
```

这样 Swagger UI 在加载完之后还会去请求 `data.json` 来获取定义内容，最终效果是一样的。但坏处是一部分 `Definition` 被拆到了另一个文件，一个JSON搞不定，而且请求多了也会慢。 

### 3. 灵活使用 zircote/swagger-php 让我们先回头看看是怎么使用 zircote/swagger-php 返回JSON格式 Swagger 定义的： 

```php
    <?php
    
    // ...
    
        /**
         * @SWG\Swagger(
         *   @SWG\Info(
         *     title="我的`Swagger`API文档",
         *     version="1.0.0"
         *   )
         * )
         */
        public function getJSON()
        {
            $swagger = \Swagger\scan(app_path('Http/Controllers/'));
    
            return response()->json($swagger, 200); //注意这一句我们直接把$swagger传给了json()方法
        }
    
    // ...
```

注意示例中的 $swagger 对象。 

在调用 `\Swagger\scan()` 方法时，实际上是扫描你指定的所有目录和文件，将其中符合规则的 `Swagger Annotation` 解析出来，并转换为各种`Class`（在 `Swagger\Annotations` 名字空间下可以找到），最终这些 `Annotation` 对象都会被加载到 `$swagger` 对象里（`Swagger\Annotations\Swagger`）。 `$swagger` 是一个 `JsonSerializable` ，所以可以直接作为 `json_encode()` 函数的参数，在转换过程中，内部的各种定义对象就会被处理成一个可以JSON化的 `stdClass` 。 

那么我们其实可以把最终生成的数据拿到，然后把复杂的定义直接写成PHP数组，在最后和 `$swagger` 转换结果中的 `definitions` 进行合并就可以了。之前也试过直接手动新建 `Swagger\Annotations\Definition` 对象，然后合并到 `$swagger->definitions` 数组中，但发现这写起来远没有直接写数组的效率高。 

在项目中我将这些手写的 `Definition` 分文件存放，然后写了一个方法加载，最后合并到返回中。 

上面例子的文件内容可以写成这样：

```php
    <?php
    
    return [
        "current_level" => 1,
        "machine_detail" => [
            "sn" => "77777777",
            "mode" => "extreme"
        ]
        "records" => [
            [
                "time" => "2017-03-28 00 =>00 =>00",
                "message" => "machine started"
            ]
        ]
    ];
```

以及改过之后的 getJSON() 方法： 

```php
    <?php
    
    // ...
    
        /**
         * @SWG\Swagger(
         *   @SWG\Info(
         *     title="我的`Swagger`API文档",
         *     version="1.0.0"
         *   )
         * )
         */
        public function getJSON()
        {
            $swagger = \Swagger\scan(app_path('Http/Controllers/'));
    
            return response()->json(
            mergeWithRawDefinitions($swagger, loadRawDefinition(app_path('Swagger/Raw/'))), 
            200
        );
        }
    
    // ...
```

本文仅仅是抛砖引玉，如果你有更“懒”的方法，欢迎在评论中与大家分享！

</font>

[1]: http://www.jianshu.com/p/b59d305acd60
[4]: http://www.jianshu.com/p/6840514c4c8e
[5]: https://github.com/zircote/swagger-php
[6]: https://laravel.com/
[7]: http://img2.tuicool.com/eqQJR3v.png
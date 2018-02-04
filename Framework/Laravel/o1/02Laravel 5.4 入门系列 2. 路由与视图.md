## Laravel 5.4 入门系列 2. 路由与视图


# 2. 路由与视图

主要知识点：

* 从路由到视图的基本流程
* 数据传递

我们来看看第一讲最后的页面是怎么出来的。先来看看路由:

    // /routes/web.php
    Route::get('/', function () {
        return view('welcome');
    });

用大白话说，就是当我们访问网站根目录的时候，就返回 welcome 视图，我们修改下视图的内容：

    // /resources/views/welcome.blade.php
    
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Document</title>
    </head>
    <body>
        你好, Laravel
    </body>
    </html>

可以看到，定义返回的视图时，可以省略 `.blade.php` 后缀，该后缀代表使用 Laravel 的 Blade 模板功能，以后会介绍到。

现在，再次访问，变成了我们定义的内容。

### 数据传递

我们在视图中，也可以使用变量的形式。首先，在路由的函数中返回给视图 name 变量：

    // /routes/web.php
    Route::get('/', function () {
        $name = "Zen";
       return view('welcome',['name'=>$name]);
    });

也可以写成：

    // /routes/web.php
    Route::get('/', function () {
       $name = "Zen";
       return view('welcome')->with('name',$name);
    });

更为常见的写法是使用 php 提供的 compact 函数，compact 函数的作用是创建一个包含**变量名**和**变量的值**的数组，更加灵活和简便:

    // /routes/web.php
    Route::get('/', function () {
        $name = "Zen";
          $age = 99;
          $sex = "男";
          return view('welcome',compact('name','age','sex'));;
    });

在视图中显示该变量：

    // /resources/views/welcome.blade.php
    // 省略
    <body>
        你好, <?php echo $name?>
    </body>

虽然可以嵌入 PHP 语言来显示变量，不过 Laravel 提供了更为简洁的语法：

    // /resources/views/welcome.blade.php
    <body>
       你好, {{ $name }} ,你的年龄是 {{ $age }}, 你的性别是 {{ $sex }}
    </body>

或者：

    // /resources/views/welcome.blade.php
    <body>
       你好, {!! $name !!} ,你的年龄是 {!! $age !!}, 你的性别是 {!! $sex !!}
    </body>

这两者有什么区别呢，看下面的例子：

    $data = '<alert>123</alert>'

在视图中两者的输出：

* `{{ $data }}` 将会输出 `<alert>123</alert>`
* `{!! $data !!}` 将会输出警告框

也就是说：

* `{{ 变量名 }}` : 转义输出
* `{!! 变量名 !!}` ：原生输出，比如图片、链接、js 代码等

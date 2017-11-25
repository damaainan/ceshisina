## Laravel 5.4 入门系列 5. 博客通用布局


# 5. 博客的通用布局

## 初始化

### 创建控制器、模型、迁移

博客的核心是文章，可以先来实现和文章有关的功能，根据前几节的介绍可知，我们至少需要创建这几类：

* PostsController：控制器
* Post：模型
* create_posts_table：迁移任务

虽然可以分别创建，但是也可以批量进行创建，只需要在创建 Model 的时候指定即可：

    $ php artisan make:model Post -mc

不过创建的控制器为单数形式 PostController，我们需要手动将文件名和类名改成复数的。

### 创建表格

接下来是生成 posts 表，通过迁移来完成:

    // /database/migrations/2017_04_12_124622_create_posts_table.php
    
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->body('text');
            $table->timestamps();
        });
    }

一开始，为了便于操作，我们只包含了标题和内容两个基本字段。接下来执行迁移即可:

    $ php artisan migrate

## 通用布局

### 通用布局

首先是博客首页，定义路由：

    /routes/web.php
    
    Route::get('/posts','PostsController@index');

控制器:

    /app/Http/Controllers/PostsController.php
    
    public function index()
    {
        return view('posts.index');
    }

视图:

    /resources/views/posts/index.blade.php

    <!DOCTYPE html>
    <html lang="zh-cn">
    <head>
        <meta charset="UTF-8">
        <title>Document</title>
    </head>
    <body>
        博客首页
    </body>
    </html>
    
    

访问下网站根目录，显示「博客首页」，框架基本搭建完成了。现在，可以回顾下，我们之前所创建的视图，每个视图都包括了一些共同的东西，比如头部、尾部等，造成大量的重复工作。Laravel 提供了优雅的解决方案，首先，我们创建一个页面用于存放这些共同的东西：

    /resources/views/layouts/master.blade.php
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>博客首页</title>
    </head>
    <body>
        <div class="container">
            @yield('content')
        </div>
        
    </body>
    </html>

@yield 指令相当于一个占位符，这就意味着，其他页面只需要继承该页面，就可以共享该页面的内容，同时也可以定义具体的 content 以满足不同的显示：

    /resources/views/posts/index.blade.php
    
    @extends('layout')
    
    @section('content')
        
        博客首页
        
    @stop

子模板要声明继承于哪个模板，使用 @extends 指令。同时，使用@section 与 @stop 来定义各自的自己的 content 的内容。

@yield 也可以定义默认值，如果子模板不继承的话就会显示该默认值

    <title>@yield('title','默认首页')</title>

如果子模板既要继承父模板的内容，也要加载自己的内容，通用视图里需要使用以下语法:

    @section('sidebar')
        这是侧边栏
    @show

继承时使用 @parent 代表加载父模板的内容：

    @section('sidebar')
        @parent
        自定义内容
    @endsection

### 使用 Bootstrap Blog 模板

接下来，我们使用 [Bootstrap Blog][0] 模板来创建博客的前台，该模板的效果如图所示:

![][1]

我们根据上图划分来对源代码进行分割。首先是通用布局：

    /resources/views/layouts/master.blade.php

    <!DOCTYPE html>
    <html lang="zh-cn">
      <head>
        <meta charset="utf-8">
        <link rel="icon" href="http://v4-alpha.getbootstrap.com/favicon.ico">

        <title>Blog Template for Bootstrap</title>

        <link href="http://v4-alpha.getbootstrap.com/dist/css/bootstrap.min.css" rel="stylesheet">

        <link href="../css/blog.css" rel="stylesheet">

      </head>

      <body>
      
        @include('layouts.nav')
        @include('layouts.header');
      
        <div class="container">
          <div class="row">
            @yield('content')
            @include('layouts.siderbar')
          </div>
        </div>
        

        @include('layouts.footer')

      </body>
    </html>
    

通用布局里面除了使用 @yield 之外，还使用了 @include，用于加载其他模板。

导航：

    /resources/views/layouts/nav.blade.php
    <div class="blog-masthead">
      <div class="container">
        <nav class="nav blog-nav">
          <a class="nav-link active" href="#">Home</a>
          <a class="nav-link" href="#">New features</a>
          <a class="nav-link" href="#">Press</a>
          <a class="nav-link" href="#">New hires</a>
          <a class="nav-link" href="#">About</a>
        </nav>
      </div>
    </div>
    

头部：

    /resources/views/layouts/header.blade.php

    <div class="blog-header">
      <div class="container">
        <h1 class="blog-title">The Bootstrap Blog</h1>
        <p class="lead blog-description">An example blog template built with Bootstrap.</p>
      </div>
    </div>

侧边栏:

    /resources/views/layouts/siderbar.blade.php
    <div class="col-sm-3 col-sm-offset-1 blog-sidebar">
      <div class="sidebar-module sidebar-module-inset">
        <h4>About</h4>
        <p>Etiam porta .</p>
      </div>
      <div class="sidebar-module">
        <h4>Archives</h4>
        <ol class="list-unstyled">
          <li><a href="#">March 2014</a></li>
          <li><a href="#">February 2014</a></li>
        </ol>
      </div>
      <div class="sidebar-module">
        <h4>Elsewhere</h4>
        <ol class="list-unstyled">
          <li><a href="#">GitHub</a></li>
          <li><a href="#">Twitter</a></li>
          <li><a href="#">Facebook</a></li>
        </ol>
      </div>
    </div><!-- /.blog-sidebar -->
    

底部:

    /resources/views/layouts/footer.blade.php

    <footer class="blog-footer">
        <p>Blog template built for <a href="http://getbootstrap.com">Bootstrap</a> by <a href="https://twitter.com/mdo">@mdo</a>.</p>
        <p>
            <a href="#">Back to top</a>
        </p>
    </footer>
    
    

最后是博客的样式:

    /public/css/blog.css
    
    /*
     * Globals
     */
    
    @media (min-width: 48em) {
      html {
        font-size: 18px;
      }
    }
    
    body {
      font-family: Georgia, "Times New Roman", Times, serif;
      color: #555;
    }
    
    h1, .h1,
    h2, .h2,
    h3, .h3,
    h4, .h4,
    h5, .h5,
    h6, .h6 {
      font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
      font-weight: normal;
      color: #333;
    }
    
    
    /*
     * Override Bootstrap's default container.
     */
    
    .container {
      max-width: 60rem;
    }
    
    
    /*
     * Masthead for nav
     */
    
    .blog-masthead {
      margin-bottom: 3rem;
      background-color: #428bca;
      -webkit-box-shadow: inset 0 -.1rem .25rem rgba(0,0,0,.1);
              box-shadow: inset 0 -.1rem .25rem rgba(0,0,0,.1);
    }
    
    /* Nav links */
    .nav-link {
      position: relative;
      padding: 1rem;
      font-weight: 500;
      color: #cdddeb;
    }
    .nav-link:hover,
    .nav-link:focus {
      color: #fff;
      background-color: transparent;
    }
    
    /* Active state gets a caret at the bottom */
    .nav-link.active {
      color: #fff;
    }
    .nav-link.active:after {
      position: absolute;
      bottom: 0;
      left: 50%;
      width: 0;
      height: 0;
      margin-left: -.3rem;
      vertical-align: middle;
      content: "";
      border-right: .3rem solid transparent;
      border-bottom: .3rem solid;
      border-left: .3rem solid transparent;
    }
    
    
    /*
     * Blog name and description
     */
    
    .blog-header {
      padding-bottom: 1.25rem;
      margin-bottom: 2rem;
      border-bottom: .05rem solid #eee;
    }
    .blog-title {
      margin-bottom: 0;
      font-size: 2rem;
      font-weight: normal;
    }
    .blog-description {
      font-size: 1.1rem;
      color: #999;
    }
    
    @media (min-width: 40em) {
      .blog-title {
        font-size: 3.5rem;
      }
    }
    
    
    /*
     * Main column and sidebar layout
     */
    
    /* Sidebar modules for boxing content */
    .sidebar-module {
      padding: 1rem;
      /*margin: 0 -1rem 1rem;*/
    }
    .sidebar-module-inset {
      padding: 1rem;
      background-color: #f5f5f5;
      border-radius: .25rem;
    }
    .sidebar-module-inset p:last-child,
    .sidebar-module-inset ul:last-child,
    .sidebar-module-inset ol:last-child {
      margin-bottom: 0;
    }
    
    
    /* Pagination */
    .blog-pagination {
      margin-bottom: 4rem;
    }
    .blog-pagination > .btn {
      border-radius: 2rem;
    }
    
    
    /*
     * Blog posts
     */
    
    .blog-post {
      margin-bottom: 4rem;
    }
    .blog-post-title {
      margin-bottom: .25rem;
      font-size: 2.5rem;
    }
    .blog-post-meta {
      margin-bottom: 1.25rem;
      color: #999;
    }
    
    
    /*
     * Footer
     */
    
    .blog-footer {
      padding: 2.5rem 0;
      color: #999;
      text-align: center;
      background-color: #f9f9f9;
      border-top: .05rem solid #e5e5e5;
    }
    .blog-footer p:last-child {
      margin-bottom: 0;
    }

现在，访问 /posts，就可以看到整个页面效果了 :)

- - -

* [Blog Template for Bootstrap][0]
* [Laravel 的 Blade 模板引擎 | Laravel 5.4 中文文档][2]

[0]: https://v4-alpha.getbootstrap.com/examples/blog/
[1]: /img/bVMaai?w=2547&h=1796
[2]: http://d.laravel-china.org/docs/5.4/blade
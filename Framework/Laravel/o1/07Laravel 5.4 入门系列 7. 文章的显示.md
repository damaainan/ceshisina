## Laravel 5.4 入门系列 7. 文章的显示

文章的显示功能比较简单，分为两部分:

* 文章列表
* 具体的某篇文章

## 显示文章列表

路由之前已经定义好：

    Route::get('/posts','PostsController@index');

控制器:

    public function index()
    {    
        $posts = Post::latest()->get();
        return view('posts.index',compact('posts'));
    }

latest() 方法等价于:

    $post = Post::orderBy('created_at','desc')->get();

最后是视图:

    /resources/views/posts/index.blade.php
    @extends('layouts.master')
    
    @section('content')
    
        <div class="col-sm-8 blog-main">
          
          @foreach ($posts as $post)
              <div class="blog-post">
                <h2 class="blog-post-title"><a href="{{ action('PostsController@show',[$post->id]) }}">{{ $post->title }}</a></h2>
                <p class="blog-post-meta">{{ $post->created_at->toFormattedDateString() }} by <a href="/about">Zen</a></p>
                <p>{{ str_limit($post->body,20)}}</p>
              </div><!-- /.blog-post -->
          @endforeach
          
          <nav class="blog-pagination">
            <a class="btn btn-outline-primary" href="#">Older</a>
            <a class="btn btn-outline-secondary disabled" href="#">Newer</a>
          </nav>
          str_limit
        </div>
    
    @endsection

created_at 字段是由迁移任务中的 timestamps() 方法生成的，而且生成的时间是 Carbon 格式，这就意味着，你在读取或者写入的时候，Laravel 都会自动帮你进行维护。因此，created_at 也是 Carbon 的一个实例，可以使用 Carbon 包提供的各种方法进行进一步操作。

str_limit() 为 Laravel 的辅助方法，用于截取字符串的前 n 个字符，然后返回前 n 个字符加 ... 的格式。

## 显示某篇文章

显示某篇文章的比较简单，路由：

    Route::get('/posts/create','PostsController@create');
    Route::get('/post/{post}','PostsController@show');

注意 show 要放在 create 下面，假如这样:

    Route::get('/post/{post}','PostsController@show');
    Route::get('/posts/create','PostsController@create');

那么，我们访问 posts/create 的时候，create 会被当成是 show 的查询参数。

控制器:

    public function show(Post $post)
    {
        return view('posts.show',compact('post'));
    }

视图:

    /resources/views/posts/show.blade.php
    @extends('layouts.master')
    
    @section('content')
        <div class="col-sm-8 blog-main">
            <div class="blog-post">
                <h2 class="blog-post-title">{{ $post->title }}</h2>
                <p class="blog-post-meta">{{ $post->created_at->toFormattedDateString() }} by <a href="#">Zen</a></p>
                <p>{{$post->body}}</p>
             </div>
        </div>
    @endsection

- - -

* [Carbon - A simple PHP API extension for DateTime.][0]

[0]: http://carbon.nesbot.com/docs/#api-formatting
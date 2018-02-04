## Laravel 5.4 入门系列 9. 注册与登录，用户关联

本节将实现文章、评论与用户关联的功能。

## 关系定义

首先修改 posts 与 comments 表，增加 user_id 字段

    /database/migrations/2017_04_12_124622_create_posts_table.php
    /database/migrations/2017_04_15_062905_create_comments_table.php
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
             
               // 增加
            $table->integer('user_id')->unsigned();
         
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

全部回滚并重新执行迁移:

    $ php artisan migrate:refresh

添加用户表与文章表、评论表的一对多关系:

    /app/User.php
    public function posts()
    {
        return $this->hasMany(\App\Post::class);
    }
    
    public function comments()
    {
        return $this->hasMany(\App\Comment::class);
    }

添加文章、评论表与用户表的多对一关系:

    /app/Comment.php
    /app/Post.php
    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }

同时，评论表的 $fillable 字段增加 user_id。

## 注册

首先，定义处理注册相关业务的控制器:

    $ php artisan make:controller RegistrationController

定义路由响应注册请求:

    Route::get('/register','RegistrationController@create');

定义方法，返回注册页面视图:

    public function create()
    {
        return view('registration.create');
    }

创建注册页面:

    /resources/views/registration/create.blade.php
    @extends('layouts.master')
    
    @section('content')
        
        <div class="col-sm-8 blog-main">
            
            <form method="post" for="/register"> 
                {{ csrf_field() }}
                <fieldset class="form-group">
                    <label for="name">用户名:</label>
                    <input type="text" name="name" id="name" class="form-control" required>
                </fieldset>
                <fieldset class="form-group">
                    <label for="email">邮箱</label>
                    <input type="email" class="form-control" name="email" id="email" required>
                    <small class="text-muted">我们不会与别人分享您的邮箱</small>
                </fieldset>
                <fieldset class="form-group">
                    <label for="password">密码:</label>
                    <input type="password" name="password" class="form-control" id="password" >
                </fieldset>    
                <fieldset class="form-group">
                    <label for="password_confirmation">再次输入密码:</label>
                    <input type="password" name="password_confirmation" class="form-control" id="password_confirmation">
                </fieldset>    
                <button type="submit" class="btn btn-primary">提交</button>
            </form>
    
        </div>
    
    @endsection

定义路由响应注册提交:

    Route::post('/register','RegistrationController@store');

定义方法处理注册提交：

    /app/Http/Controllers/RegistrationController.php
    use App\User;
    public function store()
    {    
        $this->validate(request(),[
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed',
        ]);
    
        $user = User::create(request(['name','password','email']));
    
        auth()->login($user);
    
        return redirect()->home();
    }

该方法包括了四部分：

1. 验证字段，其中 password 使用了 confirmed 验证规则，会自动去匹配 xxx 与 xxx_confirmation 是否一致，因此之前的视图要按照规范命名好。
1. 创建用户
1. 登录该用户
1. 返回名字为「home」的路由

其中，我们需要为路由命名，以匹配第四步:

    Route::get('/posts','PostsController@index')->name('home');

虽然完成了注册功能，但是我们保存密码使用的明文，我们可以定义一个修改器，让每次保存密码时都自动加密:

    /app/User.php
    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = bcrypt($password);
    }

## 登录

创建控制器，处理用户登录业务:

    $ php artisan make:controller SessionsController

用户访问 /login 时，路由分发该请求:

    Route::get('/login','SessionsController@create');

create 方法返回用户登录页面视图:

    /resources/views/sessions/create.blade.php
    @extends('layouts.master')
    
    @section('content')
        
        <div class="col-sm-8 blog-main">
            
            <form method="post" for="/login"> 
                {{ csrf_field() }}
                <fieldset class="form-group">
                    <label for="email">邮箱</label>
                    <input type="email" class="form-control" name="email" id="email" required>
                </fieldset>
                <fieldset class="form-group">
                    <label for="password">密码:</label>
                    <input type="password" name="password" class="form-control" id="password" >
                </fieldset>    
                <button type="submit" class="btn btn-primary">登录</button>
            </form>
    
        </div>
    
    @endsection

用户点击登录后，路由分发该请求：

    Route::post('/login','SessionsController@store');

最后是控制器对登录行为进行处理:

    /app/Http/Controllers/SessionsController.php
    public function store()
    {    
    
        if (!auth()->attempt(request(['email', 'password']))) {
            return back()->withErrors([
                'messages' => '请确保邮箱和密码正确!'
            ]);
        }
    
        return redirect()->home();
    } 

我们使用了 Auth 类提供的 attempt() 进行验证，只需要传入 email 和 password 即可，attempt 方法会对密码经过加密后与数据库进行比较，若匹配则  
开启一个通过认证的 session 给用户。同时，我们还自定义了返回的错误信息。

## 登出

登出的实现比较简单，首先是路由：

    Route::get('/logout','SessionsController@destroy');

控制器：

    public function destroy()
    {
        auth()->logout();
    
        return redirect()->home();
    }

最后，我们优化下导航让，令其根据用户登录信息来显示不同的设置项:

    <div class="blog-masthead">
      <div class="container">
        <nav class="nav blog-nav">
          <a class="nav-link active" href="#">Home</a>
          <a class="nav-link" href="#">New features</a>
          <a class="nav-link" href="#">Press</a>
          <a class="nav-link" href="#">New hires</a>
          <a class="nav-link" href="#">About</a>
          
          <ul class="nav nav-tabs ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">设置</a>
                <div class="dropdown-menu">
                    @if (Auth::check())
                        <a class="dropdown-item" href="#">{{ Auth::user()->name }}</a>
                        <a class="dropdown-item" href="/logout">登出</a>
                    @else
                        <a class="dropdown-item" href="/login">登录</a>
                        <a class="dropdown-item" href="/register">注册</a>
                    @endif

                </div>
            </li>
        </ul>

        </nav>
      </div>
    </div>

    <script type="text/javascript">
        $('.dropdown-toggle').dropdown()
    </script>

注意，如果要让下拉框生效，需要引入相关的 js：

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <script src="https://cdn.rawgit.com/twbs/bootstrap/v4-dev/dist/js/bootstrap.js"></script>

## 权限控制

实现了登录与登出功能，就可以对用户行为进行权限控制了。

首先是文章的权限控制，对于「未登录」的用户，只可以阅读文章，因此可以直接使用 Laravel 提供的中间件来实现:

    /app/Http/Controllers/PostsController.php
    public function __construct()
    {
        $this->middleware('auth')->except(['index','show']);
    }

意思是只有授权的用户才能够访问其他请求，除了 index 与 show 外。

然后是用户的权限控制:

    /app/Http/Controllers/SessionsController.php
    public function __construct()
    {
        $this->middleware('guest')->except(['destroy']);
    }

意思是只有游客才能访问其他请求，除了 destroy。

## 完善文章与评论的创建

最后，完善文章与评论的创建功能，绑定用户 id。首先是文章的创建:

    /app/Http/Controllers/PostsController.php
    public function store(Request $request)
    {
        $this->validate(request(), [
            'title' => 'required|unique:posts|max:255',
            'body' => 'required|min:5',
        ]);
    
        $post = new Post(request(['title', 'body']));
    
        auth()->user()->publishPost($post);
    
        return redirect("posts");
    }

创建文章直接使用关系模型：

    /app/User.php
    public function publishPost(Post $post)
    {
        $this->posts()->save($post);
    }

然后是评论的创建:

    public function store(Post $post)
    {    
        $this->validate(request(),[
            'body' => 'required|min:5'
            ]);
    
        $post->addComment(new Comment([
            'user_id' => auth()->user()->id,
            'body'       => request('body'),
    
            ]));
        return back();
    }

同样使用关系模型：

    /app/Post.php
    public function addComment(Comment $comment)
    {    
        $this->comments()->save($comment);
    }

最后，是一些视图的更新:

文章列表中，绑定作者:

    /resources/views/posts/index.blade.php
     <p class="blog-post-meta">{{ $post->created_at->toFormattedDateString() }} by <a href="#">{{$post->user->name}}</a></p>

具体文章与评论显示时，也绑定作者:

    /resources/views/posts/show.blade.php
    <div class="blog-post">
        <h2 class="blog-post-title">{{ $post->title }}</h2>
        <p class="blog-post-meta">{{ $post->created_at->toFormattedDateString() }} by <a href="#">{{ $post->user->name }}</a></p>
        <p>{{$post->body}}</p>
     </div>
    
     @foreach ($post->comments as $comment)
        <div class="card">
            <div class="card-header">
                {{$comment->created_at->diffForHumans() }}
            </div>
            <div class="card-block">
                <p class="card-text">{{ $comment->body }}</p>
                 <p class="card-text"><small class="text-muted">by {{$comment->user->name }}</small></p>
             </div>
        </div>
    
        <br>
    @endforeach

- - -

* [Eloquent: 修改器 | Laravel 5.4 中文文档][0]
* [Laravel 的用户认证系统 | Laravel 5.4 中文文档][1]
* [Navs · Bootstrap][2]

[0]: http://d.laravel-china.org/docs/5.4/eloquent-mutators#defining-a-mutator
[1]: http://d.laravel-china.org/docs/5.4/authentication#authenticating-users
[2]: https://v4.bootcss.com/components/navs/
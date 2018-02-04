## Laravel 5.4 入门系列 6. 文章的创建

## 基本功能

创建文章的第一步是用户发请求，然后返回创建文章的页面。

路由：处理用户「创建文章」的请求

    /routes/web.php
    Route::get('/posts/create','PostsController@create');

控制器: 返回文章编辑视图

    /app/Http/Controllers/PostsController.php
    public function create()
    {
        return view('posts.create');
    }

视图: 使用 Bootstrap 组件来创建文章编辑页面

    /resources/views/posts/create.blade.php
    @extends('layouts.master')
    
    @section('content')
        <div class="col-sm-8 blog-main">
            <h1>创建文章</h1>
            <hr>
            <form action="{{ action('PostsController@store') }}" method="post">
                <div class="form-group">
                    <label for="title">标题</label>
                    <input type="text" id="title" name="title" class="form-control">
                </div>
                <div class="form-group">
                    <label for="body">内容</label>
                    <textarea class="form-control" id="body" name="body" rows="10"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">提交</button>
            </form>
        </div>
    @endsection

action 方法根据控制器来生成对应的路由，也可以用之前学过的 url 方法：

    <form action="{{ url('posts') }}" method="post">

生成的 url 如下:

    <form action="http://localhost:8000/posts" method="post">

用户提交之后，需要在路由中处理用户提交的数据的请求:

    /routes/web.php
    Route::post('/posts','PostsController@store');

最后是保存文章实现，我们使用 request() 方法获取请求字段，保存完之后跳转到博客首页：

    use App\Post;
    public function store()
    {
        $post = new Post();
        $post->title = request('title');
        $post->body = request('body');
        $post->save();
        
        return redirect('posts');
    }

现在，访问 posts/create，创建文章后点击提交，查看下效果。实际上，会报错:

> TokenMismatchException in VerifyCsrfToken.php line 68

## 添加 CSRF 保护

虽然我们完成了基本功能，但是提交请求的时候还是会报错，其实这是防止 CSRF 攻击。

举一个简单的例子，你登录一个投票网站，通过发送该请求向编号为 25 的人投票:

    http://example.com/vote/25

CSRF 如何进行攻击呢，顾名思义，CSRF 是 Cross-site request forgery 的缩写，即跨站请求伪造，因此需要具备两个条件：

1. 跨站。首先，我登录了该投票网站，网站保存了我的登录信息，然后我又登录了另外一个网站；
1. 伪造请求。在另外一个网站的界面中，可能包含了类似 <img src="http://example.com/vote/30" /> 这样的 HTML 代码。由于投票网站无法区分你在哪里发送的请求，因此，就等于你向 30 号选手进行了投票；

解决方式也很简单：

1. 登录 A 网站的时候，生成一条 token
1. 提交请求的时候，该 token 也跟着提交
1. 两者进行验证即可

第一步，Laravel 已经帮我们实现了:

    /vendor/laravel/framework/src/Illuminate/Session/Store.php
    public function start()
    {
        $this->loadSession();
    
        if (! $this->has('_token')) {
            $this->regenerateToken();
        }
    
        return $this->started = true;
    }

第二步，Laravel 也帮我们封装好了，直接使用 csrf_field() 函数即可，我们在文章编辑的表单中加入即可：

    /resources/views/posts/create.blade.php
    <h1>创建文章</h1>
        <hr>
        <form action="{{ url('posts') }}" method="post">
            {{ csrf_field() }}
            <div class="form-group">
                <label for="title">标题</label>
                <input type="text" id="title" name="title" class="form-control">
            </div>

可以看看该函数长什么样:

    function csrf_field()
    {
        return new HtmlString('<input type="hidden" name="_token" value="'.csrf_token().'">');
    }

因此，我们也可以写成:

    <input type="hidden" name="_token" value="{{ csrf_token() }}">

最后一步，Laravel 通过中间件来进行自动检验:

    public function handle($request, Closure $next)
    {
        if (
            $this->isReading($request) || 
            $this->runningUnitTests() || 
            $this->inExceptArray($request) ||
            $this->tokensMatch($request)
        ) {
            return $this->addCookieToResponse($request, $next($request));
        }
    
        throw new TokenMismatchException;
    }

简单解读下该中间件的处理流程：

1. 判断请求类型，如果是 GET、HEAD、OPTIONS 等不会更改资源的请求就通过；
1. 如果处于测试环境下就通过；
1. $except 数组内添加的 url 默认通过；
1. tokens 匹配也通过；

通过之后，就会添加名为 XSRF-TOKEN 的cookie；如果没通过，就抛出异常，也就是我们上一节显示的错误信息了。

## 批量创建文章

刚才我们采用是 save() 方法来保存文章，实际上，也可以使用 create() 方法，该方法允许一次性插入多条数据，因此必须指定允许批量插入的字段：

    /app/Post.php
    class Post extends Model
    {
        protected $fillable = [
            'title',
            'body',
        ];
    }

store() 方法可以写成:

    /app/Http/Controllers/PostsController.php
    public function store(Request $request)
    {
        Post::create([
           'title' => request('title'),
           'body'  => request('body')
       ]);
    
        return redirect("posts");
    }

或者传入数组给 request():

    /app/Http/Controllers/PostsController.php
    public function store(Request $request)
    {
        Post::create(request(['title','body']));
    
        return redirect("posts");
    }

## 添加字段验证

接下来进一步完善创建文章的功能，即字段验证。可以直接使用 validate 方法：

    /app/Http/Controllers/PostsController.php
    public function store(Request $request)
    {
        $this->validate(request(), [
            'title' => 'required|unique:posts|max:255',
            'body' => 'required|min:5',
        ]);
    
        Post::create(request(['title', 'body']));
    
        return redirect("posts");
    }

我们为 title 添加了非空、唯一性以及最大字符的验证规则，对 body 字段添加了非空和最小字符的规则。

假如违反了规则，错误信息 $errors 会自动被保存在闪存的 Session 中，即只对下一次请求生效。并且，我们不需要将其返回给视图，Laravel 帮我们做了处理，我们所有的视图都可以获取到 $errors 变量，可以令其显示出来:

    /resources/views/layouts/master.blade.php
    @include('layouts.errors'); 
    @include('layouts.footer')

具体错误消息:

    /resources/views/layouts/errors.blade.php
    @if (count($errors))
        <div class="form-group">
            <div class="alert alert-warning" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

- - -

* [Forms · Bootstrap][0]
* [CSRF (Cross-site request forgery) attack example and prevention in PHP - Stack Overflow][1]

[0]: https://v4.bootcss.com/components/forms/#textual-inputs
[1]: http://stackoverflow.com/questions/2526522/csrf-cross-site-request-forgery-attack-example-and-prevention-in-php
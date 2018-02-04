## Laravel 5.4 入门系列 10.文章归档

首先，要实现的是按照日期来统计文章，原始的 SQL 如下：

    select 
        year(created_at)  year,
        monthname(created_at) month,
        count(*) published
    from posts
    group by year, month
    order by min(created_at) desc;

将其转化为 Eloquent Model:

    /app/Http/Controllers/PostsController.php
    use App\Post;
    public function index()
    {    
    
        $archives = Post::selectRaw('year(created_at)  year, monthname(created_at) month, count(*) published')
                        ->groupBy('year','month')
                        ->orderByRaw('min(created_at) desc')
                        ->get();
    
        $posts = Post::latest()->get();
    
        return view('posts.index',compact('posts','archives'));
    }

视图中显示对应的文章归档：

    /resources/views/layouts/siderbar.blade.php
      <div class="sidebar-module">
        <h4>Archives</h4>
        <ol class="list-unstyled">
          @foreach ($archives as $archive)
            <li><a href="/posts/?month={{$archive->month}}&&year={{$archive->year}}">{{$archive->month}} {{$archive->year}}</a></li>
          @endforeach
        </ol>
      </div>

用户点击某个月份的时候，向后台传入 month 和 year 参数，因此 index 方法还需要根据参数类型来进行选择:

    /app/Http/Controllers/PostsController.php
    use Carbon\Carbon;
    public function index()
    {    
    
        $archives = Post::selectRaw('year(created_at)  year, monthname(created_at) month, count(*) published')->groupBy('year','month')->orderByRaw('min(created_at) desc')->get();
    
    
        $posts = Post::latest();
    
        if ($month = request('month')) {
            $posts->whereMonth('created_at',Carbon::parse($month)->month);
        }
    
        if ($year = request('year')) {
            $posts->whereYear('created_at',$year);
        }
    
        $posts = $posts->get();
    
        return view('posts.index',compact('posts','archives'));
    }

这里使用了 Laravel 提供的 whereDate 系列方法，同时，月份用 Carbon 进行转换。

将上述的一系列查询进行封装：

    /app/Http/Controllers/PostsController.php
    public function index()
    {    
    
        $archives = Post::archives();
        $posts = Post::latest()
                    ->filter(request(['year','month']))
                    ->get();
    
        return view('posts.index',compact('posts','archives'));
    }

模型:

    /app/Post.php
    use Carbon\Carbon;
    public function scopeFilter($query, $value)
    {
        if ($month = $value['month']) {
            $query->whereMonth('created_at', Carbon::parse($month)->month);
        }
    
        if ($year = $value['year']) {
            $query->whereYear('created_at', $year);
        }
    }
    
    public static function archives()
    {
        return static::selectRaw('year(created_at)  year, monthname(created_at) month, count(*) published')
                    ->groupBy('year','month')
                    ->orderByRaw('min(created_at) desc')
                    ->get();
    }

到了这一步，我们基本上实现了文章归档的功能。但是有一个问题，文章归档实际上包括在通用视图中，这就意味着，网站的所有请求都需要返回 $archives，否则就会报错。一种做法就是在不同方法下都调用 archives() 方法来返回数据。当然，更为简单的方法就是使用「视图共享数据」功能。操作如下:

    /app/Providers/AppServiceProvider.php
    public function boot()
    {
        Schema::defaultStringLength(191);
        view()->composer('layouts.siderbar',function($view){
    
            $view->with('archives',\App\Post::archives());
    
        });
    }

该服务提供者包含两个方法：register()，用来绑定 IOC 容器（先忽略），绑定完之后，我们就可以在 boot 里面定义我们想要实现的功能了，在该例中，我们注册了 layouts.siderbar 视图，并传递给视图 archives 变量。

- - -

* [Laravel 数据库之：数据库请求构建器 | Laravel 5.4 中文文档][0]
* [Carbon - A simple PHP API extension for DateTime.][1]
* [Laravel 的视图功能 | Laravel 5.4 中文文档][2]

[0]: http://d.laravel-china.org/docs/5.4/queries#where-clauses
[1]: http://carbon.nesbot.com/docs/
[2]: http://d.laravel-china.org/docs/5.4/views#sharing-data-with-all-views
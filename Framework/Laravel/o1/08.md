## Laravel 5.4 入门系列 8. 文章评论

本节将学习 Eloquent Relations，表与表之间存在着多种关系，举例如下：

* 一对一：文章与作者
* 一对多：文章与评论
* 多对多：标签与文章

## 文章与评论的一对多关系

一对多关系，主要理解两点：

* 如何实现一对多关系
* 实现了之后能给开发带来什么便利

### 一对多关系实现

首先创建 comments 相关:

    $ php artisan make:model Comment -mc

同样，为了遵循以前的约定，把生成的 CommentController 改成复数形式。

编辑迁移文件：

    /database/migrations/2017_04_15_062905_create_comments_table.php
    public function up()
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('post_id');
            $table->string('body');
            $table->timestamps();
    
            $table->foreign('post_id')
              ->references('id')
              ->on('posts')
              ->onDelete('cascade');
        });
    }

我们为 comments 表格添加了 post_id 外键，同时生定义了 onDelete cascade 约束，该约束允许删除父表（文章）的时候，自动删除关联的子表（评论）。

最后，执行迁移：

    $ php artisan migrate

接下来，我们就可以定义文章与评论的一对多关系了:

    /app/Post.php
    public function comments()
    {
        return $this->hasMany(\App\Comment::class);
    }

在 comments 方法中，我们并没有指定对应的外键，这是因为我们在定义迁移的时候，严格按照约定 (posts_id)，因此 Laravel 会去自动寻找对应的外键。::class 方法也可以写成：

    return $this->hasMany('\App\Comment');

### 一对多关系的作用

定义好了文章与评论的一对多关系之后，我们就可以方便的进行相关操作了，先来练习下：

    $ php artisan tinker

为了方便操作，我们先允许评论内容 body 字段批量赋值：

    /app/Comment.php
    protected $fillable = ['body'];

首先是根据文章来直接创建评论:

    >>> $post = \App\Post::first()
    >>> $post->comments()->create(['body'=>'评论1'])
    >>> $post->comments()->create(['body'=>'评论2'])

可以发现，我们可以根据文章的实例来直接创建对应的评论，而且不需要去确定评论post_id 字段。

创建好之后，我们可以方便的获取文章的评论:

    >>> $post->comments;

我们传入的是 comments 属性而不是方法，Laravel 会返回该文章对应评论的集合，比如我们可以将其转化为其他格式:

    >>> $post->comments->toJson()

当然了，也可以使用 comments() 方法返回 Eloquent 模型，再进行进一步操作:

    >>> $post->comments()->get()->toArray()
    >>> $post->comments()->pluck('body')

同样的，如果我们要根据评论来操作相关文章，我们需要先定义评论与文章的多对一关系:

    /app/Comment.php
    public function post()
    {
        return $this->belongsTo(\App\Post::class);
    }

重启 tinker：

    >>> $comment = \App\Comment::first()
    >>> $comment->post;
    >>> $comment->post->title;

## 评论的显示与创建

### 显示评论

显示评论，比较简单，直接使用 `Bootstrap 的 card 模板即可：

    /resources/views/posts/show.blade.php
    <div class="blog-post">
        <h2 class="blog-post-title">{{ $post->title }}</h2>
        <p class="blog-post-meta">{{ $post->created_at->toFormattedDateString() }} by <a href="#">Zen</a></p>
        <p>{{$post->body}}</p>
     </div>
    
     @foreach ($post->comments as $comment)
        <div class="card">
            <div class="card-header">
                {{$comment->created_at->diffForHumans() }}
            </div>
            <div class="card-block">
                <p class="card-text">{{ $comment->body }}</p>
            </div>
        </div>
        <br>
    @endforeach

同时，我们使用了 Carbon 的 diffForHumans 方法，用来显示「距离现在多久」。

### 创建评论

最后是评论的创建，首先是视图，放在显示评论下方即可：

    /resources/views/posts/show.blade.php
    <div class="card">
        <div class="card-header">
            添加评论
        </div>
        <div class="card-block">
            <form method="post" action="/posts/{{$post->id}}/comments">
                {{ csrf_field() }}
                <fieldset class="form-group">
                    <textarea class="form-control" id="body" rows="3" name="body" required placeholder="请输入评论内容"></textarea>
                </fieldset>
                <button type="submit" class="btn btn-primary">提交</button>
            </form>
        </div>
    </div>

对应的路由:

    Route::post('/posts/{post}/comments','CommentsController@store');

最后是控制器:

    <?php
    
    use App\Post;
    
    class CommentsController extends Controller
    {
        public function store(Post $post)
        {    
            $this->validate(request(),[
                'body' => 'required|min:5'
                ]);
                
           $post->addComment(request('body'));
           return back();
        }
    }

首先，依旧是使用路由模型的自动绑定功能，然后将添加评论的方法进行封装，方便重复使用：

    /app/Post.php
    public function addComment($body)
    {
        $this->comments()->create(compact('body'));
    }

最后使用辅助方法 back()，该方法生成一个重定向响应让用户返回到之前的位置。

- - -

* [Eloquent: 关联 | Laravel 5.4 中文文档][0]
* [Cards · Bootstrap][1]
* [Laravel 的辅助函数列表 | Laravel 5.4 中文文档][2]

[0]: http://d.laravel-china.org/docs/5.4/eloquent-relationships#one-to-many
[1]: https://v4.bootcss.com/components/card/#example
[2]: http://d.laravel-china.org/docs/5.4/helpers#method-back
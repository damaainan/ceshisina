# Laravel5 的一些基础用法

 时间 2017-10-13 09:35:09  

原文[http://blog.duicode.com/2464.html][1]


这里简单介绍一些Laravel5的一些基础用法，话不多说，进入正题。

### 1.安装

直接使用composer安装，这个也是我比较推荐的，但是要么翻墙，要么设置国内镜像，这个在之前我都说过了

```
    composer create-project --prefer-dist laravel/laravel blog
```

### 2.数据迁移

这是Laravel的方便之处，多人协作再也不用被表搞的晕头转向了

```
    //创建一个迁移(表名)
    php artisan make:migration create_articles_table --create=articles
     
    //执行迁移
    php artisan migrate
     
    //撤销
    php artisan migrate:rollback
     
    //为表添加字段
    php artisan make:migration add_intro_column_to_articles --table=articles
```

另外如何添加字段之前也说过了，这里只举个例子(一定要注意在down中删除)

```
    public function up()
        {
            Schema::table('articles', function (Blueprint $table) {
                $table->string('intro');
            });
        }
     
        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down()
        {
            Schema::table('articles', function (Blueprint $table) {
                //需要引入doctrine/dbal
                $table->dropColumn('intro');
            });
        }
```

### 3.创建控制器

使用命令创建控制器，控制器主要是处理一些逻辑操作，存在Http目录下

```
    php artisan make:controller ArticleController
```

可以再控制器中添加方法

```
    public function index(){
            $articles = Article::all();
            return view('articles.index',compact('articles'));
    }
     
     public function show($id){
            $article=Article::findOrFail($id);
            return view('articles.show',compact('article'));
     }
     
     public function update(Request $request, $id){
            $article=Article::findOrFail($id);
            $article->update($request->all());
            return redirect('/articles');
     }
```

### 4.创建模型ORM

使用命令创建模型，Laravel中的模型跟数据库表紧密结合，做一些数据的处理

```
    php artisan make:model Article
```

模型文件存在app目录下，这里看个例子

```
    class Article extends Model
    {
        //可以填充
        protected $fillable = ['title','content','published_at','intro','user_id'];
        //这样就可以将published_at作为Carbon对象来操作了
        protected $dates = ['published_at'];
     
        //字段预处理，用Carbon来处理时间
        public function setPublishedAtAttribute($date){
            $this->attributes['published_at'] = Carbon::createFromFormat('Y-m-d',$date);
        }
     
        //注意格式
        public function scopePublished($query){
            $query->where('published_at','<=',Carbon::now());
        }
    }
```

### 5.创建视图

创建视图就比较简单了，直接在resources/views目录下创建blade.php结尾的文件即可，因为Laravel的模板引擎是blade，之前也介绍过

```
    @extends('app')
    @section('content')
        <h1>文章列表: {{ Auth::user()->name }} </h1>
        <hr>
        @foreach($articles as $article)
            <h2><a href="/articles/{{$article->id}}">{{ $article->title }}</a></h2>
            <article>
                <div class="body">
                    {{ $article->content }}
                </div>
            </article>
        @endforeach
    @stop
```

### 6.引入表单库

写表单的时候直接使用三方的表单库，开发起来很方便，直接使用composer引入

```
    //引入包
    composer require laravelcollective/html
     
    //配置，在app.config->providers添加
    Collective\Html\HtmlServiceProvider::class,
     
    //在app.config->aliases添加
    'Form'=>Collective\Html\FormFacade::class,
    'Html'=>Collective\Html\HtmlFacade::class,
     
    //写法
    {!! Form::open(['url'=>'/articles']) !!}
       <div class="form-groups">
        {!! Form::label('title') !!}
        {!! Form::text('title',null,['class'=>'form-control']) !!}
      </div>
    {!! Form::close() !!}
```

### 7.表单的验证

我这里用的是Request的方法做的表单验证，是我比较推荐也比较简单的一种方法，首先要命令行创建一个Request类

```
    php artisan make:request  CreateArticleReauest
```

文件存在Http的Requests下

```
    class CreateArticleReauest extends FormRequest
    {
        /**
         * Determine if the user is authorized to make this request.
         *
         * @return bool
         */
        public function authorize()
        {
            return true;
        }
     
        /**
         * Get the validation rules that apply to the request.
         *
         * @return array
         */
        public function rules()
        {
            return [
                //
                'title' => 'required|min:3',
                'content' => 'required',
                'published_at' => 'required',
                'intro' => 'required'
            ];
        }
    }
```

使用的时候直接用这个类来处理Request就可以了

```
    public function store(CreateArticleReauest $request){
            Article::create($request->all());        //发表
            return redirect('/articles');
    }
```

### 8.配置路由

路由文件在routes文件夹下，名为web.php，之前我们配置的几个路由来看一下

```
    Route::get('/articles','ArticleController@index');
    Route::get('/articles/create','ArticleController@create');
    Route::get('/articles/{id}','ArticleController@show');
    Route::post('/articles','ArticleController@store');
    Route::post('/articles/{id}/edit','ArticleController@edit');
```

感觉很乱，重复性高，还麻烦，Laravel就给了我们一个方法，自动注册路由，一句代码搞定

```
    Route::resource('articles','ArticleController');
```

### 9.注册登录

Laravel其实已经帮我们做好了注册登录重置密码的功能，就在Http/Auth目录下，只需要我们配置路由，创建视图即可，视图的名称可以在对应的方法中看到

```
    Route::get('home','ArticleController@index');
    Route::get('auth/login','Auth\LoginController@showLoginForm');
    Route::post('auth/login','Auth\LoginController@login');
    Route::get('auth/register','Auth\RegisterController@showRegistrationForm');
    Route::post('auth/register','Auth\RegisterController@register');
    Route::get('auth/logout','Auth\LoginController@logout');
```

视图文件存在views/auth中，这里只说明一点，在注册的时候密码需要两个输入框验证，注意字段名称

```
    <div class="form-groups">
        {!! Form::label('password','Password: ') !!}
        {!! Form::password('password',['class'=>'form-control']) !!}
    </div>
    <div class="form-groups">
        {!! Form::label('password_confirmation','Password_confirmation: ') !!}
        {!! Form::password('password_confirmation',['class'=>'form-control']) !!}
    </div>
```

### 10.关联关系

最后再说一下关联关系，比如我们这里，一篇文章只能有一个作者，一个作者可以有多篇文章，所以我们需要在模型类中添加关系处理

```
    //Article.php
    public function user(){
            return $this->belongsTo('App\User');
    }
     
    //User.php
    public function articles(){
            return $this->hasMany('App\Article');
    }
```

然后需要创建关联字段，也就是创建一个数据迁移文件添加字段

```
    public function up()
    {
        Schema::table('articles', function (Blueprint $table) {
            //
            $table->integer('user_id')->default(1); //用户id或者用下面的外键
            //$table->foreign('user_id')->references('id')->on('users');//外键
        });
    }
     
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('articles', function (Blueprint $table) {
            //需要引入doctrine/dbal
            $table->dropColumn('user_id');
        });
    }
```

这样再创建文章的时候就可以写入与用户的关系了

```
    public function store(CreateArticleReauest $request){
            Article::create(array_merge(['user_id'=>Auth::user()->id],$request->all()));        //发表
            return redirect('/articles');
    }
```


[1]: http://blog.duicode.com/2464.html

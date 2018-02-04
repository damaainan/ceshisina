## Laravel 5.4 入门系列 3. 任务列表显示

熟悉了路由与视图的基本操作之后，我们来让视图显示一个任务列表吧。主要知识点：

* 数据迁移
* 查询构造器

## 数据库

### 创建数据库

首先创建一个数据库:

    $ mysql -uroot -p
    mysql> create database laratasks;

### 数据库配置

Laravel 的配置文件保存在 config 目录下面，例如 config/database.php 保存了数据库的配置信息：

    'mysql' => [
        'driver'    => 'mysql',
        'host'      => env('DB_HOST', 'localhost'),
        'database'  => env('DB_DATABASE', 'forge'),
        'username'  => env('DB_USERNAME', 'forge'),
        'password'  => env('DB_PASSWORD', ''),
        ...
    ],

可以看到，有几个变量都是先通过 env 方法获取的，取不到的时候再使用自定义的默认值。因此通常在 .env 目录下面根据不同的开发人员的需求来进行配置：

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=laratasks
    DB_USERNAME=root
    DB_PASSWORD=

### 执行迁移

配置完数据库之后，自然想到的是如何创建和操作表？Laravel 是通过迁移来实现对表的各项操作的。而 Laravel 默认就自带了两个迁移。我们可以通过执行迁移来判断数据库是否连上：

    $ php artisan migrate

如果对该命令不熟悉，可以使用如下命令查看具体说明：

    $ php artisan help migrate

如果使用的 MySQL 版本低于 5.7，可能会报错：

> Syntax error or access violation: 1071 Specified key was too long; max key length is 767 bytes

这是因为，Laravel 5.4 采用的数据库编码为 utf8mb4，该编码可以支持 emojis 表情的保存。要解决该问题，只需要增加下面的代码：

    // /app/Providers/AppServiceProvider.php
    use Illuminate\Support\Facades\Schema;
    
    public function boot()
    {
        Schema::defaultStringLength(191);
    }

解决了该问题之后，我们需要手动先删除数据库中的表再重新执行迁移:

    $ php artisan migrate

若迁移执行成功，数据库默认会创建三张表:

    mysql> use laratasks;
    Database changed
    mysql> show tables;
    +---------------------+
    | Tables_in_laratasks |
    +---------------------+
    | migrations          |
    | password_resets     |
    | users               |
    +---------------------+
    3 rows in set (0.00 sec)

其中 migrations 是用来记录迁移信息的，其余两张则是自带的两个迁移任务生成的表。

### 创建迁移

现在，我们就可以创建一个用来生成任务表的迁移了。首先是创建迁移：

    $ php artisan make:migration create_tasks_table --create=tasks
    Created Migration: 2017_04_10_175246_create_tasks_table

--create=tasks 代表了要创建数据表 tasks，这样迁移文件会预先定义好一些内容。打开迁移表，添加几个字段:

    // /database/migrations/日期_create_tasks_table.php
     public function up()
        {
            Schema::create('tasks', function (Blueprint $table) {
                $table->increments('id');
                $table->text('name');
                $table->timestamps();
            });
        }

当我们执行迁移时，就会调用 up 方法，我们来执行下刚才创建的迁移：

    $ php artisan migrate
    Migrating: 2017_04_10_175246_create_tasks_table
    Migrated:  2017_04_10_175246_create_tasks_table

### 查询构造器（DB）

现在，数据库就多了 tasks 表格了，我们通过 tinker 来添加数据吧:

    $ php artisan tinker
    Psy Shell v0.8.3 (PHP 5.6.22 — cli) by Justin Hileman

首先，使用 DB 插入几条数据：

    >>>> DB::insert('insert into  tasks (id, name, created_at, updated_at) values (?, ?,?,?)', [1, '作业',Carbon\Carbon::now(),Carbon\Carbon::now()]);
    >>> DB::insert('insert into  tasks (id, name, created_at, updated_at) values (?, ?,?,?)', [2, '购物',Carbon\Carbon::now(),Carbon\Carbon::now()]);
    >>> DB::insert('insert into  tasks (id, name, created_at, updated_at) values (?, ?,?,?)', [3, '运动',Carbon\Carbon::now(),Carbon\Carbon::now()]);

再练习下 DB 的其他功能：

    >>> DB::table('tasks')->get();   # 获取所有表数据
    >>> DB::table('tasks')->get()->toArray();  # 将获取的数据转化为数据
    >>> DB::table('tasks')->first();  # 获取第一条数据
    >>> DB::table('tasks')->where('name','购物')->first(); # 指定条件
    >>> DB::table('tasks')->pluck('name'); # 获取姓名列表

## 显示任务列表

接下来，我们就可以让网站显示任务列表了，例如:

    Route::get('tasks', function() {
        return $tasks = DB::table('tasks')->latest()->get();
    });

访问网站的 /tasks 路径，就可以看到返回了任务列表的 json 形式。不过我们还是用视图展现吧:

    // /routes/web.php
    Route::get('tasks', function() {
        $tasks = DB::table('tasks')->latest()->get();
        return view('tasks.index',compact('tasks'));
    });

tasks.index 其实就相当于 tasks/index，接下来创建视图:

    // /resources/views/tasks/index.blade.php
    
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Document</title>
    </head>
    <body>
        {{ $tasks }}
    </body>
    </html>

访问 /tasks，直接返回了一堆任务列表，界面显示很不友好，我们可以用 PHP 的 foreach 循环来友好的显示任务列表:

    // /resources/views/tasks/index.blade.php
    <h1>任务列表</h1>
    <ul>
        <?php foreach($tasks as $task): ?>
            <li><?php echo $task->name ?></li>
        <?php endforeach; ?>
    </ul>

同样，Laravel 的 Blade 模板提供了更为简洁的语法：

    // /resources/views/tasks/index.blade.php
    <h1>任务列表</h1>
    <ul>
        @foreach ($tasks as $task)
            <li>{{ $task->name }}</li>
        @endforeach
    </ul>

## 显示具体任务

接下来是显示具体的某个任务。首先是路由：

    // /routes/web.php
    Route::get('tasks/{task}', function($id) {
        $task = DB::table('tasks')->find($id);
        return view('tasks/show',compact('task'));
    });

{task} 相当于占位符，比如用户访问 tasks/1，函数接收的 $id 就为 1。

    // /resources/views/tasks/show.blade.php
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Document</title>
    </head>
    <body>
        {{ $task->name }}
    </body>
    </html>

最后，我们创建超链接让任务列表可跳转到具体任务吧：

    <h1>任务列表</h1>
    <ul>
        @foreach ($tasks as $task)
            <li>
                <a href="/tasks/{{ $task->id }}">{{ $task->name }}</a>          
            </li>
        @endforeach
    </ul>

超链接我们也可以通过 url 函数生成：

    <a href="{{ url("tasks",[$task->id]) }}">{{ $task->name }}</a>

- - -

参考资料：

* [Laravel 5.4: Specified key was too long error - Laravel News][0]
* [Laravel 数据库之：数据库请求构建器 | Laravel 5.4 中文文档][1]
* [PHP: 流程控制的替代语法 - Manual][2]

[0]: https://laravel-news.com/laravel-5-4-key-too-long-error/
[1]: http://d.laravel-china.org/docs/5.4/queries
[2]: http://php.net/manual/zh/control-structures.alternative-syntax.php
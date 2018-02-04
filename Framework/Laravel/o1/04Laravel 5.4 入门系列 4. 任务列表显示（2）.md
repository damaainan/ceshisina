## Laravel 5.4 入门系列 4. 任务列表显示（2）

这一节，我们进一步完善上一节创建的任务列表。主要知识点：

* Eloquent Model
* 控制器
* 路由模型绑定

## Eloquent Model

### 新增迁移

首先，我们为数据库表 tasks 新增一个字段 completed，用来表示任务是否完成:

    $ php artisan make:migration add_completed_to_tasks_table --table=tasks

这次，我们使用 --table 指定已存在的表。接着添加具体字段:

    // /database/migrations/2017_04_11_060132_add_completed_to_tasks_table.php
    public function up()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->boolean('completed')->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('completed');
        });
    }

执行迁移:

    $ php artisan migrate

如果我们想要回滚本次迁移，可以执行:

    $ php artisan migrate:rollback

将会执行迁移任务的 down() 函数：

### 上手 Eloquent Model

上一节，我们使用 DB 来操纵数据库，在 Laravel 中，也叫做查询构造器。实际上，Laravel 提供了更为强大的用来与数据库交互的工具：Eloquent Model。

首先，我们来创建一个与 tasks 表格对应的 Model:

    $ php artisan make:model Task

现在，我们就可以使用 Eloquent 了，启动控制台：

    $ php artisan tinker

可以先对 Task 类实例化，然后再进行各种操作：

    >>> $task = new \App\Task;  # 新建一个 Task 实例

新建任务:

    >>> $task->name = "新的任务"
    >>> $task->completed = 1
    >>> $task->save()  # 保存新实例
    
    >>> $task 
    => App\Task {#674
         name: "新的任务",
         completed: 1,
         updated_at: "2017-04-11 06:14:10",
         created_at: "2017-04-11 06:14:10",
         id: 4,
       }

查询:

    >>> $task->all()->toJson();  # 获取数据并转化为 json 格式
    >>> $task->where('completed',1)->get(); # 查看完成的任务
    >>> $task->pluck('name')->first(); # 获取第一条记录的 name 字段

也可以不实例，直接调用「门面」方法来操作：

    >>> \App\Task::first();  # 获取第一条记录
    >>> \App\Task::latest()->get(); # 按先后顺序显示记录

### 自定义方法

刚才的例子中，有一行是用来查询已完成的任务的：

    >>> $task->where('completed',1)->get();

该查询在很多地方都有可能要用到，为了避免重复工作，我们将其封装到 Model 中：

    // /app/Task.php
    class Task extends Model
    {    
        public function completed()
        {
            return static::where('completed',1)->get();
        }
    
         public function unCompleted()
        {
            return static::where('completed',0)->get();
        }
    }

我们定义了 completed() 和 unCompleted() 方法分别获取已完成和未完成的任务。现在，需要重新启动 tinker：

    >>> $task = new \App\Task;
    >>> $task->completed();
    >>> $task->uncompleted()->pluck('name')；

那么，我们是否可以直接使用 `Task::completed()呢？当然可以，不过需要把方法改成静态的，因为静态方法可以通过类和实例访问：

     public static function completed()
    {
        return static::where('completed',1)->get();
    }
    
     public static function unCompleted()
    {
        return static::where('completed',1)->get();
    }

这样就可以通过类直接访问了,依旧要重启 tinker 才能生效

    $ php artisan tinker
    >>> \App\Task::completed()
    >>> \App\Task::unComplete()

对于上述的方法，Laravel 提供了更为便利的方式实现，叫做范围查询:

    public function scopeCompleted($query)
    {
        return $query->where('completed',1);
    }
    
     public function scopeUnCompleted($query)
    {
        return $query->where('completed',0);
    }

使用范围查询，只需要在我们的方法前面加上 scope 就行，这样就不用去定义静态方法。同时，传入 $query 参数，即已存在的查询。我们只需要在此基础上添加自己想要的查询就可以了。现在，可以方便的使用了:

    $ php artisan tinker
    >>> use \App\Task;
    >>> Task::completed()->get();
    >>> Task::unCompleted()->pluck('name')

经过对 Eloquent 的初步学习之后，我们可以将之前的 DB 查询换成 Eloquent 的：

    // /routes/web.php
    <?php
    
    use App\Task;
    
    Route::get('tasks', function() {
        $completedTasks = Task::latest()->completed()->get();
        $unCompletedTasks = Task::latest()->unCompleted()->get();
        return view('tasks/index',compact('completedTasks','unCompletedTasks'));
    });
    
    Route::get('tasks/{task}', function($id) {
        $task = Task::findorFail($id);
        return view('tasks/show',compact('task'));
    });

对应的视图文件也稍微修改下:

    // /resources/views/tasks/index.blade.php
    <body>
        <h1>任务列表</h1>
        <div>
            <h2>未完成</h2>
            <ul>
                @foreach ($unCompletedTasks as $task)
                    <li>
                        <a href="{{ url("tasks",[$task->id]) }}">{{ $task->name }}</a>          
                    </li>
                @endforeach
        
            </ul>
        </div>
        <div>
            <h2>已完成</h2>
            <ul>
                @foreach ($completedTasks as $task)
                    <li>
                        <a href="{{ url("tasks",[$task->id]) }}">{{ $task->name }}</a>          
                    </li>
                @endforeach
        
            </ul>
        </div>

## 控制器

之前，我们都是直接在路由处理请求，看上去好像没什么问题。但是，如果当网站规模大起来之后，将路由和业务处理放在一起会变得难以维护。因此，更为常见的做法是在控制器中处理路由请求。

### 创建控制器

首先是控制器的创建:

    $ php artisan make:controller TasksController

这样就创建了一个空白的控制器了。

### 在控制器中处理请求

接下来，我们就可以在控制器中定义不同的方法来处理路由请求了。先在路由中指定请求由哪个控制器的方法处理：

    Route::get('tasks','TasksController@index');
    Route::get('tasks/{task}','TasksController@show');

控制器里面可以把刚才路由的方法拷贝进来:

    // /app/Http/Controllers/TasksController.php
    <?php
    
    namespace App\Http\Controllers;
    
    use App\Task;
    use Illuminate\Http\Request;
    
    class TasksController extends Controller
    {
        public function index()
        {
            $completedTasks = Task::latest()->completed()->get();
            $unCompletedTasks = Task::latest()->unCompleted()->get();
            return view('tasks/index',compact('completedTasks','unCompletedTasks'));
        }
    
        public function show($task)
        {
            $task = Task::findorFail($task);
            return view('tasks/show',compact('task'));
        }
    }

## 路由模型绑定

### 自动路由模型绑定

刚才的 show 方法，可以进一步简写成:

    // /app/Http/Controllers/TasksController.php
    public function show(Task $task)
    {
        return view('tasks/show',compact('task'));
    }

功能完全一样，如果觉得好奇，可以打印出 $task

    // /app/Http/Controllers/TasksController.php
    public function show(Task $task)
    {    
        dd($task);
    }

你会发现，此时的 $task 已经不是参数值了，而是从数据库返回的对应实例。也就是说，我们为 $task 添加了类型提示 Task 之后，Laravel 自动帮我们进行了查询。这就叫做自动路由模型绑定。

### 手动路由模型绑定

当然，我们也可以手动进行绑定！比如我只想要显示完成的任务:

    // /app/Providers/RouteServiceProvider.php
    public function boot()
    {   
        parent::boot();
        Route::bind('task',function($task){
            return \App\Task::completed()->findOrFail($task);
        });
    }

然后访问未完成的任务，比如 task/1，就会报错。

- - -

参考：

* [PHP: Static（静态）关键字 - Manual][0]
* [Eloquent: 入门 | Laravel 5.4 中文文档][1]
* [Laravel 5.2 Error "Missing argument 1 for IlluminateAuthAuthManager::createDriver()"][2]
* [Laravel HTTP 路由功能 | Laravel 5.4 中文文档][3]

[0]: http://php.net/manual/zh/language.oop5.static.php
[1]: http://d.laravel-china.org/docs/5.4/eloquent#query-scopes
[2]: https://laracasts.com/discuss/channels/laravel/laravel-52-error-missing-argument-1-for-illuminateauthauthmanagercreatedriver?page=1
[3]: http://d.laravel-china.org/docs/5.4/routing#explicit-binding
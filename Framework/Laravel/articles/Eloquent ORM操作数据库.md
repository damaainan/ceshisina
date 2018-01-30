# [Laravel使用Eloquent ORM操作数据库][0]

### 1.定义模型

    <?php  
     namespace App;  
     use Illuminate\Database\Eloquent\Model;  
     class Flight extends Model{  
         protected $table = 'my_flights';  
         protected $primaryKey = 'my_id';  
         public $timestamps = false;   
    }


### 2.获取表中所有数据 `all/get`

    $flights = Flight::all();  
    $flights = App\Flight::where('active', 1)  
     ->orderBy('name', 'desc')  
     ->take(10)  
     ->get();

### 3.find和first获取单个记录

    // 通过主键获取模型...   
    $flight = App\Flight::find(1);  
    // 获取匹配查询条件的第一个模型...   
    $flight = App\Flight::where('active', 1)->first();

### 4.获取聚合

    $count = App\Flight::where('active', 1)->count();  
    $max = App\Flight::where('active', 1)->max('price');

### 5.新建

想要在数据库中插入新的记录，只需创建一个新的模型实例，设置模型的属性，然后调用save方法：

    $flight = new Flight;  
    $flight->name = $request->name;  
    $flight->save();

save方法还可以用于更新数据库中已存在的模型。要更新一个模型，应该先获取它，设置你想要更新的属性，然后调用save方法。

    $flight = App\Flight::find(1);  
    $flight->name = 'New Flight Name';  
    $flight->save();

create方法在数据库中插入一条新的记录，该方法返回被插入的模型实例,先要在模型设置一下：

    //可以被批量赋值的属性  
    protected $fillable = ['name'];

    $flight = App\Flight::create(['name' => 'Flight 10']);

### 6.删除

要删除一个模型，调用模型实例上的delete方法：

    $flight = App\Flight::find(1);  
    $flight->delete();

如果你知道模型的主键的话，可以直接删除而不需要获取它：

    App\Flight::destroy(1);  
    App\Flight::destroy([1, 2, 3]);  
    App\Flight::destroy(1, 2, 3);

通过查询删除多个模型

    $deletedRows = App\Flight::where('active', 0)->delete();

[0]: http://www.cnblogs.com/lamp01/p/6666669.html
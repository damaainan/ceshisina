# Eloquent ORM笔记

## 基本操作

### 新增

    $user = new User;
    $user->name = 'John';
    $user->save();
    $insertedId = $user->id;//从对象取得 id 属性值
    

使用模型的 Create 方法

    class User extends Model {
        protected $guarded = ['id', 'account_id'];//黑名单，不会被更新
    }
    
    // 在数据库中建立一个新的用户...
    $user = User::create(['name' => 'John']);
    
    // 以属性找用户，若没有则新增并取得新的实例...
    $user = User::firstOrCreate(['name' => 'John']);
    
    // 以属性找用户，若没有则建立新的实例...
    $user = User::firstOrNew(['name' => 'John']);

### 删除

    $this->where($where)->delete();
    
    或者
    $user = User::find(1);
    $user->delete();

### 更新

    return $this->where($where)->update($data);
    
    或者
    $user = User::find(1);
    $user->update($data);

### 查找

    //取出所有记录,all()得出的是对象集合，可以遍历
    $this->all()->toArray();
    
    //根据主键取出一条数据
    $one = $this->find('2');
    return array(
      $one->id,
      $one->title,
      $one->content,
    );
    
    //查找id=2的第一条数据
    $this->where('id', 2)->first()->toArray();
    
    //查找id>0的所有数据
    $this->where('id', '>', '0')->get()->toArray();
    
    //查找id>0的所有数据，降序排列
    $this->where('id', '>', '0')->orderBy('id', 'desc')->get()->toArray();
    
    //查找id>0的所有数据，降序排列，计数
    $this->where('id', '>', '0')->orderBy('id', 'desc')->count();
    
    //offset,limit
    $this->where('id', '>', '0')->orderBy($order[0], $order[1])->skip($offset)->take($limit);
    
    //等同于
    $this->where('id', '>', '0')->orderBy($order[0], $order[1])->offset($offset)->limit($limit);

更多：

    //条件类：
    
    where('id', '>', '0')
    where('id', '>=', '0')
    where('id', '<', '0')
    where('id', '<=', '0')
    where('id', 'like', 'name%')
    
    whereIn($key, $array)
    whereNotIn($key, $array)
    whereBetween($key, $array)
    whereNotBetween($key, $array)
    
    orWhereIn($key, $array)
    orWhereNotIn($key, $array)
    orWhereBetween($key, $array)
    orWhereNotBetween($key, $array)
    
    //结果方法：Illuminate\Database\Query\Builder
    
    first()取第一个
    get()取所有
    all()取所有（无条件）
    
    //聚合方法
    count()统计
    avg()求平均值
    sum()
    max()
    min()

> Eloquent ORM - Laravel 中文文档  
[http://laravel-china.org/docs/5.0/eloquent][1]

[1]: http://laravel-china.org/docs/5.0/eloquent
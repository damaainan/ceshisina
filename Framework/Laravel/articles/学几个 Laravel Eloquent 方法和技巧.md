## 学几个 Laravel Eloquent 方法和技巧

来源：[https://juejin.im/post/5ad2f772f265da237e0a2adc](https://juejin.im/post/5ad2f772f265da237e0a2adc)

时间 2018-04-15 14:56:16

 
 ![][0]
 
我第一次寻找所谓的 Laravel 框架的时候，我的其中一个目标就是要找：利用最简单的操作数据库的方法。后来目标就停在了 Eloquent ORM 上。
 
今天说一说 Eloquent ORM 的一些不易被发现和使用的方法。
 
### 1. 递增和递减函数
 
平时这么写：
 
```php
$article = Article::find($article_id);
$article->read_count++;
$article->save();
```
 
利用`increment`函数
 
```php
$article = Article::find($article_id);
$article->increment('read_count');
```
 
当然可以传入数字，不只是只增减 1：
 
```php
Article::find($article_id)->increment('read_count');
Article::find($article_id)->increment('read_count', 10); // +10
Product::find($produce_id)->decrement('stock'); // -1
```
 
我们来看看源代码是怎么实现的：
 
```php
/**
 * Increment a column's value by a given amount.
 *
 * @param  string  $column
 * @param  int     $amount
 * @param  array   $extra
 * @return int
 */
public function increment($column, $amount = 1, array $extra = [])
{
    if (! is_numeric($amount)) {
        throw new InvalidArgumentException('Non-numeric value passed to increment method.');
    }

    $wrapped = $this->grammar->wrap($column);

    $columns = array_merge([$column => $this->raw("$wrapped + $amount")], $extra);

    return $this->update($columns);
}

/**
 * Decrement a column's value by a given amount.
 *
 * @param  string  $column
 * @param  int     $amount
 * @param  array   $extra
 * @return int
 */
public function decrement($column, $amount = 1, array $extra = [])
{
    if (! is_numeric($amount)) {
        throw new InvalidArgumentException('Non-numeric value passed to decrement method.');
    }

    $wrapped = $this->grammar->wrap($column);

    $columns = array_merge([$column => $this->raw("$wrapped - $amount")], $extra);

    return $this->update($columns);
}
```
 
主要利用`$this->grammar`解析 $column 字段，转变为可执行的 sql 语句。
 
```php
/**
 * Wrap a value in keyword identifiers.
 *
 * @param  \Illuminate\Database\Query\Expression|string  $value
 * @param  bool    $prefixAlias
 * @return string
 */
public function wrap($value, $prefixAlias = false)
{
    if ($this->isExpression($value)) {
        return $this->getValue($value);
    }

    // If the value being wrapped has a column alias we will need to separate out
    // the pieces so we can wrap each of the segments of the expression on it
    // own, and then joins them both back together with the "as" connector.
    if (strpos(strtolower($value), ' as ') !== false) {
        return $this->wrapAliasedValue($value, $prefixAlias);
    }

    return $this->wrapSegments(explode('.', $value));
}

/**
 * Wrap the given value segments.
 *
 * @param  array  $segments
 * @return string
 */
protected function wrapSegments($segments)
{
    return collect($segments)->map(function ($segment, $key) use ($segments) {
        return $key == 0 && count($segments) > 1
                        ? $this->wrapTable($segment)
                        : $this->wrapValue($segment);
    })->implode('.');
}

/**
 * Wrap a single string in keyword identifiers.
 *
 * @param  string  $value
 * @return string
 */
protected function wrapValue($value)
{
    if ($value !== '*') {
        return '"'.str_replace('"', '""', $value).'"';
    }

    return $value;
}
```
  注： `$grammer`是个抽象类，项目会根据不同的数据库，而采用不同的`$grammer`继承类来实现查询功能
 
 ![][1]
 
最后一个参数是`$extra`，因为`increment`函数最后会执行`update()`方法，所以可以把额外需要操作数据的语句放在`$extra`数组中。
 
### 2. WhereX
 
这里的`where`是前缀的作用，`X`表示的是我们的字段名，可以简化我们的查询写法，平时都是这么写的：
 
```php
$users = User::where('approved', 1)->get();
```
 
简便的写法：
 
```php
$users = User::whereApproved(1)->get();
```
 
具体实现主要利用`__call`方法。
 
public mixed __call ( string arguments )
 
public static mixed __callStatic ( string arguments )
 
在对象中调用一个不可访问方法时，__call() 会被调用。
 
在静态上下文中调用一个不可访问方法时，__callStatic() 会被调用。
 
在`Query/Builder.php`中可以看出：
 
```php
/**
 * Handle dynamic method calls into the method.
 *
 * @param  string  $method
 * @param  array   $parameters
 * @return mixed
 *
 * @throws \BadMethodCallException
 */
public function __call($method, $parameters)
{
    if (static::hasMacro($method)) {
        return $this->macroCall($method, $parameters);
    }

    if (Str::startsWith($method, 'where')) {
        return $this->dynamicWhere($method, $parameters);
    }

    $className = static::class;

    throw new BadMethodCallException("Call to undefined method {$className}::{$method}()");
}
```
 `where`查询方法都会调用函数：
 
```php
return $this->dynamicWhere($method, $parameters);
```
 
```php
/**
 * Handles dynamic "where" clauses to the query.
 *
 * @param  string  $method
 * @param  string  $parameters
 * @return $this
 */
public function dynamicWhere($method, $parameters)
{
    $finder = substr($method, 5);

    $segments = preg_split(
        '/(And|Or)(?=[A-Z])/', $finder, -1, PREG_SPLIT_DELIM_CAPTURE
    );

    // The connector variable will determine which connector will be used for the
    // query condition. We will change it as we come across new boolean values
    // in the dynamic method strings, which could contain a number of these.
    $connector = 'and';

    $index = 0;

    foreach ($segments as $segment) {
        // If the segment is not a boolean connector, we can assume it is a column's name
        // and we will add it to the query as a new constraint as a where clause, then
        // we can keep iterating through the dynamic method string's segments again.
        if ($segment !== 'And' && $segment !== 'Or') {
            $this->addDynamic($segment, $connector, $parameters, $index);

            $index++;
        }

        // Otherwise, we will store the connector so we know how the next where clause we
        // find in the query should be connected to the previous ones, meaning we will
        // have the proper boolean connector to connect the next where clause found.
        else {
            $connector = $segment;
        }
    }

    return $this;
}
```
 
继续看`addDynamic`函数：
 
```php
/**
 * Add a single dynamic where clause statement to the query.
 *
 * @param  string  $segment
 * @param  string  $connector
 * @param  array   $parameters
 * @param  int     $index
 * @return void
 */
protected function addDynamic($segment, $connector, $parameters, $index)
{
    // Once we have parsed out the columns and formatted the boolean operators we
    // are ready to add it to this query as a where clause just like any other
    // clause on the query. Then we'll increment the parameter index values.
    $bool = strtolower($connector);

    $this->where(Str::snake($segment), '=', $parameters[$index], $bool);
}
```
 
最后回到了`$this->where(Str::snake($segment), '=', $parameters[$index], $bool);`常规的`where`语句上；
 
同时，这过程我们可以发现`whereX`方法，不仅可以传入一个字段，而且还可以传入多个字段，用「And」或者 「Or」连接，且字段首字母用大写「A~Z」。
 
### 3. XorY methods
 
在平时有太多的写法都是，先查询，再判断是否存在，然后再决定是输出，还是创建。
 
如：
 
```php
$user = User::where('email', $email)->first();
if (!$user) {
  User::create([
    'email' => $email
  ]);
}
```
 
一行代码解决：
 
```php
$user = User::firstOrCreate(['email' => $email]);
```
  注  ：这里还有一个函数`firstOrNew`和`firstOrCreate`相似，看代码：
 
```php
/**
     * Get the first record matching the attributes or instantiate it.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function firstOrNew(array $attributes, array $values = [])
    {
        if (! is_null($instance = $this->where($attributes)->first())) {
            return $instance;
        }

        return $this->newModelInstance($attributes + $values);
    }

    /**
     * Get the first record matching the attributes or create it.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function firstOrCreate(array $attributes, array $values = [])
    {
        if (! is_null($instance = $this->where($attributes)->first())) {
            return $instance;
        }

        return tap($this->newModelInstance($attributes + $values), function ($instance) {
            $instance->save();
        });
    }
```
 
主要区别场景在于：如果是在已有 $attributes 下查找并创建的话，就可以用`firstOrCreate`。如果当我们需要先查询然后再对 model 进行后续的操作的话，应使用`firstOrNew`方法，将 save 保存数据库操作放在最后；以免重复执行`save()`方法。
 
### 4. find()
 
find() 函数通过主键获取数据，平时都是获取单数据，其实传入的参数还可以是「主键数组」，获取多 models。
 
```php
$users = User::find([1,2,3]);
```
 
我们查看它的函数实现：
 
```php
/**
     * Find a model by its primary key.
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|static[]|static|null
     */
    public function find($id, $columns = ['*'])
    {
        if (is_array($id) || $id instanceof Arrayable) {
            return $this->findMany($id, $columns);
        }

        return $this->whereKey($id)->first($columns);
    }
```
 
首先判断的是 id 是不是 array，如果是的话，则执行 findMany 函数：
 
```php
/**
     * Find multiple models by their primary keys.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $ids
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findMany($ids, $columns = ['*'])
    {
        if (empty($ids)) {
            return $this->model->newCollection();
        }

        return $this->whereKey($ids)->get($columns);
    }
```
 
获取的结果是一个 Collection 类型。
 
## 总结
 
Laravel 框架有很多地方值得我们去研究，看 Laravel 是如何封装方法的。Eloquent ORM 还有很多方法可以一个个去看源代码是怎么实现的。
 
本文内容更多来自： [laravel-news.com/eloquent-ti…][2]
 
还有很多函数都可以拿出来分析，如：
 
Relationship with conditions and ordering
 
```php
public function orders() {
    return $this->hasMany('App\Order');    
}
```
 
其实我们可以在获取多订单的同时，加入筛选语句和排序。如，获取已支付并按更新时间倒序输出：
 
```php
public function paidOrders() {
    return $this->hasMany('App\Order')->where('paid', 1)->orderBy('updated_at');
}
```
 
#### 「未完待续」
 


[2]: https://link.juejin.im?target=https%3A%2F%2Flaravel-news.com%2Feloquent-tips-tricks
[0]: ../img/eqiay2A.png 
[1]: ../img/QfmuaqU.jpg 
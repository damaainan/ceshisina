# Laravel Database——查询构造器与语法编译器源码分析 (下) 

19天前 ⋅ 201 ⋅ 4 ⋅ 0 

## **insert 语句**[#][0]

insert 语句也是我们经常使用的数据库操作，它的源码如下：

    public function insert(array $values)
    {
        if (empty($values)) {
            return true;
        }
    
        if (! is_array(reset($values))) {
            $values = [$values];
        }
        else {
            foreach ($values as $key => $value) {
                ksort($value);
    
                $values[$key] = $value;
            }
        }
    
        return $this->connection->insert(
            $this->grammar->compileInsert($this, $values),
            $this->cleanBindings(Arr::flatten($values, 1))
        );
    }

laravel 的 insert 是允许批量插入的，方法如下：

    DB::table('users')->insert([['email' => 'foo', 'name' => 'taylor'], ['email' => 'bar', 'name' => 'dayle']]);

一个语句可以向数据库插入两条记录。sql 语句为：

    
    insert into users (`email`,`name`) values ('foo', 'taylor'), ('bar', 'dayle');

因此，laravel 在处理 insert 的时候，首先会判断当前的参数是单条插入还是批量插入。

    if (! is_array(reset($values))) {
        $values = [$values];
    }

reset 会返回 values 的第一个元素。如果是批量插入的话，第一个元素必然也是数组。如果的单条插入的话，第一个元素是列名与列值。因此如果是单条插入的话，会在最外层再套一个数组，统一插入的格式。

如果是批量插入的话，首先需要把插入的各个字段进行排序，保证插入时各个记录的列顺序一致。

### **compileInsert**[#][1]

对 insert 的编译也是按照批量插入的标准来进行的：

    public function compileInsert(Builder $query, array $values)
    {
        $table = $this->wrapTable($query->from);
    
        if (! is_array(reset($values))) {
            $values = [$values];
        }
    
        $columns = $this->columnize(array_keys(reset($values)));
    
        $parameters = collect($values)->map(function ($record) {
            return '('.$this->parameterize($record).')';
        })->implode(', ');
    
        return "insert into $table ($columns) values $parameters";
    }

首先对插入的列名进行 columnze 函数处理，之后对每个记录的插入都调用 parameterize 函数来对列值进行处理，并用 （） 包围起来。

## **update 语句**[#][2]

    public function update(array $values)
    {
        $sql = $this->grammar->compileUpdate($this, $values);
    
        return $this->connection->update($sql, $this->cleanBindings(
            $this->grammar->prepareBindingsForUpdate($this->bindings, $values)
        ));
    }
    

与插入语句相比，更新语句更加复杂，因为更新语句必然带有 where 条件，有时还会有 join 条件：

    public function compileUpdate(Builder $query, $values)
    {
        $table = $this->wrapTable($query->from);
    
        $columns = collect($values)->map(function ($value, $key) {
            return $this->wrap($key).' = '.$this->parameter($value);
        })->implode(', ');
    
        $joins = '';
    
        if (isset($query->joins)) {
            $joins = ' '.$this->compileJoins($query, $query->joins);
        }
    
        $wheres = $this->compileWheres($query);
    
        return trim("update {$table}{$joins} set $columns $wheres");
    }

## **updateOrInsert 语句**[#][3]

updateOrInsert 语句会先根据 attributes 条件查询，如果查询失败，就会合并 attributes 与 values 两个数组，并插入新的记录。如果查询成功，就会利用 values 更新数据。

    public function updateOrInsert(array $attributes, array $values = [])
    {
        if (! $this->where($attributes)->exists()) {
            return $this->insert(array_merge($attributes, $values));
        }
    
        return (bool) $this->take(1)->update($values);
    }

## **delete 语句**[#][4]

删除语句比较简单，参数仅仅需要 id 即可，delete 语句会添加 id 的 where 条件：

    public function delete($id = null)
    {
        if (! is_null($id)) {
            $this->where($this->from.'.id', '=', $id);
        }
    
        return $this->connection->delete(
            $this->grammar->compileDelete($this), $this->getBindings()
        );
    }
    

删除语句的编译需要先编译 where 条件：

    public function compileDelete(Builder $query)
    {
        $wheres = is_array($query->wheres) ? $this->compileWheres($query) : '';
    
        return trim("delete from {$this->wrapTable($query->from)} $wheres");
    }

## **动态 where**[#][5]

laravel 有一个有趣的功能：动态 where。

    DB::table('users')->whereFooBarAndBazOrQux('corge', 'waldo', 'fred')

这个语句会生成下面的 sql 语句：

    select * from users where foo_bar = 'corge' and baz = 'waldo' or qux = 'fred';

也就是说，动态 where 将函数名解析为列名与连接条件，将参数作为搜索的值。

我们先看源码：

    public function dynamicWhere($method, $parameters)
    {
    
        $finder = substr($method, 5);
    
        $segments = preg_split(
            '/(And|Or)(?=[A-Z])/', $finder, -1, PREG_SPLIT_DELIM_CAPTURE
        );
    
        $connector = 'and';
    
        $index = 0;
    
        foreach ($segments as $segment) {
            if ($segment !== 'And' && $segment !== 'Or') {
                $this->addDynamic($segment, $connector, $parameters, $index);
    
                $index++;
            }
    
            else {
                $connector = $segment;
            }
        }
    
        return $this;
    }
    
    protected function addDynamic($segment, $connector, $parameters, $index)
    {
        $bool = strtolower($connector);
    
        $this->where(Str::snake($segment), '=', $parameters[$index], $bool);
    }

* 首先，程序会提取函数名 whereFooBarAndBazOrQux，删除前 5 个字符，FooBarAndBazOrQux。
* 正则判断，根据 And 或 Or 对函数名进行切割:FooBar、And、Baz、Or、Qux。
* 添加 where 条件，将驼峰命名改为蛇型命名。

[0]: #insert-语句
[1]: #compileInsert
[2]: #update-语句
[3]: #updateOrInsert-语句
[4]: #delete-语句
[5]: #动态-where
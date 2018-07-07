## PHP Traits 的使用

来源：[https://zhuanlan.zhihu.com/p/38532039](https://zhuanlan.zhihu.com/p/38532039)

时间 2018-06-27 11:37:24

 
![][0]
 
PHP Traits 的使用

 
php不像C++一样，是多继承语言，它是一种单继承语言，如果有时我们需要继承多个类怎么办？这个时候Traits就上场了，我们仅仅需要在类中用use声明多个trait，这样当前类中的同名方法覆盖trait，而trait又覆盖基类中的同名方法，属性也是一样的。

 
```php
    <?php

    class Base {
        public function sayHello() {
            echo 'Hello';
        }
    }

    trait SayWorld {
        public function sayHello(){
            parent::sayHello();
            echo 'World';
        }
    }

    class MyHelloWorld extends Base {
        use SayWorld;
    }

    $o = new MyHelloWorld();
    $o->sayHello();
```

 
再看: trait就是为了解决这个问题的。具体事例如下:

 
## 1.简单使用：首先，声明一个Trait

 
```php
    trait first_trait{
        function first_method(){}
    }
```

 
## 2.如果在Class中使用该Trait，那么使用关键字use

 
```php
    class first_class{
        use first_trait;
    }
    $obj = new first_class();
    $obj->first_method(); // valid
```

 
## 3.使用多个trait 在同个class中可以使用多个Trait

 
```php
    trait first_trait{
        function first_method(){}
    }
    trait second_trait{
        function second_metod(){}
    }
    class first_class{
        use first_trait,second_trait;
    }
    $obj= new first_class();
    // Valid
    $obj->first_method();
    // Valid
    $obj->second_method();
```

 
## 4.trait 之间的嵌套

 
```php
    trait first_trait{
        function first_method(){}
    }
    trait second_trait{
        use first_trait;
        function second_method(){}
    }
    class first_class {
        // now using
        use second_trait;
    }
    $obj= new first_class();
    // Valid
    $obj->first_method();
    // Valid
    $obj->second_method();
```

 
## 5.trait 的抽象方法(Abstract Method)

 
我们可以在Trait中声明需要实现的抽象类，这样能使使用它的class必须实现它。

 
```php
    trait first_trait{
        function first_method(){}
        //这里可以加入修饰符，说明调用类必须实现它
        abstract public function second_method();
    }
    class first_method{
        use first_trait;
        function second_method(){/*核心代码*/}
    }
```

 
## 5.Trait冲突：

 
多个 Trait 之间同时使用难免会冲突，这需要我们去解决。PHP5.4 从语法方面带入了相关 的关键字语法：insteadof 以及 as 。

 
```php
    trait first_trait{
        function first_function(){
            echo 'aaaa';
        }
    }
    trait second_trait {
        // 这里的名称和 first_trait 一样，会有冲突
        function first_function() {
            echo "From Second Trait";
        }
    }
    class first_class {
        use first_trait, second_trait {
            // 在这里声明使用 first_trait 的 first_function 替换
            // second_trait 中声明的
            first_trait::first_function insteadof second_trait;
        }
    }
    $obj = new first_class();
    // Output: aaaa
    $obj->first_function();
```

 
## 6.trait 需要注意的几点：

 
```
trait会覆盖调用类继承的父类方法
    trait 无法如Class一样使用new 实例化
    单个trait可以有多个trait组成
    在单个class中，可以使用多个trait
    trait支持修饰词
```

 
```php
<?php
trait QueryTrait
{
    /**
     * 返回数据和分页信息
     * @param $condition CDbCriteria|array 此参数可以为CDbCriteria对象或条件数组
     * @param null $order 排序字段
     * @param null $pageSize 每页显示多少条
     * @param $currentPage 当前页码 为空时，自动获取
     * @return array 返回数组
     * [
     * model=>CActiveRecord,
     * list->CActiveRecord[],
     * pages=>CPagination
     * ]
     */
    public static function findList($condition = null, $order = null, $pageSize = null, $currentPage = null)
    {
        if (is_array($condition)) {
            $criteria = new CDbCriteria();
            if (array_key_exists(0, $condition)) {
                //索引数组 格式为 ['id=?',[2]] 或 ['id >:min and id<:max', [':min'=>2,':max'=>4]]
                $criteria->condition = $condition[0];
                if (array_key_exists(1, $condition)) {
                    $criteria->params = $condition[1];
                }
            } else {
                // 关联数组 格式为 ['name'=>'jack ','age'=>18] 或 [id=>[2,4,6]]
                foreach ($condition as $k => $v) {
                    $criteria->compare($k, $v);
                }
            }
        } else if ($condition instanceof CDbCriteria) {
            $criteria = $condition;
        } else {
            $criteria = new CDbCriteria();
        }
        $model = new static;
        // 分页处理
        $pages = new CPagination();
        if (null !== $pageSize) {
            $pages->pageSize = $pageSize;
        }
        if (null !== $currentPage) {
            $pages->currentPage($currentPage - 1);
        }
        $pages->itemCount = $model->count($criteria);
        $pages->applyLimit($criteria);
        // 排序
        if (null !== $order) {
            $columns = self::normalizeOrderBy($order);
            $orders  = [];
            foreach ($columns as $name => $direction) {
                $orders[] = Yii::app()->db->quoteColumnName($name) . ($direction === 'DESC' ? ' DESC' : '');
            }
            $criteria->order = implode(', ', $orders);
        }
        $list = $model->findAll($criteria);
        return array(
            'model' => $model,
            'list'  => $list,
            'pages' => $pages,
        );
    }
    protected static function normalizeOrderBy($columns)
    {
        if (is_array($columns)) {
            return $columns;
        } else {
            $columns = preg_split('/\s*,\s*/', trim($columns), -1, PREG_SPLIT_NO_EMPTY);
            $result  = [];
            foreach ($columns as $column) {
                if (preg_match('/^(.*?)\s+(asc|desc)$/i', $column, $matches)) {
                    $result[$matches[1]] = strcasecmp($matches[2], 'desc') ? 'ASC' : 'DESC';
                } else {
                    $result[$column] = 'ASC';
                }
            }
            return $result;
        }
    }
}

```
 


[0]: https://img1.tuicool.com/MrqYr2N.jpg 
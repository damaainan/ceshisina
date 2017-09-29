# PHP易错面试题收集-持续更新

[![](https://pic1.zhimg.com/74ea936d4_xs.jpg) 舒铭](https://www.zhihu.com/people/phpgod)   · 17 天前

```php
    <?php
    
    class SomeClass
    {
        private $properties = [];
        public function __set($name, $value)
        {
           $this->properties[$name] = $value;
        }
        public function __get($name)
        {
            return $this->properties[$name];
        }
    }
    
    
    $obj = new SomeClass();
    $obj->name = 'phpgod';
    $obj->age = 2;
    $obj->gender = 'male';
    
    var_dump($obj->name);
    //output:string(6) "phpgod"
    var_dump(isset($obj->name));
    //output:bool(false)，你的答案对了吗？为什么
```

```php
    <?php
       //第2题：
        $arr = [1,2,3];
        foreach($arr as &$v) {
            //nothing todo.
        }
        foreach($arr as $v) {
            //nothing todo.
        }
        var_export($arr);
        //output:array(0=>1,1=>2,2=>2)，你的答案对了吗？为什么
    ?>
```

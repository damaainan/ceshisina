# PHP中类静态调用和范围解析操作符的区别

 时间 2018-01-07 13:14:53  

原文[http://blog.p2hp.com/archives/4952][1]

```php
    <?php
    //在子类或类内部用“::”调用本类或父类时，不是静态调用方法，而是范围解析操作符。
    
    
    class ParentClass {
        public static $my_static = 'parent var ';
        function test() {
            self::who();    // 输出 'parent' 是范围解析，不是静态调用
            $this->who();    // 输出 'child'
            static::who();  // 延迟静态绑定 是范围解析，不是静态调用
        }
    
        function who() {
            echo 'parent<br>';
        }
    }
    
    class ChildClass extends ParentClass {
        public static $my_static = 'child var ';
        function who() {
            echo 'child<br>';
        }
    }
    
    $obj = new ChildClass();
    $obj->test();
    
    
    echo ChildClass::$my_static;//静态调用
```
上面输出

parent

child

child

child var

[1]: http://blog.p2hp.com/archives/4952
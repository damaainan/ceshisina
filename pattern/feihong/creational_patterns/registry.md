### 注册树模式
注册树模式(Registry)通过利用静态方法，更方便的存取数据；也可用于避免频繁的创建对象。

代码：
``` php
<?php
namespace Yjc;

class Register
{
    private static $objs = [];

    public static function set($key ,$obj){
        self::$objs[$key] = $obj;
    }

    public static function get($key){
        if(isset(self::$objs[$key])){
            return self::$objs[$key];
        }

        return false;
    }

    public static function remove($key){
        if(isset(self::$objs[$key])){
            unset(self::$objs[$key]);
        }
    }

}
```

测试：
``` php
$db = DbSingleton::getInstance();
Register::set('db', $db);

$db1 = Register::get('db');
$db2 = Register::get('db');

var_dump($db);
var_dump($db1);
var_dump($db2);
```
输出：
```
object(Yjc\Db)#2 (0) {
}
object(Yjc\Db)#2 (0) {
}
object(Yjc\Db)#2 (0) {
}
```
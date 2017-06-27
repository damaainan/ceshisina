### 单例模式

作为对象的创建模式，单例模式确保某一个类只有一个实例，而且自行实例化并向整个系统提供这个实例。这个类称为单例类。

单例模式(Singleton)相比工厂模式，实现了运行过程中一个类只实例化一次，减少性能开销。

单例模式的特点：

- 单例类只能有一个实例。
- 单例类必须自己创建自己的唯一实例。
- 单例类必须给所有其他对象提供这一实例。

示例：我们新建Factory类：
``` php
namespace Yjc\Singleton;
class Db
{
    public function query(){
        echo 'query'.PHP_EOL;
    }

    public function exec(){
        echo 'exec'.PHP_EOL;
    }
}

class Factory extends Db
{
    private static $db = null;

    /**
     * 单例工厂模式
     */
    public static function getInstance(){
        if(self::$db === null){
            self::$db = new Db();
        }
        return self::$db;
    }

    /**私有化构造方法，禁止外部实例化*/
    private function __construct() {}

    /**禁止克隆*/
    private function __clone() {}
}
```

测试：
``` php
$db = Factory::getInstance();
$db->query();
var_dump($db);

$db2 = Factory::getInstance();
$db->exec();
var_dump($db2);
```
输出：
```
query
object(Yjc\Db)#2 (0) {
}
exec
object(Yjc\Db)#2 (0) {
}
```
从对象ID可以看出，实例化出来的是同一个对象。数据库类、缓存类非常适合使用单例模式，减少不必要的连接。

单例模式其实还区分饿汉式、懒汉式。我们刚才实现的是懒汉式：用到才创建实例，但需要判断实例是否存在。饿汉式就是当类装载的时候就会创建类的实例，不管你用不用，先创建出来，然后每次调用的时候，就不需要再判断，节省了运行时间。

但PHP里禁止在类的属性里直接赋值表达式(new对象)，所以就忽略掉饿汉式、懒汉式啦。
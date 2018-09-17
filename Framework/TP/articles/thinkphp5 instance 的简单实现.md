# thinkphp5 instance 的简单实现

 时间 2017-07-28 18:13:01  

原文[http://www.miaoqiyuan.cn/p/php-class-instance][1]


最近学习 ThinkPHP5，第一次看到 TestClass::instance() 就能创建 TestClass 实例的方法。感到很好奇，翻阅 ThinkPHP 的源代码，大体理解了 它的 设计思想，非常的先进。

再次从零造车一次（昨天的造车： [angularjs的数组传参方式的简单实现][3] http://www.miaoqiyuan.cn/p/angularjs-array-arguments），来讲讲 他的 具体实现。本文（ [thinkphp5 instance 的简单实现][4] ）为原创文章，原文地址：http://www.miaoqiyuan.cn/p/php-class-instance，转载请注明出处。 

老规矩，直接上代码：

```php
<?php
class TestClass {

    public static function instance() {
        return new self();
    }

    public $data = [];

    public function __set($name, $val) {
        return $this->data[$name] = $val;
    }

    public function __get($name) {
        return $this->data[$name];
    }
}

$app1 = TestClass::instance();
$app1->key = 'Application 1';
echo $app1->key . '<br />';
?>
```
为了方便调用，也模仿 ThinkPHP 写了一个助手函数

```php
<?php
function app() {
    return TestClass::instance();
}

$app2 = app();
$app2->key = 'Application 2';
echo $app2->key . '<br />';
?>
```
这样就简单的实现了 instance。

不过这种方法还有一个小问题，试想以下，调用100次，就需要创建100个实例，想想都觉得可怕。

给 Test 类 增加一个 静态属性，将创建的实例保存到这里。下次如果需要调用，则直接调用这个实例。

```php
<?php
class TestClass {

    public static $instance; //用于缓存实例

    public $data = [];

    public static function instance() {
        //如果不存在实例，则返回实例
        if (empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __set($name, $val) {
        return $this->data[$name] = $val;
    }

    public function __get($name) {
        return $this->data[$name];
    }

}

function app($option = []) {
    return TestClass::instance($option);
}

header('content-type:text/plain');

$result = [];
$app1 = app();
$app1->key = "Application 1"; //修改 key 为 Application 1
$result['app1'] = [
    'app1' => $app1->key,   //实例中 key 为 Application 1
];

// 创建 app2，因为 instance 已经存在实例，直接返回 缓存的实例
$app2 = app();
$result['app2'] = [
    'setp1' => [
        'app1' => $app1->key,   // Application 1
        'app2' => $app2->key,   //因为直接调用的实例的缓存，所以 key 也是 Application 1
    ],
];

// 无论 app1，app2 都对在内存中 对应的同一个实例，无论通过谁修改，都能改变值
$app1->key = "Application 2";
$result['app2']['setp2'] = [
    'app1' => $app1->key,   // Application 2
    'app2' => $app2->key,   // Application 2
];
print_r($result);
?>
```
通过上边的实验，可以看到 无论调用多少次，都会使用同一个实例。这样就解决了效率低的问题。

到现在基本就满足大多数情况了，唯一的小缺陷，就是 可能 实例的 初始参数不同，这样没法灵活调用（常见的比如同一个程序调用两个数据库）。在 上边的 例子中稍作改造，以传入的参数为key，将不通的 实例缓存到数组中 就可以解决。

```php
<?php
class TestClass {

    public static $instance = [];   //用于缓存实例数组
    public $data = [];

    public function __construct($opt = []) {
        $this->data = $opt;
    }

    public static function instance($option = []) {
        // 根据传入的参数 通过 serialize 转换为字符串，md5 后 作为数组的 key
        $instance_id = md5(serialize($option));
        //如果 不存在实例，则创建
        if (empty(self::$instance[$instance_id])) {
            self::$instance[$instance_id] = new self($option);
        }
        return self::$instance[$instance_id];
    }

    public function __set($name, $val) {
        return $this->data[$name] = $val;
    }

    public function __get($name) {
        return $this->data[$name];
    }

}

function app($option = []) {
    return TestClass::instance($option);
}

header('content-type:text/plain');

$result = [];
//传入 初始数据
$app1 = app(['key' => '123']);
$result['init'] = $app1->key;   // 使用 传入的数据，即：123
$app1->key = "app1";
$result['app'] = $app1->key;    // 现在值改为了 自定义的 app1了
print_r($result);

$result = [];
// 创建 app2，注意 初始参数不一样
$app2 = app();
// 因为初始参数不一样，所以还是创建新的实例
$app2->key = "app2";
$result['app1'] = $app1->key;   // app1
$result['app2'] = $app2->key;   // app2
print_r($result);

$result = [];
// 创建 app3，传入的参数 和 app1 一样，所以会直接返回 和app1相同 的 实例
$app3 = app(['key' => '123']);
$result['log'] = [
    'app1' => $app1->key,   // app1
    'app2' => $app2->key,   // app2
    'app3' => $app3->key,   // app1
];

// 设置 app3 的key，会自动修改 app1 的值，因为他们两个是同一个实例
$app3->key = 'app3';
$result['app3_set'] = [
    'app1' => $app1->key,   // app3
    'app2' => $app2->key,   // app2
    'app3' => $app3->key,   // app3
];

// 同理，设置 app1 的key，app3 的 key 也会修改
$app1->key = 'app1';
$result['app1_set'] = [
    'app1' => $app1->key,   // app1
    'app2' => $app2->key,   // app2
    'app3' => $app3->key,   // app1
];
print_r($result);
?>
```
[1]: http://www.miaoqiyuan.cn/p/php-class-instance
[3]: http://www.miaoqiyuan.cn/p/angularjs-array-arguments
[4]: http://www.miaoqiyuan.cn/p/php-class-instance
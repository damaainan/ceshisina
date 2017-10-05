## PHP大小写不敏感问题汇总


PHP的大小写敏感可以总结写“变量敏感，函数不敏感”，所有变量、常量、PHP.ini中配置参数都是敏感的，而函数、类、类中的方法、魔术常量，都是不区分大小写的，PHP6的命名空间还未测试，但应该是区分大小写的。但我还是建议使用统统敏感的写法， 推荐大家始终坚持“大小写敏感”，遵循统一的代码规范，不定义大小相同的函数和方法。

## 一、大小写敏感

### 1. 变量名区分大小写

所有变量均区分大小写，包括普通变量以及$_GET,$_POST,$_REQUEST,$_COOKIE,$_SESSION,$GLOBALS,$_SERVER,$_FILES,$_ENV等；
```php
    <?php
    $abc = 'abcd';
    echo $abc; //输出 'abcd';
    
    echo $aBc; //无输出
    echo $ABC; //无输出
```

### 2. 常量名默认区分大小写，通常都写为大写
```php
    <?php
    define("ABC","Hello World");
    echo ABC;   //输出 Hello World
    echo abc;   //输出 abc
```

### 3. php.ini配置项指令区分大小写

如 file_uploads = 1 不能写成 File_uploads = 1

## 二、大小写不敏感

### 1. 函数名、方法名、类名 不区分大小写，但推荐使用与定义时相同的名字
```php
    function show(){
    echo "Hello World";
    }
    show(); //输出 Hello World    推荐写法
    SHOW(); //输出 Hello World
```
```php
    class cls{
    static function func(){
    echo "hello world";
    }
    }
    
    Cls::FunC();  //输出hello world
```

### 2. 魔术常量不区分大小写，推荐大写

包括： `__LINE__` 、 `__FILE__` 、 `__DIR__` 、 `__FUNCTION__` 、 `__CLASS__` 、 `__METHOD__` 、 `__NAMESPACE__` 。

    echo __line__;  //输出 2
    echo __LINE__;  //输出 3
    

### 3. NULL、TRUE、FALSE不区分大小写

    $a = null;
    $b = NULL;
    
    $c = true;
    $d = TRUE;
    
    $e = false;
    $f = FALSE;
    
    var_dump($a == $b); //输出 boolean true
    var_dump($c == $d); //输出 boolean true
    var_dump($e == $f); //输出 boolean true
    

### 4.类型强制转换，不区分大小写

包括

* (int)，(integer) – 转换成整型
* (bool)，(boolean) – 转换成布尔型
* (float)，(double)，(real) – 转换成浮点型
* (string) – 转换成字符串
* (array) – 转换成数组
* (object) – 转换成对象
```php
    $a=1;
    var_dump($a);  //输出 int 1
    
    $b=(STRING)$a;
    var_dump($b);  //输出string ';1'; (length=1)
    
    $c=(string)$a;
    var_dump($c);  //输出string ';1'; (length=1)
```

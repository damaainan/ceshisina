# PHP编码小结

 时间 2017-10-24 13:48:06  

原文[https://www.reallyli.xin/index.php/archives/4/][1]


![][3]


* sql过长
```php
    $sql = <<<SQL
    SELECT delivery_id
    FROM d_test
    WHERE delivery_id
    IN (123,234)
    GROUP BY delivery_id
    HAVING SUM(send_number) <= 0;
SQL;
```

* if等控制结构条件过长
```php
    if ($a > 0
        && $b > 0
        && $c > 0
        && $d > 0
        && $e > 0) {
    
    }
```

* 方法或函数参数大于三个换行
```php
    public function tooLangFunction(
          $valueOne   = '',
          $valueTwo   = '',
          $valueThree = '',
          $valueFour  = '',
          $valueFive  = '',
          $valueSix   = '')
    {
        //coding...
    }
```

* 链式操作超过两个
```php
    $this->nameTest->functionOne()
                   ->functionTwo()
                   ->functionThree();
```

* 方法(类/trait中)名称必须符合 camelCase 式的小写开头驼峰命名规范。
```php
    class StudlyCaps
    {
        public function studlyCaps()
        {
            // coding...
        }
    }
```

* 函数名称必须符合 snake_case 式的下划线式命名规范。
```php
    function snake_case()
    {
        // coding...
    }
```

* 类/trait/Interface的命名必须遵循 StudlyCaps 大写开头的驼峰命名规范。
```php
    class StudlyCaps
    {
    
    }
    
    trait StudlyCaps
    {
    
    }
    
    Interface StudlyCaps
    {
    
    }
```

* 私有的(private)方法(类/trait中)名称必须符合 _camelCase 式的前置下划线小写开头驼峰命名规范。
```php
    class StudlyCaps
    {
        private function _studlyCaps()
        {
            // coding...
        }
    }
```

* 方法名称 第一个单词 为动词。
```php
    class StudlyCaps
    {
        public function doSomething()
        {
            // coding...
        }
    }
```

* 每个 namespace 命名空间声明语句块 和 use 声明语句块后面，必须 插入一个空白行。
```php
    namespace Standard;
    // 空一行
    use Test\TestClass;//use引入类
    // 空一行
```

* 数组php5.4以后，使用 []代替array
```php
    $a = [
        'aaa' => 'aaa',
        'bbb' => 'bbb'
    ];
```

* 单引号多引号，
字符串中无变量，单引号

字符串中有变量，双引号

```php
    $str = 'str';
    $arg = "$str";
```

* 声明类或者方法或函数添加描述&属性描述&作者
```php
    /**
     * 类描述
     *
     * desc
     */
    class StandardExample
    {
      /**
       *  常量描述.
       *
       * @var string
       */
      const THIS_IS_A_CONST = '';
    
      /**
       * 属性描述.
       *
       * @var string
       */
      public $nameTest = '';
    
      /**
       * 构造函数.
       *
       * 构造函数描述
       * @author name <email>
       * @param  string $value 形参名称/描述
       * @return 返回值类型        返回值描述
       * 返回值类型：string，array，object，mixed（多种，不确定的），void（无返回值）
       */
      public function __construct($value = '')
      {
        // coding...
      }
    }
```

* api方法提供测试样例example
```php
    /**
     * 成员方法名称.
     *
     * 成员方法描述
     *
     * @param  string $value 形参名称/描述
     *
     * @example domain/api/controller/action?argu1=111&argu2=222
     */
    public function testFunction($value = '')
    {
        // code...
    }
```

* 使用try…catch…
```php
    try {
    
        // coding...
    
    } catch (\Exception $e) {
      // coding...
    }
```

* 连续调用多个方法(大于3个)使用foreach
```php
    // 改写doSome为doSomething
    class StandardExample
    {
      /**
       * 方法列表
       *
       * @var array
       */
      private $_functionList = [];
    
      public function __construct($functionList = array())
      {
        $this->_functionList = $value;
      }
    
      public function doSome()
      {
        $this->functionOne();
        $this->functionTwo();
        $this->functionThree();
        $this->functionFour();
      }
    
      public function doSomething()
      {
          foreach($this->_functionList as $function) {
              $this->$function();
          }
      }
    
      ...
    }
```

* 文件顶部进行版权声明
```php
    // +----------------------------------------------------------------------
    // | Company Name  xx服务
    // +----------------------------------------------------------------------
    // | Copyright (c) 2017 http://domain All rights reserved.
    // +----------------------------------------------------------------------
    // | Author: name <email>
    // +----------------------------------------------------------------------
```

[1]: https://www.reallyli.xin/index.php/archives/4/

[3]: https://img1.tuicool.com/r6F7VvF.png
# [PHP 三种方式实现链式操作][0]

1月18日发布 


在php中有很多字符串函数，例如要先过滤字符串收尾的空格，再求出其长度，一般的写法是：

    strlen(trim($str))

如果要实现类似js中的链式操作，比如像下面这样应该怎么写？

    $str->trim()->strlen()    

下面分别用三种方式来实现：

#### 方法一、使用魔法函数__call结合call_user_func来实现

**思想：**首先定义一个字符串类StringHelper，构造函数直接赋值value，然后链式调用`trim()`和`strlen()`函数，通过在调用的魔法函数`__call()`中使用`call_user_func`来处理调用关系，实现如下：

```php
    <?php
    
    
    class StringHelper 
    {
        private $value;
        
        function __construct($value)
        {
            $this->value = $value;
        }
    
        function __call($function, $args){
            $this->value = call_user_func($function, $this->value, $args[0]);
            return $this;
        }
    
        function strlen() {
            return strlen($this->value);
        }
    }
    
    $str = new StringHelper("  sd f  0");
    echo $str->trim('0')->strlen();
```

终端执行脚本：

    php test.php 
    8

#### 方法二、使用魔法函数__call结合call_user_func_array来实现

```php
    <?php
    
    
    class StringHelper 
    {
        private $value;
        
        function __construct($value)
        {
            $this->value = $value;
        }
    
        function __call($function, $args){
            array_unshift($args, $this->value);
            $this->value = call_user_func_array($function, $args);
            return $this;
        }
    
        function strlen() {
            return strlen($this->value);
        }
    }
    
    $str = new StringHelper("  sd f  0");
    echo $str->trim('0')->strlen();
```

说明：

    array_unshift(array,value1,value2,value3...)

`array_unshift()` 函数用于向数组插入新元素。新数组的值将被插入到数组的开头。

`call_user_func()`和`call_user_func_array`都是动态调用函数的方法，区别在于参数的传递方式不同。

#### 方法三、不使用魔法函数__call来实现

只需要修改`_call()`为`trim()`函数即可：

```php
    public function trim($t)
    {
        $this->value = trim($this->value, $t);
        return $this;
    }
```

重点在于，返回$this指针，方便调用后者函数。

[0]: https://segmentfault.com/a/1190000008159324
[1]: /t/php/blogs
[2]: /u/zailushang

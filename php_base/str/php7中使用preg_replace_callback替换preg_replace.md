# php7中使用preg_replace_callback替换preg_replace 

[叶秋][0] 发布于 07-24 16:08  已修改  469 点击

php7中，`preg_replace()`不再支持"\e" (PREG_REPLACE_EVAL)，需要使用`preg_replace_callback()`来代替。 比如下面的代码在php7是不行的。 

```php 

$out = "<?php \n" . '$k = ' . preg_replace("/(\'\\$[^,]+)/e", "stripslashes(trim('\\1','\''));", var_export($t, true)) . ";\n"; 
```

需要使用`preg_replace_callback`函数改造下。 

```php 

$out = "<?php \n" . '$k = ' . preg_replace_callback("/(\'\\$[^,]+)/", function ($matches) { return stripslashes(trim($matches[0], '\'')); }, var_export($t, true)) . ";\n"; 

```
如果在回调函数中要使用类的方法 

```php 

class Template { function fetch_str($source) { return preg_replace("/{([^\}\{\n]*)}/e", "\$this->select('\\1');", $source); } public function select($tag) { return stripslashes(trim($tag)); } } 
```
则需要改造成下面的形式。 

```php 
class Template { function fetch_str($source) { return preg_replace_callback("/{([^\}\{\n]*)}/", [$this, 'select'], $source); } public function select($tag) { return stripslashes(trim($tag[1])); } } 

```

php7中，preg_replace()不再支持"\e" (PREG_REPLACE_EVAL)，需要使用preg_replace_callback()来代替。

比如下面的代码在php7是不行的。
```php
    $out = "<?php \n" . '$k = ' . preg_replace("/(\'\\$[^,]+)/e", "stripslashes(trim('\\1','\''));", var_export($t, true)) . ";\n";
```
需要使用preg_replace_callback函数改造下。
```php
    $out = "<?php \n" . '$k = ' . preg_replace_callback("/(\'\\$[^,]+)/", function ($matches) {
                                return stripslashes(trim($matches[0], '\''));
                            },
                                var_export($t, true)) . ";\n";
```
如果在回调函数中要使用类的方法
```php
    class Template {
        function fetch_str($source)
        {
            return preg_replace("/{([^\}\{\n]*)}/e", "\$this->select('\\1');", $source);
        }
    
        public function select($tag) 
        {
            return stripslashes(trim($tag));
        }
    }
```
则需要改造成下面的形式。
```php
    class Template {
        function fetch_str($source)
        {
            return preg_replace_callback("/{([^\}\{\n]*)}/", [$this, 'select'], $source);
        }
    
        public function select($tag) 
        {
            return stripslashes(trim($tag[1]));
        }
    }
```
[0]: https://www.shiqidu.com/u/%E5%8F%B6%E7%A7%8B
# PHP模板引擎的原理与实践 

    发表于 2015-08-21   |   分类于  [后端开发][0]   |  | 阅读次数 : 52

## 0x00 模板引擎的原理

模板引擎就是在模板文件中使用一系列提前约定好的标签代替原生PHP代码，通过访问一个PHP的入口文件，会有一个PHP编译文件根据约定替换模板内标签以及标签内变量，最终将模板文件编译成一个PHP文件，然后展示到浏览器中。

​### 模板文件  
前端开发者将前端代码中的所有数据替换成与服务端开发者约定好的标签及变量名。

### PHP入口文件

服务端开发者将前端代码中所需要的变量注入到前端。

### PHP编译文件

该文件中是模板引擎中的核心，在这里我们定义了 标签语句 等，通过读取模板文件，使用正则表达式去匹配模板文件中与后台约定好的标签及变量，并将标签及变量替换成PHP代码，最终生成一个前后端结合的PHP文件。

## 0x01 约定标签

PHP的语法中，包括 if...elseforeach 等语法，以及需要替换的普通变量 $value , PHP原生语句，注释等等。一般情况下，大家习惯使用以下标签

```
{$value}

// 对应原生

<?php echo $value; ?>
```
    
```
{foreach $array}

    {V}

{/foreach}

// 对应原生
```
```php
<?php

foreach($array as $K => $V) {

    echo $V;

}

?>
```
    

```
{if $data == 'XiaoMing'}

    I'm XiaoMing;

{else if $data == 'XiaoHong' }

    I'm XiaoHong;

{else}

    I'm XiaoLi;

{/if}

// 对应原生
```

```php 
<?php

    if($data == 'XiaoMing') {

        echo "XiaoMing";

    } else if ($data == 'XiaoHong') {

        echo "XiaoHong";

    } else {

        echo "XiaoLi";

    }

?>
```
等等，这些大家可以参考 SmartyDiscuz 的标签。

## 0x02 构造正则表达式匹配标签及变量 [正则表达式30分钟入门教程][1]

对于正则表达式，大家可以戳进上面的教程，简单易用。  
下面直接给出相关标签的正则表达式。

```
// 匹配的正则表达式

$this->T_P[] = "#\{\\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\}#";    // 匹配普通变量

$this->T_P[] = "#\{foreach \\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\}#";     // 匹配{foreach $array}

$this->T_P[] = "#\{\/(foreach|if)\}#";    // 匹配{/foreach} or {/if}

// 对应的替换内容

$this->T_R[] = "<?php echo \$this->value['\\1']; ?>";

$this->T_R[] = "<?php foreach((array)\$this->value['\\1'] as \$K => \$V) { ?>";

$this->T_R[] = "<?php }?>";
```
## 0x03 对模板文件进行编译

编译就是对模板文件读取，使用正则表达式对模板标签及变量进行替换，最终将替换后的内容保存在一个PHP文件中即可。

使用的相关函数:

```php
<?php

// 读取文件内容

file_get_contents($file)

// 正则替换

preg_replace(pattern, replacement, subject);

?>
```
## 0x04 结束并声明

通过这三步，一个简单的模板引擎就已经制作成功了，但是模板引擎的工作原理上面已经说过了，在进行正则匹配替换的过程中，效率极低，PHP自身效率本来就很低，在加上正则匹配，就可想而知了。所以，一般情况下，模板引擎都会有自己的缓存机制，将解析成功的内容保存成一个html文件，并设置缓存有效期，这样可以很大程度上提升效率。

### 声明

本文是学习[《PHP核心技术与最佳实践》列旭松 陈文著][2] 第6章 PHP模板引擎的原理与实践 学习笔记。  
感谢作者！

[0]: /categories/后端开发/
[1]: http://www.jb51.net/tools/zhengze.html
[2]: http://item.jd.com/11123177.html
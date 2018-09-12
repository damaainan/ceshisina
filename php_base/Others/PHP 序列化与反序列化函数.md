# PHP 序列化与反序列化函数

 时间 2017-05-01 18:28:00  

原文[http://www.cnblogs.com/yuxb/p/6792413.html][1]

#### 序列化与反序列化

把复杂的数据类型压缩到一个字符串中

`serialize()` 把变量和它们的值编码成文本形式

`unserialize()` 恢复原先变量

 1.创建一个$arr数组用于储存用户基本信息，并在浏览器中输出查看结果 ； 
```php
$arr=array();
$arr['name']='张三';
$arr['age']='22';
$arr['sex']='男';
$arr['phone']='123456789';
$arr['address']='上海市浦东新区';
var_dump($arr)；
```
输出结果：
 
    array(5) { 
    ["name"]=> string(6) "张三" 
    ["age"]=> string(2) "22" 
    ["sex"]=> string(3) "男" 
    ["phone"]=> string(9) "123456789" 
    ["address"]=> string(21) "上海市浦东新区"
     } 
    
2.将$arr数组进行序列化赋值给$info字符串，并在浏览器中输出查看结果；
```php
$info=serialize($arr);
var_dump($info);
```
输出结果：
 
    string(140) "a:5:{s:4:"name";s:6:"张三";s:3:"age";s:2:"22";s:3:"sex";s:3:"男";s:5:"phone";s:9:"123456789";s:7:"address";s:21:"上海市浦东新区";}" 
    

使用序列化`serialize($arr)`函数，将数组中元素的键和值按照规则顺序连接成字符串。`a：5`标志序列化为array包含5个键值对，`s：4`标志内容为字符串包含4个字符。

通过序列化我们可以将一些模块化的数据使用字符串的形式存储在数据库或session等，可以减少创建众多繁琐的数据表字段，当然序列化为字符串存储会增加额外的空间，应合理的设计和应用。
    
    
3.最后使用`unserialize($info)`反序列化将字符串还原成我们需要的数组模式；
```php
$zhangsan=unserialize($info);
var_dump($zhangsan);
```
输出结果：

    array(5) {
        ["name"]=> string(6) "张三" 
        ["age"]=> string(2) "22" 
        ["sex"]=> string(3) "男" 
        ["phone"]=> string(9) "123456789" 
        ["address"]=> string(21) "上海市浦东新区" 
    }


[1]: http://www.cnblogs.com/yuxb/p/6792413.html
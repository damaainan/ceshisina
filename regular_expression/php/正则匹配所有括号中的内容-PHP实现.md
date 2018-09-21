## 正则匹配所有括号中的内容&amp;PHP实现

来源：[https://segmentfault.com/a/1190000015348826](https://segmentfault.com/a/1190000015348826)

 **`正则表达式：`(?<=【)[^】]+``** 
注：以匹配中文括号中内容为例，如果匹配非中文括号，则需要在括号前增加转义符
 **`PHP实现示例：`** 

```php
<?php
$strSubject = "abc【111】abc【222】abc【333】abc";
$strPattern = "/(?<=【)[^】]+/";
$arrMatches = [];
preg_match_all($strPattern, $strSubject, $arrMatches);

var_dump($arrMatches);
```
 **`执行结果：`** 

```
~ » php mytest/test_preg.php                                                                                                                                                                  iwaimai@bogon
array(1) {
  [0]=>
  array(3) {
    [0]=>
    string(3) "111"
    [1]=>
    string(3) "222"
    [2]=>
    string(3) "333"
  }
}
```
 **`解析：`**    
1、`(?<=【)`
第一个表达式是一个『非获取匹配』，即匹配括号，但并不获取括号；

![][0]

2、`[^】]+`
第二个表达式中`[]`匹配单个字符，`^】`代表除了`】`的字符，`+`是限定符代表匹配前面子表达式一次或多次，即匹配除了`】`的连续多个字符；

组合起来即实现了预期效果~

[0]: ../img/bVbcy5d.png
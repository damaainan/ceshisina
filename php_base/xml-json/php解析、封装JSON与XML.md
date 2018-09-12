## php解析、封装JSON与XML

来源：[http://www.cnblogs.com/meichao/p/9356028.html](http://www.cnblogs.com/meichao/p/9356028.html)

时间 2018-07-23 22:47:00

 
比如阿里、腾讯、百度在提供第三方服务的时候都是通过JSON或XML进行传递数据。在工作的时候和第三方公司对接的时候也是这两种数据格式，所以在这总结一下这两种格式的封装和解析。
 
## JSON的封装和解析  
 
## 封装JSON数据  
 
```php
<?php
$items = array(
  array('id'=>1,'name'=>"衣服",'parId'=>0),
  array('id'=>2,'name'=>"书籍",'parId'=>0),
  array('id'=>3,'name'=>"T恤",'parId'=>1),
  array('id'=>4,'name'=>"裤子",'parId'=>1),
  array('id'=>5,'name'=>"鞋子",'parId'=>1),
  array('id'=>6,'name'=>"皮鞋",'parId'=>5),
  array('id'=>7,'name'=>"运动鞋",'parId'=>5),
  array('id'=>8,'name'=>"耐克",'parId'=>7),
  array('id'=>9,'name'=>"耐克",'parId'=>3),
  array('id'=>10,'name'=>"鸿星尔克",'parId'=>7),
  array('id'=>11,'name'=>"小说",'parId'=>2),
  array('id'=>12,'name'=>"科幻小说",'parId'=>11),
  array('id'=>13,'name'=>"古典名著",'parId'=>11),
  array('id'=>14,'name'=>"文学",'parId'=>2),
  array('id'=>15,'name'=>"四书五经",'parId'=>14)
);
$message = json_encode($items,JSON_UNESCAPED_UNICODE);
echo $message;
```
 
效果：
 
![][0]
 
描述：第二参数是将中文不转为UNICODE的编码（JSON_UNESCAPED_UNICODE），默认转换成UNICODE的编码；
 
## 解析JSON数据
 
```php
<?php
$str = '[{"id":1,"name":"衣服","parId":0},{"id":2,"name":"书籍","parId":0},{"id":3,"name":"T恤","parId":1},{"id":4,"name":"裤子","parId":1},{"id":5,"name":"鞋子","parId":1},{"id":6,"name":"皮鞋","parId":5},{"id":7,"name":"运动鞋","parId":5},{"id":8,"name":"耐克","parId":7},
{"id":9,"name":"耐克","parId":3},{"id":10,"name":"鸿星尔克","parId":7},{"id":11,"name":"小说","parId":2},{"id":12,"name":"科幻小说","parId":11},{"id":13,"name":"古典名著","parId":11},{"id":14,"name":"文学","parId":2},{"id":15,"name":"四书五经","parId":14}]';
$res = json_decode($str, true);
var_dump($res);
```
 
效果：
 
![][1]
 
描述：第二个参数是将数据转换为数组的格式（true），默认是json对象的格式
 
## XML的封装和解析  
 
## 封装XML数据
 
```php
<?php
function data_to_xml($data) {
    $xml = '';
    foreach ($data as $key => $val) {
        if(is_numeric($key)) $key = "item id=$key";
        $xml    .=  "<$key>";
        $xml    .=  is_array($val) ? data_to_xml($val) : $val;
        list($key) = explode(' ', $key);
        $xml    .=  "</$key>";
    }
    return $xml;
}
	$items = array(
  array('id'=>1,'name'=>"衣服",'parId'=>0),
  array('id'=>2,'name'=>"书籍",'parId'=>0),
  array('id'=>3,'name'=>"T恤",'parId'=>1),
  array('id'=>4,'name'=>"裤子",'parId'=>1),
  array('id'=>5,'name'=>"鞋子",'parId'=>1),
  array('id'=>6,'name'=>"皮鞋",'parId'=>5),
  array('id'=>7,'name'=>"运动鞋",'parId'=>5),
  array('id'=>8,'name'=>"耐克",'parId'=>7),
  array('id'=>9,'name'=>"耐克",'parId'=>3),
  array('id'=>10,'name'=>"鸿星尔克",'parId'=>7),
  array('id'=>11,'name'=>"小说",'parId'=>2),
  array('id'=>12,'name'=>"科幻小说",'parId'=>11),
  array('id'=>13,'name'=>"古典名著",'parId'=>11),
  array('id'=>14,'name'=>"文学",'parId'=>2),
  array('id'=>15,'name'=>"四书五经",'parId'=>14)
);
	$xml = '<root>';
	$xml .= data_to_xml($items);
	$xml .= '</root>';
	echo $xml;
```
 
效果：
 
![][2]
 
## 解析XML数据
 
```php
<?php
	$str = '<root>
    <item id="0">
        <id>1</id>
        <name>衣服</name>
0</parId>
    </item>
    <item id="1">
        <id>2</id>
        <name>书籍</name>
0</parId>
    </item>
    <item id="2">
        <id>3</id>
        <name>T恤</name>
1</parId>
    </item>
    <item id="3">
        <id>4</id>
        <name>裤子</name>
1</parId>
    </item>
    <item id="4">
        <id>5</id>
        <name>鞋子</name>
1</parId>
    </item>
    <item id="5">
        <id>6</id>
        <name>皮鞋</name>
5</parId>
    </item>
    <item id="6">
        <id>7</id>
        <name>运动鞋</name>
5</parId>
    </item>
    <item id="7">
        <id>8</id>
        <name>耐克</name>
7</parId>
    </item>
    <item id="8">
        <id>9</id>
        <name>耐克</name>
3</parId>
    </item>
    <item id="9">
        <id>10</id>
        <name>鸿星尔克</name>
7</parId>
    </item>
    <item id="10">
        <id>11</id>
        <name>小说</name>
2</parId>
    </item>
    <item id="11">
        <id>12</id>
        <name>科幻小说</name>
11</parId>
    </item>
    <item id="12">
        <id>13</id>
        <name>古典名著</name>
11</parId>
    </item>
    <item id="13">
        <id>14</id>
        <name>文学</name>
2</parId>
    </item>
    <item id="14">
        <id>15</id>
        <name>四书五经</name>
14</parId>
    </item>
</root>';
$xml =simplexml_load_string($str);
$xmljson= json_encode($xml);
$res=json_decode($xmljson,true);
var_dump($res);
```
 
效果：
 
![][3]
 


[0]: https://img2.tuicool.com/26rUvqU.png 
[1]: https://img0.tuicool.com/RBBfMvU.png 
[2]: https://img1.tuicool.com/uqqMzyf.png 
[3]: https://img2.tuicool.com/byQv6ja.png 
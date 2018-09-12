## PHP原生 DOM 对象操作 XML并生成sitemap.xml

对于操作XML类型文件，PHP内置有一套DOM对象可以进行处理。对XML的操作，从创建、添加到修改、删除都可以使用DOM对象中的函数来进行。

**创建**

创建一个新的XML文件，并且写入一些数据到这个XML文件中。

```php
/*
* 创建xml文件
*/
 
$info = array(
    array('obj' => 'power','info' => 'power is shutdown'),
    array('obj' => 'memcache','info' => 'memcache used than 90%'),
    array('obj' => 'cpu','info' => 'cpu used than 95%'),
    array('obj' => 'disk','info' => 'disk is removed')
);//用来写入的数据
 
$dom = new DOMDocument('1.0');
$dom->formatOutput = true;//格式化
 
$eventList = $dom->createElement('EventList');//创建根节点EventList
$dom->appendChild($eventList);//添加根节点
 
for($i = 0; $i < count($info); $i++){
    $event = $dom->createElement('event');//创建节点event
    $text = $dom->createTextNode('PHP'.$i);//创建文本节点，值为PHP0,PHP1...
    $event->appendChild($text);//将文本节点添加到节点event，做为节点event的值
 
    $attr_obj = $dom->createAttribute('obj');//创建属性obj
    $attr_obj->value = $info[$i]['obj'];//为obj属性赋值
    $event->appendChild($attr_obj);//将obj属性添加到event节点中，做为event节点的属性
 
    $attr_info = $dom->createAttribute('info');
    $attr_info->value = $info[$i]['info'];
    $event->appendChild($attr_info);
 
    $eventList->appendChild($event);//将event节点添加到根节点EventList中
}
 
//echo $dom->saveXML();
$dom->save('./t.xml');//保存信息到当前目录下的t.xml文件中
```

上面的代码段可以创建一个XML文件，并添加一些信息到这个文件中，包括值和属性，最终形成的文件为当前目录下的sitemap.xml，可以看一下它的内容。

![111.png][0]

 **读取XML信息&添加新的属性**

以上一节创建的sitemap.xml文件为操作对象，读取出sitemap.xml文件中的信息，并给URL节点添加一个新的属性index，其值为1,2,3...

```php
$dom = new DOMDocument('1.0');
$dom->preserveWhiteSpace = false; //忽略空格（很重要）
$dom->formatOutput = true;
$dom->load('./sitemap.xml'); //加载要操作的文件

$list = $dom->getElementsByTagName('urlset')->item(0);

for($i = 0; $i < $list->childNodes->length; $i ++ )
{
    $node = $list->childNodes->item( $i );

    if( $node->tagName == 'url' )
    {
        $node->setAttribute("index", $i + 1);
    }
}
$dom->save('./sitemap.xml');
```

上面的代码段可以修改XML文件， 再看一下现在的sitemap.xml文件的内容，index属性已经添加上 。

![34.png][1]

**删除节点**

要添加就会有删除。以上节的sitemap.xml文件为操作对象，删除url的属性index为2的节点。

```php
$dom = new DOMDocument('1.0');
$dom->preserveWhiteSpace = false; //忽略空格（很重要）
$dom->formatOutput = true;
$dom->load('./sitemap.xml'); //加载要操作的文件

$list = $dom->getElementsByTagName('urlset')->item(0);

for($i = 0; $i < $list->childNodes->length; $i ++ )
{
    $node = $list->childNodes->item( $i );

    //删除属性 index 为 2 的节点
    if( $node->getAttribute('index') == 2 )
    {
        $list->removeChild( $node );
    }
}
$dom->save('./sitemap.xml');
```

运行后，发现 sitemap.xml的第2个URL被删除

[0]: ../img/1482142404559268.png
[1]: ../img/1482142404703163.png
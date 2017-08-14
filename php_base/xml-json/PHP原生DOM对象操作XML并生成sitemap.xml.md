## PHP原生 DOM 对象操作 XML并生成sitemap.xml

对于操作XML类型文件，PHP内置有一套DOM对象可以进行处理。对XML的操作，从创建、添加到修改、删除都可以使用DOM对象中的函数来进行。

**创建**

创建一个新的XML文件，并且写入一些数据到这个XML文件中。

```php
    //要输出的数据
    $info = array();
    for( $i = 1; $i 'http://www.yduba.com/Index/shows/arid/2.html', 
            'priority'=>'0.9', 
            'lastmod'=>date('Y-m-d\TH:i:s+08:00'), 
            'changefreq'=>'Always'
        );
    }
    
    $dom = new DOMDocument('1.0');
    $dom->formatOutput = true; //是否格式化输出
    
    //创建根节点 urlset
    $urlset = $dom->createElement('urlset');
    
    //创建 xmlns 属性并设置属性的值
    $eventList_xmlns = $dom->createAttribute('xmlns');
    $eventList_xmlns->value = "http://www.sitemaps.org/schemas/sitemap/0.9";
    
    //将属性添加到节点上
    $urlset->appendChild( $eventList_xmlns ); 
    
    foreach($info as $node)
    {
        //创建一个URL节点
        $url = $dom->createElement('url');
    
        //创建4个节点并添加到 url 节点下
        foreach($node as $k => $v)
        {
            $_node = $dom->createElement($k, $v);
            $url -> appendChild( $_node );
        }
    
        //将 url 节点添加到根节点下
        $urlset->appendChild( $url );
    }
    
    //将根节点添加到DOM
    $dom->appendChild($urlset);
    $dom->save('./sitemap.xml');//保存信息到当前目录下的sitemap.xml文件中
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
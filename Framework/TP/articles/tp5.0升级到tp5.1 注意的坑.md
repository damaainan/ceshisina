## tp5.0升级到tp5.1 注意的坑

来源：[http://wuyudong.com/2018/11/10/3271.html](http://wuyudong.com/2018/11/10/3271.html)

时间 2018-11-10 08:01:55



官网已经给出了比较详细的升级方案：[tp升级指导][0]

我这里给出一些不容易察觉到的:

1、模板的变量输出默认添加了`htmlentities`安全过滤，如果你需要输出html内容的话，请使用`{$var|raw}`方式替换，并且`date`方法已经做了内部封装，无需再使用`###`变量替换了。

2、tp5.1 取消了collection助手函数，取代的是模型的toArray()函数

```
$data = self::order('sort')->select();
//tp5.0版的实现
//$data = collection($data)->toArray();
//tp5.1 LTS 取消了collection助手函数
$data = $data->toArray();
```

3、判断数据集非空

5.0使用的是Empty函数，5.1使用的是数据集对象的isEmpty方法

待续……


[0]: https://www.kancloud.cn/manual/thinkphp5_1/354155
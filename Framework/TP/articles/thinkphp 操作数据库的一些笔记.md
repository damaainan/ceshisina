## thinkphp 操作数据库的一些笔记

来源：[http://blog.collin2.xyz/index.php/archives/115/](http://blog.collin2.xyz/index.php/archives/115/)

时间 2018-07-18 22:39:00



### join

join操作在日常数据库的使用中，是非常常见的。所以我们有必要搞清楚在tp中怎么使用join:

```php
$model->alias('alias')
    ->join('LEFT JOIN `table1` t1 ON t1.id=alias.client_id')
    ->where($where)->order($order)->select();
```

要在join中，详细的写出使用的时何种连结查询，如果需要连结多张表，只需要多次调用join就可以了。


### or

同字段or:

```php
$where['status'] = array($status, $status + 1, 'or');
```

不同字段同值or:

```php
$where['realname|username'] = 'collin';
```

or和like同时使用

```php
$where['realname|username'] = array('like', "%{$user}%", 'or');
```

[参考][0]


[0]: https://www.kancloud.cn/manual/thinkphp/1769
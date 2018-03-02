## 【mongoDB中级篇①】游标cursor

来源：[https://segmentfault.com/a/1190000004181817](https://segmentfault.com/a/1190000004181817)


## 简述

通俗的说,游标不是查询结果,可以理解为数据在遍历过程中的内部指针,其返回的是一个资源,或者说数据读取接口.
客户端通过对游标进行一些设置就能对查询结果进行有效地控制，如可以限制查询得到的结果数量、跳过部分结果、或对结果集按任意键进行排序等！
直接对一个集合调用find()方法时，我们会发现，如果查询结果超过二十条，只会返回二十条的结果，这是因为Mongodb会自动递归find() 返回的游标。
## 基本操作

当我们使用一个变量来保存 find()的返回值时，其将不会自动进行遍历显示查询结果的操作,并没有真正的去查询数据库，只要当用到的时候（也就是遍历游标的时候）才会到数据库中将数据取出来,和PHP链接mysql资源一样:

`php代码`

```LANG
$result = mysql_query('select * from message'); //返回的是一个资源

$row=mysql_fetch_assoc($result);//返回sql查询的数组(仅为满足条件的第一条),其内部就有一个指针游标,可以通过循环反复的取出数据

while($f=mysql_fetch_assoc($result)){//每循环一次游标就前进一次,游标走到尾的时候,就不返回值了
  $row[]=$f; 
}
var_dump($row);
```

`mongoDB代码(js)`

```LANG

// while循环
var cursor = db.goods.find({goods_id:{$lte:20}},{_id:0,goods_id:1}); //使用变量来保存游标
while(cursor.hasNext()){ //cursor.hasNext()判断游标是否取到尽头
  printjson(cursor.next()); //cursor.next()取出游标的下1个单元(从0开始游),注意print打印的是二进制对象,printjson打印出来的才是可阅读的json格式数据
}

// for循环
var cursor = db.goods.find({goods_id:{$lte:100}},{_id:0,goods_id:1}); //使用变量来保存游标
for (;cursor.hasNext();) { //由于cursor.hasNext()自动判断的特性这里的for循环可以很简单
  printjson(cursor.next());
}

//forEach循环
var cursor = db.goods.find({goods_id:{$lte:100}},{_id:0,goods_id:1,goods_name:1}); 
var callback = function(obj){ //obj就是查出的文档对象
  printjson(obj.goods_id) //直接取出goods_id的值;
}
cursor.forEach(callback);
```
## 游标在分页中的应用 limit，skip，sort

比如查到10000行,跳过100页,取10行.一般地,我们假设每页N行, 当前是page页,就需要跳过前 (page-1)*N 行, 再取N行, 在mysql中, limit offset,N来实现

在mongo中,用skip(), limit()函数来实现的,当获得游标后，我们可以先对游标进行处理后，再让访问数据库的动作按照我们的意愿发生。在这里我们就可以使用limit，skip，sort三个函数来处理游标。同时这三个函数可以组成方法链式调用的形式。

```LANG
limit：限制游标返回的数量，指定了上限
skip：忽略前面的部分文档，如果文档总数量小于忽略的数量，则返回空集合
sort：得到的子集合进行排序，可以按照多个键进行正反排序！

```

```LANG
# 查询5条
> var cursor = db.goods.find({},{_id:0,goods_id:1}).limit(5); //注意,再次使用游标的时候,游标得重置,因为使用过一次就游到最后了;
> cursor.forEach(function(obj){print(obj.goods_id);})

# 查询5条,并按照shop_price的降序排列
> var cursor = db.goods.find({},{_id:0,goods_id:1,shop_price:1}).limit(5).sort({shop_price:-1})
> cursor.forEach(function(obj){
    print(obj.goods_id+' '+obj.shop_price)
  })
22 5999
23 3700
32 3010
18 2878
14 2625

# 每页10条取第二页,并且升序排列
var cursor = db.goods.find({},{_id:0,goods_id:1,shop_price:1}).limit(10).sort({goods_id:1}).skip(10)
cursor.forEach(function(obj){
  print(obj.goods_id+' '+obj.shop_price)
})

# 一次性打印所有的行,以易读的模式
var cursor = db.goods.find({},{_id:0,goods_id:1,shop_price:1});
printjson(cursor.toArray());

/*
注意: 不要随意使用toArray()
原因: 会把所有的行立即以对象形式组织在内存里.可以在取出少数几行时,用此功能.
*/
```

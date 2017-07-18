# [MongoDB学习笔记——聚合操作之MapReduce][0] 

### MapReduce

 MongoDB 中的 MapReduce 相当于关系数据库中的 group by 。使用 MapReduce 要实现两个函数 Map 和 Reduce 函数。 Map 函数调用 emit （ key,value ），遍历 Collection 中所有的记录，将 key 与 value 传递给 Reduce 函数进行处理。 Mapreduce 使用惯用的 javascript 操作来做 map 和 reduce 操作，因此 Mapreduce 的灵活性和复杂性都会比 aggregate 更高一些，并且相对 aggregate 而言更消耗性能；

 语法格式：
```
 db.runCommand(

 {

 mapReduce: <collection>,

 map: <function>,

 reduce: <function>,

 finalize: <function>,

 out: <output>,

 query: <document>,

 sort: <document>,

 limit: <number>,

 scope: <document>,

 jsMode: <boolean>,

 verbose: <boolean>

 }

 )
```
 等同于语法
```
 db.collection.mapReduce(

 <map>,

 <reduce>,

 {

 out: <collection>,

 query: <document>,

 sort: <document>,

 limit: <number>,

 finalize: <function>,

 scope: <document>,

 jsMode: <boolean>,

 verbose: <boolean>

 })
```
 参数说明：

* mapReduce: 要操作的目标集合
* map: 映射函数 ( 生成键值对序列 , 作为 reduce 函数参数 ), Map 方法使用 this 来操作当前对象，至少调用一次 emit(key, value) 方法向 reduce 提供参数。其中的 key 为最终结果集中的 _id
* reduce: 统计函数，该函数接受 map 函数传来的 key 和 value 值。 reduce 函数中的 key 就是 emit(key,value) 中的 key ，而 value 是 emit 函数中同一个 key 返回的 value 数组。
* query: 一个筛选条件，只有满足条件的文档才会调用 map 函数。（ query 。 limit ， sort 可以随意组合）
* sort : 和 limit 结合的 sort 排序参数（也是在发往 map 函数前给文档排序），可以优化分组机制 , 提升 mapreduce 性能，  处理未排序的集合意味着 MapReduce 引擎将得到随机顺序的值，在 RAM 中根本无法 reduce 。相反，它将不得不把所有文章写入一个临时收集的磁盘，然后按顺序读取并 reduce 。
* limit: 发往 map 函数的文档数量的上限（要是没有 limit ，单独使用 sort 的用处不大）
* finalize: 最终处理函数（对 reduce 返回结果进行最终整理后存入结果集合） finalize 函数可能会在 Reduce 函数结束之后运行，这个函数是可选的，对于很多 Map/Reduce 任务来说不是必需的。 finalize 函数接收一个 key 和一个 value ，返回一个最终的 value. 针对一个对象你的 Reduce 函数可能被调用了多次。当最后只需针对一个对象进行一次操作时可以使用 finalize 函数，比如计算平均值。
* scope: 向 map 、 reduce 、 finalize 导入外部变量
* verbose : 指定是否在结果信息中包含的计时信息，默认 true
* jsMode: 布尔值，是否减少执行过程中 BSON 和 JS 的转换，默认 false 对于 MongoDB2.0 及以上的版本，通常 Map/Reduce 的执行遵循下面两个步骤： a. 从 BSON 转化为 JSON, 执行 Map 过程，将 JSON 转化为 BOSN b. 从 BSON 转化为 JSON, 执行 Reduce 过程，将 JSON 转化为 BSON 因此，需要多次转化格式，但是可以利用临时集合在 Map 阶段处理很大的数据集。为了节省时间，可以利用 {jsMode:ture} 使 Map/Reduce 的执行保持在 JSON 状态。遵循如下两个步骤： a. 从 BSON 转化为 JSON, 执行 Map 过程 b. 执行 Reduce 过程，从 JSON 转化为 BSON 这样，执行时间可以大大减小，但需要注意， jsMode 受到 JSON 堆大小和独立主键最大 500KB 的限制。因此，对于较大的任务 jsMode 并不适用，在这种情况下会转变为通常的模式。
* out: 统计结果存放集合 ( 必填 ) ，  在 MongoDB1.8 之前的版本，如果你没有指定 out 的值，那么结果将会被放到一个临时集合中，集合的名字在输出指令中指定，否则，你可以指定一个集合的名字作为 out 的选项，而结果将会被存储到你指定的集合中。  对于 MongoDB1.8 以及以后的版本，输出选项改变了。 Map/Reduce 不再产生临时集合，你必须为 out 指定一个值，设置 out 指令如下：

##### out 参数格式：
```
 out: { <action>: <collectionName>

 [, db: <dbName>]

 [, sharded: <boolean> ]

 [, nonAtomic: <boolean> ] }
```
##### out 参数说明

  * Action 可以为 replace( 默认 ) 、 merge 、 reduce {replace:"collectionName"}: 输出结果将被插入到一个集合中，并且会自动替换掉现有的同名集合。该选项为默认的。 {merge:"collectionName"}: 这个选项将会把新的数据连接到旧的输出结合中。换句话说，如果在结果集和旧集合中存在相同键值，那么新的键将会被覆盖掉。 {reduce:"collectionName"}: 如果具有某个键值的文档同时存在于结果集和旧集合中，那么一个 Reduce 操作（利用特定的 reduce 函数 ) 将作用于这个两个值，并且结果将会被写到输出集合中。如果指定了 finalize 函数，那么当 Reduce 结束后它将被执行。
  * db: 指明接收输出结果的数据库名称 out:{replace:"collectionName",db:"otherDB"}
  * shard: {shared:true}: 适用于 MongoDB1.9 及以上的版本。如果设置为 true ，并且设置了数据库分片，那么输出的 collection 将被进行分片，并选择 _id 作为其片键。

#### MapReduce 执行聚合的步骤

* 1. 执行 `query` 操作，针对想要聚合的集合进行数据筛选，只有满足条件的文档才会被继续执行
* 2. 执行 `sort` 操作，对满足条件的数据进行排序，可以优化分组的机制 , 通常与 limit 一起使用
* 3. 执行 `limit` 操作，对已经排序的数据进行过滤，筛选出能够执行 map 函数的文档上限，（要是没有 limit ，单独使用 sort 的用处不大）
* 4. 执行 `map` 操作，通过变量 this 来检验当前考察的对象，调用 `emit(key, value)` 生成键值对序列 , 作为 reduce 函数参数
* 5. 执行 `reduce` 操作，处理需要统计的字段
* 6. 执行 `finalize` 操作，对 reduce 的结果执行 `finalize` 方法进行处理
* 7. 执行 `out` 操作，将结果集进行输出
* 8. 断开连接，临时 Collection 删除或保留。

#### 编写 MapReduce 程序

 所有的 map-reduce 函数都是用 JavaScript 书写，然后在 mongod 实例进程上运行。在进行 map-reduce 操作的时候， MongoDB 会将满足查询条件的文档进行 map 所定义的操作， map 函数会产生 ( emit) 键值型的数据。  如果某个键所对应的值有多个的话，会进行 reduce 的操作，最后将结果保存到一个集合中。通过定义一个 finalize 函数可以对 reduce 的结果做进一步的处理，比如：进行投影或者规范化输出、进一步的计算等。  当我们的 key-values 中的 values 集合过大，会被再切分成很多个小的 key-values 块，然后分别执行 Reduce 函数，再将多个块的结果组合成一个新的集合，作为 Reduce 函数的第二个参数，继续 Reducer 操作。可以预见，如果我们初始的 values 非常大，可能还会对第一次分块计算后组成的集合再次 Reduce 。这就类似于多阶的归并排序了。具体会有多少重，就看数据量了。  上面这一内部机制，我们不必非常了解，但我们必须了解这一机制会要求我们遵守的原则，那就是当我们书写 Map 函数时， emit 的第二个参数形式是我们的 Reduce 函数的第二个参数，而 Reduce 函数的返回值，可能会作为新的输入参数再次执行 Reduce 操作，所以 Reduce 函数的返回值也需要和 Reduce 函数的第二个参数结构一致

 首先在 order 集合中插入测试数据
```

db.order.insert([{
    "_id": ObjectId("528312e716b20807b2152db5"),
    "cust_id": "1",
    "ord_date": ISODate("2013-11-13T16:00:00Z"),
    "status": "A",
    "price": 25,
    "items": [
        {
            "sku": "mmm",
            "qty": 5,
            "price": 2.5
        },
        {
            "sku": "nnn",
            "qty": 5,
            "price": 2.5
        }
    ]
},{
    "_id": ObjectId("528312f716b20807b2152db6"),
    "cust_id": "2",
    "ord_date": ISODate("2013-11-13T16:00:00Z"),
    "status": "A",
    "price": 25,
    "items": [
        {
            "sku": "mmm",
            "qty": 5,
            "price": 2.5
        },
        {
            "sku": "nnn",
            "qty": 5,
            "price": 2.5
        }
    ]
},{
    "_id": ObjectId("5283130816b20807b2152db7"),
    "cust_id": "3",
    "ord_date": ISODate("2013-11-13T16:00:00Z"),
    "status": "A",
    "price": 25,
    "items": [
        {
            "sku": "mmm",
            "qty": 5,
            "price": 2.5
        },
        {
            "sku": "nnn",
            "qty": 5,
            "price": 2.5
        }
    ]
},{
    "_id": ObjectId("5283132c16b20807b2152db8"),
    "cust_id": "3",
    "ord_date": ISODate("2013-11-13T16:00:00Z"),
    "status": "A",
    "price": 30,
    "items": [
        {
            "sku": "mmm",
            "qty": 6,
            "price": 2.5
        },
        {
            "sku": "nnn",
            "qty": 6,
            "price": 2.5
        }
    ]
},{
    "_id": ObjectId("5283134d16b20807b2152db9"),
    "cust_id": "2",
    "ord_date": ISODate("2013-11-13T16:00:00Z"),
    "status": "A",
    "price": 20,
    "items": [
        {
            "sku": "mmm",
            "qty": 4,
            "price": 2.5
        },
        {
            "sku": "nnn",
            "qty": 4,
            "price": 2.5
        }
    ]
}])
```

统计每个顾客的消费总金额
```
 var mapFunc = function () {
     emit(this.cust_id, this.price);
 }
 var reduceFunc = function (key, values) {
     return Array.sum(values);
 }
 db.order.mapReduce(mapFunc, reduceFunc, { out: 'ordermapreduce' })
```
 统计每种商品的购买次数和平均每次购买数量
```
 var mapFunc = function () {
     for (var i = 0; i < this.items.length; i++) {
         var key = this.items[i].sku;
         var value = { count: 1, qty: this.items[i].qty }
         emit(key, value);
     }
 };

 var reduceFunc = function (key, values) {
     var result = { count: 0, qty: 0 };
         for (var i = 0; i < values.length; i++) {
             result.count += values[0].count;
             result.qty += values[0].qty;
         }
     return result;
 }

 var finalizeFunc = function (key, reduceVal) {
     reduceVal.avg = reduceVal.qty / reduceVal.count;
     return reduceVal;
 };

 db.order.mapReduce(mapFunc, reduceFunc, { out: { merge: "ordermapreduce1" }, finalize: finalizeFunc });
```
[0]: http://www.cnblogs.com/AlvinLee/p/6076746.html
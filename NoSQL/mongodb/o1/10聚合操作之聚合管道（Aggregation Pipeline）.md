# [MongoDB学习笔记——聚合操作之聚合管道（Aggregation Pipeline）][0]

### MongoDB 聚合管道  

 使用聚合管道可以对集合中的文档进行变换和组合。  管道是由一个个功能节点组成的，这些节点用管道操作符来进行表示。聚合管道以一个集合中的所有文档作为开始，然后这些文档从一个操作节点流向下一个节点  ，每个操作节点对文档做相应的操作。这些操作可能会创建新的文档或者过滤掉一些不符合条件的文档，在管道中可以对文档进行重复操作。  管道表达式只可以操作当前管道中的文档，不能访问其他的文档：表达式操作可以在内存中完成对文档的转换。

 语法格式：

    db.runCommand({
    
        aggregate: "<collection>",
    
        pipeline: [ <stage>, <...> ],
    
        explain: <boolean>,
    
        allowDiskUse: <boolean>,
    
        cursor: <document>,
    
        bypassDocumentValidation: <boolean>
    
        })
    
    //或
                        
        db.collection.aggregate([ <pipeline>, <...> ], options)
    

 参数说明：

 **操作符** | **描述**
 -|-
 aggregate | 要聚合的集合名称
 pipeline | 管道操作符
 explain | 返回指定 aggregate 各个阶段管道的执行计划信息
 allowDiskUse | 每个阶段管道限制为 100MB 的内存，如果大于 100MB 的数据可以先写入临时文件。设置为 true 时， aggregate 操作可时可以先将数据写入对应数据目录的子目录中  的唯一并以 _tmp 结尾的文档中。
 cursor | 指定游标的初始批批大小。光标的字段的值是一个与场 batchSize 文件。 }
 bypassDocumentValidation | 只有当你指定了 $out 操作符，使 db.collection.aggregate 绕过文档验证操作过程中。这让您插入不符合验证要求的文档。

 管道操作符：

 **管道操作符** | **描述**
 -|-
 $project | 数据投影，主要用于重命名、增加和删除字段
 $match | 过滤操作，筛选符合条件文档，作为下一阶段的输入  $match 的语法和查询表达式 db.collection.find()  的语法相同  注意： 1. 不能在 $match 操作符中使用 $where  表达式操作符。 2. $match 尽量出现在管道的前面，这样可以提早过滤文档，加快聚合速度。 3. 如果 $match 出现在最前面的话，可以使用索引来加快查询。
 $limit | 限制经过管道的文档数量  $limit 的参数只能是一个正整数
 $skip | 从待操作集合开始的位置跳过文档的数目  $skip 参数也只能为一个正整数
 $unwind | 将数组分解为单个的元素，并与文档的其余部分一同返回  注意： 1. 如果 $unwind 目标字段不存在，则整个文档都会被忽略过滤掉 2. 如果 $unwind 目标字段不是一个数组，则会报错 3. 如果 $unwind 目标字段数组为空，则该文档也会被忽略过滤掉
 $group | 可以将文档依据指定字段的不同值进行分组，如果选定了需要进行分组的字段，就可以将指定的字段传递给 $group 函数的 _id 字段  注意： 1. $group 的输出是无序的。 2. $group 操作默认实在内存中进行的，超过此限制会报错，若要允许处理大型数据集， allowDiskUse 将选项设置为启用 $group 操作真实写入临时文件。具体请参考[官方文档][1]
 $sort | 对文档按照指定字段排序  注意： 1. 如果将 $sort 放到管道前面的话可以利用索引，提高效率 2. 在管道中如果 $sort 出现在 $limit 之前的话， $sort 只会对前 $limit 个文档进行操作，这样在内存中也只会保留前 $limit 个文档，从而可以极大的节省内存 3. $sort 操作符默认在内存中进行，，超过此限制会报错，若要允许处理大型数据集， allowDiskUse 将选项设置为启用 $group 操作真实写入临时文件。具体请参考[官方文档][1]
 $geoNear | 会返回一些坐标值，这些值以按照距离指定点距离由近到远进行排序
 $sample | 从待操作的集合中随机返回指定数量的文档  注意：如果指定的数量 N 大于等于集合文档总数的 5% ， $sample  执行集合扫描，执行排序，然后选择前 N 的文档 ( 受排序的内存限制 ) 如果 N 是小于 5% 的集合中的文档总数  如果使用 WiredTiger 存储引擎， $sample  使用伪随机游标在抽样 N 文档集合。  如果使用 MMAPv1 存储引擎， $sample  使用 _id 索引随机选择 N 个文档。
 $lookup | 用于与统一数据库中其他集合之间进行 join 操作
 $out | 用户将聚合的结果输出到指定的集合，如果要使用 $out 则必须在整个管道操作的最后阶段  如果指定的集合尚不存在， $out  操作会在当前数据库中创建一个新的集合。集合不是可见的直到聚合完成。如果聚合失败， MongoDB 不会创建集合。  如果集合指定的  $out  操作已经存在，然后完成后的聚合， $out  阶段以原子方式以新的结果集合替换现有集合的， $out  操作不会更改任何存在于以前的集合的索引。如果聚合失败 $out  则不会对现有集合做任何更改。
 $redact | 字段所处的 document 结构的级别 . $redact 还有三个重要的参数： 1 ） $$DESCEND ：  返回包含当前 document 级别的所有字段，并且会继续判字段包含内嵌文档，内嵌文档的字段也会去判断是否符合条件。 2 ） $$PRUNE ：返回不包含当前文档或者内嵌文档级别的所有字段，不会继续检测此级别的其他字段，即使这些字段的内嵌文档持有相同的访问级别。 3 ） $$KEEP ：返回包含当前文档或内嵌文档级别的所有字段，不再继续检测此级别的其他字段，即使这些字段的内嵌文档中持有不同的访问级别。

### 聚合管道操作实例  

 提取字段

    db.order.aggregate({ $project: { cust_id: 1, price: 1 } });
    db.order.aggregate({ $project: { items: 1, items: { sku: 1 } } });
    

 重命名字段

    db.order.aggregate({ $project: { "orderid": '$_id', _id: 0, "custid": '$cust_id' } });
    
    db.order.aggregate({ $project: { items: { '_sku': '$sku', sku: 1 } } })
    

 新建文档

    db.order.aggregate({
        $project: { 
            price: 1,
            details: { price: '$items.price' }
        } 
    });
    

 注意：由于对字段进行重命名时， MongoDB 并不会记录字段的历史名称，所以如果针对原来的字段创建过索引，那么聚合管道在进行排序时无法在下面的排序操作时使用索引，  应当尽量在修改字段名称之前使用排序

    db.order.aggregate([{ $project: { "orderid": '$_id', _id: 0, "custid": '$cust_id' } }, { $sort: { custid: -1 } }])
    //建议写法                   
    db.order.aggregate([{ $sort: { cust_id: -1 } }, { $project: { "orderid": '$_id', _id: 0, "custid": '$cust_id' } }])
    

 { $add: [ <expression1>, <expression2>, ... ] } 将多个数字或日期进行相加，只支持数字和时间格式，如果其中一个参数是日期， $add 会将其他参数视为要添加到日期的毫秒

    db.order.aggregate({
        $project: {
            "add": {
                "$add": ["$price", 1]
            }
        }
    })
    

 { $subtract: [ <expression1>, <expression2> ] } 接受两个表达式作为参数，使用第一个表达式减去第二个表达式作为结果

    db.order.aggregate({
        $project: {
            "subtract": {
                "$subtract": [{ "$add": ["$price", 12] }, 10]
            }
        }
    })
    

 { $multiply: [ <expression1>, <expression2>, ... ] } 接受一个或多个表达式，并将它们相乘

    db.order.aggregate({
        $project: {
            "multiply": { "$multiply": ["$price", 3] }
        }
    })
    

 { $divide: [ <expression1>, <expression2> ] } 接受两个表达式作为参数，使用第一个表达式除以第二个表达式的商作为结果

    db.order.aggregate({
        $project: {
            "divide": { "$divide": ["$price", 3] }
        }
    })
    

 更多数学表达式参考官方文档 https://docs.mongodb.com/manual/reference/operator/aggregation-arithmetic/

 { $cond: { if: <boolean-expression>, then: <true-case>, else: <false-case-> } } 或 { $cond: [ <boolean-expression>, <true-case>, <false-case> ] } 如果 boolean-expression 为 true 则执行 true-case 否则执行 false-case

    db.order.aggregate({
        $project: {
            "price": {
                "$cond": { if: { $gt: ["$price", 25] }, then: true, else: false }
            }
        }
    });
    //或
                
    db.order.aggregate({
        $project: {
            "price": {
                "$cond": [{ $gt: ["$price", 25] }, true, false]
            }
        }
    });

 { $ifNull: [ , ] } 如果 expression 为 null 则返回 replacement-expression-if-null 否则返回 expression 的值

    db.order.aggregate({
        $project: {
            "total": {
                "$ifNull": ["$total", 0]
            }
        }
    })
    

 { $and: [ <expression1>, <expression2>, ... ] } 如果所有表达式都返回 true ，则结果为 true ，否则为 false 除了布尔类型 false ，以下几种类型也为 false ，如： null,0,undefined ；其它值则为 true ，包括非 0 的值及数组

        //Example                             Result
    { $and: [1, "green"] }              true
    { $and: [] }                        true
    { $and: [[null], [false], [0]] }    true
    { $and: [null, true] }              false
    { $and: [0, true] }                 false

 { $or: [ <expression1>, <expression2>, ... ] } 只要有任意表达式为 true ，则结果为 true ，否则为 false 除了布尔类型 false ，以下几种类型也为 false ，如： null,0,undefined ；其它值则为 true ，包括非 0 的值及数组

    //Example                            Result
    { $or: [ true, false ] }            true
    { $or: [ [ false ], false ] }       true
    { $or: [ null, 0, undefined ] }       false
    { $or: [] }                        false
    

 { $not: [ <expression> ] } 对 expression 取反  除了布尔类型 false ，以下几种类型也为 false ，如： null,0,undefined ；其它值则为 true ，包括非 0 的值及数组

    //total不存在为false，取反结果则为true
    db.order.aggregate({
        $project: {
            not: {
                $not: "$total"
            }
        }
    })
    

 $match 过滤操作，筛选符合条件文档，作为下一阶段的输入 $match 的语法和查询表达式 db.collection.find() 的语法相同

        db.order.aggregate([{ $match: { cust_id: "1" } }, {
        $project: {
            "total": {
                "$ifNull": ["$total", 0]
            }
        }
    }])
    

 $out 用户将聚合的结果输出到指定的集合

    db.order.aggregate([{ $match: { cust_id: "1" } }, {
        $project: {
            "total": {
                "$ifNull": ["$total", 0]
            }
        }
    },
    { $out: "testaggregate" }])
    

 $unwind 将数组分解为单个的元素，并与文档的其余部分一同返回

    db.order.aggregate({$unwind:"$items"})
    

 $group 对数据进行分组 $group 的时候必须要指定一个 _id 域，同时也可以包含一些算术类型的表达式操作符

        db.order.aggregate([
       {
           $unwind: "$items"
       },
       {
           $group: {
               _id: "$cust_id",
               qty: {
                   $sum: "$items.qty"
               }
           }
       }
    ])
    

 关系型数据库与 MongoDB 关于聚合的一些对比

 **关系型数据库** |  **Mongodb**
 -|-
 WHERE | $match
 GROUP BY | $group
 HAVING | $match
 SELECT | $project
 ORDER BY | $sort
 LIMIT | $limit
 SUM() | $sum
 COUNT() | $sum
 join | $lookup

[0]: http://www.cnblogs.com/AlvinLee/p/6085442.html
[1]: https://docs.mongodb.com/manual/reference/method/db.collection.aggregate/

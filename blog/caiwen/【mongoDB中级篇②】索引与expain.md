## 【mongoDB中级篇②】索引与expain

来源：[https://segmentfault.com/a/1190000004263246](https://segmentfault.com/a/1190000004263246)


## 索引的操作

数据库百分之八十的工作基本上都是查询,而索引能帮我们更快的查询到想要的数据.但是其降低了数据的写入速度,所以要权衡常用的查询字段,不必在太多字段上建立索引.
在mongoDB中默认是用btree来组织索引文件,并且可以按字段升序/降序来创建,便于排序.
### 数据准备

```LANG
for (var i = 1; i <100000; i++) {
  db.test.insert({name:'user'+i,num:i,sn:Math.floor(Math.random()*10000000)})
}
```
### 索引常用操作
#### 查看当前集合的索引

```LANG
> db.test.getIndexes();
[
    {
        "v" : 1,
        "key" : {
                "_id" : 1
        },
        "name" : "_id_",
        "ns" : "test.test"
    }
]
```

MongoDB有个默认的`_id`的键，他相当于“主键”的角色。集合创建后系统会自动创建一个索引在`_id`键上，它是默认索引，索引名叫“`_id`”，是无法被删除的。

另外, system.indexes集合中包含了每个索引的详细信息，因此可以通过下面的命令查询已经存在的索引

```LANG
 db.system.indexes.find({});
```
#### 创建单列索引

```LANG
db.collection.ensureIndex({field:1/-1}) # 1是正序,-1是倒序
```
#### 创建多列索引(组合索引)

```LANG
db.collection.ensureIndex({field1:1/-1, field2:1/-1})  
```

在大多数情况下我们创建的索引都是多列索引,因为数据库查询器只会选择最优的索引来进行查询,在多列分别建立索引,查询器只会选择其中一列索引来进行查询,而直接建立一个多列索引的话,该索引由于是作用于多列的,效率更高于单列索引,具体多列索引建立技巧可以查看下文中的 <<新版Explain的分析实例>>，另外，mongoDB的多列索引也遵循着 **`最左前缀的原则`** 

```LANG
db.test.ensureIndex({"username":1, "age":-1})
```

该索引被创建后，基于username和age的查询将会用到该索引，或者是基于username的查询也会用到该索引，但是只是基于age的查询将不会用到该复合索引。因此可以说，如果想用到复合索引，必须在查询条件中包含复合索引中的前N个索引列。然而如果查询条件中的键值顺序和复合索引中的创建顺序不一致的话，MongoDB可以智能的帮助我们调整该顺序，以便使复合索引可以为查询所用

```LANG
 db.test.find({"age": 30, "username": "stephen"})
```

对于上面示例中的查询条件，MongoDB在检索之前将会动态的调整查询条件文档的顺序，以使该查询可以用到刚刚创建的复合索引。
#### 创建子文档索引

```LANG
db.collection.ensureIndex({'filed.subfield':1/-1});
```
#### 创建唯一索引

```LANG
db.collection.ensureIndex({filed:1/-1}, {unique:true});
```
#### 创建稀疏索引

稀疏索引的特点------如果针对field做索引,针对不含field列的文档,将不建立索引.
与之相对,普通索引,会把该文档的field列的值认为NULL,并建索引.
适宜于: 小部分文档含有某列时.

```LANG
 db.tea.find();
{ "_id" : ObjectId("5275f99b87437c610023597b"), "email" : "a@163.com" }
{ "_id" : ObjectId("5275f99e87437c610023597c"), "email" : "b@163.com" }
{ "_id" : ObjectId("5275f9e887437c610023597e"), "email" : "c@163.com" }
{ "_id" : ObjectId("5275fa3887437c6100235980") }
```

```LANG
db.collection.ensureIndex({field:1/-1},{sparse:true});
```

如上内容,最后一行没有email列,如果分别加普通索引,和稀疏索引,对于最后一行的email分别当成null 和 忽略最后一行来处理.根据{email:null}来查询,前者能利用到索引,而后者用不到索引,是一个全表扫描的过程;
#### 创建哈希索引

哈希索引速度比普通索引快,但是,无能对范围查询进行优化.
适宜于 **`随机性强的散列`** 

```LANG
db.collection.ensureIndex({file:’hashed’});
```
#### 重建索引

一个表经过很多次修改后,导致表的文件产生空洞,索引文件也如此.可以通过索引的重建,减少索引文件碎片,并提高索引的效率.类似mysql中的optimize table

```LANG
db.collection.reIndex()
```
#### 删除索引

```LANG
db.collection.dropIndex({filed:1/-1});  #删除单个索引
db.collection.dropIndexes(); #删除所有索引
```
### 正则表达式在索引中的应用

正则表达式可以灵活地匹配查询条件，如果希望正则表达式能命中索引，就要注意了：
Mongodb能为前缀型的正则表达式命中索引(和mysql一样)，比如：需要查询Mail中user以z开头的：`/^z/`
如果有user索引,这种查询很高效,但其他的即使有索引,也不会命中索引，比说：需要查询Mail中的user中含有z的：

```LANG
/.*z.*/
/^.*z.*/
```

这种查询是不会命中到索引的，当数据量很大，速度很慢
总之，^后的条件必须明确，不能^.* ^[a-z]之类开头的
## 查询计划explain

我学习mongodb比较晚,安装的是3.05版本的,发现此版本的explain的使用方法跟教程上有很大不同,究竟是从什么版本开始发生改变的,我也就不去追溯了.
### 新版explain介绍

新版本的explain有三种模式,作为explain的参数传进去

* queryPlanner`默认`

* executionStats

* allPlansExecution



#### queryPlanner

queryPlanner是现版本explain的默认模式，queryPlanner模式下并不会去真正进行query语句查询，而是针对query语句进行执行计划分析并选出winning plan。

```LANG
{
    "queryPlanner": {
        "plannerVersion": 1,
        "namespace": "game_db.game_user", 
        "indexFilterSet": false,//针对该query是否有indexfilter（详见下文）
        "parsedQuery": {
            "w": {
                "$eq": 1
            }
        },
        "winningPlan": {  // 查询优化器针对该query所返回的最优执行计划的详细内容。
            "stage": "FETCH", //最优执行计划的stage，这里返回是FETCH，可以理解为通过返回的index位置去检索具体的文档(详见下文)
            "inputStage": { // 上一个stage的child stage，此处是IXSCAN，表示进行的是index scanning。
                "stage": "IXSCAN",  
                "keyPattern": { 
                    "w": 1, //所扫描的index内容
                    "n": 1 // 返回的条数?
                },
                "indexName": "w_1_n_1", //索引名称
                "isMultiKey": false, //是否是Multikey，此处返回是false，如果索引建立在array上，此处将是true
                "direction": "forward", //此query的查询顺序，此处是forward，如果用了.sort({w:-1})将显示backward。
                "indexBounds": { //winningplan所扫描的索引范围，此处查询条件是w:1,使用的index是w与n的联合索引，故w是[1.0,1.0]而n没有指定在查询条件中，故是[MinKey,MaxKey]。
                    "w": ["[1.0, 1.0]"],
                    "n": ["[MinKey, MaxKey]"]
                }
            }
        },
        "rejectedPlans": [{ //他执行计划（非最优而被查询优化器reject的）的详细返回，其中具体信息与winningPlan的返回中意义相同
            "stage": "FETCH",
            "inputStage": { 
                "stage": "IXSCAN",
                "keyPattern": {
                    "w": 1,
                    "v": 1
                },
                "indexName": "w_1_v_1",
                "isMultiKey": false,
                "direction": "forward",
                "indexBounds": {
                    "w": ["[1.0, 1.0]"],
                    "v": ["[MinKey, MaxKey]"]
                }
            }
        }]
    }
```
##### **`indexFilterSet`** 

IndexFilter决定了查询优化器对于某一类型的查询将如何使用index，indexFilter仅影响查询优化器对于该类查询可以用尝试哪些index的执行计划分析，查询优化器还是根据分析情况选择最优计划。
如果某一类型的查询设定了IndexFilter，那么执行时通过hint指定了其他的index，查询优化器将会忽略hint所设置index，仍然使用indexfilter中设定的查询计划。
IndexFilter可以通过命令移除，也将在实例重启后清空。
###### IndexFilter的创建

```LANG
db.runCommand(
   {
      planCacheSetFilter: <collection>,
      query: <query>,
      sort: <sort>,
      projection:,
      indexes: [ <index1>, <index2>, ...]
   }
)
```

```LANG
db.runCommand(
   {
      planCacheSetFilter: "orders",
      query: { status: "A" },
      indexes: [
         { cust_id: 1, status: 1 },
         { status: 1, order_date: -1 }
      ]
   }
)
```

针对orders表建立了一个indexFilter，indexFilter指定了对于orders表只有status条件（仅对status进行查询，无sort等）的查询的indexes，所以以下的查询语句的查询优化器仅仅会从{cust_id:1,status:1}和{status:1,order_date:-1}中进行winning plan的选择

```LANG
db.orders.find( { status: "D" } )
db.orders.find( { status: "P" } )
```
###### indexFilter的列表

可以通过如下命令展示某一个collecton的所有indexFilter

```LANG
db.runCommand( { planCacheListFilters: <collection> } )
```
###### indexFilter的删除

可以通过如下命令对IndexFilter进行删除

```LANG
db.runCommand(
   {
      planCacheClearFilters: <collection>,
      query: <query pattern>,
      sort: <sort specification>,
      projection:  }
)
```
##### Stage返回参数说明

```LANG
COLLSCAN #全表扫描

IXSCAN #索引扫描

FETCH #根据索引去检索指定document

SHARD_MERGE #将各个分片返回数据进行merge

SORT #表明在内存中进行了排序（与老版本的scanAndOrder:true一致）

LIMIT #使用limit限制返回数

SKIP #使用skip进行跳过

IDHACK #针对_id进行查询

SHARDING_FILTER #通过mongos对分片数据进行查询

COUNT #利用db.coll.explain().count()之类进行count运算

COUNTSCAN #count不使用Index进行count时的stage返回

COUNT_SCAN #count使用了Index进行count时的stage返回

SUBPLA #未使用到索引的$or查询的stage返回

TEXT #使用全文索引进行查询时候的stage返回

PROJECTION #限定返回字段时候stage的返回
```
#### **`executionStats`** 

该模式是mongoDB查询的执行状态,类似老版本的explain

```LANG
  "executionStats": {
    "executionSuccess": true, //是否执行成功
    "nReturned": 29861, //查询的返回条数
    "executionTimeMillis": 23079, //整体执行时间 毫秒
    "totalKeysExamined": 29861, // 索引扫描次数
    "totalDocsExamined": 29861, // document扫描次数
    "executionStages": {
      "stage": "FETCH", //这里是FETCH去扫描对于documents
      "nReturned": 29861, //由于是FETCH，所以这里该值与executionStats.nReturned一致
      "executionTimeMillisEstimate": 22685,
      "works": 29862, //查看源码中发现，每次操作会加1，且会把执行时间记录在executionTimeMillis中。
      "advanced": 29861,//而在查询结束EOF，works又会加1，advanced不加。正常的返回works会比nReturned多1，这时候isEOF为true（1）：另外advanced的返回值只有在命中的时候+1，在skip,eof的时候不会增加
      "needTime": 0,
      "needFetch": 0,
      "saveState": 946,
      "restoreState": 946,
      "isEOF": 1,
      "invalidates": 0,
      "docsExamined": 29861, // 与executionStats.totalDocsExamined一致
      "alreadyHasObj": 0,
      "inputStage": {
        "stage": "IXSCAN",
        "nReturned": 29861,
        "executionTimeMillisEstimate": 70,
        "works": 29862,
        "advanced": 29861,
        "needTime": 0,
        "needFetch": 0,
        "saveState": 946,
        "restoreState": 946,
        "isEOF": 1,
        "invalidates": 0,
        "keyPattern": {
          "w": 1,
          "n": 1
        },
        "indexName": "w_1_n_1",
        "isMultiKey": false,
        "direction": "forward",
        "indexBounds": {
          "w": ["[1.0, 1.0]"],
          "n": ["[MinKey, MaxKey]"]
        },
        "keysExamined": 29861,
        "dupsTested": 0,
        "dupsDropped": 0,
        "seenInvalidated": 0,
        "matchTested": 0
      }
    }
  }
```
#### **`allPlansExecution`** 

该模式可以看做是以上两个模式加起来;
### **`如何通过新版explain来分析索引`** 
#### **`分析executionTimeMillis`** 

```LANG
"executionStats" : {
  "nReturned" : 29861,
  "totalKeysExamined" : 29861,
  "totalDocsExamined" : 29861,
  "executionTimeMillis" : 66948, # 该query的整体查询时间
  ...
  "executionStages" : {
    ...
    "executionTimeMillisEstimate" : 66244, # 该查询根据index去检索document获取29861条具体数据的时间
    ...
    "inputStage" : {
            "stage" : "IXSCAN",
            ...
            
            "executionTimeMillisEstimate" : 290, #该查询扫描29861行index所用时间
            
            ...
}
```

这三个值我们都希望越少越好，那么是什么影响这这三个返回值呢？
#### **`分析index与document扫描数与查询返回条目数`** 

这里主要谈3个返回项，`nReturned`，`totalKeysExamined`与`totalDocsExamined`，分别代表该条查询返回的条目、索引扫描条目和文档扫描条目。
理想状态如下:

nReturned=totalKeysExamined & totalDocsExamined=0 （cover index，仅仅使用到了index，无需文档扫描，这是最理想状态。）

或者

nReturned=totalKeysExamined=totalDocsExamined(需要具体情况具体分析)（正常index利用，无多余index扫描与文档扫描。）

如果有sort的时候，为了使得sort不在内存中进行，我们可以在保证nReturned=totalDocsExamined的基础上，totalKeysExamined可以大于totalDocsExamined与nReturned，因为量级较大的时候内存排序非常消耗性能。
#### **`分析Stage状态`** 

对于普通查询，我们最希望看到的组合有这些：

```LANG
Fetch+IDHACK

Fetch+ixscan

Limit+（Fetch+ixscan）

PROJECTION+ixscan

SHARDING_FILTER+ixscan

等

```

不希望看到包含如下的stage：

```LANG
COLLSCAN（全表扫），SORT（使用sort但是无index），不合理的SKIP，SUBPLA（未用到index的$or）

```

对于count查询，希望看到的有：

```LANG
COUNT_SCAN

```

不希望看到的有:

```LANG
COUNTSCAN

```
### **`新版Explain的分析实例`** 

表中数据如下(简单测试用例，仅10条数据，主要是对explain分析的逻辑进行解析)：

```LANG
{ "_id" : ObjectId("55b86d6bd7e3f4ccaaf20d70"), "a" : 1, "b" : 1, "c" : 1 }
{ "_id" : ObjectId("55b86d6fd7e3f4ccaaf20d71"), "a" : 1, "b" : 2, "c" : 2 }
{ "_id" : ObjectId("55b86d72d7e3f4ccaaf20d72"), "a" : 1, "b" : 3, "c" : 3 }
{ "_id" : ObjectId("55b86d74d7e3f4ccaaf20d73"), "a" : 4, "b" : 2, "c" : 3 }
{ "_id" : ObjectId("55b86d75d7e3f4ccaaf20d74"), "a" : 4, "b" : 2, "c" : 5 }
{ "_id" : ObjectId("55b86d77d7e3f4ccaaf20d75"), "a" : 4, "b" : 2, "c" : 5 }
{ "_id" : ObjectId("55b879b442bfd1a462bd8990"), "a" : 2, "b" : 1, "c" : 1 }
{ "_id" : ObjectId("55b87fe842bfd1a462bd8991"), "a" : 1, "b" : 9, "c" : 1 }
{ "_id" : ObjectId("55b87fe942bfd1a462bd8992"), "a" : 1, "b" : 9, "c" : 1 }
{ "_id" : ObjectId("55b87fe942bfd1a462bd8993"), "a" : 1, "b" : 9, "c" : 1 }
```

查询语句

```LANG
db.test.find({a:1,b:{$lt:3}}).sort({c:-1}).explain();

```

未加索引前

```LANG
"executionStats": {
  "executionSuccess": true,
  "nReturned": 2,
  "executionTimeMillis": 0,
  "totalKeysExamined": 0, // 为0表示没有使用索引
  "totalDocsExamined": 10, // 扫描了所有记录
  "executionStages": {
    "stage": "SORT",  //为SORT,未使用index的sort
    "nReturned": 2,
    ..."sortPattern": {
      "c": -1
    },
    "memUsage": 126, //占用的内存
    "memLimit": 33554432, //内存限制
    "inputStage": {
      "stage": "COLLSCAN", //全表扫描
      "filter": {
        "$and": [{
          "a": {
            "$eq": 1
          }
        },
        {
          "b": {
            "$lt": 3
          }
        }]
      },
      "nReturned": 2,
      ..."direction": "forward",
      "docsExamined": 10
    }
```

很明显，没有index的时候， **`进行了全表扫描，在内存中sort`** ，数据量达百万级以后就会有明显的慢

接着我们对C加一个正序索引

```LANG
 db.d.ensureIndex({c:1})

```

再来看一下

```LANG
"executionStats": {
  "executionSuccess": true, 
  "nReturned": 2,
  "executionTimeMillis": 1,
  "totalKeysExamined": 10,
  "totalDocsExamined": 10,
  "executionStages": {
    "stage": "FETCH",
    "filter": {
      "$and": [{
        "a": {
          "$eq": 1
        }
      },
      {
        "b": {
          "$lt": 3
        }
      }]
    },
    "nReturned": 2,
    ..."inputStage": {
      "stage": "IXSCAN",
      "nReturned": 10,
      ..."keyPattern": {
        "c": 1
      },
      "indexName": "c_1",
      "isMultiKey": false,
      "direction": "backward",
      "indexBounds": {
        "c": ["[MaxKey, MinKey]"]
      }
```

我们发现，Stage没有了SORT，因为我们sort字段有了index，但是由于查询还是没有index，故totalDocsExamined还是10，但是由于sort用了index，totalKeysExamined也是10，但是仅对sort排序做了优化，查询性能还是一样的低效。

**`接下来， 我们对查询条件做index`** 

```LANG
db.test.ensureIndex({b:1,a:1,c:1})

```

```LANG
"executionStats": {
  "executionSuccess": true,
  "nReturned": 2,
  "executionTimeMillis": 0,
  "totalKeysExamined": 4,
  "totalDocsExamined": 2,
  "executionStages": {
    "stage": "SORT",
    "nReturned": 2,
    ..."sortPattern": {
      "c": -1
    },
    "memUsage": 126,
    "memLimit": 33554432,
    "inputStage": {
      "stage": "FETCH",
      "nReturned": 2,
      ..."inputStage": {
        "stage": "IXSCAN",
        "nReturned": 2,
        ..."keyPattern": {
          "b": 1,
          "a": 1,
          "c": 1
        },
        "indexName": "b_1_a_1_c_1",
        "isMultiKey": false,
        "direction": "forward",
        "indexBounds": {
          "b": ["[-inf.0, 3.0)"],
          "a": ["[1.0, 1.0]"],
          "c": ["[MinKey, MaxKey]"]
        },
        
```

nReturned为2，返回2条记录
totalKeysExamined为4，扫描了4个index
totalDocsExamined为2，扫描了2个docs

此时`nReturned=totalDocsExamined<totalKeysExamined`，不符合我们的期望。
且`executionStages.Stage`为`Sort`，在内存中进行排序了，也不符合我们的期望

```LANG
db.test.ensureIndex({a:1,b:1,c:1})

```

```LANG
"executionStats": {
  "executionSuccess": true,
  "nReturned": 2,
  "executionTimeMillis": 0,
  "totalKeysExamined": 2,
  "totalDocsExamined": 2,
  "executionStages": {
    "stage": "SORT",
    "nReturned": 2,
    ..."sortPattern": {
      "c": -1
    },
    "memUsage": 126,
    "memLimit": 33554432,
    "inputStage": {
      "stage": "FETCH",
      "nReturned": 2,
      ..."inputStage": {
        "stage": "IXSCAN",
        "nReturned": 2,
        ..."keyPattern": {
          "a": 1,
          "b": 1,
          "c": 1
        },
        "indexName": "a_1_b_1_c_1",
        "isMultiKey": false,
        "direction": "forward",
        "indexBounds": {
          "a": ["[1.0, 1.0]"],
          "b": ["[-inf.0, 3.0)"],
          "c": ["[MinKey, MaxKey]"]
        },
        
```

nReturned为2，返回2条记录
totalKeysExamined为2，扫描了2个index
totalDocsExamined为2，扫描了2个docs
此时nReturned=totalDocsExamined=totalKeysExamined，符合我们的期望。
但是！executionStages.Stage为Sort，在内存中进行排序了，这个在生产环境中尤其是在数据量较大的时候，是非常消耗性能的，这个千万不能忽视了，我们需要改进这个点。

```LANG
db.test.ensureIndex({a:1,c:1,b:1})

```

```LANG
"executionStats": {
  "executionSuccess": true,
  "nReturned": 2,
  "executionTimeMillis": 0,
  "totalKeysExamined": 4,
  "totalDocsExamined": 2,
  "executionStages": {
    "stage": "FETCH",
    "nReturned": 2,
    ..."inputStage": {
      "stage": "IXSCAN",
      "nReturned": 2,
      ..."keyPattern": {
        "a": 1,
        "c": 1,
        "b": 1
      },
      "indexName": "a_1_c_1_b_1",
      "isMultiKey": false,
      "direction": "backward",
      "indexBounds": {
        "a": ["[1.0, 1.0]"],
        "c": ["[MaxKey, MinKey]"],
        "b": ["(3.0, -inf.0]"]
      },
      "keysExamined": 4,
      "dupsTested": 0,
      "dupsDropped": 0,
      "seenInvalidated": 0,
      "matchTested": 0
```

我们可以看到

nReturned为2，返回2条记录
totalKeysExamined为4，扫描了4个index
totalDocsExamined为2，扫描了2个docs

虽然不是nReturned=totalKeysExamined=totalDocsExamined，但是Stage无Sort，即利用了index进行排序，而非内存，这个性能的提升高于多扫几个index的代价。
综上可以有一个小结论，当查询覆盖精确匹配，范围查询与排序的时候， **`{精确匹配字段,排序字段,范围查询字段}`** 这样的索引排序会更为高效
### **`旧版本的explain`** 

```LANG
> db.blogs.find({"comment.author":"joe"}).explain();  
{  
        "cursor" : "BtreeCursor comment.author_1",  
        "nscanned" : 1,  
        "nscannedObjects" : 1,  
        "n" : 1,  
        "millis" : 70,  
        "nYields" : 0,  
        "nChunkSkips" : 0,  
        "isMultiKey" : true,  
        "indexOnly" : false,  
        "indexBounds" : {  
                "comment.author" : [  
                        [  
                                "joe",  
                                "joe"  
                        ]  
                ]  
        }  
} 
```

参数说明:

* `cursor`：因为这个查询使用了索引，MongoDB中索引存储在B树结构中，所以这是也使用了BtreeCursor类型的游标。如果没有使用索引，游标的类型是BasicCursor。这个键还会给出你所使用的索引的名称，你通过这个名称可以查看当前数据库下的system.indexes集合（系统自动创建，由于存储索引信息，这个稍微会提到）来得到索引的详细信息。

* `nscanned/nscannedObjects`：表明当前这次查询一共扫描了集合中多少个文档，我们的目的是，让这个数值和返回文档的数量越接近越好。

* `n`：当前查询返回的文档数量。

* `millis`：当前查询所需时间，毫秒数。

* `indexBounds`：当前查询具体使用的索引



### hint强制使用某个索引

```LANG
> db.user.ensureIndex({"name":1,"age":1});
> db.user.ensureIndex({"age":1,"name":1});
> db.user.find({"age":40, "name":"tim"}).explain();
{
    "cursor" : "BtreeCursor name_1_age_1",
    "nscanned" : 1,
    "nscannedObjects" : 1,
    "n" : 1,
    "millis" : 0,
    "nYields" : 0,
    "nChunkSkips" : 0,
    "isMultiKey" : false,
    "indexOnly" : false,
    "indexBounds" : {
            "name" : [
                    [
                            "tim",
                            "tim"
                    ]
            ],
            "age" : [
                    [
                            40,
                            40
                    ]
            ]
    }
}
```

返回文档的键没有区别，其默认使用了索引"name_1_age_1"，这是查询优化器为我们使用的索引！我们此处可以通过hint进行更行，即强制这个查询使用我们定义的“age_1_name_1”索引，如下

```LANG
> var cursor = db.user.find({"age":40, "name":"tim"}).hint({"age":1,"name":1});
> cursor.explain();
{
    "cursor" : "BtreeCursor age_1_name_1",
    "nscanned" : 1,
    "nscannedObjects" : 1,
    "n" : 1,
    "millis" : 0,
    "nYields" : 0,
    "nChunkSkips" : 0,
    "isMultiKey" : false,
    "indexOnly" : false,
    "indexBounds" : {
            "age" : [
                    [
                            40,
                            40
                    ]
            ],
            "name" : [
                    [
                            "tim",
                            "tim"
                    ]
            ]
    }
}
```

hint函数会返回游标，我们可以在游标上调用explain查看索引的使用情况！99%的情况，我们没有必要通过hint去强制使用某个索引，MongoDB的查询优化器非常智能，绝对能帮助我们使用最佳的索引去进行查询！

# [MongoDB学习笔记——聚合操作之group,distinct,count][0] 

### 单独的聚合命令（ group,distinct,count ）

 单独聚合命令  比 aggregate 性能低，比 Map-reduce 灵活度低；但是可以节省几行 javascript 代码，后面那句话我自己加的，哈哈哈～

`count()` 方法可以查询统计符合条件的集合的总数

     db.COLLECTION_NAME.count(<query>) // 此方法等价于 db.COLLECTION_NAME.find(<query>).count()

 在分布式集合中，会出现计算错误的情况，这个时候推荐使用 aggregate ；

`distinct` 命令可以找出给定键的所有去重之后的值。使用时也必须指定集合和键

     db.runCommand({ distinct: "<collection>", key: "<field>", query: <query> }) // 此方法等价于 db.collection.distinct(field, query)

 参数说明：

* collection : 要查询的集合的名称
* key: 需要去重的字段的名称
* query : 可选参数，  指明查询条件，相当于 SQL 中的 where 语句

`Group` 操作： mongodb2.2 版本对于返回数据最多只包涵 20000 个元素，最多支持 20000 独立分组；对于超过 20000 的独立分组建议采用 mapreduce ；
```
 db.runCommand({
    group:
    {
    ns: <namespace>,
    key: <key>,
    $reduce: <reduce function>,
    $keyf: <key function>,
    cond: <query>,
    finalize: <finalize function>
    }
 }) // 该方法等价于 db.collection.group({ key, reduce, initial [, keyf] [, cond] [, finalize] })
```
 参数说明

* ns: 集合名称
* key ：用来分组文档的字段。和 keyf 两者必须有一个
* keyf ：可以接受一个 javascript 函数。用来动态的确定分组文档的字段。和 key 两者必须有一个
* initial ： reduce 中使用变量的初始化
* reduce ：执行的 reduce 函数。函数需要返回值。
* cond ：执行过滤的条件。
* finallize ：在 reduce 执行完成，结果集返回之前对结果集最终执行的函数。可选的。

插入测试数据
```
 for(var i=1; i<20; i++){
    var num=i%6;
    db.test.insert({_id:i,name:"user_"+i,age:num});
 }
```
 普通分组查询
```
 db.test.group({
    key:{age:true},
    initial:{num:0},
    $reduce:function(doc,prev){
        prev.num++
    }
 });

 db.runCommand({
    group: {
        ns: "test",
        key: {
            age: true
        },
        initial: {
            num: 0
        },
    
    $reduce: function(doc,
        prev){
            prev.num++
        }
    }

 });
```
 筛选后分组查询
```
 db.test.group({
    key: {
        age: true
    },
    initial: {
        num: 0
    },
    
    $reduce: function(doc,
    prev){
        prev.num++
    },
    
    condition: {
        age: {
            $gt: 2
        }
    }
    
 });

 db.runCommand({
    group: {
        ns: "test",
        key: {
            age: true
        },
        
        initial: {
            num: 0
        },
        
        $reduce: function(doc,
        prev){
            prev.num++
        },
        
        condition: {
            age: {
                $gt: 2
            }
        }
    }
    
 });
```
 group 联合 $where 查询
```
 db.test.group({
    key: {
        age: true
    },
    
    initial: {
        num: 0
    },
    
    $reduce: function(doc,
    prev){
        prev.num++
    },
    
    condition: {
        $where: function(){
            returnthis.age>2;
        }
    }
    
 });
```
 使用函数返回值分组
```
 // 注意， $keyf 指定的函数一定要返回一个对象

db.test.group({
    $keyf: function(doc){
        return{
            age: doc.age
        };
    },
    
    initial: {
        num: 0
    },
    
    $reduce: function(doc,
    prev){
        prev.num++
    }
    
});
    
db.runCommand({
    group: {
        ns: "test",
        $keyf: function(doc){
            return{
                age: doc.age
            };
        },
        
        initial: {
            num: 0
        },
        
        $reduce: function(doc,
        prev){
            prev.num++
        }    
    }
    
});
```
 使用终结器
```
 db.test.group({
    
    $keyf: function(doc){
        return{
            age: doc.age
        };
    },
    
    initial: {
        num: 0
    },
    
    $reduce: function(doc,
    prev){
        prev.num++
    },
    
    finalize: function(doc){
        doc.count=doc.num;deletedoc.num;
    }
    
 });

 db.runCommand({
    group: {
        ns: "test",
        $keyf: function(doc){
            return{
                age: doc.age
            };
        },
        
        initial: {
            num: 0
        },
        
        $reduce: function(doc,
        prev){
            prev.num++
        },
        
        finalize: function(doc){
            doc.count=doc.num;deletedoc.num;
        }
    }
    
 });
```
#### 关系型数据库与 MongoDB 数据库在一些术语上的对

 **MongoDB 操作符** | **范例** | **关系型数据库 (mysql)** | **关系型数据库范例**
-|-|-
 count() | count({"key":value}) 或 find({"key":value}).count() | count | select count(1) from table where key=value
 distinct | db.runCommand({"distinct":collectionname, "key":"key1",{'key2':value2}}) 或 db.collectionname.distinct("key1",{key2:value2}) | distinct | select distinct key1 from table where key2=value2
 group | db.test.group({ key:{age:true}, initial:{num:0}, $reduce:function(doc,prev){ prev.num++ }, condition:{$where:function(){ return this.age>2; } } }); | group by | select count(1),key1 from table where key2=1 group by key1

[0]: http://www.cnblogs.com/AlvinLee/p/6069637.html
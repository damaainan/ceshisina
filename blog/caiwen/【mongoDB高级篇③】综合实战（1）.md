## 【mongoDB高级篇③】综合实战(1): 分析国家地震数据

来源：[https://segmentfault.com/a/1190000004536424](https://segmentfault.com/a/1190000004536424)


## 数据准备

* 下载国家地震数据 [http://data.earthquake.cn/data/][0]

* 通过navicat导入到数据库,方便和mysql语句做对比



## shard分片集群配置

```LANG
# step 1
mkdir -p ./data/shard/s0 ./data/shard/s1  #创建数据目录
mkdir -p ./data/shard/log # 创建日志目录
./bin/mongod --port 27017 --dbpath /usr/local/mongodb/data/shard/s0 --fork --logpath /usr/local/mongodb/data/shard/log/s0.log # 启动Shard Server实例1
./bin/mongod --port 27018 --dbpath /usr/local/mongodb/data/shard/s1 --fork --logpath /usr/local/mongodb/data/shard/log/s1.log # 启动Shard Server实例2

# step 2
mkdir -p ./data/shard/config #创建数据目录
./bin/mongod --port 27027 --dbpath /usr/local/mongodb/data/shard/config --fork --logpath /usr/local/mongodb/data/shard/log/config.log #启动Config Server实例

# step 3
./bin/mongos --port 4000 --configdb localhost:27027 --fork --logpath /usr/local/mongodb/data/shard/log/route.log --chunkSize=1 # 启动Route Server实例

# step 4
./bin/mongo admin --port 4000 #此操作需要连接admin库
> db.runCommand({ addshard:"localhost:27017" }) #添加 Shard Server 或者用 sh.addshard()命令来添加,下同;
{ "shardAdded" : "shard0000", "ok" : 1 }
> db.runCommand({ addshard:"localhost:27018" })
{ "shardAdded" : "shard0001", "ok" : 1 }
> db.runCommand({ enablesharding:"map" }) #设置分片存储的数据库
{ "ok" : 1 }
> db.runCommand({ shardcollection: "map.dz", key: { id:1 }}) # 设置分片的集合名称。且必须指定Shard Key，系统会自动创建索引，然后根据这个shard Key来计算
{ "collectionsharded" : "map.dz", "ok" : 1 }

# 手动预先分片
 for(var i=1;i<=30;i++) { sh.splitAt('map.dz',{id:i*1000}) }
```

然后通过MongoVUE把mysql中的数据导入到mongos(4000)中

## 数据分析实战
### 根据震级类型来求和

```LANG
/******通过group******/
db.dz.group({
  key:{type:1},
  initial:{count:0},
  reduce: function ( curr, result ) { 
    result.count ++;
  }
})
// Error: group command failed: { "ok" : 0, "errmsg" : "can't do command: group on sharded collection" } 
// group不能使用在分片上

/******通过聚合管道aggregate******/
db.dz.aggregate([
 {
   $group:{
     _id:"$type",
     count:{$sum:1}
   }
 }
 
 /******通过映射化简mapReduce******/
 var map = function(){
  emit(this.type,1); //把1映射到每个this.type上,然后sum就为count,还有一个技巧就是把count映射到1上,就是求总和
}

var reduce = function(type,count){
  var total = Array.sum(count);
  // return {type:type,count:total}; 注意,这样返回是错误的,total是一个对象??? {type:type,count:count};
  return total;
}

//或者
var reduce = function(type,count){
  var res = 0;
  for (var i = 0; i < count.length;i++) {
    res +=count[i];
  }
  return res;
}

db.dz.mapReduce(map,reduce,{out:'res'});
```
### 根据日期来分组看哪一月的地震最多

```LANG
/*****地震每日发生次数最多的地方*****/
db.dz.aggregate([
  { $group:{
    _id:{date:"$date"}, //还不知道如何通过 date.substring(0,6)来分组,先跳过,做按日来分组,当然这里的date还是字符串,如果是日期类型的话,就好处理了,这就延伸出另外一个问题,字符串如何转换为时间类型;
    count:{$sum:1},
    }
  },
  {
    $sort:{count:-1} // 做了个降序
  },
  {
    $limit:1
  }
]);

/*****每日发生地震次数最多的10个地方,并求出最大值*****/
db.dz.aggregate([
  { $group:{
    _id:{date:"$date",address:"$address"}, 
    count:{$sum:1},
    maxvalue:{$max:"$value"},
    }
  },
  {
    $sort:{count:-1}
  },
  {
    $limit:10
  }
]);
```
### 求每5个经纬度范围的地震次数;

```LANG
var map = function(){
  //映射到经纬度
  var latitude = Math.floor(this.latitude/5)*5;
  var longitude = Math.floor(this.longitude/5)*5; //除5下取整又乘以5,目的得到的经纬度都是5的倍数,也就是每隔5就一个数;
  var block = latitude+':'+longitude;
  emit(block,1); //总共统计每block出现地震的次数;
}

var reduce = function(block,value){
  return Array.sum(value);
}

db.runCommand({
  mapReduce:'dz',
  map:map,
  reduce:reduce,
  out:'res'
})

db.res.find().sort({value:-1});
```
### 每月发生地震次数最多的10个地方,并求出震级最大值
#### **`方法一,该方法有误,未完成,先记录`** 

注意,本方法有一些问题我是花了很多功夫都没解决,先记录一下,如果有玩mongoDB的朋友有缘看到这篇文章,又有心的话,希望留言指正; 
当然,这属于技术上的一个钻牛角尖,其实完全可以绕开的...

```LANG
var map = function(){
  var date = this.date.substring(0,6);
  emit(date,{count:this.address,value:this.value});//把地点和值映射到月份上
}


var reduce = function(date,result){ 

/*
  // 此时result的结构应该如下,为每月的地址数据明细
  // 注意这里说的是应该,但实际上不是,这与我理解的mapReduce有误,并且我暂时还不能理解该结构最终为什么会呈现出差异,所以,我先按以下的结构,来在Reduce中做js处理
    "result": [
        {
            "address": "新疆阿图什",
            "value": 1.6
        },
        {
            "address": "云南澜沧",
            "value": 1.3
        },
        {
            "address": "新疆哈密",
            "value": 2
        }
    ]

  //我想要得到的结果如下:
  [{'四川木里':{count:2,max:5.2},'云南玉龙':{count:100,max:4.5}}]
*/

 var arr = [];

 for (var i = 0; i < result.length;i++) {
    var arrTmp = [result[i]];
    var address = result[i]['address'];

    for (var j = i+1; j < result.length; j++) {
      if(result[j]['address'] == address){
        arrTmp.push(result[j]);
        result.splice(j,1);
        j--;
      }
    };

    var value = []

    for(var a=0; a <arrTmp.length;a++){
      if(value.indexOf(arrTmp[a]['value']) == '-1'){
        value.push(arrTmp[a]['value']);
      }
    }
    
    var max = 0;
    for(var i=0;i<value.length;i++){
      max = max < value[i]?value[i]:max;
    }

    var ele = {};
    ele[address] = {count:arrTmp.length,max:max};
    arr.push(ele);
 }
 return  {result:arr};
}

db.runCommand({
  mapReduce:'dz',
  map:map,
  reduce:reduce,
  finalize:finalize, // 由于Reduce返回的结构是有误的,所以finalize还没办法处理,先留空;
  out:'res'
})
```
#### **`方法二`** 

本方法也有一个让我百思不得其解的问题,在注释部分有说明;

```LANG
var map = function(){
  var date = this.date.substring(0,6);
  var map = date+'_'+this.address;
  emit(map,{count:1,value:this.value});
}

var reduce = function(date,result){
  var count = 0;
  for(var i=0;i<result.length;i++){
    count += result[i]['count'];  // result[i]['count']的值都是1
  }

  //var count = result.length; // 一开始我的count值是这样写的,但是结果是错误的与mysql算出来的不符合,改成上面的才正确,这里也让我很郁闷,result[i]['count']的值都是1,result.length是其result元素的总合,按道理这个count和上面的count是一样的,但事实证明,我又错了,居然不一样....又是一个理解不了的问题;
  
  var value = [];
  for(var i=0;i<result.length;i++){
    value.push(result[i].value);
  }
  var max=0;
  for(var i=0;i<value.length;i++){
    max = max < value[i]?value[i]:max;
  }
  return {count:count,max:max};
}

db.runCommand({
  mapReduce:'dz',
  map:map,
  reduce:reduce,
  out:'res'
})

db.res.find().sort({'value.count':-1}).limit(10); //在输出集合中再进行筛选
// 但是,第一多的数据和mysql算下来的不同,其后9名都是相同的
```

mongoDB系列文章到此先告一段落,后续再添加 【mongoDB高级篇】mongoDB在LBS中的应用; 2015-9-17

[0]: http://data.earthquake.cn/data/
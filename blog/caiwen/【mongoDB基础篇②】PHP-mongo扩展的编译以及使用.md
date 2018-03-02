## 【mongoDB基础篇②】PHP-mongo扩展的编译以及使用

来源：[https://segmentfault.com/a/1190000004181774](https://segmentfault.com/a/1190000004181774)


## 安装PHP-mongo扩展

安装php-mongo扩展和安装其他php扩展的步骤一样:

```LANG
#1.首先上http://pecl.php.net上面搜索mongo,得到下载地址
wget http://pecl.php.net/get/mongo-1.6.11.tgz
tar zxvf ./mongo-1.6.11.tgz

#2.解压进入,phpize后进行编译
cd ./mongo-1.6.11
phpize #有可能要写全phpize的地址
./configure --with-php-config=/usr/local/php/bin/php-config
make && make install

#3.编译成功后出现:
Installing shared extensions:     /usr/local/php/lib/php/extensions/no-debug-non-zts-20100525/

#4.得其地址写入php.ini
extension = mongo.so #有可能要写全mongo.so的路径,也就是上面的提示

#5.安装完以后,看phpinfo()中有没有这个扩展,有就表示安装成功;
```

以上基本上也是其他PHP扩展安装的常规方法
## 官方的PHP-mongo类使用

一般都是进行二次封装后再使用,便于后续的扩展;在此之前,我们先熟悉官方的的使用方法,php官方类使用起来跟其他扩展的类方法相比略为特殊

```LANG
<?php
# 首先通过MongoClient(Mongo)类来链接mongo的客户端
# class mongo: This class extends MongoClient and provides access to several deprecated methods. 一般我们用mongoClient就可以了

$client = new MongoClient(); // 得到$client客户端对象,如果没有传入参数，它会连接到 "localhost:27017",实际上还可以传入参数mongodb://[username:password@]host1[:port1][,host2[:port2:],...]/ 链接多个客户端,比如链接三个mongos,其中一个崩溃,它会马上去链接第二个,直到全部链接不上,才会抛出一个异常 更多constructor参考:http://php.net/manual/zh/mongoclient.construct.php

$db = $client -> shop; //获取名称为shop的数据库对象,或者使用$db = $m->selectDB("example"),这个时候$db是由class mongoDB来实例的,更多可以参考http://php.net/manual/zh/class.mongodb.php  

$goods = $db -> goods; //得到goods集合对象,这个时候的$goods对象是由class MongoCollection类来实例的;

//db.goods.find({},{'_id':0,'goods_id':1,'goods_name':1})
$cursor = $goods -> $goods -> find(array(),array('_id' => 0,'goods_id' => 1,'goods_name' => 1));  //得到cursor对象,这个时候的cursor对象是由class MongoCursor来实例的

#接下来才是正式操作mongoDB的数据,把json转换为数组既可;
$data = array();
foreach ($cursor as $v) {
  $data[] = $v;
}

#或者
$data = iterator_to_array($cursor); # 内置函数: 将迭代器中的元素拷贝到数组

/**********部分操作示例**********/

$users = $db -> users;

# 返回值
$insert = $users -> insert(array('user_id' => 1,'user_name' => 'zxg','sex' => 'boy'));
/*
  Array
  (
      [ok] => 1 //除非 last_error 本身出现错误,否则都是1,代表成功;
      [n] => 0 //受影响的数量,在insert的时候这个值始终是0;
      [err] => null
      [errmsg] => null
  )
 */
 
# 执行js,注意这是class mongoDB类的方法
$response = $db->execute("function(greeting, name) { return greeting+', '+name+'!'; }", array("Good bye", "Joe"));
echo $response['retval']; // Good bye, Joe!
?>
```
## 参考

PHP: MongoClient - Manual: [http://php.net/manual/zh/class.mongoclient.php][0]

[0]: http://php.net/manual/zh/class.mongoclient.php
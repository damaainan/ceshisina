# 使用PHP实现查找附近的人

[李海涛][0]

16 天前

最近有个业务场景使用到了查找附近的人，于是查阅了相关资料，并对使用PHP实现相关功能的多种方式和具体实现做一篇技术总结，欢迎各位看官提出意见和纠错，下面开始进入正题：

## LBS(基于位置的服务)

查找附近的人有个更大的专有名词叫做LBS(基于位置的服务)，LBS是指是指通过电信移动运营商的无线电通讯网络或外部定位方式，获取移动终端用户的位置信息，在GIS平台的支持下，为用户提供相应服务的一种增值业务。因此首先得获取用户的位置，获取用户的位置有基于GPS、基于运营商基站、WIFI等方式，一般由客户端获取用户位置的经纬度坐标上传至应用服务器，应用服务器对用户坐标进行保存，客户端获取附近的人数据的时候，应用服务器基于请求人的地理位置配合一定的条件(距离，性别，活跃时间等)去数据库进行筛选和排序。

## 根据经纬度如何得出两点之间的距离？

我们都知道平面坐标内的两点坐标可以使用平面坐标距离公式来计算，但经纬度是利用三度空间的球面来定义地球上的空间的球面坐标系统，假定地球是正球体，关于球面距离计算公式如下：

![d(x1,y1,x2,y2)=r*arccos(sin(x1)*sin(x2)+cos(x1)*cos(x2)*cos(y1-y2))][1]

具体推断过程有兴趣的推荐这篇文章：[根据经纬度计算地面两点间的距离-数学公式及推导][2]

PHP函数代码如下：

    /**
         * 根据两点间的经纬度计算距离
         * @param $lat1
         * @param $lng1
         * @param $lat2
         * @param $lng2
         * @return float
         */
        public static function getDistance($lat1, $lng1, $lat2, $lng2){
            $earthRadius = 6367000; //approximate radius of earth in meters
            $lat1 = ($lat1 * pi() ) / 180;
            $lng1 = ($lng1 * pi() ) / 180;
            $lat2 = ($lat2 * pi() ) / 180;
            $lng2 = ($lng2 * pi() ) / 180;
            $calcLongitude = $lng2 - $lng1;
            $calcLatitude = $lat2 - $lat1;
            $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
            $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
            $calculatedDistance = $earthRadius * $stepTwo;
            return round($calculatedDistance);
        }

MySQL代码如下：

    SELECT  
      id, (  
        3959 * acos (  
          cos ( radians(78.3232) )  
          * cos( radians( lat ) )  
          * cos( radians( lng ) - radians(65.3234) )  
          + sin ( radians(78.3232) )  
          * sin( radians( lat ) )  
        )  
      ) AS distance  
    FROM markers  
    HAVING distance < 30  
    ORDER BY distance  
    LIMIT 0 , 20;

除了上面通过计算球面距离公式来获取，我们可以使用某些数据库服务得到，比如Redis和MongoDB：

Redis 3.2提供GEO地理位置功能，不仅可以获取两个位置之间的距离，获取指定位置范围内的地理信息位置集合也很简单。[Redis命令文档][3]

1.增加地理位置

    GEOADD key longitude latitude member [longitude latitude member ...]

2.获取地理位置

    GEOPOS key member [member ...]

3.获取两个地理位置的距离

    GEODIST key member1 member2 [unit]

4.获取指定经纬度的地理信息位置集合

    GEORADIUS key longitude latitude radius m|km|ft|mi [WITHCOORD] [WITHDIST] [WITHHASH] [COUNT count] [ASC|DESC] [STORE key] [STOREDIST key]

5.获取指定成员的地理信息位置集合

    GEORADIUSBYMEMBER key member radius m|km|ft|mi [WITHCOORD] [WITHDIST] [WITHHASH] [COUNT count] [ASC|DESC] [STORE key] [STOREDIST key]

MongoDB专门针对这种查询建立了地理空间索引。 2d和2dsphere索引，分别是针对平面和球面。 [MongoDB文档][4]

1.添加数据

    db.location.insert( {uin : 1 , loc : { lon : 50 , lat : 50 } } )

2.建立索引

    db.location.ensureIndex( { loc : "2d" } )

3.查找附近的点

    db.location.find( { loc :{ $near : [50, 50] } )

4.最大距离和限制条数

    db.location.find( { loc : { $near : [50, 50] , $maxDistance : 5 } } ).limit(20)

5.使用geoNear在查询结果中返回每个点距离查询点的距离

    db.runCommand( { geoNear : "location" , near : [ 50 , 50 ], num : 10, query : { type : "museum" } } )

6.使用geoNear附带查询条件和返回条数，geoNear使用runCommand命令不支持find查询中分页相关limit和skip参数的功能

    db.runCommand( { geoNear : "location" , near : [ 50 , 50 ], num : 10, query : { uin : 1 } })

## PHP多种方式和具体实现

1.基于MySql

成员添加方法：

    public function geoAdd($uin, $lon, $lat)
    {
        $pdo = $this->getPdo();
        $sql = 'INSERT INTO `markers`(`uin`, `lon`, `lat`) VALUES (?, ?, ?)';
        $stmt = $pdo->prepare($sql);
        return $stmt->execute(array($uin, $lon, $lat));
    }

查询附近的人(支持查询条件和分页)：

    public function geoNearFind($lon, $lat, $maxDistance = 0, $where = array(), $page = 0)
    {
        $pdo = $this->getPdo();
        $sql = "SELECT  
                  id, (  
                    3959 * acos (  
                      cos ( radians(:lat) )  
                      * cos( radians( lat ) )  
                      * cos( radians( lon ) - radians(:lon) )  
                      + sin ( radians(:lat) )  
                      * sin( radians( lat ) )  
                    )  
                  ) AS distance  
                FROM markers";
    
        $input[':lat'] = $lat;
        $input[':lon'] = $lon;
    
        if ($where) {
            $sqlWhere = ' WHERE ';
            foreach ($where as $key => $value) {
                $sqlWhere .= "`{$key}` = :{$key} ,";
                $input[":{$key}"] = $value;
            }
            $sql .= rtrim($sqlWhere, ',');
        }
    
        if ($maxDistance) {
            $sqlHaving = " HAVING distance < :maxDistance";
            $sql .= $sqlHaving;
            $input[':maxDistance'] = $maxDistance;
        }
    
        $sql .= ' ORDER BY distance';
    
        if ($page) {
            $page > 1 ? $offset = ($page - 1) * $this->pageCount : $offset = 0;
            $sqlLimit = " LIMIT {$offset} , {$this->pageCount}";
            $sql .= $sqlLimit;
        }
    
        $stmt = $pdo->prepare($sql);
        $stmt->execute($input);
        $list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        return $list;
    }

2.基于Redis(3.2以上)

PHP使用Redis可以安装[redis][5]扩展或者通过composer安装[predis][6]类库，本文使用redis扩展来实现。

成员添加方法：

    public function geoAdd($uin, $lon, $lat)
    {
        $redis = $this->getRedis();
        $redis->geoAdd('markers', $lon, $lat, $uin);
        return true;
    }

查询附近的人(不支持查询条件和分页)：

    public function geoNearFind($uin, $maxDistance = 0, $unit = 'km')
    {
        $redis = $this->getRedis();
        $options = ['WITHDIST']; //显示距离
        $list = $redis->geoRadiusByMember('markers', $uin, $maxDistance, $unit, $options);
        return $list;
    }

3.基于MongoDB

PHP使用MongoDB的扩展有[mongo][7]([文档][8])和[mongodb][9]([文档][10])，两者写法差别很大，选择好扩展需要对应相应的文档查看，由于mongodb扩展是新版，本文选择mongodb扩展。

假设我们创建db库和location集合

设置索引：

    db.getCollection('location').ensureIndex({"uin":1},{"unique":true}) 
    db.getCollection('location').ensureIndex({loc:"2d"})
    #若查询位置附带查询，可以将常查询条件添加至组合索引
    #db.getCollection('location').ensureIndex({loc:"2d",uin:1})

成员添加方法：

    public function geoAdd($uin, $lon, $lat)
    {
        $document = array(
            'uin' => $uin,
            'loc' => array(
                'lon' =>  $lon,
                'lat' =>  $lat,
            ),
        );
    
        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->update(
            ['uin' => $uin],
            $document,
            [ 'upsert' => true]
        );
        //出现noreply 可以改成确认式写入
        $manager = $this->getMongoManager();
        $writeConcern = new MongoDB\Driver\WriteConcern(1, 100);
        //$writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 100);
        $result = $manager->executeBulkWrite('db.location', $bulk, $writeConcern);
    
        if ($result->getWriteErrors()) {
            return false;
        }
        return true;
    }  

查询附近的人(返回结果没有距离，支持查询条件，支持分页)

    public function geoNearFind($lon, $lat, $maxDistance = 0, $where = array(), $page = 0)
    {
        $filter = array(
            'loc' => array(
                '$near' => array($lon, $lat),
            ),
        );
        if ($maxDistance) {
            $filter['loc']['$maxDistance'] = $maxDistance;
        }
        if ($where) {
            $filter = array_merge($filter, $where);
        }
        $options = array();
        if ($page) {
            $page > 1 ? $skip = ($page - 1) * $this->pageCount : $skip = 0;
            $options = [
                'limit' => $this->pageCount,
                'skip' => $skip
            ];
        }
    
        $query = new MongoDB\Driver\Query($filter, $options);
        $manager = $this->getMongoManager();
        $cursor = $manager->executeQuery('db.location', $query);
        $list = $cursor->toArray();
        return $list;
    }

查询附近的人(返回结果带距离，支持查询条件，支付返回数量，不支持分页)：

    public function geoNearFindReturnDistance($lon, $lat, $maxDistance = 0, $where = array(), $num = 0)
    {
        $params = array(
            'geoNear' => "location",
            'near' => array($lon, $lat),
            'spherical' => true, // spherical设为false（默认），dis的单位与坐标的单位保持一致，spherical设为true，dis的单位是弧度
            'distanceMultiplier' => 6371, // 计算成公里，坐标单位distanceMultiplier: 111。 弧度单位 distanceMultiplier: 6371
        );
    
        if ($maxDistance) {
            $params['maxDistance'] = $maxDistance;
        }
        if ($num) {
            $params['num'] = $num;
        }
        if ($where) {
            $params['query'] = $where;
        }
    
        $command = new MongoDB\Driver\Command($params);
        $manager = $this->getMongoManager();
        $cursor = $manager->executeCommand('db', $command);
        $response = (array) $cursor->toArray()[0];
        $list = $response['results'];
        return $list;
    }

注意事项：

1.选择好扩展，mongo和mongodb扩展写法差别很大

2.写数据时出现noreply请检查写入确认级别

3.使用find查询的数据需要自己计算距离，使用geoNear查询的不支持分页

4.使用geoNear查询的距离需要转化成km使用spherical和distanceMultiplier参数

上述demo可以戳这里：[demo][11]

## 总结

以上介绍了三种方式去实现查询附近的人的功能，各种方式都有各自的适用场景，比如数据行比较少，例如查询用户和几座城市之间的距离使用Mysql就足够了，如果需要实时快速响应并且普通查找范围内的距离，可以使用Redis，但如果数据量大并且多种属性筛选条件，使用mongo会更方便，以上只是建议，具体实现方案还要视具体业务去进行方案评审。

[0]: https://www.zhihu.com/people/li-hai-tao-83
[1]: http://www.zhihu.com/equation?tex=d%28x1%2Cy1%2Cx2%2Cy2%29%3Dr%2Aarccos%28sin%28x1%29%2Asin%28x2%29%2Bcos%28x1%29%2Acos%28x2%29%2Acos%28y1-y2%29%29
[2]: http://link.zhihu.com/?target=http%3A//www.cnblogs.com/chengyujia/archive/2013/01/13/2858484.html
[3]: http://link.zhihu.com/?target=https%3A//redis.io/commands/
[4]: http://link.zhihu.com/?target=https%3A//docs.mongodb.com/manual/tutorial/build-a-2d-index/
[5]: http://link.zhihu.com/?target=http%3A//pecl.php.net/package/redis
[6]: http://link.zhihu.com/?target=https%3A//packagist.org/packages/predis/predis
[7]: http://link.zhihu.com/?target=http%3A//pecl.php.net/package/mongo
[8]: http://link.zhihu.com/?target=http%3A//php.net/manual/zh/book.mongo.php
[9]: http://link.zhihu.com/?target=http%3A//pecl.php.net/package/mongodb
[10]: http://link.zhihu.com/?target=http%3A//php.net/manual/zh/book.mongodb.php
[11]: http://link.zhihu.com/?target=https%3A//github.com/Mr-litt/lbs
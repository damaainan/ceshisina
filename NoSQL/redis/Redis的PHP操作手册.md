## Redis的PHP操作手册（纯手稿版）


## String 类型操作

**string是redis最基本的类型，而且string类型是二进制安全的。意思是redis的string可以包含任何数据。比如jpg图片或者序列化的对象**

    $redis->set('key','TK');
    

    $redis->set('number','1');
    

    $redis->setex('key',5,'TK'); //设置有效期为5秒的键值
    

    $redis->psetex('key',5000,'TK'); //设置有效期为5000毫秒(同5秒)的键值
    

    $redis->setnx('key','XK'); //若键值存在返回false 不存在返回true

    $redis->delete('key'); 删除键值 可以传入数组 array('key1','key2')删除多个键
    

    $redis->getSet('key','XK'); //将键key的值设置为XK， 并返回这个键值原来的值TK
    

     $ret = $redis->multi()  //批量事务处理,不保证处理数据的原子性
            ->set('key1', 'val1')
            ->get('key1')
            ->setnx('key', 'val2')
            ->get('key2')
            ->exec();
    

    $redis->watch('key');   // 监控键key 是否被其他客户端修改
                               如果KEY在调用watch()和exec()之间被修改，exec失败
    

    function f($redis, $chan, $msg) {  //频道订阅
        switch($chan) {
            case 'chan-1':
                echo $msg;
                break;
    
            case 'chan-2':
                echo $msg;
                break;
    
            case 'chan-2':
                echo $msg;
                break;
        }
    }
    
    $redis->subscribe(array('chan-1', 'chan-2', 'chan-3'), 'f'); // subscribe to 3 chans
    
    $redis->publish('chan-1', 'hello, world!'); // send message. 

    $redis->exists('key'); //验证键是否存在，存在返回true

    $redis->incr('number'); //键值加1

    $redis->incrby('number',-10); //键值加减10

    $redis->incrByFloat('number', +/- 1.5); //键值加减小数

    $redis->decr('number'); // 键值减1

    $redis->decrBy('number',10); // 键值减10

    $mget = $redis->mget(array('number','key')); // 批量获取键值,返回一个数组

    $redis->mset(array('key0' => 'value0', 'key1' => 'value1')); // 批量设置键值

    $redis->msetnx(array('key0' => 'value0', 'key1' => 'value1')); 
                                            // 批量设置键值，类似将setnx()方法批量操作

    $redis->append('key', '-Smudge'); //原键值TK，将值追加到键值后面，键值为TK-Smudge
    

    $redis->getRange('key', 0, 5); // 键值截取从0位置开始到5位置结束
    

    $redis->getRange('key', -6, -1); // 字符串截取从-6(倒数第6位置)开始到-1(倒数第1位置)结束
    

    $redis->setRange('key', 0, 'Smudge'); 
                                        // 键值中替换字符串，0表示从0位置开始
                                           有多少个字符替换多少位置，其中汉字占2个位置
    

    $redis->strlen('key'); //键值长度
    

    $redis->getBit('key');
    

    $redis->setBit('key');
    

- - -

## list链表操作

    $redis->delete('list-key'); // 删除链表
    

    $redis->lPush('list-key', 'A'); //插入链表头部/左侧，返回链表长度
    

    $redis->rPush('list-key', 'B'); //插入链表尾部/右侧，返回链表长度
    

    $redis->lPushx('list-key', 'C'); 
                     // 插入链表头部/左侧,链表不存在返回0，存在即插入成功，返回当前链表长度
    

    $redis->rPushx('list-key', 'C'); 
                     // 插入链表尾部/右侧,链表不存在返回0，存在即插入成功，返回当前链表长度
    

    $redis->lPop('list-key'); //返回LIST顶部（左侧）的VALUE ,后入先出(栈)
    

    $redis->rPop('list-key'); //返回LIST尾部（右侧）的VALUE ,先入先出（队列）
    

    $redis->blPop();
    

    $redis->brPop();
    

    $redis->lSize('list-key'); 
                        // 如果是链表则返回链表长度，空链表返回0 
                           若不是链表或者不为空，则返回false ,判断非链表 " === false "

    $redis->lGet('list-key',-1); // 通过索引获取链表元素 0获取左侧一个  -1获取最后一个
    

    $redis->lSet('list-key', 0, 'X'); //0位置元素替换为 X
    

    $redis->lRange('list-key', 0, 3); 
                        //链表截取 从0开始 3位置结束 ，结束位置为-1 获取开始位置之后的全部

    $redis->lTrim('list-key', 0, 1); // 截取链表(不可逆) 从0索引开始 1索引结束 

    $redis->lRem('list-key', 'C', 2); //链表从左开始删除元素2个C
    

    $redis->lInsert('list-key', Redis::BEFORE, 'C', 'X'); 
                        // 在C元素前面插入X  , Redis::AfTER(表示后面插入) 
                           链表不存在则插入失败 返回0 若元素不存在返回-1

    $redis->rpoplpush('list-key', 'list-key2'); 
                        //从源LIST的最后弹出一个元素
                          并且把这个元素从目标LIST的顶部（左侧）压入目标LIST。 
    

    
    $redis->brpoplpush();
                        //rpoplpush的阻塞版本，这个版本有第三个参数用于设置阻塞时间
                          即如果源LIST为空，那么可以阻塞监听timeout的时间，如果有元素了则执行操作。
    

- - -

## Set集合类型

    set无序集合 不允许出现重复的元素 服务端可以实现多个 集合操作
    

    $redis->sMembers('key'); //获取容器key中所有元素
    

    $redis->sAdd('key' , 'TK');
                     // (从左侧插入,最后插入的元素在0位置),集合中已经存在TK 则返回false 
                         不存在添加成功 返回true

    $redis->sRem('key' , 'TK'); // 移除容器中的TK
    

    $redis->sMove('key','key1','TK'); //将容易key中的元素TK 移动到容器key1  操作成功返回TRUE
    

    $redis->sIsMember('key','TK'); //检查VALUE是否是SET容器中的成员
    

    $redis->sCard('key'); //返回SET容器的成员数
    

    $redis->sPop('key'); //随机返回容器中一个元素，并移除该元素
    

    $redis->sRandMember('key');//随机返回容器中一个元素，不移除该元素
    

    $redis->sInter('key','key1'); 
         // 返回两个集合的交集 没有交集返回一个空数组，若参数只有一个集合，则返回集合对应的完整的数组
    

    $redis->sInterStore('store','key','key1'); //将集合key和集合key1的交集 存入容器store 成功返回1

    $redis->sUnion('key','key1'); //集合key和集合key1的并集  注意即使多个集合有相同元素 只保留一个
    
    $redis->sUnionStore('store','key','key1'); 
                //集合key和集合key1的并集保存在集合store中,  注意即使多个集合有相同元素 只保留一个
    

    $redis->sDiff('key','key1','key2'); //返回数组，该数组元素是存在于key集合而不存在于集合key1 key2
    

- - -

## Zset数据类型

**(stored set) 和 set 一样是字符串的集合，不同的是每个元素都会关联一个 double 类型的 score  
redis的list类型其实就是一个每个子元素都是string类型的双向链表。**

    $redis->zAdd('tkey', 1, 'A'); 
                               //  插入集合tkey中，A元素关联一个分数，插入成功返回1
                                   同时集合元素不可以重复, 如果元素已经存在返回 0

    $redis->zRange('tkey',0,-1); // 获取集合元素，从0位置 到 -1 位置
    

    $redis->zRange('tkey',0,-1, true); 
                        // 获取集合元素，从0位置 到 -1 位置, 返回一个关联数组 带分数 
                          array([A] => 0.01,[B] => 0.02,[D] => 0.03) 其中小数来自zAdd方法第二个参数
    

    $redis->zDelete('tkey', 'B'); // 移除集合tkey中元素B  成功返回1 失败返回 0

    $redis->zRevRange('tkey', 0, -1); // 获取集合元素，从0位置 到 -1 位置，数组按照score降序处理
    
    $redis->zRevRange('tkey', 0, -1,true); 
                    // 获取集合元素，从0位置 到 -1 位置，数组按照score降序处理 返回score关联数组
    

    $redis->zRangeByScore('tkey', 0, 0.2,array('withscores' => true)); 
                //获取几个tkey中score在区间[0,0.2]元素 ,score由低到高排序,
                    元素具有相同的score，那么会按照字典顺序排列 , withscores 控制返回关联数组
    

    $redis->zRangeByScore('tkey', 0.1, 0.36, array('withscores' => TRUE, 'limit' => array(0, 1)));
                 //其中limit中 0和1 表示取符合条件集合中 从0位置开始，向后扫描1个 返回关联数组

    $redis->zCount('tkey', 2, 10); // 获取tkey中score在区间[2, 10]元素的个数
    

    $redis->zRemRangeByScore('tkey', 1, 3); // 移除tkey中score在区间[1, 3](含边界)的元素
    

    $redis->zRemRangeByRank('tkey', 0, 1); 
                             //默认元素score是递增的，移除tkey中元素 从0开始到-1位置结束
    

    $redis->zSize('tkey');  //返回存储在key对应的有序集合中的元素的个数
    

    $redis->zScore('tkey', 'A'); // 返回集合tkey中元素A的score值

    $redis->zRank('tkey', 'A'); 
                          // 返回集合tkey中元素A的索引值 
                             z集合中元素按照score从低到高进行排列 ，即最低的score index索引为0

    $redis->zIncrBy('tkey', 2.5, 'A'); // 将集合tkey中元素A的score值 加 2.5

    $redis->zUnion('union', array('tkey', 'tkey1')); 
            // 将集合tkey和集合tkey1元素合并于集合union , 并且新集合中元素不能重复
               返回新集合的元素个数， 如果元素A在tkey和tkey1都存在，则合并后的元素A的score相加
    

    $redis->zUnion('ko2', array('k1', 'k2'), array(5, 2)); 
            // 集合k1和集合k2并集于k02 ，array(5,1)中元素的个数与子集合对应，然后 5 对应k1 
               k1每个元素score都要乘以5 ，同理1对应k2，k2每个元素score乘以1 
               然后元素按照递增排序，默认相同的元素score(SUM)相加
    

    $redis->zUnion('ko2', array('k1', 'k2'), array(10, 2),'MAX'); 
            // 各个子集乘以因子之后，元素按照递增排序，相同的元素的score取最大值(MAX)
               也可以设置MIN 取最小值
    

    $redis->zInter('ko1', array('k1', 'k2')); 
            // 集合k1和集合k2取交集于k01 ，且按照score值递增排序
               如果集合元素相同，则新集合中的元素的score值相加
    

    $redis->zInter('ko1', array('k1', 'k2'), array(5, 1)); 
            //集合k1和集合k2取交集于k01 ，array(5,1)中元素的个数与子集合对应，然后 5 对应k1 
              k1每个元素score都要乘以5 ，同理1对应k2，k2每个元素score乘以1 
              ，然后元素score按照递增排序，默认相同的元素score(SUM)相加
    

    $redis->zInter('ko1', array('k1', 'k2'), array(5, 1),'MAX'); 
            // 各个子集乘以因子之后，元素score按照递增排序，相同的元素score取最大值(MAX)
               也可以设置MIN 取最小值
    

- - -

## Hash数据类型

**redis hash是一个string类型的field和value的映射表.它的添加，删除操作都是O(1)（平均）.hash特别适合用于存储对象。**

    $redis->hSet('h', 'name', 'TK'); // 在h表中 添加name字段 value为TK
    

    $redis->hSetNx('h', 'name', 'TK');
             // 在h表中 添加name字段 value为TK 如果字段name的value存在返回false 否则返回 true

    $redis->hGet('h', 'name'); // 获取h表中name字段value
    

    $redis->hLen('h'); // 获取h表长度即字段的个数
    

    $redis->hDel('h','email'); // 删除h表中email 字段
    

    $redis->hKeys('h'); // 获取h表中所有字段
    

    $redis->hVals('h'); // 获取h表中所有字段value
    

    $redis->hGetAll('h'); // 获取h表中所有字段和value 返回一个关联数组(字段为键值)
    

    $redis->hExists('h', 'email'); //判断email 字段是否存在与表h 不存在返回false

    $redis->hSet('h', 'age', 28);
    

    $redis->hIncrBy('h', 'age', -2); 
     // 设置h表中age字段value加(-2) 如果value是个非数值 则返回false 否则，返回操作后的value

    $redis->hIncrByFloat('h', 'age', -0.33);  
            // 设置h表中age字段value加(-2.6) 如果value是个非数值 则返回false 否则
               返回操作后的value(小数点保留15位)
    

    $redis->hMset('h', array('score' => '80', 'salary' => 2000)); // 表h 批量设置字段和value

    $redis->hMGet('h', array('score','salary')); // 表h 批量获取字段的value


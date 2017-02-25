# php操作mongo


    //连接localhost:27017
    $conn = new Mongo();
    
    //连接远程主机默认端口
    $conn = new Mongo('test.com');
    
    //连接远程主机22011端口
    $conn = new Mongo('test.com:22011');
    
    //MongoDB有用户名密码
    $conn = new Mongo("mongodb://${username}:${password}@localhost")
    
    //MongoDB有用户名密码并指定数据库blog
    $conn = new Mongo("mongodb://${username}:${password}@localhost/blog");
    
    //多个服务器
    $conn = new Mongo("mongodb://localhost:27017,localhost:27018");
    //选择数据库blog
    $db = $conn->blog;
    //制定结果集（表名：users）
    $collection = $db->users;
    //新增
    $user = array('name' => 'caleng', 'email' => 'admin#admin.com');
    $collection->insert($user);
    //修改
    $newdata = array('$set' => array("email" => "test@test.com"));
    $collection->update(array("name" => "caleng"), $newdata);
    //删除
    $collection->remove(array('name'=>'caleng'), array("justOne" => true));
    //查找
    $cursor = $collection->find();
    var_dump($cursor);
    //查找一条
    $user = $collection->findOne(array('name' => 'caleng'), array('email'));
    var_dump($user);
    //关闭数据库
    $conn->close();


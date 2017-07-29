# PHP构建表利器-Dbman

作者  麻城东 关注 2017.05.12 23:15*  字数 790  

Dbman是一个可以让PHP开发者简单的修改和维护管理数据库表的小工具, 它避免了人为的手写 SQL语句，轻松的管理数据库迁移。

Dbman的代码结构如下：

![][1]

1. demo目录已经写好了两个demo文件。  
2. Dbman.php是Dbman的主代码文件，遵行PSR-4规范。  
3. config.php是Dbman的配置文件。  
4. dbman是可执行脚本文件，提供了6个方法。

### 开始使用Dbman

首先我们使用 php dbman ， 效果如下图：

![][2]

接下来我们使用 php dbman help，如下图：

![][3]

在配置好config.php的前提下，使用php dbman init创建系统管理表sys_schema_info，针对已经存在数据表的数据库，使用php dbman init --data初始化表信息，效果如下图：

![][4]

接下来我们一个一个介绍其他方法的使用：  
`php dbman update` 添加和更新表字段，包括添加表、删除表。  
`php dbman delete` 删除表字段。  
`php dbman maintain` 等于update方法和delete方法。  
`php dbman backups` 备份数据表成php文件。

Dbman的设计理念很简单，一个文件就是一张表，文件名即表名。根据系统表sys_schema_info和INFORMATION_SCHEMA.TABLES表判断xxx.php文件即xxx表是否存在，既而断定是创建表，还是更新表。如果xxx表存在，就判断表文件的md5值和上个版本的md5值是否相同，既而断定是否更新表。Dbman为每个php表文件添加了一个版本号version，以version 等于 -1表示删除表。

#### 下面我们使用Dbman创建一张demo表。

首先，创建一个demo.php文件，如下：

    return array(
        'fields' => //数据库demo表的字段定义信息
            array(
                'id' => //id列的字段属性
                    array(
                        'name' => 'id', //字段名
                        'type' => 'int(10)', //字段类型
                        'notnull' => false, //是否为空
                        'default' => NULL,  //默认值
                        'primary' => true,  //是否主键，是主键
                        'autoinc' => true,  //是否自增长，是自增长
                    ),
                'uname' =>
                    array(
                        'name' => 'uname',  //字段名
                        'type' => 'char(30)', //字段类型
                        'notnull' => false, //是否为空
                        'default' => NULL, //默认值
                        'primary' => false, //是否主键，不是主键
                        'autoinc' => false, //是否自增长，非自增长
                    ),
    
            ),
        'index' => //数据库demo表的索引定义信息
            array(
    
            ),
        'version' => '1.0', //版本号，默认1.0
        'engine' => 'innodb', //存储引擎，默认innodb
        'comment' => 'demo表', //备注
    );

接着，我们执行命令: **php dbman maintain**

![][11]

  
最后，我们查看数据库，会发现多了一张demo表，如下：

![][5]

到这里demo表就已经创建成功了，接着我们继续为它添加字段和索引 

```
    return array(
        'fields' => //数据库demo表的字段定义信息
            array(
                'id' => //id列的字段属性
                    array(
                        'name' => 'id', //字段名
                        'type' => 'int(10)', //字段类型
                        'notnull' => false, //是否为空
                        'default' => NULL,  //默认值
                        'primary' => true,  //是否主键，是主键
                        'autoinc' => true,  //是否自增长，是自增长
                    ),
                'uname' =>
                    array(
                        'name' => 'uname',  //字段名
                        'type' => 'char(30)', //字段类型
                        'notnull' => false, //是否为空
                        'default' => NULL, //默认值
                        'primary' => false, //是否主键，不是主键
                        'autoinc' => false, //是否自增长，非自增长
                    ),
                'password' =>
                    array(
                        'name' => 'password',
                        'type' => 'char(32)',
                        'notnull' => false,
                        'default' => NULL,
                        'primary' => false,
                        'autoinc' => false,
                    ),
                'start_status' =>
                    array(
                        'name' => 'start_status',
                        'type' => 'enum(\'Y\',\'N\')',
                        'notnull' => false,
                        'default' => 'Y',
                        'primary' => false,
                        'autoinc' => false,
                    ),
                'login_status' =>
                    array(
                        'name' => 'login_status',
                        'type' => 'enum(\'Y\',\'N\')',
                        'notnull' => false,
                        'default' => 'N',
                        'primary' => false,
                        'autoinc' => false,
                    ),
    
            ),
        'index' => //数据库demo表的索引定义信息
            array(
                'index_demo_uname'=>
                    array(
                        //索引名
                        'name'=>'index_demo_uname',
                        //索引类型(normal 普通索引| unique 唯一索引| primary 主键索引)
                        'type' => 'unique',
                        //索引字段
                        'fields'=> 'uname',
                        //索引方法，默认空即可
                        'method'=>'',
                    ),
                'index_demo_status'=>
                    array(
                        //索引名
                        'name'=>'index_demo_status',
                        //索引类型(默认是： normal 普通索引)
                        'type' => '',
                        //索引字段(array 表示组合索引)
                        'fields'=> array(
                            'start_status',
                            'login_status',
                        ),
                        //索引方法，默认空即可
                        'method'=>'',
                    ),
            ),
        'version' => '1.0', //版本号，默认1.0
        'engine' => 'innodb', //存储引擎，默认innodb
        'comment' => 'demo表', //备注
    );
```

再次，执行命令: **php dbman maintain**![][6]

![][7]

可以出来，字段和索引都已经添加成功了。  
最后我们来看看删除字段，删除字段其实很容易，直接删除文件里的字段信息即可，如：删除password字段 :

```
    return array(
        'fields' => //数据库demo表的字段定义信息
            array(
                'id' => //id列的字段属性
                    array(
                        'name' => 'id', //字段名
                        'type' => 'int(10)', //字段类型
                        'notnull' => false, //是否为空
                        'default' => NULL,  //默认值
                        'primary' => true,  //是否主键，是主键
                        'autoinc' => true,  //是否自增长，是自增长
                    ),
                'uname' =>
                    array(
                        'name' => 'uname',  //字段名
                        'type' => 'char(30)', //字段类型
                        'notnull' => false, //是否为空
                        'default' => NULL, //默认值
                        'primary' => false, //是否主键，不是主键
                        'autoinc' => false, //是否自增长，非自增长
                    ),
                'start_status' =>
                    array(
                        'name' => 'start_status',
                        'type' => 'enum(\'Y\',\'N\')',
                        'notnull' => false,
                        'default' => 'Y',
                        'primary' => false,
                        'autoinc' => false,
                    ),
                'login_status' =>
                    array(
                        'name' => 'login_status',
                        'type' => 'enum(\'Y\',\'N\')',
                        'notnull' => false,
                        'default' => 'N',
                        'primary' => false,
                        'autoinc' => false,
                    ),
    
            ),
        'index' => //数据库demo表的索引定义信息
            array(
                'index_demo_uname'=>
                    array(
                        //索引名
                        'name'=>'index_demo_uname',
                        //索引类型(normal 普通索引| unique 唯一索引| primary 主键索引)
                        'type' => 'unique',
                        //索引字段
                        'fields'=> 'uname',
                        //索引方法，默认空即可
                        'method'=>'',
                    ),
                'index_demo_status'=>
                    array(
                        //索引名
                        'name'=>'index_demo_status',
                        //索引类型(默认是： normal 普通索引)
                        'type' => '',
                        //索引字段(array 表示组合索引)
                        'fields'=> array(
                            'start_status',
                            'login_status',
                        ),
                        //索引方法，默认空即可
                        'method'=>'',
                    ),
            ),
        'version' => '1.0', //版本号，默认1.0
        'engine' => 'innodb', //存储引擎，默认innodb
        'comment' => 'demo表', //备注
    );
```
再次，执行命令: **php dbman maintain**

![][8]

于此password字段就已经成功删除了。  
至于删除表，修改version号为-1即可，这里就不多演示了。

### 如何在TP5中使用Dbman

首先，将代码放到extend目录，如下：

![][9]
  
接着，在tp5/application/command.php文件里面添加以下两行：

        'app\admin\command\dbman\Init',
        'app\admin\command\dbman\Maintain',

最后，在tp5/application/admin/command/dbman/目录添加两个文件：

1.tp5/application/admin/command/dbman/Maintain.php
```php
    <?php
    namespace app\admin\command\dbman;
    
    use think\console\Command;
    use think\console\Input;
    use think\console\Output;
    use dbman\Dbman as BaseDb;
    class Maintain extends Command
    {
        protected function configure()
        {
            $this->setName('Dbman:maintain')->setDescription('Update the database');
        }
    
        protected function execute(Input $input, Output $output)
        {
            $sysObj = new BaseDb();
            $sysObj->maintain();
            $output->writeln("update complete!");
        }
    }
```

2.tp5/application/admin/command/dbman/Init.php

```php
    <?php
    namespace app\admin\command\dbman;
    
    use think\console\Command;
    use think\console\Input;
    use think\console\Output;
    use dbman\Dbman as BaseDb;
    class Init extends Command
    {
        protected function configure()
        {
            $this->setName('Dbman:init')->setDescription('The system is initialized');
        }
    
        protected function execute(Input $input, Output $output)
        {
            $sysObj = new BaseDb();
            $sysObj->init();
            $output->writeln("The initial complete!");
        }
    }
```
执行命令：**php think**

![][10]

可以看到，我们已经在TP5中完美的融入了Dbman


[1]: ./img/5983766-92305d91dc9c2bd1.png
[2]: ./img/5983766-8e82696b55c5b461.png
[3]: ./img/5983766-d85478186d6b7df1.png
[4]: ./img/5983766-1e84c38a4ced376d.png
[5]: ./img/5983766-8b3a6f3cc72027f5.png
[6]: ./img/5983766-d39e12a14b72a691.png
[7]: ./img/5983766-18d8799e491800be.png
[8]: ./img/5983766-dff3db1f51ad3d38.png
[9]: ./img/5983766-19c87de80847e2b3.png
[10]: ./img/5983766-52d775accbcbfd0d.png
[11]: ./img/5983766-a776d85cf9f3fc50.png
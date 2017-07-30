# [PHPUnit单元测试YAF模型层][0]

## January 18, 2014

在Yaf应用中，创建一个模型层UserModel：

```php
    Class UserModel {
        // 初始化连接数据库，pre_common_member只有三条数据ID:1,2,3
        public function __construct() {
            $this->_db = Core_LinkMySQL::get('hiapk_x2');
        }
    
        // 通过uid获取用户名，数据不存在时返回FALSE
        public function fetchUsernameById($id) {
            return $this->_db->select('username')
                ->from('pre_common_member')
                ->where('uid', $id)
                ->fetchValue();
        }
    
        // 通过uid获取用户行数据，数据不存在时返回FALSE
        public function fetchRowById($id) {
            return $this->_db->select('username', 'groupid', 'adminid', 'regdate')
                ->from('pre_common_member')
                ->where('uid', $id)
                ->fetchOne();
        }
    }
```

创建一个测试用例用于测试该模型层，需要几个条件：

1. 必须实例化`Yaf_Application Final`类；
1. 同时载入我们的`Application`必备的配置文件；
1. 官方文档有注明，`Yaf_Application`代表的是一个产品/项目，必须保证单例。

基于以上3点，模型层的测试用例就很好编写了，创建一个Test Case：UserModelTest.php 该文件仅用于测试UserModel的业务。

```php
    define('APP_PATH', dirname(__FILE__) . '/../../');
    define('APP_ENV', 'loc');
    error_reporting(E_ERROR | E_PARSE);
    
    Class UserModelTest extends PHPUnit_Framework_TestCase {
    
        // 用于保存模型的单例
        private static $__model = NULL;
    
        // 初始化实例化YAF应用，PHPUnit每次test函数都会实例化对象一次，
        // YAF application保证单例
        public function __construct() {
            if ( ! Yaf_Registry::get('Application') ) {
                $application = new Yaf_Application(APP_PATH."/config/application.ini", APP_ENV);
                Yaf_Application::set('Application', $application);
            }
    
            if ( ! self::$__model )
                self::$__model = new UserModel();
        }
    
        // 测试 fetchRowById
        public function testFetchRowById() {
            $uid = 3;
            $row = self::$__model->fetchRowById($uid);
            $this->assertInternalType('array', $row);
            $this->assertStringMatchesFormat('%s', $row['username']);
            $this->assertStringMatchesFormat('%i', $row['groupid']);
            $this->assertStringMatchesFormat('%i', $row['adminid']);
            $this->assertStringMatchesFormat('%i', $row['regdate']);
        }
    
        // 测试 fetchUsernameById，由于我的数据表中只有3条数据，查询结果如果不存在是返回FALSE
        public function testFetchUsernameById() {
            $uid  = 4;
            $name = self::$__model->fetchUsernameById($uid);
            $this->assertInternalType('string', $name, $uid . ':' . gettype($name) );
        }
    }
```

测试结果：第二个Function由于查不到指定数据返回FALSE测试类型失败。

![TestYafModel][1]

[0]: http://www.crackedzone.com/phpunit-yaf-model.html
[1]: ../img/TestYafModel.jpg
## 写Laravel测试代码(二)

来源：[https://segmentfault.com/a/1190000010417990](https://segmentfault.com/a/1190000010417990)

本文主要探讨数据库测试。

在[写Laravel测试代码(一)][3] 中聊了关于如何提高 laravel 数据库测试性能，其实简单一句就是：`每一个test case, 只重新 seed 被污染的表。`OK，这里有一个前提问题：那如何构建临时测试数据库呢？本文主要探讨如何构建临时测试数据库。
## 数据库设计图纸

任何一个软件都需要数据库设计图纸，可以使用免费的`MySqlWorkbench`或者收费的`Navicat Data Modler`软件。这里使用免费的`MySqlWorkbench`来设计数据库图纸，类似下图：

![][0]

这里作为范例简单设计了5个model，当然大型程序都会有100个以上model。再利用软件的`Export SQL`功能导出数据库的`schema`，这个`schema`文件就作为构建临时测试数据库的原料，`schema`文件类似如下：

![][1]
## 临时数据库构建类

在得到`schema`文件后，就可以写一个临时数据库构建类来创建临时测试数据库。这里`临时`表示该测试数据库使用完后即`drop`掉，且数据库名字是随机的，这样可以保证同时`并发`进行测试。需要先在`phpunit.xml`中指定数据库配置信息：

```xml
...
<env name="APP_ENV" value="testing"/>
        <env name="CACHE_DRIVER" value="array"/>
        <env name="SESSION_DRIVER" value="array"/>
        <env name="QUEUE_DRIVER" value="sync"/>
        <env name="DB_DATABASE" value="lx1036"/>
        <env name="DB_USERNAME" value="testing"/>
        <env name="DB_PASSWORD" value="testing"/>
    </php>
</phpunit>
```

然后在`config/database.php`中写上当运行测试时指定新构建的测试数据库：

```php
'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('APP_ENV') === 'testing' ? \Tests\Database::getRandomDBName(env('DB_DATABASE', 'lx1036'), env('DB_HOST', 'localhost'), env('DB_USERNAME', 'root'), env('DB_PASSWORD')) : env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],
```

然后写一个临时测试数据库构建类：

```php
<?php

namespace Tests;

use PDO;

/**
 * Singleton class to enable parallel PHPUnit processes
 *
 * 1) Generate a random testing database with automatic destroy upon finish
 * 2) Initialize the database schemas using SQL file specified by constant SQL_PATH
 * 3) Remove orphan test databases
 */
class Database
{
    /** @var  \Tests\Database singleton to drop test database in destructor */
    protected static $instance;

    /** @var string */
    protected static $db_name;

    /** @var string */
    protected static $host;

    /** @var string */
    protected static $username;

    /** @var string */
    protected static $password;

    public function __construct(string $db_name)
    {
        static::$db_name = $db_name;
    }

    public function __destruct()
    {
        if (static::$db_name) {
            $pdo = new PDO('mysql:host=' . static::$host . ';' . 'dbname=' . static::$db_name, static::$username, static::$password);
            $pdo->exec('DROP DATABASE `' . static::$db_name . '`');
        }
    }

    public static function getRandomDBName(string $prefix, string $host, string $username, string $password, string $charset = 'utf8mb4', string $collation = 'utf8mb4_unicode_ci'): string
    {
        if (static::$instance) {
            return static::$instance->getDBName();
        }

        $db_name = $prefix . '_' . date('ymd') . '_' . str_random();

        $pdo = new PDO('mysql:host=' . $host, $username, $password);

        // Remove orphan database
        static::removeOrphans($pdo, $prefix);

        // Create random database
        $pdo->exec('CREATE DATABASE `' . $db_name . '` DEFAULT CHARACTER SET ' . $charset . ' COLLATE ' . $collation);
        $pdo->exec('USE `' . $db_name . '`');

        // Create tables in specified random database
        $schema_file = __DIR__ . '/../database/seeds/mysql.sql';

        if ($pdo->exec(file_get_contents($schema_file)) === false) {
            throw new \ErrorException("Cannot create tables by sql file: " . $schema_file . ' because of ' . $pdo->errorInfo()[2]);
        }

        /*
        // Check if tables are inserted.
        $result = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_NUM);
        dump($result);*/

        static::$instance = new static($db_name);
        static::$host     = $host;
        static::$username = $username;
        static::$password = $password;

        dump($db_name);
        return $db_name;
    }

    /**
     * Remove orphan database if exists.
     *
     * @param PDO $pdo
     * @param string $prefix
     */
    public static function removeOrphans(PDO $pdo, string $prefix)
    {
        $databases = $pdo->query('SHOW DATABASES LIKE "' . $prefix . '%"')->fetchAll();

        foreach ($databases as $database) {
            $database = reset($database);

            if (starts_with($database, $prefix) && is_numeric(explode('_', $database)[1])) {
                $pdo->exec('DROP DATABASE `' . $database . '`');

                echo 'Drop database ' . $database . PHP_EOL;
            }
        }
    }

    /**
     * @return string
     */
    public static function getDBName(): string
    {
        return static::$db_name;
    }

    /**
     * @return string
     */
    public static function getHost(): string
    {
        return static::$host;
    }

    /**
     * @return string
     */
    public static function getUsername(): string
    {
        return static::$username;
    }

    /**
     * @return string
     */
    public static function getPassword(): string
    {
        return static::$password;
    }
}
```

这样，当运行测试时连接的就是临时构建的测试数据库，测试运行完毕就drop掉数据库，并且可以同时开多个窗口(线程)来分组运行`test cases`。最后还得在`mysql localhost`中创建`testing@testing`用户并授权，以`root`用户登录`local mysql`：

```sql
CREATE USER 'testing'@'localhost' IDENTIFIED BY 'testing';
GRANT ALL ON `lx1036%`.* TO 'testing'@'localhost';
```

这样就临时测试数据库就准备完毕了，然后就是`seed 测试数据，执行unit/feature tests， 执行assert等等`，可以参考[写Laravel测试代码(一)][3]。这里运行`phpunit`时得到的临时测试数据库是：

![][2]

OK，后续再聊执行`unit/feature tests`时一些实践技巧。

[RightCapital][5]招聘[Laravel DevOps][6]

[3]: https://segmentfault.com/a/1190000009893350
[4]: https://segmentfault.com/a/1190000009893350
[5]: https://www.rightcapital.com
[6]: https://join.rightcapital.com
[0]: ./img/img/bVRNMa.png
[1]: ./img/img/bVRNNh.png
[2]: ./img/img/bVRSkT.png
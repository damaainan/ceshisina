## 写Laravel测试代码(一)

来源：[https://segmentfault.com/a/1190000009893350](https://segmentfault.com/a/1190000009893350)

本文主要探讨写数据库测试。

写laravel程序时，除了写生产代码，还需要写测试代码。其中，写数据库测试比较麻烦，因为需要针对每一个`test case`需要建立好数据集，该次`test case`污染的数据表还需要恢复现场，避免影响下一个`test case`运行，同时还得保证性能问题，否则随着程序不断膨胀，测试数量也越多，那每一次测试运行需要花费大量时间。

有两个比较好的方法可以提高数据库测试性能：

* 对大量的`tests`按照功能分组。如有1000个tests，可以按照业务功能分组，如`group1:1-200, group2:201-800, group3: 801-1000`。这样可以`并发运行`每组测试包裹。

* 只恢复每个`test case`污染的表，而不需要把所有的数据表重新恢复，否则表数量越多测试代码执行越慢。


这里聊下方法2的具体做法。

假设程序有50张表，每次运行测试时首先需要为每组构建好独立的对应数据库，然后创建数据表，最后就是填充测试数据(`fixtures`)。`fixtures`可用`yml`格式定义，既直观也方便维护，如：

```yaml
#simple.yml
accounts:
  - id: 1
    person_id: 2
    type: investment
    is_included: true
  - id: 2
    person_id: 2
    type: investment
    is_included: true
transactions:
  - account_id: 1
    posted_date: '2017-01-01'
    amount: 10000
    transaction_category_id: 1   
  - account_id: 2
    posted_date: '2017-01-02'
    amount: 10001
    transaction_category_id: 2
```

然后需要写个`yamlSeeder class`来把数据集填充到临时数据库里:

```php
abstract class YamlSeeder extends \Illuminate\Database\Seeder
{
    private $files;

    public function __construct(array $files)
    {
        $this->files = $files
    }
    
    public function run(array $tables = []): void
    {
        // Close unique and foreign key constraint
        $db = $this->container['db'];
        $db->statement('SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;');
        $db->statement('SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;');
        
        foreach($this->files as $file) {
            ...
            
            // Convert yaml data to array
            $fixtures = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($file));
            
            ...
            
            foreach($fixtures as $table => $data) {
                // Only seed specified tables, it is important!!!
                if ($tables && !in_array($table, $tables, true)) {
                    continue;
                }
                
                $db->table($table)->truncate();

                if (!$db->table($table)->insert($data)) {
                    throw new \RuntimeException('xxx');
                }
            }
            
            ...
        }
        
        // Open unique and foreign key constraint
        $db->statement('SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;');
        $db->statement('SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;');
    }
}

class SimpleYamlSeeder extends YamlSeeder
{
    public function __construct()
    {
        parent::__construct([database.path('seeds/simple.yml')]);
    }
}
```

上面的代码有一个关键处是参数`$tables`：如果参数是空数组，就把所有数据表数据插入随机数据库里；如果是指定的数据表，只重刷指定的数据表。这样会很大提高数据库测试的性能，因为可以在每一个test case里只需要指定本次测试所污染的数据表。在`tests/TestCase.php`中可以在`setUp()`设置数据库重装操作：

```php
    abstract class TestCase extends \Illuminate\Foundation\Testing\TestCase
    {
        protected static $tablesToReseed = [];
        
        public function seed($class = 'DatabaseSeeder', array $tables = []): void
        {
            $this->artisan('db:seed', ['--class' => $class, '--tables' => implode(',', $tables)]);
        }
        
        protected function reseed(): void
        {
            // TEST_SEEDERS is defined in phpunit.xml, e.g. <env name="TEST_SEEDERS" value="\SimpleYamlSeeder"/>
            $seeders = env('TEST_SEEDERS') ? explode(',', env('TEST_SEEDERS')) : [];
            
            if ($seeders && is_array(static::$tablesToReseed)) {
                foreach ($seeders as $seeder) {
                    $this->seed($seeder, static::$tablesToReseed);
                }
            }
            
            \Cache::flush();
            
            static::$tablesToReseed = false;
        }
        
        protected static function reseedInNextTest(array $tables = []): void
        {
            static::$tablesToReseed = $tables;
        }
    }
```

这样就可以在每一个`test case`中定义本次污染的数据表，保证下一个`test case`在运行前重刷下被污染的数据表，如:

```php
    final class AccountControllerTest extends TestCase
    {
        ...
        
        public function testUpdateAccount()
        {
            static::reseedInNextTest([Account::class, Transaction::class]);
            
            ...
        }
        
    }

```

这样会极大提高数据库测试效率，不推荐使用Laravel给出的`\Illuminate\Foundation\Testing\DatabaseMigrations 和 \Illuminate\Foundation\Testing\DatabaseTransactions`，效率并不高。

laravel的`db:seed`命令没有`--tables`这个`options`，所以需要扩展`\Illuminate\Database\Console\Seeds\SeedCommand`:

```php
class SeedCommand extends \Illuminate\Database\Console\Seeds\SeedCommand
{
    public function fire()
    {
        if (!$this->confirmToProceed()) {
            return;
        }

        $this->resolver->setDefaultConnection($this->getDatabase());

        Model::unguarded(function () {
            $this->getSeeder()->run($this->getTables());
        });
    }
    
    protected function getTables()
    {
        $tables = $this->input->getOption('tables');

        return $tables ? explode(',', $tables) : [];
    }

    protected function getOptions()
    {
        $options   = parent::getOptions();
        $options[] = ['tables', null, InputOption::VALUE_OPTIONAL, 'A comma-separated list of tables to seed, all if left empty'];

        return $options;
    }
}
```

当然还得写`SeedServiceProvider()来覆盖原有的Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::registerSeedCommand()中注册的command.seed`，然后在`config/app.php`中注册：

```php
class SeedServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * @see \Illuminate\Database\SeedServiceProvider::registerSeedCommand()
     */
    public function register()
    {
        $this->app->singleton('command.seed', function ($app) {
            return new SeedCommand($app['db']);
        });

        $this->commands('command.seed');
    }

    public function provides()
    {
        return ['command.seed'];
    }
}
```

OK,这样所有的工作都做完了。。以后写数据库测试性能会提高很多，大量的`test case`可以在短时间内运行完毕。

最后，`写测试代码`是必须的，好处非常多，随着项目程序越来越大，就会深深感觉到写测试是必须的，一劳永逸，值得花时间投资。也是作为一名软件工程师的必备要求。

[RightCapital][0]招聘[Laravel DevOps][1]

[0]: https://www.rightcapital.com
[1]: https://join.rightcapital.com
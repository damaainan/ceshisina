# Laravel源码解读系列第三篇-Response 

Published on Jul 4, 2017 in [Laravel][0][PHP][1] with [0 comment][2]

## 前言

* [Laravel源码解读系列第一篇-初始化][3]
* [Laravel源码解读系列第二篇-Request][4]

- - -

## Response

### 生成Response

前面我们已经讲了从初始化到一个Request请求的生成过程，接下来一篇我们继续解读Response的过程，Response相对于前面两篇要复杂一些，其核心内容主要是几个bootstrap的初始化:

    // Http/Kernel.php
    public function handle($request)
        {
            try {
    //            设置请求参数可以覆盖
                $request->enableHttpMethodParameterOverride();
    
                $response = $this->sendRequestThroughRouter($request);
            } catch (Exception $e) {
                $this->reportException($e);
    
                $response = $this->renderException($request, $e);
            } catch (Throwable $e) {
                $this->reportException($e = new FatalThrowableError($e));
    
                $response = $this->renderException($request, $e);
            }
    
            event(new Events\RequestHandled($request, $response));
    
            return $response;
        }
    
    protected function sendRequestThroughRouter($request)
        {
    //        这里把$request注入到$this->app的instances中
            $this->app->instance('request', $request);
    
            Facade::clearResolvedInstance('request');
    
    //        这个方法主要是用来给$app注入一些其他的元素,把$bootstrappers数组的内容一一遍历，并调用这个类的bootstrap来完成一些载入初始化，实现服务提供者，设置别名，配置等工作。
            $this->bootstrap();
    
            //        shouldSkipMiddleware用来判断middle.disable是否有绑定过，或者可以实例化
            return (new Pipeline($this->app))
                        ->send($request)
                        ->through($this->app->shouldSkipMiddleware() ? [] : $this->middleware)
                        ->then($this->dispatchToRouter());
        }
    
    public function bootstrap()
        {
            if (! $this->app->hasBeenBootstrapped()) {
                $this->app->bootstrapWith($this->bootstrappers());
            }
        }

    // Foundation/Application.php
    public function hasBeenBootstrapped()
        {
            return $this->hasBeenBootstrapped;
        }
    
    public function bootstrapWith(array $bootstrappers)
        {
            $this->hasBeenBootstrapped = true;
    
            foreach ($bootstrappers as $bootstrapper) {
    //            $this['events']是用到了arrayacces方法，当使用$this['events']时，会调用container的offsetget方法，而这个offsetget方法，最终会调用一个make方法，来帮我们执行之前给events的闭包，返回一个对象，不过这个对象返回的是一个dispatcher对象
                $this['events']->fire('bootstrapping: '.$bootstrapper, [$this]);
    
                $this->make($bootstrapper)->bootstrap($this);
                $this['events']->fire('bootstrapped: '.$bootstrapper, [$this]);
            }
        }

我们可以看到，其实这段代码的核心代码也就只有一行

    $this->make($bootstrapper)->bootstrap($this);

我们会遍历我们提供的bootstrappers列表，然后一一遍历通过$app->make来生成一个对象，接着就调用这个对象的bootstrap方法来做一些工作，至于怎么通过make生成的过程这里就不再赘述，前面[第一篇初始化][3]中有详细的介绍。  
bootstrappers列表:

        protected $bootstrappers = [
    //        引入env配置文件
            \Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
    //引入config文件 给instances注入了一个键为config，值为repository的实例
            \Illuminate\Foundation\Bootstrap\LoadConfiguration::class,
    //主要涉及开发的异常，报错
            \Illuminate\Foundation\Bootstrap\HandleExceptions::class,
    //这里会设置自动加载，使用别名时，自动加载别名所对应的类文件
            \Illuminate\Foundation\Bootstrap\RegisterFacades::class,
    //对服务提供者进行初始化，以及缓存到bootstrap/cache/services.php中
            \Illuminate\Foundation\Bootstrap\RegisterProviders::class,
    //执行每一个服务提供者
            \Illuminate\Foundation\Bootstrap\BootProviders::class,
        ];

#### bootstrappers列表解读

正如前面所介绍的，我们每次弹出一个bootstrapper出来，都会执行这个类的bootstrap方法，所以接下来的几项我们直接来看各个的bootstrap方法在做一些什么。

##### 引入env(LoadEnvironmentVariables)

我们经常会使用到Laravel的`.env`文件的内容，使用env去获取我们的配置，这个功能就是在这里实现的。

    public function bootstrap(Application $app)
        {
    //        用来判断根目录下的bootstrap/cache/config.php文件是否存在
            if ($app->configurationIsCached()) {
                return;
            }
    
            $this->checkForSpecificEnvironmentFile($app);
    
            try {
    //            $app->enviromentPath()用来获取我们的环境路径，我们之前是有设置一个basePath，这里会根据是否有设置来判断
    //            $app->enviromentFile()是用来获取根目录下面的.env文件
                (new Dotenv($app->environmentPath(), $app->environmentFile()))->load();
            } catch (InvalidPathException $e) {
                //
            }
        }
    protected function checkForSpecificEnvironmentFile($app)
        {
    //        判断php的执行环境 cli是命令行，cli-server是浏览器请求
            if (php_sapi_name() == 'cli' && with($input = new ArgvInput)->hasParameterOption('--env')) {
                $this->setEnvironmentFilePath(
                    $app, $app->environmentFile().'.'.$input->getParameterOption('--env')
                );
            }
    
            //        调用env全局函数查看.env文件是否有配置APP_ENV参数
            if (! env('APP_ENV')) {
                return;
            }
    
            $this->setEnvironmentFilePath(
                $app, $app->environmentFile().'.'.env('APP_ENV')
            );
        }
    protected function setEnvironmentFilePath($app, $file)
        {
            if (file_exists($app->environmentPath().'/'.$file)) {
                $app->loadEnvironmentFrom($file);
            }
        }

首先`configurationIsCached`方法会帮我们判断是否有缓存`cache/config.php`文件，接着我们会执行`checkForSpecificEnvironmentFile`方法，在这个方法中我们需要注意是`env('APP_ENV')`，因为我们通过

    php artisan serve

和直接

    php -S localhost:8889 -t your/path

启动的效果是不一样的，当我们使用`php artisan serve`时，其实我们的`.env`已经载入(这里后面会研究Laravel的artisan)，所以这里能获取到APP_ENV的值，但是下面的启动方式是无法获取这个值得，所以如果以下面这种启动方式来启动，其实这里就会直接返回了。

在这里有使用了一个比较有意思的方法:

    with($input = new ArgvInput)
    
    function with($object)
        {
            return $object;
        }

非常简单的一个方法，这里为什么要多此一举呢？实际上这里解决了一个非常有意思的痛点，如果我们要调用这个对象的一个方法，那么我们要先实例化，然后再赋值给一个变量，然后通过这个变量去调用他的方法来判断，即:

    $input = new ArgvInput();
    if($input->hasParameterOption('--env')){
    ...
    }

而这个with直接返回了我们生成的实例，直接调用其方法，非常方便。

咱们继续，后面会执行一个`setEnvironmentFilePath`方法:

    protected function setEnvironmentFilePath($app, $file)
        {
            if (file_exists($app->environmentPath().'/'.$file)) {
                $app->loadEnvironmentFrom($file);
            }
        }

这个方法主要是用来判断你的环境参数的文件是否存在，如果存在就使用这个，例如`.env.local`，如果你通过`artisan serve`来启动，并且设置了`.env.local`方法，那么这里就会使用这个文件的配置参数。

我们再回到我们的bootstrap方法中:

    public function bootstrap(Application $app)
        {
    //        用来判断根目录下的bootstrap/cache/config.php文件是否存在
            if ($app->configurationIsCached()) {
                return;
            }
    
            $this->checkForSpecificEnvironmentFile($app);
    
            try {
    //            $app->enviromentPath()用来获取我们的环境路径，我们之前是有设置一个basePath，这里会根据是否有设置来判断
    //            $app->enviromentFile()是用来获取根目录下面的.env文件
                (new Dotenv($app->environmentPath(), $app->environmentFile()))->load();
            } catch (InvalidPathException $e) {
                //
            }
        }

接下来会执行`LoadEnvironmentVariables`中最为核心的代码，也就是:

    (new Dotenv($app->environmentPath(), $app->environmentFile()))->load();

    public function environmentPath()
        {
    //        这里是一个三目运算符的简写，如果前者为true，则返回前者，反之，后者。
            return $this->environmentPath ?: $this->basePath;
        }
    
    public function environmentFile()
        {
            return $this->environmentFile ?: '.env';
        }

如果没有单独设置`$app->environmentPath`，那么就返回我们最开始设置的basePath，即整个项目的绝对路径。environmentFile同理，就像上面说的，如果我们设置了`.env.local`文件，并且通过artisan启动，这里就会载入`.env.local`文件了。

    //Doenv.php
    public function __construct($path, $file = '.env')
        {
            $this->filePath = $this->getFilePath($path, $file);
            $this->loader = new Loader($this->filePath, true);
        }
    
    protected function getFilePath($path, $file)
        {
            if (!is_string($file)) {
                $file = '.env';
            }
    
            $filePath = rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$file;
    
            return $filePath;
        }
    
    public function load()
        {
            return $this->loadData();
        }
    
    protected function loadData($overload = false)
        {
            $this->loader = new Loader($this->filePath, !$overload);
    
            return $this->loader->load();
        }
    
    //Loader.php
    
    public function __construct($filePath, $immutable = false)
        {
            $this->filePath = $filePath;
            $this->immutable = $immutable;
        }
    
    public function load()
        {
    //        确保文件存在，并且是可读的
            $this->ensureFileIsReadable();
    
            $filePath = $this->filePath;
    //        调用file函数读取文本，放入一个数组中
            $lines = $this->readLinesFromFile($filePath);
            foreach ($lines as $line) {
    //            $this->isComment用来判断是否有用#注释，$this->looksLikeSetter用来判断是否有"="
                if (!$this->isComment($line) && $this->looksLikeSetter($line)) {
    //                把配置放入全局变量，以及$_ENV和$_SERVER中
                    $this->setEnvironmentVariable($line);
                }
            }
    
            return $lines;
        }
    
    protected function ensureFileIsReadable()
        {
            if (!is_readable($this->filePath) || !is_file($this->filePath)) {
                throw new InvalidPathException(sprintf('Unable to read the environment file at %s.', $this->filePath));
            }
        }
    
    protected function readLinesFromFile($filePath)
        {
            // Read file into an array of lines with auto-detected line endings
            $autodetect = ini_get('auto_detect_line_endings');
            ini_set('auto_detect_line_endings', '1');
            $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            ini_set('auto_detect_line_endings', $autodetect);
    
            return $lines;
        }
    
    protected function isComment($line)
        {
            return strpos(ltrim($line), '#') === 0;
        }
    
    protected function looksLikeSetter($line)
        {
            return strpos($line, '=') !== false;
        }
    
    public function setEnvironmentVariable($name, $value = null)
        {
            list($name, $value) = $this->normaliseEnvironmentVariable($name, $value);
    
            // Don't overwrite existing environment variables if we're immutable
            // Ruby's dotenv does this with `ENV[key] ||= value`.
    //        如果已经存在了，就不能覆盖
            if ($this->immutable && $this->getEnvironmentVariable($name) !== null) {
                return;
            }
    
            // If PHP is running as an Apache module and an existing
            // Apache environment variable exists, overwrite it
            if (function_exists('apache_getenv') && function_exists('apache_setenv') && apache_getenv($name)) {
                apache_setenv($name, $value);
            }
    
            if (function_exists('putenv')) {
                putenv("$name=$value");
            }
    
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    protected function normaliseEnvironmentVariable($name, $value)
        {
    //        用=切割成一个数组，并去掉左右空格
            list($name, $value) = $this->splitCompoundStringIntoParts($name, $value);
    //      去掉export ' "这三个
            list($name, $value) = $this->sanitiseVariableName($name, $value);
    //      去掉一些不合法的写法
            list($name, $value) = $this->sanitiseVariableValue($name, $value);
    
    //        匹配是否存在${([a-zA-Z0-9_]+)}这样的值，如果存在，就把子模式的值用来在$_ENV,$_SERVER,getenv中查询，
    //        如果能查到就返回子模式，如果不能查到就返回全部匹配到的值
            $value = $this->resolveNestedVariables($value);
    
            return array($name, $value);
        }
    public function setEnvironmentVariable($name, $value = null)
        {
    //        分解内容
            list($name, $value) = $this->normaliseEnvironmentVariable($name, $value);
    
            // Don't overwrite existing environment variables if we're immutable
            // Ruby's dotenv does this with `ENV[key] ||= value`.
    //        如果已经存在了，就不能覆盖
            if ($this->immutable && $this->getEnvironmentVariable($name) !== null) {
                return;
            }
    
            // If PHP is running as an Apache module and an existing
            // Apache environment variable exists, overwrite it
            if (function_exists('apache_getenv') && function_exists('apache_setenv') && apache_getenv($name)) {
                apache_setenv($name, $value);
            }
    
            if (function_exists('putenv')) {
                putenv("$name=$value");
            }
    
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }

这里的方法虽然很多，但是其实都很简单，总的来说，就是通过file函数来分解我们的`.env`文件来获取配置，然后将配置逐行通过`=`来分解，最后作为键值对来分别设置到`$_ENV`,`$_SERVER`以及`putenv`中。

##### 引入config(LoadConfiguration)

这里主要是帮我们引入config下面的所有的文件，不过Laravel的实现过程非常的'曲折'，使用到了大量的PHP的预定义接口，在讲这个之前必须要先讲两个小的知识点:

1. 聚合式迭代器  
这个其实我在之前的[PHP七大预定义接口][5]中有提过，当一个类实现了`IteratorAggregate`这个接口时，与此同时必须要实现他的`getIterator`方法，可以帮助我们对对象进行迭代，这里不再赘述。
1. `SplFileInfo&RecursiveDirectoryIterator`  
`SplFileInfo和RecursiveDirectoryIterator`都是PHP提供的文件处理的类，这里分别使用到了他们的方法:

* `RecursiveDirectoryIterator::getFilename()`:一层层遍历目录下面的所有文件
* `SplFileInfo::getRealPath()`:获取文件的绝对路径  
`RecursiveDirectoryIterator`是`SplFileInfo`的前提，在实例化的过程中传入一个存在的文件路径(相对路径和绝对路径都行)，那么最后可以调用他的`getRealPath()`方法来获取这个文件所在的绝对路径。

举个例子好了:

    $dir = new DirectoryIterator(__DIR__ . '/config');
    
        foreach ($dir as $file) {
            $fileName = $file->getFilename();
            echo $fileName . PHP_EOL;
            $spl = new SplFileInfo(getcwd() . '/config/' . $fileName);
            echo $spl->getRealPath() . PHP_EOL;
        }

目录结构:

![9501340f-0b1b-404a-963b-192a45fd29d4.png][6]

其结果:

![f3740577-a642-4f9f-bd48-24f2efe44366.png][7]

其实这里Laravel使用的大致流程也是如此:

    //LoadConfiguration.php
    public function bootstrap(Application $app)
        {
            $items = [];
    
    //        查看bootstrap/cache/config.php文件是否存在，如果存在，就引入，并设置已经加载
            if (file_exists($cached = $app->getCachedConfigPath())) {
                $items = require $cached;
    
                $loadedFromCache = true;
            }
    
    //        把Repository的实例放入instances的config中，这里后面会用到
            $app->instance('config', $config = new Repository($items));
    
    //
            if (! isset($loadedFromCache)) {
    //            设置配置信息
                $this->loadConfigurationFiles($app, $config);
            }
    
    //       判断$this->items['app']['env']是否设置，如果没有设置就设置成production
            $app->detectEnvironment(function () use ($config) {
                return $config->get('app.env', 'production');
            });
    
    //        根据配置设置时区
            date_default_timezone_set($config->get('app.timezone', 'UTC'));
    
            mb_internal_encoding('UTF-8');
        }
    
    protected function getConfigurationFiles(Application $app)
        {
            $files = [];
    //返回config的绝对路径
            $configPath = realpath($app->configPath());
    
    //        这里的files，name ， 以及in都使用了链式调用，所以通过Finder::create()->files()->name('*.php')->in($configPath)返回的是一个Finder的实例，但是这个对象实现了IteratorAggregate接口，所以遍历的时候会调用getIterator返回了一个SqlFileInfo对象
    
            foreach (Finder::create()->files()->name('*.php')->in($configPath) as $file) {
                $directory = $this->getNestedDirectory($file, $configPath);
    
                $files[$directory.basename($file->getRealPath(), '.php')] = $file->getRealPath();
            }
    
            return $files;
        }

其核心代码也就是

    foreach (Finder::create()->files()->name('*.php')->in($configPath) as $file) {
                $directory = $this->getNestedDirectory($file, $configPath);
    
                $files[$directory.basename($file->getRealPath(), '.php')] = $file->getRealPath();
            }

这里我们会遍历Finder这个类，而这个类实现了`IteratorAggregate`，所以当我们foreach时，其实会调用他的getIterator方法:

    //Finder.php
    public function getIterator()
        {
    
            if (0 === count($this->dirs) && 0 === count($this->iterators)) {
                throw new \LogicException('You must call one of in() or append() methods before iterating over a Finder.');
            }
    //因为此时的长度是1，所以走了这个判断
            if (1 === count($this->dirs) && 0 === count($this->iterators)) {
                return $this->searchInDirectory($this->dirs[0]);
            }
    
            $iterator = new \AppendIterator();
            foreach ($this->dirs as $dir) {
                $iterator->append($this->searchInDirectory($dir));
            }
    
            foreach ($this->iterators as $it) {
                $iterator->append($it);
            }
            return $iterator;
        }
    
    private function searchInDirectory($dir)
        {
    ...
            $iterator = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST);
    ...
            return $iterator;
        }

当我们调用getIterator时，最后会返回一个`RecursiveIteratorIterator`的实例，而这个实例有一个关键方法current来帮助我们实现迭代:

    //RecursiveIteratorIterator.php
    public function current()
        {
            // the logic here avoids redoing the same work in all iterations
    
            if (null === $subPathname = $this->subPath) {
                $subPathname = $this->subPath = (string) $this->getSubPath();
            }
    
            if ('' !== $subPathname) {
                $subPathname .= $this->directorySeparator;
            }
    //        一层层的获取config下面的文件
            $subPathname .= $this->getFilename();
            return new SplFileInfo($this->rootPath.$this->directorySeparator.$subPathname, $this->subPath, $subPathname);
        }

这个对象的getFilename方法其实前面也有所阐述，所以我们前面遍历Finder对象时，其实每个`$file`都是一个`SplFileInfo`的实例，最后这个实例使用了前面我们所提到的getRealPath方法来返回绝对路径。

所以此时返回的`$files`数组是一个以文件名为键，文件名所在的绝对路径为值的数组:

![0437e81b-ce2f-4957-8452-e860f3a85a98.png][8]

接下来的逻辑就比较简单了，通过Repository的set方法来把这个`$files`数组存放在`$this->items`，最后就是判断是否设置生成环境了。

##### 设置异常(HandleExceptions)

这里主要是对一些异常的设置，非常容易理解，这里就不多作赘述。

    public function bootstrap(Application $app)
        {
            $this->app = $app;
    
    //        报告所有的错误
            error_reporting(-1);
            //        设置报错的回调函数
            set_error_handler([$this, 'handleError']);
    
    //        设置抛出异常的回调函数
            set_exception_handler([$this, 'handleException']);
    
    //        程序运行结束时的自动操作
            register_shutdown_function([$this, 'handleShutdown']);
    
    //        如果环境不是testing就关闭错误信息显示
            if (! $app->environment('testing')) {
                ini_set('display_errors', 'Off');
            }
        }

##### 注册别名(RegisterFacades)

别名，也就是我们说到的Facade，他的主要作用是能让我们使用到诸如`Route::get('/')`之类的静态方法，其实现过程非常有意思。

    public function bootstrap(Application $app)
        {
    //        清空resolvedInstances数组
            Facade::clearResolvedInstances();
    
    //        配置app
            Facade::setFacadeApplication($app);
    
    //        首先通过$app->make('config')来获取前面在instances中注入的config,即repository，然后调用对象的config,再调用get方法获取app.aliases数组
    //        getInstance会根据是否设置静态属性instances,然后返回一个AliasLoader的实例,在生成实例的时候，会把alias这个数组置于$this->instances中
            AliasLoader::getInstance($app->make('config')->get('app.aliases', []))->register();
        }

其核心代码在于:

    AliasLoader::getInstance($app->make('config')->get('app.aliases', []))->register();

    // LoadConfiguration.php
    public function bootstrap(Application $app)
        {
    //        把Repository的实例放入instances的config中，这里后面会用到
            $app->instance('config', $config = new Repository($items));
        }

前面其实我们已经在`LoadConfiguration`中我们有说过了，我们会给config绑定一个Repository的实例，所以此处会帮我们返回一个Repository的实例，而通过他的get方法会调用`Arr:get`方法，来帮我们返回我们在很早之前注入在`config/app.php`中的`aliases`这个数组。

接着会生成一个AliasLoader的单例，再调用这个单例的register方法。

    public function register()
        {
            if (! $this->registered) {
    //            设置一个自动加载类的方法，注入的是当前对象的load方法，然后把registered设置为true
    //            这里的自动加载非常有意思，当我们通过别名调用时，我们先调用class_alias来设置别名，同时又会重新自动加载一次，重新调用load方法，这时候，他会执行上面的loadFacade方法，而这个方法会用一个stub模板，来换成我们要载入的文件，然后返回这个类
                $this->prependToLoaderStack();
    
                $this->registered = true;
            }
        }
    
    protected function prependToLoaderStack()
        {
            spl_autoload_register([$this, 'load'], true, true);
        }
    
    public function load($alias)
        {
    //        第二次执行的时候，此时的$alias已经是我们上一次$this->aliases中的值所对应的classname
            if (static::$facadeNamespace && strpos($alias, static::$facadeNamespace) === 0) {
    //            这个方法会用一个模板来做替换，来返回一个类文件
                $this->loadFacade($alias);
    
                return true;
            }
    
    //        第一次我们会执行下面的内容,这时候会给他注册一个类的别名，class_alias的第三个属性(默认true)会重新调用自动加载
            if (isset($this->aliases[$alias])) {
                return class_alias($this->aliases[$alias], $alias);
            }
        }
    
    protected function loadFacade($alias)
        {
            require $this->ensureFacadeExists($alias);
        }
    
    protected function ensureFacadeExists($alias)
        {
    //        判断是否有做缓存
            if (file_exists($path = storage_path('framework/cache/facade-'.sha1($alias).'.php'))) {
                return $path;
            }
    
    //        formatFacadeStub会返回一个用模板替换的类
            file_put_contents($path, $this->formatFacadeStub(
                $alias, file_get_contents(__DIR__.'/stubs/facade.stub')
            ));
    
            return $path;
        }
    
    protected function formatFacadeStub($alias, $stub)
        {
    //        $replacements有三个参数，分别对应的是他的命名空间，类名以及唯一的方法中返回的名字
            $replacements = [
                str_replace('/', '\\', dirname(str_replace('\\', '/', $alias))),
    //            这个是helper的一个方法，用来返回文件的类名
                class_basename($alias),
                substr($alias, strlen(static::$facadeNamespace)),
            ];
    
            return str_replace(
                ['DummyNamespace', 'DummyClass', 'DummyTarget'], $replacements, $stub
            );
        }

当我们调用register方法时，他会帮我们注册一个对象的自动加载，如果我们使用其中的某个类的时候，他会自动的去执行这个load方法，而这个load方法有一个`class_alias`的方法，会帮我们自动注册并调用这个名字所对应的类，因此他会重新的去使用这个loadFacade方法，最后调用`ensureFacadeExists`方法。而这个`ensureFacadeExists`方法呢，会帮我们先判断是否有加载过模板，如果没有的话，则会通过`formatFacadeStub`方法来帮我们把`facade.stub`这个模板，并一一把其中的类名和返回的结果。

    //facade.stub
    <?php
    
    namespace DummyNamespace;
    
    use Illuminate\Support\Facades\Facade;
    
    /**
     * @see \DummyTarget
     */
    class DummyClass extends Facade
    {
        /**
         * Get the registered name of the component.
         *
         * @return string
         */
        protected static function getFacadeAccessor()
        {
            return 'DummyTarget';
        }
    }

##### 自定义服务提供者初始化(RegisterProviders)

我们会在`app/providers`下面添加一些我们自定义的服务提供者，同时也需要在`app.php`的providers数组中去声明这个服务提供者，但是这些服务提供者具体是怎么被调用的呢？`RegisterProviders`就是来做这个事情的。

    // RegisterProviders.php
    public function bootstrap(Application $app)
        {
            $app->registerConfiguredProviders();
        }

    //Application.php
    public function registerConfiguredProviders()
        {
    //        $this->config会返回repository 同样的，他也继承了ArrayAccess这个接口，直接调用get返回我们的服务提供者的数组
    //        $this->getCachedServicesPath()返回的是/bootstrap/cache/services.php
            (new ProviderRepository($this, new Filesystem, $this->getCachedServicesPath()))
                        ->load($this->config['app.providers']);
        }

    //ProviderRepository.php
    public function load(array $providers)
        {
    //        返回一个['when' => [...], 'providers'=>[...] , 'eager'=>[...] , 'deferred' => [...] ]数组 , $manifest是已经载入的/bootstrap/cache/services.php中的数组
            $manifest = $this->loadManifest();
    
    //        如果$manifest是null或者说$providers和$manifest['providers']不相同时执行
            if ($this->shouldRecompile($manifest, $providers)) {
    //            重新载入每一个providers，然后重新生成一个manifest
                $manifest = $this->compileManifest($providers);
            }
    
            foreach ($manifest['when'] as $provider => $events) {
    //            如果events数组长度超过2，则把events会注入到Dispathcher中的wildcards或者listeners中
                $this->registerLoadEvents($provider, $events);
            }
    
            foreach ($manifest['eager'] as $provider) {
    //            注册每一个服务提供者，这个方法之前也有阐述
                $this->app->register($provider);
            }
    
    //        合并已经deffer的服务提供者
            $this->app->addDeferredServices($manifest['deferred']);
        }

这个实现的过程也是非常的简单，首先通过`ProviderRepository`来生成实例，然后调用他的load方法，帮我们加载`app.php`下的providers数组，实现过程也和之前讲的`ArrayAccess`一致。当我们最终把providers都加载进来之后，我们会遍历这个数组，把他们一一通过`$app->register`来注册，这个注册方法就不再细说了，不过需要注意的是，我们注册的同时，其实我们会一一调用他的register和boot方法，这就好比我们做的钩子。

    //Application.php
    if (method_exists($provider, 'register')) {
                $provider->register();
            }
    
    //        绑定到$this->serverProvider中，同时在$this->loadedProvider设置这个类为true，表示已经加载
    
            $this->markAsRegistered($provider);
            if ($this->booted) {
                $this->bootProvider($provider);
            }

这里我们可以以官方提供的`RouteServiceProvider.php`和`EventServiceProvider.php`来讲解，这是我认为写得很不错的两个地方。

###### RouteServiceProvider

正如前面所介绍的，如果这个服务提供者有register和boot方法的话，那么会先执行register，然后再执行boot。

    //RouteServiceProvider.php
    public function boot()
        {
            $this->setRootControllerNamespace();
    
    //        判断文件目录缓存文件是否存在
            if ($this->app->routesAreCached()) {
                $this->loadCachedRoutes();
            } else {
    //            判断当前对象的map方法是否存在，如果存在则载入路由文件 载入了之后，其实就相当于执行了
                $this->loadRoutes();
    
                $this->app->booted(function () {
                    $this->app['router']->getRoutes()->refreshNameLookups();
                    $this->app['router']->getRoutes()->refreshActionLookups();
                });
            }
        }
    
    protected function loadRoutes()
        {
            if (method_exists($this, 'map')) {
                $this->app->call([$this, 'map']);
            }
        }

    // RouteServiceProvider.php
    public function map()
        {
    //        这里是通过Container的call方法执行的
    //        这两个方法主要是把两个路由文件中的内容载入
            $this->mapApiRoutes();
    
            $this->mapWebRoutes();
    
            //
        }
    
    protected function mapApiRoutes()
        {
    //        前面已经提过，Route继承自Facade，调用一个不存在的静态方法，会使用$app['router']的prefix来执行,
    //        而$app['router']则会返回我们在最开始注册的基础服务提供者中的Routing/Router.php的实例
    //        再加上prefix方法是受保护的，所以实例化的对象无法调用这个方法，会调用Router的__call方法
            Route::prefix('api')
    //            所以Route::prefix('api')实际上返回的是一个RouteRegistrar的对象，当这个对象没有middleware这个方法时，会调用当前方法的__call方法
    //                继续在attribute中设置middleware=>'api'
                 ->middleware('api')
    //            namespace同理
                 ->namespace($this->namespace)
                 ->group(base_path('routes/api.php'));
        }

实现路由的过程比较有意思，首先我们会执行这个服务提供者的map方法，而这个方法会分别调用两个方法，这两个方法分别对应我们的api路由以及我们的web路由。  
我们可以看到，`Route::prefix('api')`这里执行的是一个静态方法，这个对象继承自Facade，所以他调用一个不存在的静态方法，会返回一个`$app['router']`的实例，也就是`Routing/Router.php`，这个对象也没有prefix方法，但是他有一个`__call`方法:

    public function __call($method, $parameters)
        {
            if (static::hasMacro($method)) {
                return $this->macroCall($method, $parameters);
            }
    
    //       在  RouteRegistrar的attributes数组中设置key value 例如$this->attributes['prefix'] = 'api';
            return (new RouteRegistrar($this))->attribute($method, $parameters[0]);
        }

    //RouteRegister.php
    public function attribute($key, $value)
        {
            if (! in_array($key, $this->allowedAttributes)) {
                throw new InvalidArgumentException("Attribute [{$key}] does not exist.");
            }
    
            $this->attributes[array_get($this->aliases, $key, $key)] = $value;
    
            return $this;
        }

由此可见，他最终会实现一个RouteRegistrar的实例并调用他的attribute这个方法，会一一把`prefix=>api`等以这种形式的键值对存放在attributes这个数组中，middleware和namespace也是同理，不过group不太一样。这个方法是存在的。

    public function group($callback)
        {
            $this->router->group($this->attributes, $callback);
        }

这里会又重新去执行Router.php的group方法:

    //Router.php
    
    
    ----------
    
    
    public function group(array $attributes, $routes)
        {
    //        把内容放入groupStack数组中
            $this->updateGroupStack($attributes);
    
    //        载入路由列表
            $this->loadRoutes($routes);
    
    //        弹出最后添加进入groupStack中的内容
            array_pop($this->groupStack);
        }
    protected function updateGroupStack(array $attributes)
        {
            if (! empty($this->groupStack)) {
                $attributes = RouteGroup::merge($attributes, end($this->groupStack));
            }
    
            $this->groupStack[] = $attributes;
        }
    protected function loadRoutes($routes)
        {
            if ($routes instanceof Closure) {
                $routes($this);
            } else {
                $router = $this;
    
                require $routes;
            }
        }

当我们执行到require $routes时，我们会载入对应的route文件，而这些文件里面的大致内容都是:

    Route::middleware('auth:api')->get('/user', function (Request $request) {
        return $request->user();
    });

所以这个时候其实又像我们前面说的变量，再次去调用Facade的`__callStatic`方法，通过前面生成的router的实例来调用middleware这个方法，而这个对象又没有这个方法，所以又会调用`__call`方法，然后返回一个RouteRegistrar的实例并调用其attribute方法，此时会返回这个实例来帮助我们链式调用。  
所以此时我们又通过RouteRegistrar调用了他的get方法，但是我们又发现，get方法也是不存在的，所以又只能通过`RouteRegistrar`的`__call`方法来使用Router.php的get方法。

    //Router.php
    public function get($uri, $action = null)
        {
            return $this->addRoute(['GET', 'HEAD'], $uri, $action);
        }
    
    protected function addRoute($methods, $uri, $action)
        {
    //        $this->createRoute($methods, $uri, $action)返回一个Route.php的实例
    //        add主要是把一些路由之类的配置放到对应的数组中
            return $this->routes->add($this->createRoute($methods, $uri, $action));
        }
    
    protected function createRoute($methods, $uri, $action)
        {
    //        如果action不属于一个闭包并且action有设置uses且值为一个字符串时执行
            if ($this->actionReferencesController($action)) {
                $action = $this->convertToControllerAction($action);
            }
    //        $this->prefix($uri)会将uri拼接成xx/xx或者/的形式,返回一个route的对象
            $route = $this->newRoute(
                $methods, $this->prefix($uri), $action
            );
    
    //        如果$this->groupStack不为空则执行
            if ($this->hasGroupStack()) {
                $this->mergeGroupAttributesIntoRoute($route);
            }
    
    //        解析where
            $this->addWhereClausesToRoute($route);
    
            return $route;
        }
    
    protected function newRoute($methods, $uri, $action)
        {
    //        链式调用，返回一个Route的实例，并且设置了$this->uri , $this->action , $this->methods
    //        注入了router对象和container对象
            return (new Route($methods, $uri, $action))
                        ->setRouter($this)
                        ->setContainer($this->container);
        }
    
    public function add(Route $route)
        {
    //        uri methods放到routes数组和allRoutes数组中
            $this->addToCollections($route);
    
            $this->addLookups($route);
    
            return $route;
        }

核心代码主要就是这些，最终调用get方法之后，会把名字所对应的Route对象一一对应，放置在RouteCollection的routes数组中(每个方法源码的解释，注释我都已经放置在我的[GitHub][9])，如下图所示:

![43c42e39-eb2d-47f1-b16d-54d73c28829b.png][10]

##### 执行服务提供者(BootProviders)

这个系列里面最后执行的是服务提供者，这个方法比较简单，主要是遍历的去执行每个服务提供者的boot方法:

    //Application.php
        public function boot()
        {
            if ($this->booted) {
                return;
            }
    
            $this->fireAppCallbacks($this->bootingCallbacks);
    
    //        遍历之前register的所有的服务提供者
            array_walk($this->serviceProviders, function ($p) {
    //            如果服务提供者的boot方法存在，就调用这个方法
                $this->bootProvider($p);
            });
    
            $this->booted = true;
    
            $this->fireAppCallbacks($this->bootedCallbacks);
        }
    
    protected function bootProvider(ServiceProvider $provider)
        {
            if (method_exists($provider, 'boot')) {
                return $this->call([$provider, 'boot']);
            }
        }

至此，我们的$bootstrappers部分算是告一段落了。

#### Response

我们继续回到我们的Kernel.php中的sendRequestThroughRouter方法:

    protected function sendRequestThroughRouter($request)
        {
    //        这里把$request注入到$this->app的instances中
            $this->app->instance('request', $request);
    
            Facade::clearResolvedInstance('request');
    
    //        这个方法主要是用来给$app注入一些其他的元素,把$bootstrappers数组的内容一一遍历，并调用这个类的bootstrap来完成一些载入初始化，实现服务提供者，设置别名，配置等工作。
            $this->bootstrap();
    
            //        shouldSkipMiddleware用来判断middle.disable是否有绑定过，或者可以实例化
            return (new Pipeline($this->app))
                        ->send($request)
                        ->through($this->app->shouldSkipMiddleware() ? [] : $this->middleware)
                        ->then($this->dispatchToRouter());
        }

当我们执行完`$this->bootstrap()`之后，我们需要做的就是执行最后的返回response部分了。

其中最重要的就是then方法:

    //Pipeline/Pipeline.php
    public function then(Closure $destination)
        {
    //        这里需要注意的是，这里的$this->carry()和$this->prepareDestination都是调用的子类的方法，子类是Routing/Pipeline.php
    //        array_reverse将$this->pipes顺序颠倒，
    //        array_reduce 如果制定了第三个参数，那么在第一个参数不为空的情况下，会作为array_reverse第二个参数，也就是我们设置的回调函数的第一次执行时的第一个参数，如果为空的情况下，那么就会作为这个函数的返回值返回
            $pipeline = array_reduce(
                array_reverse($this->pipes), $this->carry(), $this->prepareDestination($destination)
            );
    //   $pipeline实际上获取的是一个$this->carry()返回的第二层的回调函数，下面才正式执行
            return $pipeline($this->passable);
        }
    
    protected function carry()
        {
    //        这里最终会被他的子类来调用执行回调
            return function ($stack, $pipe) {
    
                return function ($passable) use ($stack, $pipe) {
                    if ($pipe instanceof Closure) {
                        return $pipe($passable, $stack);
                    } elseif (! is_object($pipe)) {
    //                    因为我们传入的都是类名，所以走的是这个条件判断
                        list($name, $parameters) = $this->parsePipeString($pipe);
    //                    通过make，获取一个实例
                        $pipe = $this->getContainer()->make($name);
    //这里后面会传入一个request对象，作为参数合并
                        $parameters = array_merge([$passable, $stack], $parameters);
                    } else {
                        $parameters = [$passable, $stack];
                    }
    //                调用生成的实例的handle方法
                    return $pipe->{$this->method}(...$parameters);
                };
            };
        }
    
    protected function prepareDestination(Closure $destination)
        {
            return function ($passable) use ($destination) {
                return $destination($passable);
            };
        }

这里我们需要注意的是，其实这里并不是执行的Pipeline/Pipeline.php这个对象，而是执行的他的子类的这两个方法，因为这两个方法已经被覆盖:

    //Routing/Pipeline.php
    protected function prepareDestination(Closure $destination)
        {
            return function ($passable) use ($destination) {
                try {
                    return $destination($passable);
                } catch (Exception $e) {
                    return $this->handleException($passable, $e);
                } catch (Throwable $e) {
                    return $this->handleException($passable, new FatalThrowableError($e));
                }
            };
        }
    
    protected function carry()
        {
    //        返回一个回调,而这里的回调会通过array_reduce来执行，所以其实返回的是这个回调里面的函数
    //        $stack要注意的是，这里的$stack是上一次注入的回调，也就是本方法，来帮助后面的handle
            return function ($stack, $pipe) {
                return function ($passable) use ($stack, $pipe) {
                    try {
    //                    调用父类返回一个回调
                        $slice = parent::carry();
    //                      执行这个回调再返回一个回调里面的回调函数
                        $callable = $slice($stack, $pipe);
    //                      执行刚刚返回的第二层回调
                        return $callable($passable);
                    } catch (Exception $e) {
                        return $this->handleException($passable, $e);
                    } catch (Throwable $e) {
                        return $this->handleException($passable, new FatalThrowableError($e));
                    }
                };
            };
        }

我们再集合起来前面的`array_reduce`方法来看，我们就能得知，首先执行then方法中的

    array_reverse($this->pipes)

对我们最开始出传入的`$this->middleware`颠倒，由于在`array_reduce`中设置了第三个参数，所以第一次循环的时候的第一个参数也就是Routing/Pipeline.php中的prepareDestination方法的返回值，然后不断循环一直到最后一个，

    return $pipeline($this->passable);

此时的$pipeline的值就是来自于`Routing/Pipeline.php`中的carry方法中第二层的回调函数，所以此时执行，就会执行这样几行代码:

    //                    调用父类返回一个回调
                        $slice = parent::carry();
    //                      执行这个回调再返回一个回调里面的回调函数
                        $callable = $slice($stack, $pipe);
    //                      执行刚刚返回的第二层回调
                        return $callable($passable);

然后父类的carry再执行最后这行代码:

    //                调用生成的实例的handle方法
                    return $pipe->{$this->method}(...$parameters);

这里的$this->method的默认值是handle。  
所以此时会执行到了CheckForMaintenanceMode这个类的handle中:

    public function handle($request, Closure $next)
        {
            if ($this->app->isDownForMaintenance()) {
                $data = json_decode(file_get_contents($this->app->storagePath().'/framework/down'), true);
    
                throw new MaintenanceModeException($data['time'], $data['retry'], $data['message']);
            }
    
            return $next($request);
        }

我们可以注意到，他会继续调用传入的回调函数:

    $next($request);

这样，就形成了一个循环的栈去调用执行下一个，一直执行到我们最开始第一次循环时传入的第一个参数，也就是Routing/Pipeline.php中的prepareDestination方法的返回值，所以最后也就执行了Kernel类的dispatchToRouter方法:

    protected function dispatchToRouter()
        {
            return function ($request) {
    //            把requirt注册到$this->app->instances中
                $this->app->instance('request', $request);
    
                return $this->router->dispatch($request);
            };
        }

至此，就得到了我们最后的Response对象。

本文由 [nine][11] 创作，采用 [知识共享署名4.0][12] 国际许可协议进行许可  
本站文章除注明转载/出处外，均为本站原创或翻译，转载前请务必署名  
最后编辑时间为: Sep 3, 2017 at 04:13 pm

[0]: http://www.hellonine.top/index.php/category/laravel/
[1]: http://www.hellonine.top/index.php/category/PHP/
[2]: #comments
[3]: http://www.hellonine.top/index.php/archives/6/
[4]: http://www.hellonine.top/index.php/archives/16/
[5]: http://www.hellonine.top/index.php/archives/17/#directory078321039605173896
[6]: ../img/2629106754.png
[7]: ../img/1820296847.png
[8]: ../img/1129394031.png
[9]: https://github.com/nineyang/laravel_interpretation
[10]: ../img/3428703604.png
[11]: http://www.hellonine.top/index.php/author/1/
[12]: https://creativecommons.org/licenses/by/4.0/
## PHP Laravel 的 Container 理解

来源：[http://marklin-blog.logdown.com/posts/7818566-container-understanding-of-php-laravel](http://marklin-blog.logdown.com/posts/7818566-container-understanding-of-php-laravel)

时间 2018-11-30 18:01:00

 
 ![][0]
 
### Container 是什麼 ?
 
Laravel Container 是什麼呢 ? 我們先來理解 Container 容器 是什麼。
 
容器抽象一點概念是指用來裝東西的載體，向菜籃也算個容器，而在 Laravel 中所代表的意思就是指 :
 
裡面裝了一堆可以用的服務載體，就叫 Container。
 
像我們每當要執行 Laravel 時，都會先執行下面這段程式碼，其中 $app 就是我們的 Container，然後接下來會使用 Container 來實體化一些物件，例如 $kernel。

```php
<?php
public/index.php

$app = require_once __DIR__.'/../bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request
| through the kernel, and send the associated response back to
| the client's browser allowing them to enjoy the creative
| and wonderful application we have prepared for them.
|
*/

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
```

 
### 為什麼要使用 Container ?
 
上面我們理解 Container 是做什麼用以後，接下來我們要來想想一件事情。
 
為什麼 Laravel 要使用 Container 呢，為什麼上面的要實體化 $knernel 時，不使用 new Knernel() 這種實體化的方式呢 ?
 
因為它想解決依賴與耦合。
 
這就是 Conainter 想解決的事情。
 
#### (高)依賴與耦合
 
高依賴與耦合 : 程式碼中綁死了某個模組，如下面程式碼綁死了 Log Service。
 
假設有一段程式碼如下 :

```php
<?php

class Log
{
    public function send(log): void
    {
      $awsLogService = new AWSLogService();
      $awsLogService->send(log);
    }  
}

class AWSLogService
{
    public function send(log): void
    {
       ....
    }
}
```

 
但假設今天我們要將 Log 改傳到 GCP ( Google 雲端 )，那我們程式碼要修改成如下 :

```php
<?php

class Log
{
    public function send(log): void
    {
      //$awsLogService = new AWSLogService();
      //$awsLogService->send(log);
      
      $gcpLogService = new GCPLogService();
      $gcpLogService->send(log);
    }  
}

class GCPLogService
{
    public function send(log): void
    {
       ....
    }
}

// 使用

$log = new Log();
$log->send('log.....');
```

 
從上面程式碼中，我們可以注意到我們沒當要換個服務時，都需要修改程式碼，並且這裡還有一個缺點，你要如何做單元測試 ? 程式碼裡面完全的綁死了 AWSLogService 或是 GCPLogService，沒有地方可以給我們進行替換，沒辦法替換就代表我們在做測試時，只能真的將資料丟到 AWS 或 GCP。
 
#### (低) 依賴與耦合
 
然後由於有上面說的缺點，因此會將程式碼改成如下。基本上就是將 LogService 改成由使用這個物件時來決定是用選擇 AWS 還是 GCP，並且這兩個 service 都實作同一個 ILogService 的 interface。

```php
<?php

class Log
{
    private ILogService $logService;
  
    public function __construct(ILogService $logService)
    {
      $this->logService = $logService;
    }

    public function send(log): void
    {
      $this->logService->send(log);
    }  
}

class GCPLogService implements ILogService
{
    public function send(log): void
    {
       ....
    }
}

class AWSLogService implements ILogService
{
    public function send(log): void
    {
       ....
    }
}

interface ILogService 
{
    public function send();
}

// 使用
$log = new Log(new AWSLogServcie());
$log->send('log......');
```

 
好接下來在拉回主題。
 
#### 為什麼要使用 Laravel Container ?
 
上面我們的範例程式碼最後要執行時，會如下 :

```php
<?php

$log = new Log(new AWSLogServcie());
$log->send('log......');
```

 
這樣事實上沒什麼問題。
 
但是如果這一段程式碼有很多地方使用怎麼辦 ? 有沒有可能系統中統一都要使用 AWS 的，但是其中一個地方忘了改，而不小心使用到 GCP ? 嗯這是有可能發生的。
 
還有另一個問題，這一段程式碼本身就依賴了`Log`這個類別，這樣事實上還是沒有解決依賴的問題。
 
因此 Laravel 建立了 Container，並且會在開啟服務時，先行註冊好，例如下面偽代碼。只要在這個 conatiner 內部的 class 都會根據它註冊好的東西來進行處理。

```php
<?php

$containter = require('Container');

// 它會在這一段先將 ILogService 綁定好，如果 construct 中有使用到它的，將會將它實體化為 // AWSLogServcie。 
$containter->bind(ILogService, AWSLogServcie::class);

// 實體化 Log 類別。
$log = $container->make(Log::class);

$log->send('log....');
```

 
#### 那有兩個類別，它們內部有使用相同抽像類別，但這時它們實際上要使用不同的類別要怎麼處理呢 ?
 
Laravel 官網有給個範例如下，Photo 與 Video 都有使用到 Filesystem 這個抽象類別，但它們實際上要使用不一樣的類別，則可以使用如下的方法來進行指定。

```php
<?php

$this->app->when(PhotoController::class)
          ->needs(Filesystem::class)
          ->give(function () {
              return Storage::disk('local');
          });

$this->app->when(VideoController::class)
          ->needs(Filesystem::class)
          ->give(function () {
              return Storage::disk('s3');
          });
```

 
[Contextual Bindings (上下文绑定)][3]
 
### Laravel 如何建立 Container ?
 
這裡我們就要開始來研究一下 Laravel Container 的原始碼。
 
首先最一開始是這裡，它會實體化一個 $app conatiner。

```php
<?php

$app = new Illuminate\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);
```

 
接下來我們來看一下 Illuminate\Foundation\Application 的程式碼。這裡可以知道 Application 繼承了 Container 這個類別。

```php
<?php

class Application extends Container implements ApplicationContract, HttpKernelInterface
{
    public function __construct($basePath = null)
    {
        if ($basePath) {
            $this->setBasePath($basePath);
        }
        $this->registerBaseBindings();
        $this->registerBaseServiceProviders();
        $this->registerCoreContainerAliases();
    }

}
```

 
[laravel5.7-container][4]
 
然後 Container 類別中，有兩個方法是重點那就是`bind`與`make`。
 
#### bind
 
建立抽象與實體的綁定表
 
 ![][1]
 
#### bind 使用方式
 
基本上分為以下四種 :

```php
<?php

// 1. 類別綁定 clouse
App::bind('UserRepository', function()
{
    return new AWSUserRepository;
});

// 2. 抽像類別綁定實際類別
App::bind('UserRepositoryInterface', 'DbUserRepository');

// 3. 實際類別綁定
APP::bind('UserRepository')

// 4. singleton 綁定
App::singleton('UserRepository', function()
{
    return new AWSUserRepository;
});
```

 
#### 原始碼解析
 
[laravel5.7-container-bind][5]

```php
<?php

/**
     * Register a binding with the container.
     *
     * @param  string  $abstract
     * @param  \Closure|string|null  $concrete
     * @param  bool  $shared
     * @return void
     */
    public function bind($abstract, $concrete = null, $shared = false)
    {
        $this->dropStaleInstances($abstract);
       
        // 例如這種 APP::bind('UserRepository') 的註冊，就會執行這一段。
        if (is_null($concrete)) {
            $concrete = $abstract;
        }
        
        // 如果是上面那種情況或是沒有 Closure，就直接產生一個 Closure。
        if (! $concrete instanceof Closure) {
            $concrete = $this->getClosure($abstract, $concrete);
        }

        // 綁定，就是用一個 HashTable 來建立綁定對應。
        $this->bindings[$abstract] = compact('concrete', 'shared');
        
        // 如果此類別已被 resolve 則進行 rebound。
        if ($this->resolved($abstract)) {
            $this->rebound($abstract);
        }
    }
    
        /**
     * Get the Closure to be used when building a type.
     *
     * @param  string  $abstract
     * @param  string  $concrete
     * @return \Closure
     */
    protected function getClosure($abstract, $concrete)
    {
        return function ($container, $parameters = []) use ($abstract, $concrete) {
            if ($abstract == $concrete) {
                return $container->build($concrete);
            }

            return $container->make($concrete, $parameters);
        };
    }
```

 
#### make
 
產生實際的實體物件
 
 ![][2]
 
#### 使用方法

```php
<?php

$app->make('UserRepository');
```

 
#### 原始碼解析
 
  
[laravel5.7-container-make][6]
[laravel5.7-containier-resolve][7] 
 

```php
<?php

 /**
     * Resolve the given type from the container.
     *
     * @param  string  $abstract
     * @param  array  $parameters
     * @return mixed
     */
    public function make($abstract, array $parameters = [])
    {
        return $this->resolve($abstract, $parameters);
    }


 /**
     * Resolve the given type from the container.
     *
     * @param  string  $abstract
     * @param  array  $parameters
     * @return mixed
     */
    protected function resolve($abstract, $parameters = [])
    {
        $abstract = $this->getAlias($abstract);

        $needsContextualBuild = ! empty($parameters) || ! is_null(
            $this->getContextualConcrete($abstract)
        );


        // 如果此抽象類別已經實體化了，且 construct 沒使用其它外部注入，則回傳此物件。
        if (isset($this->instances[$abstract]) && ! $needsContextualBuild)
        {
            return $this->instances[$abstract];
        }

        $this->with[] = $parameters;

        // 這個地方有兩種情況
        // 1. 從抽象類別的建構式取出有使用的類別，並回傳。
        // 2. 如果沒有，則從 bindings 中找出對應的實體類別。
        $concrete = $this->getConcrete($abstract);
        
        // isBuildable => true
        // 1. $concrete 與 $abstract 為相同 (也就直接使用類別來綁定)
        // 
        // isBuildable => false
        // 1. 直接使用介面。 
        // 2. $abstract 本身內部還有依賴的外部套件。
        if ($this->isBuildable($concrete, $abstract)) {
            $object = $this->build($concrete);
        } else {
            $object = $this->make($concrete);
        }
        
        // 不太懂
        foreach ($this->getExtenders($abstract) as $extender) {
            $object = $extender($object, $this);
        }

        // 註冊的類別如果被指定為 singleton 就要 cache 它。
        if ($this->isShared($abstract) && ! $needsContextualBuild) {
            $this->instances[$abstract] = $object;
        }

        $this->fireResolvingCallbacks($abstract, $object);

        // 記錄那個類別已經被 resolve
        $this->resolved[$abstract] = true。;

        array_pop($this->with);

        return $object;
    }

    /**
     * Determine if the given concrete is buildable.
     *
     * @param  mixed   $concrete
     * @param  string  $abstract
     * @return bool
     */
    protected function isBuildable($concrete, $abstract)
    {
        return $concrete === $abstract || $concrete instanceof Closure;
    }
```

 
### 參考資料


[3]: https://laravel-china.org/docs/laravel/5.5/container/1289#contextual-binding
[4]: https://github.com/laravel/framework/blob/5.7/src/Illuminate/Container/Container.php
[5]: https://github.com/laravel/framework/blob/5.7/src/Illuminate/Container/Container.php#L222
[6]: https://github.com/laravel/framework/blob/5.7/src/Illuminate/Container/Container.php#L607
[7]: https://github.com/laravel/framework/blob/5.7/src/Illuminate/Container/Container.php#L635
[0]: ../img/FfIj22r.png
[1]: ../img/I7Jnmyn.png
[2]: ../img/MnEzyaB.png
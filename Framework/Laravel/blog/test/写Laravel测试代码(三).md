## 写Laravel测试代码(三)

来源：[https://segmentfault.commg/a/1190000010456626](https://segmentfault.com/a/1190000010456626)

本文主要聊一聊写测试时如何`mock`第三方`json api`数据。

在开发时经常会调用第三方API接口，抓取`json api data`后进行加工处理，那如何写测试呢？如何mock数据呢？

这里举一个简单例子，`AccountController::class调用Connector::class， Connector::class 会调用第三方 json api来读取数据`，代码如下：

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

class AccountController extends Controller
{
    /**
     * @var Connector
     */
    private $connector;

    public function index()
    {
        $connector = $this->getConnector();

        return $connector->call('accounts');
    }

    public function show(string $id)
    {
        $connector = $this->getConnector();

        return $connector->call('accounts/' . $id);
    }

    private function getConnector()
    {
        if (!$this->connector) {
            $this->connector = new Connector();
        }

        return $this->connector;
    }
}



namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

class Connector
{
    public function call(string $path): array
    {
        $client = new Client();

        $response = $client->request(Request::METHOD_GET, config('app.url') . DIRECTORY_SEPARATOR . $path);

        return \GuzzleHttp\json_decode($response);
    }
}
```

代码很简单，但是场景却经常会遇到，关键是如何mock数据而不是发送真实http请求数据。`其实很简单，只需运用Mockery库mock请求代码，从本地读取fixtures数据`。

首先`是在tests/fixtures文件夹下准备下fixtures数据，这些json文件的数据都是真实的接口返回的数据，可以先用postman或其他工具拿到真实数据`， simple_dataset 是dataset的名称，可以自定义，一般项目里都会有一个或多个dataset数据集，vendor 是第三方名称，自定义：

![][0]

![][1]

然后写上`AccountControllerTest::class`：

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Http\Request;

class AccountControllerTest extends TestCase
{
    public function testIndex()
    {
        $response = $this->call(Request::METHOD_GET, 'api/v1/accounts');

        dump($response->json());
    }

    public function testShow()
    {
        $response = $this->call(Request::METHOD_GET, 'api/v1/accounts/1');

        dump($response->json());
    }
}

```

然后写上路由：

```php
Route::group(['prefix' => 'v1'], function () {
    $resources = [
        'accounts' => [\App\Http\Controllers\AccountController::class => ['index', 'show']],
    ];

    foreach ($resources as $name => $controllers) {
        foreach ($controllers as $fqcn => $actions) {
            Route::resource($name, $fqcn, ['only' => $actions]);
        }
    }
});

```

既然用了全局类名`\App\Http\Controllers\AccountController::class`，那就别忘了在`app/Providers/RouteServiceProvider::mapApiRoutes 抹掉namespace`：

```php
    protected function mapApiRoutes()
    {
        Route::prefix('api')
             ->middleware('api')
             ->group(base_path('routes/api.php'));
    }
```

最后同时在`TestCase::class写上mock数据代码`：

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Connector;
use Symfony\Component\Finder\SplFileInfo;
use Tests\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected const MOCK_PATH = 'tests/fixtures/simple_dataset/vendor'; // tests/fixtures/{$dataset_name}/{$vendor_name}

    public function setUp()
    {
        parent::setUp();
    
        /** @see http://docs.mockery.io/en/latest/cookbook/mocking_hard_dependencies.html?highlight=overload */
        $mock      = \Mockery::mock('overload:' . Connector::class); // Mock hard dependencies
        $mock_path = base_path(static::MOCK_PATH);

        /** @var SplFileInfo[] $files */
        $files = \File::allFiles($mock_path);

        foreach ($files as $file) {
            $api_name = substr($file->getRelativePathname(), 0, -5); // remove '.json'

            // mock Connector::call('accounts/1') && Connector::call('accounts')
            $mock->shouldReceive('call')->with($api_name)->andReturn(\GuzzleHttp\json_decode(file_get_contents($file->getRealPath()), true));
        }
    }
}


```

这样执行测试时就实现了`读取本地的真实json数据`，而不用发起真实的http请求。两个测试的response数据的确来源于本地json文件的数据：

![][2]

其实，就是一句话，`写测试时如果调用了第三方 json api 读取数据时，使用Mockery库去mock数据，数据来源于本地文件夹的数据，且是真实有效的数据。至于mock部分的代码想咋写就咋写。`同时，上面代码里还需要注意一点是，由于`Connector::class是AccountController::class 的 hard dependency，别忘了加上 overload， 代码里已经添加链接，可看官网介绍`。

写测试是非常重要的，需要会使用`PHPUnit和Mockery这两个基本库`，官网是[PHP手册][3]和[Mockery手册][4]。

[3]: https://phpunit.de/manual/current/zh_cn/index.html
[4]: http://docs.mockery.io/en/latest/index.html
[0]: ./img/img/bVR2ic.png
[1]: ./img/img/bVR2ij.png
[2]: ./img/img/bVR2lH.png
## 写 Laravel 测试代码(五)

来源：[https://segmentfault.com/a/1190000011665315](https://segmentfault.com/a/1190000011665315)

本文主要探讨写laravel integration/functional test cases时候，如何assert。前面几篇文章主要聊了如何reseed测试数据，mock数据，本篇主要聊下assert的可行实践，尽管laravel官方文档聊了[Testing JSON APIs][2]，并提供了一些辅助的assert方法，如`assertStatus(), assertJson()等等`，但可行不实用，不建议这么做。

最佳需要是对api产生的response做更精细的assert。那如何是更精细的assertion？`简单一句就是把response code/headers/content 完整内容进行比对(assert)。`方法就是把response的内容存入json文件里作为`baseline`。OK，接下来聊下如何做。

写一个AccountControllerTest，call的是`/api/v1/accounts`，AccountController的内容参照[写Laravel测试代码(三)][3]，然后写上integration/functional test cases:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\AssertApiBaseline;

final class AccountControllerTest extends TestCase
{
    use AssertApiBaseline;

    protected const ROUTE_NAME = 'accounts';

    public function testIndex()
    {
        $this->assertApiIndex();
    }

    public function testShow()
    {
        $this->assertApiShow(1);
    }
}


```

很明显，这里测试的是index/show api，即`/api/v1/accounts和/api/v1/accounts/{account_id}`，AssertApiBaseline是一个自定义的trait，主要功能就是实现了assert 全部response，并保存在json文件里作为baseline。所以，重点就是AssertApiBaseline该如何写，这里就直接贴代码：

```php
<?php

declare(strict_types=1);

namespace Tests;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Foundation\Testing\TestResponse;

trait AssertApiBaseline
{
    private static $middlewareGroup = 'web';

    private static $cookies = [
        'web' => [
            'D' => 'DiJeb7IQHo8FOFkXulieyA',
        ],
        'api' => [
        ],
    ];

    private static $servers = [
        'web' => [
            'HTTP_ACCEPT'  => 'application/json',
            'HTTP_ORIGIN'  => 'https://test.company.com',
            'HTTP_REFERER' => 'https://test.company.com',
        ],
        'api' => [
            'HTTP_ACCEPT' => 'application/json',
        ],
    ];

    public static function assertJsonResponse(TestResponse $response, string $message = '', array $ignores = []): TestResponse
    {
        static::assertJsonResponseCode($response, $message);
        static::assertJsonResponseContent($response, $message);
        static::assertJsonResponseHeaders($response, $message);

        return $response;
    }

    public static function assertJsonResponseCode(TestResponse $response, string $message = ''): void
    {
        static::assert($response->getStatusCode(), $message);
    }

    public static function assertJsonResponseContent(TestResponse $response, string $message = '', array $ignores = []): void
    {
        static::assert($response->json(), $message);
    }

    public static function assertJsonResponseHeaders(TestResponse $response, string $message = ''): void
    {
        $headers = $response->headers->all();

        $headers = array_except($headers, [
            'date',
            'set-cookie',
        ]); // except useless headers

        static::assert($headers, $message);
    }

    public static function assert($actual, string $message = '', float $delta = 0.0, int $maxDepth = 10, bool $canonicalize = false, bool $ignoreCase = false): void
    {
        // assert $actual with $expected which is from baseline json file
        // if there is no baseline json file, put $actual data into baseline file (or -d rebase)
        // baseline file path
        // support multiple assertion in a test case

        static $assert_counters = [];
        static $baselines       = [];

        $class     = get_called_class();
        $function  = static::getFunctionName(); // 'testIndex'
        $signature = "$class::$function";

        if (!isset($assert_counters[$signature])) {
            $assert_counters[$signature] = 0;
        } else {
            $assert_counters[$signature]++;
        }

        $test_id = $assert_counters[$signature];

        $baseline_path = static::getBaselinesPath($class, $function);

        if (!array_key_exists($signature, $baselines)) {
            if (file_exists($baseline_path) && array_search('rebase', $_SERVER['argv'], true) === false) { // '-d rebase'
                $baselines[$signature] = \GuzzleHttp\json_decode(file_get_contents($baseline_path), true);
            } else {
                $baselines[$signature] = [];
            }
        }

        $actual = static::prepareActual($actual);

        if (array_key_exists($test_id, $baselines[$signature])) {
            static::assertEquals($baselines[$signature][$test_id], $actual, $message, $delta, $maxDepth, $canonicalize, $ignoreCase);
        } else {
            $baselines[$signature][$test_id] = $actual;

            file_put_contents($baseline_path, \GuzzleHttp\json_encode($baselines[$signature], JSON_PRETTY_PRINT));

            static::assertTrue(true);

            echo 'R';
        }
    }

    /**
     * @param string|string[]|null  $route_parameters
     * @param array $parameters
     *
     * @return mixed
     */
    protected function assertApiIndex($route_parameters = null, array $parameters = [])
    {
        return static::assertApiCall('index', $route_parameters ? (array) $route_parameters : null, $parameters);
    }

    protected function assertApiShow($route_parameters, array $parameters = [])
    {
        assert($route_parameters !== null, '$route_parameters cannot be null');

        return static::assertApiCall('show', (array) $route_parameters, $parameters);
    }

    protected static function getFunctionName(): string
    {
        $stacks = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        do {
            $stack = array_pop($stacks);
        } while ($stack && substr($stack['function'], 0, 4) !== 'test');

        return $stack['function']; // 'testList'
    }

    protected static function getBaselinesPath(string $class, string $function): string
    {
        $class = explode('\\', $class);

        $dir = implode('/', array_merge(
            [strtolower($class[0])],
            array_slice($class, 1, -1),
            ['_baseline', array_pop($class)]
        ));

        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        return base_path() . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $function . '.json';
    }

    protected static function prepareActual($actual)
    {
        if ($actual instanceof Arrayable) {
            $actual = $actual->toArray();
        }

        if (is_array($actual)) {
            array_walk_recursive($actual, function (&$value, $key): void {
                if ($value instanceof Arrayable) {
                    $value = $value->toArray();
                } elseif ($value instanceof Carbon) {
                    $value = 'Carbon:' . $value->toIso8601String();
                } elseif (in_array($key, ['created_at', 'updated_at', 'deleted_at'], true)) {
                    $value = Carbon::now()->format(DATE_RFC3339);
                }
            });
        }

        return $actual;
    }

    private function assertApiCall(string $route_action, array $route_parameters = null, array $parameters = [])
    {
        [$uri, $method] = static::resolveRouteUrlAndMethod(static::resolveRouteName($route_action), $route_parameters);

        /** @var \Illuminate\Foundation\Testing\TestResponse $response */
        $response = $this->call($method, $uri, $parameters, $this->getCookies(), [], $this->getServers(), null);

        return static::assertJsonResponse($response, '');
    }

    private static function resolveRouteName(string $route_action): string
    {
        return static::ROUTE_NAME . '.' . $route_action;
    }

    private static function resolveRouteUrlAndMethod(string $route_name, array $route_parameters = null)
    {
        $route = \Route::getRoutes()->getByName($route_name);
        assert($route, "Route [$route_name] must be existed.");

        return [route($route_name, $route_parameters), $route->methods()[0]];
    }

    private function getCookies(array $overrides = []): array
    {
        $cookies = $overrides + self::$cookies[static::$middlewareGroup];

        return $cookies;
    }

    private function getServers(array $overrides = []): array
    {
        return $overrides + self::$servers[static::$middlewareGroup];
    }
}


```

虽然AssertApiBaseline有点长，但重点只有assert()方法，该方法实现了：

* 如果初始没有baseline文件，就把response内容存入json文件
* 如果有json文件，就拿baseline作为expected data，来和本次api产生的response内容即actual data做assertion
* 如果有'rebase'指令表示本次api产生的response作为新的baseline存入json文件中
* 支持一个test case里执行多次assert()方法


所以，当执行phpunit指令后会生成对应的baseline文件：

![][0]

![][1]

OK，首次执行的时候重新生成baseline文件，查看是不是想要的结果，以后每次改动该api后，如果手滑写错了api，如response content是空，这时候执行测试时会把baseline作为expected data和错误actual data 进行assert就报错，很容易知道代码写错了；如果git diff知道最新的response 就是想要的(如也无需求需要把'name'换另一个)，就`phpunit -d rebase`把新的response作为新的baseline就行。。

这比laravel文档中说明的写json api test cases的优点在哪？`就是对response做了精细控制`。对response 的status code，headers，尤其是response content做了精细控制(content的每一个字段都行了assert对比)。
这是我们这边写api test cases的实践，有疑问可留言交流。

[2]: https://laravel.com/docs/5.5/http-tests#testing-json-apis
[3]: https://segmentfault.com/a/1190000010456626
[0]: ./img/img/bVW6O9.png
[1]: ./img/img/bVW6Pb.png
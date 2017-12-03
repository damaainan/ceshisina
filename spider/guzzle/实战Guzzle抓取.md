# 实战Guzzle抓取

 时间 2017-08-23 20:30:53 

原文[https://huoding.com/2017/08/23/633][2]


虽然早就知道很多人用 [Guzzle][4] 爬数据，但是从来没有真正实践过，在我的潜意识里，抓取是 Python 的地盘。不过前段时间，在我抓汽车之家数据的时候，有人跟我提起 [Goutte][5] 搭配 Guzzle 是最好的爬虫，让我一直记挂在心上，加上最近打算更新一下车型数据，所以我打算重写抓取汽车之家数据的脚本。 

因为我是通过接口抓取，而不是网页，所以暂时用不上 Goutte，只用 Guzzle 就可以了，抓取过程中需要注意两点：首先需要注意的是通过并发节省时间，其次需要注意的是失败重试的步骤。算了，我不想说了，直接贴代码吧。 

```php
    <?php
    
    require "vendor/autoload.php";
    
    use GuzzleHttp\Pool;
    use GuzzleHttp\Client;
    use GuzzleHttp\Middleware;
    use GuzzleHttp\HandlerStack;
    use GuzzleHttp\Psr7\Request;
    
    // 品牌
    $brands = [];
    // 车系
    $series = [];
    // 车型
    $models = [];
    
    // 配置
    $configs = [];
    
    $timeout = 10;
    $concurrency = 100;
    
    ini_set("memory_limit", "512M");
    
    $stack = HandlerStack::create();
    $stack->push(Middleware::retry(
        function($retries) { return $retries < 3; },
        function($retries) { return pow(2, $retries - 1); }
    ));
    
    $client = new Client([
        "debug" => true,
        "timeout" => $timeout,
        "base_uri" => "https://cars.app.autohome.com.cn",
        "headers" => [
            "User-Agent" => "Android\t6.0.1\tautohome\t8.3.0\tAndroid",
        ],
        "handler" => $stack,
    ]);
    
    // 品牌列表页
    $url = "/cars_v8.3.0/cars/brands-pm2.json";
    
    $response = $client->get($url);
    $contents = $response->getBody()->getContents();
    $contents = json_decode($contents, true);
    $contents = $contents["result"]["brandlist"];
    
    static $position = 1;
    
    foreach ($contents as $values) {
        $initial = $values["letter"];
    
        foreach ($values["list"] as $v) {
            $brands[] = [
                "id" => $v["id"],
                "name" => $v["name"],
                "initial" => $initial,
                "position" => $position++,
            ];
        }
    }
    
    ###
    
    $requests = function ($brands) {
        foreach ($brands as $v) {
            $id = $v["id"];
            // 品牌介绍页
            $url = "/cars_v8.3.0/cars/getbrandinfo-pm2-b{$id}.json";
            yield new Request("GET", $url);
        }
    };
    
    $pool = new Pool($client, $requests($brands), [
        "concurrency" => $concurrency,
        "fulfilled" => function ($response, $index) use(&$brands) {
            $contents = $response->getBody()->getContents();
            $contents = json_decode($contents, true);
            $contents = $contents["result"]["list"];
            $contents = $contents ? $contents[0]["decription"] : "暂无";
            $contents = trim(str_replace(["\r\n", ","], ["\n", "，"], $contents));
    
            $brands[$index]["decription"] = $contents;
        },
    ]);
    
    $pool->promise()->wait();
    
    ###
    
    $requests = function ($brands) {
        foreach ($brands as $v) {
            $id = $v["id"];
            // 车系列表页
            $url = "/cars_v8.3.0/cars/seriesprice-pm2-b{$id}-t16-v8.3.0.json";
            yield new Request("GET", $url);
        }
    };
    
    $pool = new Pool($client, $requests($brands), [
        "concurrency" => $concurrency,
        "fulfilled" => function ($response, $index) use(&$series, $brands) {
            static $position = 1;
    
            $contents = $response->getBody()->getContents();
            $contents = json_decode($contents, true);
            $contents = $contents["result"];
    
            $brand_id = $brands[$index]["id"];
            
            foreach (["fctlist", "otherfctlist"] as $field) {
                $values = $contents[$field];
    
                foreach ($values as $value) {
                    $factory = $value["name"];
    
                    foreach ($value["serieslist"] as $v) {
                        list($min, $max) = explode("-", $v["price"]) + [1 => 0];
    
                        $series[] = [
                            "id" => $v["id"],
                            "name" => $v["name"],
                            "level" => $v["levelname"],
                            "factory" => $factory,
                            "min_price" => $min * 10000,
                            "max_price" => $max * 10000,
                            "brand_id" => $brand_id,
                            "position" => $position++,
                        ];
                    }
                }
            }
        },
    ]);
    
    $pool->promise()->wait();
    
    ###
    
    $requests = function ($series) {
        foreach ($series as $v) {
            $id = $v["id"];
            // 车型列表页
            $url = "/carinfo_v8.3.0/cars/seriessummary-pm2-s{$id}-t-c110100-v8.3.0.json";
            yield new Request("GET", $url);
        }
    };
    
    $pool = new Pool($client, $requests($series), [
        "concurrency" => $concurrency,
        "fulfilled" => function ($response, $index) use(&$models, $series) {
            static $position = 1;
    
            $contents = $response->getBody()->getContents();
            $contents = json_decode($contents, true);
            $contents = $contents["result"]['enginelist'];
    
            $series_id = $series[$index]["id"];
    
            foreach ($contents as $values) {
                if (in_array($values["yearvalue"], [0, 1])) {
                    continue;
                }
    
                foreach ($values["yearspeclist"] as $value) {
                    foreach ($value["speclist"] as $v) {
                        if (isset($models[$v["id"]])) {
                            continue;
                        }
    
                        $models[$v["id"]] = [
                            "id" => $v["id"],
                            "name" => $v["name"],
                            "description" => $v["description"],
                            "status" => $v["state"],
                            "price" => $v["price"] * 10000,
                            "series_id" => $series_id,
                            "position" => $position++,
                        ];
                    }
                }
            }
        },
    ]);
    
    $pool->promise()->wait();
    
    ###
    
    $models = array_values($models);
    
    $requests = function ($models) {
        foreach ($models as $v) {
            $id = $v["id"];
            // 车型参数页
            $url = "/cfg_v8.3.0/cars/speccompare.ashx?pm=2&type=1&specids={$id}&cityid=110100&site=2&pl=2";
            yield new Request("GET", $url);
        }
    };
    
    $pool = new Pool($client, $requests($models), [
        "concurrency" => $concurrency,
        "fulfilled" => function ($response, $index) use(&$models, &$configs) {
            $contents = $response->getBody()->getContents();
            $contents = json_decode($contents, true);
            $contents = $contents["result"];
    
            $models[$index]["config"] = [];
    
            foreach (["paramitems", "configitems"] as $key) {
                $values = $contents[$key];
    
                foreach ($values as $value) {
                    $category = $value["itemtype"];
    
                    foreach ($value["items"] as $v) {
                        $id = $v["id"];
    
                        if ($id < 1) {
                            continue;
                        }
    
                        $name = $v["name"];
                        $value = $v["modelexcessids"][0]["value"];
    
                        $models[$index]["config"][$id] = $value;
    
                        if (!isset($configs[$category])) {
                            $configs[$category] = [];
                        }
    
                        if (!isset($configs[$category][$name])) {
                            $configs[$category][$name] = [
                                "id" => $id,
                                "name" => $name,
                                "category" => $category,
                                "position" => count($configs[$category]) + 1,
                            ];
                        }
                    }
                }
            }
    
            $models[$index]["config"] = json_encode(
                $models[$index]["config"], JSON_UNESCAPED_UNICODE
            );
        },
    ]);
    
    $pool->promise()->wait();
    
    /*
        处理如下数据:
    
        brands
        series
        models
        configs
    
        入库或者保存到文件
    */
    
    ?>
```

此类工具性质的脚本无需考虑面向对象之类的弯弯绕，一马平川的流水账是最好的选择。运行前记得先通过 composer 安装 guzzle，整个运行过程大概 10mins 左右，能够抓取汽车之家完整的品牌，车系，车型，参数等数据。


[2]: https://huoding.com/2017/08/23/633

[4]: http://docs.guzzlephp.org/en/stable/
[5]: https://github.com/FriendsOfPHP/Goutte
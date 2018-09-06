## [nginx lua 小项目：根据 user_agent 显示不同的页面，附带和 php 性能的对比](https://yq.aliyun.com/articles/212753?utm_content=m_31035)

_摘要：_ pc、mobile 一个地址有两套页面，需要在后端根据浏览器的 user_agent 来显示不同的页面。 通过 php 来做，当然可以，但是活动页面访问量一般都比较大，想优化些，所以想尝试下 lua。 

怎么快速学习一门新的语言呢？  
如果你已经熟练掌握了一门语言，那么其他语言都是想通的。一个小小的需求，可能会遇到很多问题，但是搜索相关的关键字，就能快速实现出来，完成一个小目标，事半功倍。  
死记硬背手册，太枯燥了，反正我是看不下去，不如直接来个小项目。

### 一个小需求

pc、mobile 一个地址有两套页面，需要在后端根据浏览器的 user_agent 来显示不同的页面。  
通过 php 来做，当然可以，但是活动页面访问量一般都比较大，想优化些，所以想尝试下 lua。

### nginx 安装 lua-nginx-module

可以直接上 openresty，不过有时候就是想折腾。  
安装的步骤 [https://mengkang.net/994.html][0] （如果你想实践的话再看吧）

### lua demo 脚本

```lua
-- 判断是否是手机浏览器
function isMobile(userAgent)
    -- 99% 前三个都能匹配上吧
    local mobile = {
        "phone", "android", "mobile", "itouch", "ipod", "symbian", "htc", "palmos", "blackberry", "opera mini", "windows ce", "nokia", "fennec",
        "hiptop", "kindle", "mot", "webos", "samsung", "sonyericsson", "wap", "avantgo", "eudoraweb", "minimo", "netfront", "teleca"
    }
    userAgent = string.lower(userAgent)

    for i, v in ipairs(mobile) do
        if string.match(userAgent, v) then
            return true
        end
    end

    return false
end

-- 根据id + 浏览器类型展示活动页面
function showPromotionHtml(id, isMobile)
    local path = "/data/www/mengkang/demo/promotion/"
    local filename

    if isMobile then
        path = path .. "mobile"
    else
        path = path .. "pc"
    end

    filename = path .. "/" .. id .. ".html"

    if file_exists(filename) then
        local file = io.open(filename,"r")
        io.input(file)
        print(io.read("*a"))
        io.close(file)
    else
        print("文件不存在: " .. string.gsub(filename, "/data/www/mengkang/demo", ""))
    end
end

-- 判断文件是否存在
function file_exists(path)
    local file = io.open(path, "rb")
    if file then file:close() end
    return file ~= nil
end

local id = 1
local userAgent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.79 Safari/537.36"
showPromotionHtml(id, isMobile(userAgent))
    
```

### 小结

作为一个 lua 菜鸟，通过这个小需求我查了哪些资料

变量的定义  
函数的写法  
循环的了解  
判断逻辑的写法  
注释的写法  
文件 i/o  
字符串拼接 `..`  
字符串查找 `string.match`  
字符串转小写 `string.lower`

### 稍微调整适配 nginx lua 模块

```lua
-- 判断是否是手机浏览器
function isMobile(userAgent)
    -- 99% 前三个都能匹配上吧
    local mobile = {
        "phone", "android", "mobile", "itouch", "ipod", "symbian", "htc", "palmos", "blackberry", "opera mini", "windows ce", "nokia", "fennec",
        "hiptop", "kindle", "mot", "webos", "samsung", "sonyericsson", "wap", "avantgo", "eudoraweb", "minimo", "netfront", "teleca"
    }
    userAgent = string.lower(userAgent)

    for i, v in ipairs(mobile) do
        if string.match(userAgent, v) then
            return true
        end
    end

    return false
end

-- 根据id + 浏览器类型展示活动页面
function showPromotionHtml(id, isMobile)
    local path = "/data/www/mengkang/demo/promotion/"
    local filename

    if isMobile then
        path = path .. "mobile"
    else
        path = path .. "pc"
    end

    filename = path .. "/" .. id .. ".html"

    if file_exists(filename) then
        local file = io.open(filename,"r")
        io.input(file)
        ngx.say(io.read("*a"))
        io.close(file)
    else
        ngx.say("file not found : " .. string.gsub(filename, "/data/www/mengkang/demo", ""))
    end
end

-- 判断文件是否存在
function file_exists(path)
    local file = io.open(path, "rb")
    if file then file:close() end
    return file ~= nil
end

local id = ngx.var.id
local userAgent = ngx.req.get_headers().user_agent
showPromotionHtml(id, isMobile(userAgent))
    
```

### nginx 配置

```nginx
server
{
    listen       80;
    server_name mengkang.net

    location ~ /promotion/(\d+)
    {
        set $id $1;
        default_type "text/html";
        content_by_lua_file /data/www/lua/1.lua;
    }
}
```

nginx 单独安装 `lua` 模块也行，使用 `openresty` 也可以。单独安装参考：[https://mengkang.net/994.html][0]

### 演示地址

[https://mengkang.net/promotion/1][1]  
[https://mengkang.net/promotion/100][2]  
切换 `user_agent` 即可看到，不同的 `pc` 和 `mobile` 两个版本的页面

### 和 php 性能对比

#### nginx 配置

    rewrite ^/promotion2/(.*)$  /demo/promotion.php last;
    

#### php 代码

```php
<?php
header("Content-type:text/html;charset=utf-8");
header_remove('x-powered-by');
ini_set('display_errors', 'Off');

function getId(){
    $uri = $_SERVER["REQUEST_URI"];
    $tmp = explode("?",$uri);
    $uri = $tmp[0];
    $uri = trim($uri,"/");
    $tmp = explode("/",$uri);
    return intval(array_pop($tmp));
}

function isMobile()
{
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower ( $_SERVER['HTTP_USER_AGENT'] ) : '';

    if ( preg_match ( "/phone|itouch|ipod|symbian|android|htc_|htc-|palmos|blackberry|opera mini|windows ce|nokia|fennec|hiptop|kindle|mot |mot-|webos\/|samsung|sonyericsson|mobile|pda;|avantgo|eudoraweb|minimo|netfront|nintendo/", $user_agent ) ) {
        return true;
    }

    return false;
}

$id = getId();

$isMobile = isMobile();


if ($isMobile){
    $filename = __DIR__."/promotion/mobile/".$id.".html";
}else{
    $filename = __DIR__."/promotion/pc/".$id.".html";
}


if (file_exists($filename)) {
    echo file_get_contents($filename);
}else{
    echo "file not found : /promotion/pc/".$id.".html";
}

exit;
```

也就是说访问  
[http://mengkang.net/promotion/1][3]  
和  
[http://mengkang.net/promotion2/1][4]  
是一样的结果

##### 配置说明

> 双核4G  
> nginx 配置一致  
> php 版本：7.0.11  
> php-fpm 配置 

```cfg
    pm = dynamic
    pm.max_children = 10
    pm.start_servers = 4
    pm.min_spare_servers = 4
    pm.max_spare_servers = 10
```

#### php 压测结果

```
    ab -n 1000 -c 100 http://mengkang.net/promotion2/1
    Requests per second:    3105.21 [#/sec] (mean)
    Time per request:       32.204 [ms] (mean)
    
    ab -n 4000 -c 400 http://mengkang.net/promotion2/1
    Requests per second:    3361.87 [#/sec] (mean)
    Time per request:       118.981 [ms] (mean)
    Complete requests:      4000
    Failed requests:        259
    
    ab -n 8000 -c 800 http://mengkang.net/promotion2/1
    Requests per second:    3358.20 [#/sec] (mean)
    Time per request:       238.223 [ms] (mean)
    Complete requests:      8000
    Failed requests:        654
    
    ab -n 10000 -c 1000 http://mengkang.net/promotion2/1
    Requests per second:    3275.30 [#/sec] (mean)
    Time per request:       305.315 [ms] (mean)
    Complete requests:      10000
    Failed requests:        9150
```

#### lua 压测结果

    ab -n 1000 -c 100 http://mengkang.net/promotion/1
    Requests per second:    6014.89 [#/sec] (mean)
    Time per request:       16.625 [ms] (mean)
    
    ab -n 4000 -c 400 http://mengkang.net/promotion/1
    Complete requests:      4000
    Failed requests:        0
    Requests per second:    6190.57 [#/sec] (mean)
    Time per request:       64.614 [ms] (mean)
    
    ab -n 8000 -c 800 http://mengkang.net/promotion/1
    Complete requests:      8000
    Failed requests:        0
    Requests per second:    7046.66 [#/sec] (mean)
    Time per request:       113.529 [ms] (mean
    
    ab -n 10000 -c 1000 http://mengkang.net/promotion/1
    Complete requests:      10000
    Failed requests:        0
    Requests per second:    5670.38 [#/sec] (mean)
    Time per request:       176.355 [ms] (mean)
    

对比发现性能提示相当可观。  
PHP qps 在 3000左右，nginx_lua qps 在 7000 左右。  
php 在 400 个并发的时候开始出现比较多的失败请求，吞吐率开始下降。  
而 lua 的结果在 1000 个并发的时候，失败的请求数依旧是0。

[0]: https://mengkang.net/994.html
[1]: https://mengkang.net/promotion/1
[2]: https://mengkang.net/promotion/100
[3]: http://mengkang.net/promotion/1
[4]: http://mengkang.net/promotion2/1
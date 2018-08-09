## 彻底弄懂 Laravel 中间件原理

来源：[https://blog.tanteng.me/2018/07/understand-laravel-middleware/](https://blog.tanteng.me/2018/07/understand-laravel-middleware/)

时间 2018-07-30 00:25:08

 
Laravel 的中间件机制提供了一种管道的方式，每个 HTTP 请求经过一个又一个中间件进行过滤，Laravel 内置了很多中间件，比如 CSRF 机制，身份认证，Cookie 加密，设置 Cookie 等等。
 
本文就来探究 Laravel 中间件的实现原理，看 Laravel 如何把 PHP 的 array_reduce 函数和闭包用到了极致。
 
需要先了解 Laravel 中间件的用法，如何定义一个中间件，还有前置中间件，后置中间件的概念。（文档： [Laravel 5.5 中间件][3] ）
 
### 开始
 
为了彻底弄懂 Laravel 中间件原理，可以构造一个路由，并使用 debug_backtrace 函数来打印方法调用过程。
 
```php
Route::get('test',function(){
   dump(debug_backtrace());
});


```
 
如图，可见许多地方都跟 Pipeline 组件有关，并且重复执行一个闭包方法。
 
![][0]
 
这里 pipes 数组就是需要用到的中间件。
 
### 中间件核心类 Pipeline
 
在 Laravel 框架 index.php 入口文件里，$kernel->handle() 方法就调用了 Pipeline 的方法，可以说它是贯穿始终的，这是把请求发到中间件进行处理的方法：
 
```php
/**
 * Send the given request through the middleware / router.
 *
 * @param  \Illuminate\Http\Request  $request
 * @return \Illuminate\Http\Response
 */
protected function sendRequestThroughRouter($request)
{
    $this->app->instance('request', $request);
 
    Facade::clearResolvedInstance('request');
 
    $this->bootstrap();
 
    return (new Pipeline($this->app))
                ->send($request)
                ->through($this->app->shouldSkipMiddleware() ? [] : $this->middleware)
                ->then($this->dispatchToRouter());
}


```
 
其中 send 方法是设置 passable 属性也就是 $request，through 是设置 pipes 属性，也就是需要用到的中间件，是一个数组，重点是这里的 then 方法，参数也是一个闭包函数。
 
```php
/**
 * Run the pipeline with a final destination callback.
 *
 * @param  \Closure  $destination
 * @return mixed
 */
public function then(Closure $destination)
{
    $pipeline = array_reduce(
        array_reverse($this->pipes), $this->carry(), $this->prepareDestination($destination)
    );
 
    return $pipeline($this->passable);
}


```
 
#### array_reduce 使用
 
这里就需要讲解一下 array_reduce 的用法了，可以说是妙用，这是理解 Laravel 中间件的重点，深刻领会了它的用法，就弄懂了 Laravel 中间件的原理。
 
先看一个官方例子明白它的基本用法：
 
```php
function sum($carry, $item)
{
    $carry += $item;
    return $carry;
}
 
$a = array(1, 2, 3, 4, 5);
 
var_dump(array_reduce($a, "sum")); // int(15)


```
 
基础用法参考 PHP 文档： [array_reduce][4]
 
这是一个最简单的例子，array_reduce 会迭代每个元素，回调函数第一个参数是上次执行的结果，然后返回最终的一个值。
 
那么第二个参数的回调函数返回的是一个闭包呢？
 
```php
$arr = ['AAAA', 'BBBB', 'CCCC'];
 
$res = array_reduce($arr, function($carry, $item){
    return function() use ($carry,$item){
        dump("item:".$item);
        if(is_null($carry)){
            return "CARRY is null. item:".$item;
        }
        if($carry instanceof Closure){
            dump($carry());
            return strtolower($item);
        }
        return $item;
    };
});
 
dump($res());


```
 
这个例子第二个参数回调函数返回的是一个闭包，也就是说 array_reduce 函数最终返回的也是一个闭包，除非执行这个闭包，否则里面的逻辑不会执行，这也是闭包的神奇之处，我们可以把函数“暂存”起来以后执行。
 
第一次迭代，$carry 是空的，返回一个字符串。
 
第二次迭代，因为第一次返回了一个闭包，所以这次 $carry 是一个闭包，返回小写字母。
 
第三次迭代，因为第二次迭代返回的是一个闭包，所以也是返回一个小写字母。
 
这个闭包的执行结果是：
 
“item:CCCC”
 
“item:BBBB  ”
 
“item:AAAA  ”
 
“CARRY is null. item:AAAA  ”
 
“bbbb  ”
 
“
 
cccc
 
“
 
一定要弄懂为什么这样输出，它的执行顺序是反的，可以理解为每一次迭代，就是把闭包函数丢到一个栈里面，后进先出。
 
### 实现的核心
 
接下来要分析 $this->carry() 这个方法，它是中间件实现的核心。
 
```php
/**
 * Get a Closure that represents a slice of the application onion.
 *
 * @return \Closure
 */
protected function carry()
{
    return function ($stack, $pipe) {
        return function ($passable) use ($stack, $pipe) {
            if (is_callable($pipe)) {
                // If the pipe is an instance of a Closure, we will just call it directly but
                // otherwise we'll resolve the pipes out of the container and call it with
                // the appropriate method and arguments, returning the results back out.
                return $pipe($passable, $stack);
            } elseif (! is_object($pipe)) {
                list($name, $parameters) = $this->parsePipeString($pipe);
 
                // If the pipe is a string we will parse the string and resolve the class out
                // of the dependency injection container. We can then build a callable and
                // execute the pipe function giving in the parameters that are required.
                $pipe = $this->getContainer()->make($name);
 
                $parameters = array_merge([$passable, $stack], $parameters);
            } else {
                // If the pipe is already an object we'll just make a callable and pass it to
                // the pipe as-is. There is no need to do any extra parsing and formatting
                // since the object we're given was already a fully instantiated object.
                $parameters = [$passable, $stack];
            }
 
            return method_exists($pipe, $this->method)
                            ? $pipe->{$this->method}(...$parameters)
                            : $pipe(...$parameters);
        };
    };
}


```
 
这个 carry 方法返回一个闭包（或者说函数也可以），作为 array_reduce 的第二个参数作为回调函数。这个方法看上去很复杂，闭包里面返回闭包，但是搞清楚了之后就没这么难。
 
这个作为 array_reduce 的回调函数的闭包，接受两个参数，第一个参数也是个闭包，而且第一次迭代的闭包是另外一个方法提供的，第二个参数是中间件，是一个字符串形式。
 
第一次迭代，$stack 参数是 $this->dispatchToRouter() 返回的闭包，实际上放到最后执行了，$pipe 参数是 Illuminate\Routing\Middleware\SubstituteBindings，（注意 array_reverse 把 pipes 数组反转了，实际上理解了原理就知道这样做反而是要按中间件定义的顺序执行），那么根据判断逻辑，从容器中取出中间件，最后执行中间件的 handle 方法，并传入 $request 和 $stack 作为参数，但实际上并没有任何实际的执行，注意这个函数返回的也是一个闭包。
 
第二次迭代，还是执行这个回调函数，此时 $stack 就变成了第一次也就是上次迭代返回的闭包了，第二个参数 $pipe 就是 App\Http\Middleware\VerifyCsrfToken，其他过程同上，也返回一个闭包。
 
……
 
最后一次迭代，$stack 是上一次返回的闭包，$pipe 就是 App\Http\Middleware\EncryptCookies，但到此没有任何实际的执行，因为没有调用。
 
这些闭包，可以理解为放到一个“栈”里面了，执行的时候从最外层开始往里面执行，后进先出。
 
最后，then 方法里 return $pipeline($this->passable) 才是调用 array_reduce 返回的最终的闭包，开始真正执行这些中间件了。
 
### 前置和后置中间件
 
我们把控制器方法改成：
 
```php
Route::get('test',function(){
    dump('this is controller');
   //dump(debug_backtrace());
});


```
 
然后随便找一个中间件在 $response = $next($request) 前后打印点内容：
 
![][1]
 
执行，页面上的输出如图：
 
![][2]
 
这是为什么呢？
 
```php
$response = $next($request);


```
 
这里 $next 就是前文中的 $stack，执行这句的时候就会把所有中间件都执行完，然后别忘了前面说的第一个闭包是 $this->dispatchToRouter() 提供的，它会进入到控制器逻辑，然后再是执行每个中间件中 $response = $next($request) 接下来的逻辑。这也是前置中间件和后置中间件的原理。
 
要彻底弄懂 Laravel 中间件原理，还需要亲自熟悉 array_reduce 方法和理解闭包的概念。
 


[3]: https://laravel-china.org/docs/laravel/5.5/middleware/1294
[4]: http://php.net/manual/zh/function.array-reduce.php
[0]: ../img/R3aIF37.png 
[1]: ../img/iqARN3R.png 
[2]: ../img/UR7F3iq.png 
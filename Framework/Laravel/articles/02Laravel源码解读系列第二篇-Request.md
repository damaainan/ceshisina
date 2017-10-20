# Laravel源码解读系列第二篇-Request 

Published on Jun 11, 2017 in [Laravel][0][PHP][1] with [0 comment][2]

## 前言

在前一片中，我们已经聊完了整个的[初始化][3]的过程，接下来我将和大家一起探讨Laravel的Request和Response部分

- - -

## Request

接着之前的篇幅来，我们执行到了index.php中的生成`$kernel`核心类，接着我们需要执行:

    //1.首先要生成一个Http/Request的实例
    //2.调用我们刚刚生成好的Http\Kernel核心类的handle方法
    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    );

### 生成Request

Request主要是通过`Illuminate\Http\Request::capture()`来帮我们生成:

    public static function capture()
        {
    //        这个方法主要是返回一个request的对象
    //        设置$httpMethodParameterOverride变量，后面会用到这个判断
            static::enableHttpMethodParameterOverride();
    //        1.首先我们先执行SymfonyRequest::createFromGlobals()，这个方法其实会帮我们返回一个http-foundation/Question.php生成的request实例
            return static::createFromBase(SymfonyRequest::createFromGlobals());
        }

我们通过执行`SymfonyRequest::createFromGlobals()`，然后调用一个`createRequestFromFactory`方法来帮我们生成一个request对象，这里用到了一个static的语法糖:

    private static function createRequestFromFactory(array $query = array(), array $request = array(), array $attributes = array(), array $cookies = array(), array $files = array(), array $server = array(), $content = null)
        {
    //        self::$requestFactory如果有设置的话，是一个闭包，会直接执行，返回一个request的对象，其实在RequestTest中有所调用，返回一个NewRequest，而这个对象继承自Request
            if (self::$requestFactory) {
                $request = call_user_func(self::$requestFactory, $query, $request, $attributes, $cookies, $files, $server, $content);
    
                if (!$request instanceof self) {
                    throw new \LogicException('The Request factory must return an instance of Symfony\Component\HttpFoundation\Request.');
                }
    
                return $request;
            }
    //如果不存在的情况下，会直接执行，new一个对象，不过需要注意的是，这里并非直接new Request，而是用的static，static是php的一个语法糖：静态绑定，比如我现在有另外一个TestRequest继承自Request，如果这里使用的是new static,返回的就是TestRequest的实例了
            return new static($query, $request, $attributes, $cookies, $files, $server, $content);
        }

当然，在生成这个实例的时候，会调用一个初始化的方法，里面没有太复杂的业务逻辑，对应的注释我也写到了[GitHub][4]上。  
需要注意的是，此时生成的request实例是`Symfony\Component\HttpFoundation\Request`下的，而我们最后需要的结果是一个Illuminate\Http\Request的实例。  
所以此时，我们返回的结果需要进一步转换:

    $request = (new static)->duplicate(
                $request->query->all(), $request->request->all(), $request->attributes->all(),
                $request->cookies->all(), $request->files->all(), $request->server->all()
            );

这是createFromBase方法中的一段代码，这里需要说明一下的是，因为createFromBase方法是一个静态方法，所以在这个方法中我们无法使用$this，只能通过`new static`的方式来转换。我们可以看到，此时调用了当前对象的duplicate的方法，而其中诸如`$request->query->all()`方法，都是返回我们之前在生成`Symfony\Component\HttpFoundation\Request`时提到的初始化的方法所绑定在$parameters数组中的内容。  
duplicate是一个非常有意思的方法，他最终会调用到父类的这个方法，这个方法所做的工作就好像鸠占鹊巢，会把之前我们在生成`Symfony\Component\HttpFoundation\Request`是的工作都剽窃，然后重新根据我们制定的内容去生成参数:

    public function duplicate(array $query = null, array $request = null, array $attributes = null, array $cookies = null, array $files = null, array $server = null)
        {
            $dup = clone $this;
            if ($query !== null) {
                $dup->query = new ParameterBag($query);
            }
            if ($request !== null) {
                $dup->request = new ParameterBag($request);
            }
            if ($attributes !== null) {
                $dup->attributes = new ParameterBag($attributes);
            }
            if ($cookies !== null) {
                $dup->cookies = new ParameterBag($cookies);
            }
            if ($files !== null) {
                $dup->files = new FileBag($files);
            }
            if ($server !== null) {
                $dup->server = new ServerBag($server);
                $dup->headers = new HeaderBag($dup->server->getHeaders());
            }
            $dup->languages = null;
            $dup->charsets = null;
            $dup->encodings = null;
            $dup->acceptableContentTypes = null;
            $dup->pathInfo = null;
            $dup->requestUri = null;
            $dup->baseUrl = null;
            $dup->basePath = null;
            $dup->method = null;
            $dup->format = null;
    
            if (!$dup->get('_format') && $this->get('_format')) {
                $dup->attributes->set('_format', $this->get('_format'));
            }
    
            if (!$dup->getRequestFormat(null)) {
                $dup->setRequestFormat($this->getRequestFormat(null));
            }
    
            return $dup;
        }

其实当我们在clone的时候，也会调用到`Symfony\Component\HttpFoundation\Request`的`__clone`魔术方法，所以也就把数据传递了过去:

    public function __clone()
        {
            $this->query = clone $this->query;
            $this->request = clone $this->request;
            $this->attributes = clone $this->attributes;
            $this->cookies = clone $this->cookies;
            $this->files = clone $this->files;
            $this->server = clone $this->server;
            $this->headers = clone $this->headers;
        }

就这样，现在的所返回的$dup对象，也就是我们一直想要的`Illuminate\Http\Request`的实例了，最后，他还会执行一下`getInputSource`方法，主要是根据我们请求方法的不同，把请求数据都存到`$request->request`中。

本文由 [nine][5] 创作，采用 [知识共享署名4.0][6] 国际许可协议进行许可  
本站文章除注明转载/出处外，均为本站原创或翻译，转载前请务必署名  
最后编辑时间为: Jul 3, 2017 at 09:33 am

[0]: http://www.hellonine.top/index.php/category/laravel/
[1]: http://www.hellonine.top/index.php/category/PHP/
[2]: #comments
[3]: http://www.hellonine.top/index.php/archives/6/
[4]: https://github.com/nineyang/laravel_interpretation
[5]: http://www.hellonine.top/index.php/author/1/
[6]: https://creativecommons.org/licenses/by/4.0/
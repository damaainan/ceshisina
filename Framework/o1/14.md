# Laravel HTTP—— RESTFul 风格路由的使用与源码分析 

3个月前 ⋅ 1154 ⋅ 14 ⋅ 0 

## **前言**[#][0]

我们在前面的文章已经讲了整个路由与控制器的源码，我们今天这个文章开始向大家介绍在 laravel 中创建 RESTFul 风格的控制器。

关于什么是RESTFul风格及其规范可参考这篇文章：[理解RESTful架构][1]。

关于 laravel 中 RESTFul 风格控制器的创建简要介绍 ： [HTTP控制器实例教程 —— 创建 RESTFul 风格控制器实现文章增删改查][2]

## **创建 RESTFul 风格控制器**[#][3]

要想在 laravel 中创建 RESTFul 风格控制器，只需要一句：

    Route::resource('post','PostController');

该路由包含了指向多个动作的子路由：

方法 路径 动作 路由名称 GET /post index post.index GET /post/create create post.create POST /post store post.store GET /post/{post} show post.show GET /post/{post}/edit edit post.edit PUT/PATCH /post/{post} update post.update DELETE /post/{post} destroy post.destroy 

这种用法既简单又方便，接下来，我们将会说一下 laravel 为我们提供的更加灵活的用法。

### **前缀 RESTFul 路由**[#][4]

可以为 RESTFul 路由定义前缀：

    $router->resource('prefix/foos', 'FooController');
    
    $this->assertEquals('prefix/foos/{foo}', $routes[3]->uri());

### **多参数 RESTFul 路由**[#][5]

laravel 允许定义拥有多个参数的 RESTFul 路由：

    $router->resource('foos.bars', 'FooController');
    
    $this->assertEquals('foos/{foo}/bars/{bar}', $routes[3]->uri());

### **参数自定义命名**[#][6]

一般来说，RESTFul 路由的参数命名规则是路由单数，符号 - 转为 _，例如下面例子中 bars，和 foo-baz。

    $router->resource('foos', 'FooController');
    $this->assertEquals('foos/{foo}', $routes[3]->uri());
    
    $router->resource('foo-bar.foo-baz', 'FooController', ['only' => ['show']]);
    $this->assertEquals('foo-bar/{foo_bar}/foo-baz/{foo_baz}', $routes[0]->uri());

我们可以利用 parameters 强制这种单数模式：

    $router->resource('foos', 'FooController', ['parameters' => 'singular']);
    $this->assertEquals('foos/{foo}', $routes[3]->uri());

我们也可以利用 singularParameters 来强制：

    ResourceRegistrar::singularParameters(true);
    
    $router->resource('foos', 'FooController', ['parameters' => 'singular']);
    $this->assertEquals('foos/{foo}', $routes[3]->uri());

我们还可以不使用单数，利用 parameters 用自己自定义的名字来定义参数：

    $router->resource('bars.foos.bazs', 'FooController', ['parameters' => ['foos' => 'oof', 'bazs' => 'b']]);
    
    $this->assertEquals('bars/{bar}/foos/{oof}/bazs/{b}', $routes[3]->uri());

同时，我们仍然可以利用 setParameters 函数来自定义参数命名：

    ResourceRegistrar::setParameters(['foos' => 'oof', 'bazs' => 'b']);
    
    $router->resource('bars.foos.bazs', 'FooController');
    $this->assertEquals('bars/{bar}/foos/{oof}/bazs/{b}', $routes[3]->uri());
    

### **RESTFul 路由动词控制**[#][7]

laravel 为 RESTFul 路由生成了两个带有动词的路由： create 、 edit，分别用于加载订单的创建页面与编辑页面，这两个动词 laravel 是允许修改的：

    ResourceRegistrar::verbs([
        'create' => 'ajouter',
        'edit' => 'modifier',
    ]);
    
    $router->resource('foo', 'FooController');
    $routes = $router->getRoutes();
    
    $this->assertEquals('foo/ajouter', $routes->getByName('foo.create')->uri());
    $this->assertEquals('foo/{foo}/modifier', $routes->getByName('foo.edit')->uri());

### **控制器方法约束**[#][8]

一般情况下，我们都会一次性想要上面所生成的七个路由，然而，有时候，我们只需要其中几个，或者不想要其中几个。这时候就可以利用 only 或者 except:

    $router = $this->getRouter();
    $router->resource('foo', 'FooController', ['only' => ['show', 'destroy']]);
    $routes = $router->getRoutes();
    
    $this->assertCount(2, $routes);

    $router = $this->getRouter();
    $router->resource('foo', 'FooController', ['except' => ['show', 'destroy']]);
    $routes = $router->getRoutes();
    
    $this->assertCount(5, $routes);

### **RESTFul 路由名称自定义**[#][9]

RESTFul 路由的每个路由都要自己默认的路由名称，laravel 允许我们对路由名称进行修改：

我们可以用 as 来为路由名称添加前缀：

    $router->resource('foo-bars', 'FooController', ['only' => ['show'], 'as' => 'prefix']);
    
    $this->assertEquals('prefix.foo-bars.show', $routes[0]->getName());

当有多个路由参数的时候，路由参数默认添加到了路由名称中：

    $router->resource('prefix/foo.bar', 'FooController');
    
    $this->assertTrue($router->getRoutes()->hasNamedRoute('foo.bar.index'));

可以利用 names 为单个路由来命名：

    $router->resource('foo', 'FooController', ['names' => [
        'index' => 'foo',
        'show' => 'bar',
    ]]);
    
    $this->assertTrue($router->getRoutes()->hasNamedRoute('foo'));
    $this->assertTrue($router->getRoutes()->hasNamedRoute('bar'));

还可以利用 names 为所有路由来命名：

    $router->resource('foo', 'FooController', ['names' => 'bar']);
    
    $this->assertTrue($router->getRoutes()->hasNamedRoute('bar.index'));

## **RESTFul 路由源码分析**[#][10]

RESTFul 路由的创建工作由类 ResourceRegistrar 负责，这个类为默认为用户创建七个路由，函数方法 register 是创建路由的主函数：

    class ResourceRegistrar
    {
        public function register($name, $controller, array $options = [])
        {
            if (isset($options['parameters']) && ! isset($this->parameters)) {
                $this->parameters = $options['parameters'];
            }
    
            if (Str::contains($name, '/')) {
                $this->prefixedResource($name, $controller, $options);
    
                return;
            }
    
            $base = $this->getResourceWildcard(last(explode('.', $name)));
    
            $defaults = $this->resourceDefaults;
    
            foreach ($this->getResourceMethods($defaults, $options) as $m) {
                $this->{'addResource'.ucfirst($m)}($name, $base, $controller, $options);
            }
        }
    }

这个函数主要流程分为三段：

* 判断是否由前缀
* 获取路由的基础参数
* 添加路由

### **拥有前缀的 RESTFul 路由**[#][11]

如果我们为 RESTFul 路由添加了前缀，那么 laravel 将会以 group 的形式添加路由：

    protected function prefixedResource($name, $controller, array $options)
    {
        list($name, $prefix) = $this->getResourcePrefix($name);
    
        $callback = function ($me) use ($name, $controller, $options) {
            $me->resource($name, $controller, $options);
        };
    
        return $this->router->group(compact('prefix'), $callback);
    }
    
    protected function getResourcePrefix($name)
    {
        $segments = explode('/', $name);
    
        $prefix = implode('/', array_slice($segments, 0, -1));
    
        return [end($segments), $prefix];
    }

### **获取基础 RESTFul 路由参数**[#][12]

在添加各种路由之前，我们需要先获取路由的基础参数，也就是当存在多参数情况下，最后的参数。获取参数后，如果用户有自定义命名，则获取自定义命名：

    public function getResourceWildcard($value)
    {
        if (isset($this->parameters[$value])) {
            $value = $this->parameters[$value];
        } elseif (isset(static::$parameterMap[$value])) {
            $value = static::$parameterMap[$value];
        } elseif ($this->parameters === 'singular' || static::$singularParameters) {
            $value = Str::singular($value);
        }
    
        return str_replace('-', '_', $value);
    }

### **添加各种路由**[#][13]

添加路由主要有三个步骤：

* 计算路由 uri
* 获取路由属性
* 创建路由

    protected function addResourceIndex($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name);
    
        $action = $this->getResourceAction($name, $controller, 'index', $options);
    
        return $this->router->get($uri, $action);
    }

当计算路由 uri 时，由于存在多参数的情况，需要循环计算路由参数：

    public function getResourceUri($resource)
    {
        if (! Str::contains($resource, '.')) {
            return $resource;
        }
    
        $segments = explode('.', $resource);
    
        $uri = $this->getNestedResourceUri($segments);
    
        return str_replace('/{'.$this->getResourceWildcard(end($segments)).'}', '', $uri);
    }
    
    protected function getNestedResourceUri(array $segments)
    {  
        return implode('/', array_map(function ($s) {
            return $s.'/{'.$this->getResourceWildcard($s).'}';
        }, $segments));
    }

当计算路由的属性时，最重要的是获取路由的名字，路由的名字可以是默认，也可以是用户利用 names 或者 as 属性来自定义：

    protected function getResourceAction($resource, $controller, $method, $options)
    {
        $name = $this->getResourceRouteName($resource, $method, $options);
    
        $action = ['as' => $name, 'uses' => $controller.'@'.$method];
    
        if (isset($options['middleware'])) {
            $action['middleware'] = $options['middleware'];
        }
    
        return $action;
    }
    
    protected function getResourceRouteName($resource, $method, $options)
    {
        $name = $resource;
    
        if (isset($options['names'])) {
            if (is_string($options['names'])) {
                $name = $options['names'];
            } elseif (isset($options['names'][$method])) {
                return $options['names'][$method];
            }
        }
    
        $prefix = isset($options['as']) ? $options['as'].'.' : '';
    
        return trim(sprintf('%s%s.%s', $prefix, $name, $method), '.');
    }

值得注意的是，如果单独为某一个方法命名，那么直接回返回命名，而不会受 as 和方法名 'method' 的影响。

[0]: #前言
[1]: http://www.ruanyifeng.com/blog/2011/09/restful.html
[2]: http://laravelacademy.org/post/549.html
[3]: #创建-RESTFul-风格控制器
[4]: #前缀-RESTFul-路由
[5]: #多参数-RESTFul-路由
[6]: #参数自定义命名
[7]: #RESTFul-路由动词控制
[8]: #控制器方法约束
[9]: #RESTFul-路由名称自定义
[10]: #RESTFul-路由源码分析
[11]: #拥有前缀的-RESTFul-路由
[12]: #获取基础-RESTFul-路由参数
[13]: #添加各种路由
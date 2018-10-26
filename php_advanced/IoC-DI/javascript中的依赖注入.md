## [译]javascript中的依赖注入

来源：[http://www.cnblogs.com/pqjwyn/p/9850428.html](http://www.cnblogs.com/pqjwyn/p/9850428.html)

时间 2018-10-25 16:01:00



## 前言

在上文介绍过控制反转之后，本来打算写篇文章介绍下控制反转的常见模式-依赖注入。在翻看资料的时候，发现了一篇好文[Dependency injection in JavaScript][0]，就不自己折腾了，结合自己理解翻译一下，好文共赏。


我喜欢引用这样一句话‘编程是对复杂性的管理’。可能你也听过计算机世界是一个巨大的抽象结构。我们简单的包装东西并重复的生产新的工具。思考那么一下下，我们使用的编程语言都包括内置的功能，这些功能可能是基于其他低级操作的抽象方法，包括我们是用的javascript。

迟早，我们都会需要使用别的开发者开发的抽象功能，也就是我们要依赖其他人的代码。我希望使用没有依赖的模块，显然这是很难实现的。即使你创建了很好的像黑盒一样的组件，但总有个将所有部分合并起来的地方。这就是依赖注入起作用的地方，当前来看，高效管理依赖的能力是迫切需要的，本文总结了原作者对这个问题的看法。

  
## 目标

假设我们有两个模块，一个是发出ajax请求的服务，一个是路由：

```js
var service = function() {
    return { name: 'Service' };
}
var router = function() {
    return { name: 'Router' };
}
```

下面是另一个依赖了上述模块的函数：

```js
var doSomething = function(other) {
    var s = service();
    var r = router();
};
```


为了更有趣一点，该函数需要接受一个参数。当然我们可以使用上面的代码，但是这不太灵活。

如果我们想使用ServiceXML、ServiceJSON，或者我们想要mock一些测试模块，这样我们不能每次都是编辑函数体。为了解决这个现状，首先我们提出将依赖当做参数传给函数，如下：

```js
var doSomething = function(service, router, other) {
    var s = service();
    var r = router();
};
```


这样，我们把需要的模块的具体实例传递过来。然而这样有个新的问题：想一下如果dosomething函数在很多地方被调用，如果有第三个依赖条件，我们不能改变所有的调用doSomething的地方。

举个小栗子：

假如我们有很多地方用到了doSomething：

```js
//a.js
var a = doSomething(service,router,1)
//b.js 
var b = doSomething(service,router,2)
// 假如依赖条件更改了，即doSomething需要第三个依赖，才能正常工作
// 这时候就需要在上面不同文件中修改了，如果文件数量够多，就不合适了。
var doSomething = function(service, router, third,thother) {
    var s = service();
    var r = router();
    //***
};
```

因此，我们需要一个帮助我们来管理依赖的工具。这就是依赖注入器想要解决的问题，先看一下我们想要达到的目标：


* 可以注册依赖
* 注入器应该接受一个函数并且返回一个已经获得需要资源的函数
* 我们不应该写复杂的代码，需要简短优雅的语法
* 注入器应该保持传入函数的作用域
* 被传入的函数应该可以接受自定义参数，不仅仅是被描述的依赖。
  

看起来比较完美的列表就如上了，让我们来尝试实现它。


### requirejs/AMD的方式

大家都可能听说过requirejs，它是很不错的依赖管理方案。

```js
define(['service', 'router'], function(service, router) {       
    // ...
});
```

这种思路是首先声明需要的依赖，然后开始编写函数。这里参数的顺序是很重要的。我们来试试写一个名为injector的模块，可以接受相同语法。

```js
var doSomething = injector.resolve(['service', 'router'], function(service, router, other) {
    expect(service().name).to.be('Service');
    expect(router().name).to.be('Router');
    expect(other).to.be('Other');
});
doSomething("Other");
```

这里稍微停顿一下，解释一下doSomething的函数体，使用expect.js来作为断言库来确保我的代码能像期望那样正常工作。体现了一点点[TDD（测试驱动开发）][1]的开发模式。

下面是我们injector模块的开始，一个单例模式是很好的选择，因此可以在我们应用的不同部分运行的很不错。

```js
var injector = {
    dependencies: {},
    register: function(key, value) {
        this.dependencies[key] = value;
    },
    resolve: function(deps, func, scope) {

    }
}
```

从代码来看，确实是一个很简单的对象。有两个函数和一个作为存储队列的变量。我们需要做的是检查deps依赖数组，并且从dependencies队列中查找答案。剩下的就是调用.apply方法来拼接被传递过来函数的参数。

```js
//处理之后将依赖项当做参数传入给func
resolve: function(deps, func, scope) {
    var args = [];
    //处理依赖，如果依赖队列中不存在对应的依赖模块，显然该依赖不能被调用那么报错，
    for(var i=0; i<deps.length, d=deps[i]; i++) {
        if(this.dependencies[d]) {
            args.push(this.dependencies[d]);
        } else {
            throw new Error('Can\'t resolve ' + d);
        }
    }
    //处理参数，将参数拼接在依赖后面，以便和函数中参数位置对应
    return function() {
        func.apply(scope || {}, args.concat(Array.prototype.slice.call(arguments, 0)));
    }        
}
```


如果scope存在，是可以被有效传递的。Array.prototype.slice.call(arguments, 0)将arguments(类数组)转换成真正的数组。

目前来看很不错的，可以通过测试。当前的问题时，我们必须写两次需要的依赖，并且顺序不可变动，额外的参数只能在最后面。

  
### 反射实现

从维基百科来说，反射是程序在运行时可以检查和修改对象结构和行为的一种能力。简而言之，在js的上下文中，是指读取并且分析对象或者函数的源码。看下开头的doSomething，如果使用doSomething.toString() 可以得到下面的结果。

这种的思路就是在

```js
"function (service, router, other) {
    var s = service();
    var r = router();
}"
```

这种将函数转成字符串的方式赋予我们获取预期参数的能力。并且更重要的是，他们的name。下面是Angular依赖注入的实现方式，我从Angular那拿了点可以获取arguments的正则表达式：

```js
/^function\s*[^\(]*\(\s*([^\)]*)\)/m
```

这样我们可以修改resolve方法了：


#### tip

这里，我将测试例子拿上来应该更好理解一点。

```js
var doSomething = injector.resolve(function(service, other, router) {
    expect(service().name).to.be('Service');
    expect(router().name).to.be('Router');
    expect(other).to.be('Other');
});
doSomething("Other");
```

继续来看我们的实现。

```js
resolve: function() {
    // agrs 传给func的参数数组，包括依赖模块及自定义参数
    var func, deps, scope, args = [], self = this;
    // 获取传入的func，主要是为了下面来拆分字符串
    func = arguments[0];
    // 正则拆分，获取依赖模块的数组
    deps = func.toString().match(/^functions*[^(]*(s*([^)]*))/m)[1].replace(/ /g, '').split(',');
    //待绑定作用域，不存在则不指定
    scope = arguments[1] || {};
    return function() {
        // 将arguments转为数组
        // 即后面再次调用的时候，doSomething("Other");   
        // 这里的Other就是a，用来补充缺失的模块。
        var a = Array.prototype.slice.call(arguments, 0);
        //循环依赖模块数组
        for(var i=0; i<deps.length; i++) {
            var d = deps[i];
            // 依赖队列中模块存在且不为空的话，push进参数数组中。
            // 依赖队列中不存在对应模块的话从a中取第一个元素push进去(shift之后，数组在改变)
            args.push(self.dependencies[d] && d != '' ? self.dependencies[d] : a.shift());
        }
        //依赖当做参数传入
        func.apply(scope || {}, args);
    }        
}
```

使用这个正则来处理函数时，可以得到下面结果：

```js
["function (service, router, other)", "service, router, other"]
```

我们需要的只是第二项，一旦我们清除数组并拆分字符串，我们将会得到依赖数组。主要变化在下面：

```js
var a = Array.prototype.slice.call(arguments, 0);
...
args.push(self.dependencies[d] && d != '' ? self.dependencies[d] : a.shift());
```

这样我们就循环遍历依赖项，如果缺少某些东西，我们可以尝试从arguments对象中获取。幸好，当数组为空的时候shift方法也只是返回undefined而非抛错。所以新版的用法如下：

```js
//不用在前面声明依赖模块了
var doSomething = injector.resolve(function(service, other, router) {
    expect(service().name).to.be('Service');
    expect(router().name).to.be('Router');
    expect(other).to.be('Other');
});
doSomething("Other");
```


这样就不用重复声明了，顺序也可变。我们复制了Angular的魔力。

然而，这并不完美，压缩会破坏我们的逻辑，这是反射注入的一大问题。因为压缩改变了参数的名称所以我们没有能力去解决这些依赖。例如：

```js
// 显然根据key来匹配就是有问题的了
var doSomething=function(e,t,n){var r=e();var i=t()}
```

Angular团队的解决方案如下：

```js
var doSomething = injector.resolve(['service', 'router', function(service, router) {

}]);
```

看起来就和开始的require.js的方式一样了。作者个人不能找到更优的解决方案，为了适应这两种方式。最终方案看起来如下：

```js
var injector = {
    dependencies: {},
    register: function(key, value) {
        this.dependencies[key] = value;
    },
    resolve: function() {
        var func, deps, scope, args = [], self = this;
        // 该种情况是兼容形式，先声明
        if(typeof arguments[0] === 'string') {
            func = arguments[1];
            deps = arguments[0].replace(/ /g, '').split(',');
            scope = arguments[2] || {};
        } else {
            // 反射的第一种方式
            func = arguments[0];
            deps = func.toString().match(/^function\s*[^\(]*\(\s*([^\)]*)\)/m)[1].replace(/ /g, '').split(',');
            scope = arguments[1] || {};
        }
        return function() {
            var a = Array.prototype.slice.call(arguments, 0);
            for(var i=0; i<deps.length; i++) {
                var d = deps[i];
                args.push(self.dependencies[d] && d != '' ? self.dependencies[d] : a.shift());
            }
            func.apply(scope || {}, args);
        }        
    }
}
```

现在resolve接受两或者三个参数，如果是两个就是我们写的第一种了，如果是三个，会将第一个参数解析并填充到deps。下面就是测试例子(我一直认为将这段例子放在前面可能大家更好阅读一些。)：

```js
// 缺失了一项模块other
var doSomething = injector.resolve('router,,service', function(a, b, c) {
    expect(a().name).to.be('Router');
    expect(b).to.be('Other');
    expect(c().name).to.be('Service');
});
// 这里传的Other将会用来拼凑
doSomething("Other");
```

可能会注意到argumets[0]中确实了一项，就是为了测试填充功能的。


### 直接注入作用域

有时候，我们使用第三种的注入方式，它涉及到函数作用域的操作(或者其他名字，this对象)，并不经常使用

```js
var injector = {
    dependencies: {},
    register: function(key, value) {
        this.dependencies[key] = value;
    },
    resolve: function(deps, func, scope) {
        var args = [];
        scope = scope || {};
        for(var i=0; i<deps.length, d=deps[i]; i++) {
            if(this.dependencies[d]) {
                //区别就在这里了，直接将依赖加到scope上
                //这样就可以直接在函数作用域中调用了
                scope[d] = this.dependencies[d];
            } else {
                throw new Error('Can\'t resolve ' + d);
            }
        }
        return function() {
            func.apply(scope || {}, Array.prototype.slice.call(arguments, 0));
        }        
    }
}
```

我们做的就是将依赖加到作用域上，这样的好处是不用再参数里加依赖了，已经是函数作用域的一部分了。

```js
var doSomething = injector.resolve(['service', 'router'], function(other) {
    expect(this.service().name).to.be('Service');
    expect(this.router().name).to.be('Router');
    expect(other).to.be('Other');
});
doSomething("Other");
```


### 结束语


依赖注入是我们所有人都做过的事情中的一种，可能没有意识到罢了。即使没有听过，你也可能用过很多次了。

通过这篇文章对于这个熟悉而又陌生的概念的了解加深了不少，希望能帮助到有需要的同学。最后个人能力有限，翻译有误的地方欢迎大家指出，共同进步。

再次感谢原文作者[原文地址][2]


[0]: http://krasimirtsonev.com/blog/article/Dependency-injection-in-JavaScript
[1]: https://zh.wikipedia.org/wiki/%E6%B5%8B%E8%AF%95%E9%A9%B1%E5%8A%A8%E5%BC%80%E5%8F%91
[2]: http://krasimirtsonev.com/blog/article/Dependency-injection-in-JavaScript
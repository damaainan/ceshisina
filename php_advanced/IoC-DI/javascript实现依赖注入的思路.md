## javascript实现依赖注入的思路

来源：[http://www.ajiehome.com/2018/08/02/jsru-he-shi-xian-di/](http://www.ajiehome.com/2018/08/02/jsru-he-shi-xian-di/)

时间 2018-08-02 14:25:21

 
![][0]
 
作为一个开发人员，你不可避免要使用别的开发者提供的模块。我个人不喜欢依赖第三方模块，但这很难实现。即使你已经有了封装的非常好的组件，你仍然需要能将这些组件完美组合起来的东西。这就是依赖注入的作用。有效地管理依赖关系的能力现在是绝对有必要的。这篇文章总结了我对这个问题和一些解决方案的看法。
 
## 看一个例子
 
假设目前有两个模块: service(实现ajax调用的服务)和router(实现路由控制的模块)。
 
```js
var service = function() {
    return { name: 'Service' };
}
var router = function() {
    return { name: 'Router' };
}
```
 
现在有一个地方需要依赖上面两个模块:
 
```js
var doSomething = function(other) {
    var s = service();
    var r = router();
};
```
 
为了让示例显得更有趣一点，我们为`doSomething`函数传了一个参数。上面的代码完全是可以完成需求，但是缺乏一些弹性。想象一下，如果我们想使用`ServiceXML`或`ServiceJSON`怎么办? 不想每次都去修改函数, 首先想到的是将依赖项作为参数传递给函数：
 
```js
var doSomething = function(service, router, other) {
    var s = service();
    var r = router();
};
```
 
每次函数调用时我们只需要传递我们想要的具体模块(如`ServiceXML`)就可以了。但这带来了一个新问题: 如果我们需要加入第三个依赖项，会怎么样?
 
#### 我们需要的是一种能够为我们做到这一点的工具。这就是依赖注入器要解决的问题。
 
依赖注入解决方案应该实现的目标:
 
 
* 注册依赖项; 
* 接收一个函数，并返回一个函数，以某种方式获得所需的资源; 
* 尽量语法简单; 
* 能够保持函数作用域; 
* 能够支持自定义参数; 
 
 
## RequireJS / AMD
 
你们应该听说过 [RequireJS.][1] 。它提供了一种很好的思路:
 
```js
define(['service', 'router'], function(service, router) {       
    // ...
});
```
 
它的主要原理是首先描述所需的依赖关系，然后编写函数。这里参数的顺序很重要。假设我们编写一个名为`injector`的模块，采用相同的语法实现。
 
```js
var doSomething = injector.resolve(['service', 'router'], function(service, router, other) {
    expect(service().name).to.be('Service');
    expect(router().name).to.be('Router');
    expect(other).to.be('Other');
});
doSomething("Other");
```
 
这里我使用了expect断言库，确保我写的代码是符合我的预期行为的，这是一种TDD测试方法。
 
接下来看`injector`是如何工作的？为保证它在整个应用正常调用，把它设计成单例模式:
 
```js
const injector = {
    dependencies: {},
    register: function(key, value) {
        this.dependencies[key] = value;
    },
    resolve: function(deps, func, scope) {

    }
}
```
 `injector`对象非常简单：两个函数和一个对象(`dependencies`存储依赖)。我们要做的是检查`deps`数组并在`dependencies`变量中查找依赖的模块。其余的只是针对以前的`func`参数调用`.apply`方法。
 
```js
resolve: function(deps, func, scope) {
    var args = [];
    for(var i=0; i<deps.length, d=deps[i]; i++) {
        if(this.dependencies[d]) {
            args.push(this.dependencies[d]);
        } else {
            throw new Error('Can\'t resolve ' + d);
        }
    }
    return function() {
        func.apply(scope || {}, args.concat(Array.prototype.slice.call(arguments, 0)));
    }        
}
```
 `Array.prototype.slice.call(arguments, 0)`将`arguments`转换成一个真正的数组。运行我们的代码，测试用例能够正常跑通, 说明测试通过。
 
这个版本目前存在的问题是：模块依赖项的顺序不能变，而我们额外增加的参数other往往在最后。
 
## 引入反射：Reflection
 
维基百科的解释: Reflection，程序在运行时检查和修改对象的结构和行为的能力。简单地说，在JavaScript上下文中，就是读取对象或函数的源代码并对其进行分析。
 
让我们回到文章一开始的时候对`doSomething`函数的定义， 通过`console.log(doSomething.toString())`得到如下的信息:
 
```
"function (service, router, other) {
    var s = service();
    var r = router();
}"
```
 
通过这个方法，我们得到获取函数预期参数的能力。最关键的是我们得到了参数的名称，大名鼎鼎的`Angular`采用的也是这种方式。 如何得到参数，借鉴了`Angular`内部的正则表达式:
 
```js
/^functions*[^(]*(s*([^)]*))/m
```
 
重新对`resolve`方法修改如下:
 
```js
resolve: function() {
    var func, deps, scope, args = [], self = this;
    func = arguments[0];
    deps = func.toString().match(/^functions*[^(]*(s*([^)]*))/m)[1].replace(/ /g, '').split(',');
    scope = arguments[1] || {};
    return function() {
        var a = Array.prototype.slice.call(arguments, 0);
        for(var i=0; i<deps.length; i++) {
            var d = deps[i];
            args.push(self.dependencies[d] && d != '' ? self.dependencies[d] : a.shift());
        }
        func.apply(scope || {}, args);
    }        
}
```
 
通过正则表达式，我们提取到了`doSomething`的结果：
 
```js
["function (service, router, other)", "service, router, other"]
```
 
我们关心的就是这个数组的第二项，通过替换空格，字符串分割的方式，得到了`dept`数组。
 
接下来的处理非常简单:
 
```js
var a = Array.prototype.slice.call(arguments, 0);
...
args.push(self.dependencies[d] && d != '' ? self.dependencies[d] : a.shift());
```
 
通过dependencies 查找对应的依赖，如果没有找到就使用`arguments`对象。使用`shift`方法的好处就是即使我们的数组为空，会返回`undefined`而不是抛出一个异常。
 
```js
var doSomething = injector.resolve(function(service, other, router) {
    expect(service().name).to.be('Service');
    expect(router().name).to.be('Router');
    expect(other).to.be('Other');
});
doSomething("Other");
```
 
我们发现代码变精简了，最重要的是参数的顺序可以灵活的改变了。我们复制了`Angular`的能力。
 
然后当我们准备把这段代码发布到生产环境的时候，会发现一个严重的问题: 上线之前的代码一般都会经过压缩处理，由于改变了参数名称，会影响程序对依赖的处理。例如我们的`doSomething`会处理成：
 
```js
var doSomething=function(e,t,n){var r=e();var i=t()}
```
 `Angular`的解决方案是:
 
```js
var doSomething = injector.resolve(['service', 'router', function(service, router) {
    ...
}]);
```
 
很像文章一开始的写法。最终我们需要将两种方案结合起来：
 
```js
var injector = {
    dependencies: {},
    register: function(key, value) {
        this.dependencies[key] = value;
    },
    resolve: function() {
        var func, deps, scope, args = [], self = this;
        if(typeof arguments[0] === 'string') {
            func = arguments[1];
            deps = arguments[0].replace(/ /g, '').split(',');
            scope = arguments[2] || {};
        } else {
            func = arguments[0];
            deps = func.toString().match(/^functions*[^(]*(s*([^)]*))/m)[1].replace(/ /g, '').split(',');
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
 
## 注入作用域
 
有时我使用第三个变种的注入。它涉及的操作函数的作用域。所以，它不适用于大多数情况，所以我单独拿出来讨论。
 
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
                scope[d] = this.dependencies[d];
            } else {
                throw new Error('Can't resolve ' + d);
            }
        }
        return function() {
            func.apply(scope || {}, Array.prototype.slice.call(arguments, 0));
        }        
    }
}
```
 
我们要做的是把所有依赖项注入到执行函数的作用域中。这么做的好处是, 依赖项不需要通过参数的形式传递:
 
```js
var doSomething = injector.resolve(['service', 'router'], function(other) {
    expect(this.service().name).to.be('Service');
    expect(this.router().name).to.be('Router');
    expect(other).to.be('Other');
});
doSomething("Other");
```
 
## 结束
 
依赖注入是一个我们平常都在做, 却可能从未认真思考过。即使你现在还不知道这个词, 你可能项目中使用了无数次。希望这篇文章能让你更好的理解它的实现原理。
 


[1]: http://requirejs.org/
[0]: https://img1.tuicool.com/aa6FZz2.jpg
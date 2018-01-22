## [读懂 SOLID 的「依赖倒置」原则](https://segmentfault.com/a/1190000012929864)


> 这是理解 SOLID 原则中，关于** 依赖倒置** 原则如何帮助我们编写低耦合和可测试代码的第一篇文章。

## 写在前头

当我们在读书，或者在和一些别的开发者聊天的时候，可能会谈及或者听到术语SOILD。在这些讨论中，一些人会提及它的重要性，以及一个理想中的系统，应当包含它所包含的5条原则的特性。

我们在每次的工作中，你可能没有那么多时间思考关于**架构**这个比较大的概念，或者在有限的时间内或督促下，你也没有办法实践一些好的设计理念。

但是，这些原则存在的意义不是让我们“跳过”它们。软件工程师应当将这些原则应用到他们的开发工作中。所以，在你每一次敲代码的时候，如何能够正确的将这些原则付诸于行，才是真正的问题所在。如果可以那样的话，你的代码会变得更优雅。

SOLID原则是由5个基本的原则构成的。这些概念会帮助创造更好（或者说更健壮）的软件架构。这些原则包含（SOLID是这5个原则的开头字母组成的缩略词）：

* S stands for SRP (Single responsibility principle)：单一职能原则
* O stands for OCP (Open closed principle)：开闭原则
* L stands for LSP (Liskov substitution principle)：里氏替换原则
* I stand for ISP ( Interface segregation principle)：接口隔离原则
* D stands for DIP ( Dependency inversion principle)：依赖倒置原则

起初这些原则是[Robert C. Martin][0]在1990年提出的，遵循这些原则可以帮助我们更好的构建，低耦合、高内聚的软件架构，同时能够真正的对现实中的业务逻辑进行恰到好处的封装。

不过这些原则并不会使一个差劲的程序员转变为一个优秀的程序员。这些法则取决于你如何应用它们，如果你是很随意的应用它们，那等同于你并没有使用它们一样。

关于原则和模式的知识能够帮助你决定在何时何地正确的使用它们。尽管这些原则仅仅是启示性的，它们是常见问题的常规解决方案。实践中，这些原则的正确性已经被证实了很多次，所以它们应当成为一种常识。

## 依赖倒置原则是什么

* 高级模块不应当依赖于低级模块。它们都应当依赖于抽象。
* 抽象不应当依赖于实现，实现应当依赖于抽象。

这两句话的意思是什么呢？

一方面，你会抽象一些东西。在软件工程和计算机科学中，抽象是一种关于规划计算机系统中的复杂性的技术。它的工作原理一般是在一个人与系统交互的复杂环境中，隐藏当前级别下的更复杂的实现细节，同时它的范围很广，常常会覆盖多个子系统。这样，当我们在与一个以高级层面作为抽象的系统协作时，我们仅仅需要在意，我们能做什么，而不是我们**如何**做。

另外，你会针对你的抽象，有一写低级别的模块或者具体实现逻辑。这些东西与抽象是相反的。它们是被用于解决某些特定问题所编写的代码。它们的作用域仅仅在某个单元和子系统中。比如，建立一个与MySQL数据库的连接就是一个低级别的实现逻辑，因为它与某个特定的技术领域所绑定。

现在仔细读这两句话，我们能够得到什么暗示呢？

依赖倒置原则存在的真正意义是指，我们需要将一些对象解耦，它们的耦合关系需要达到当一个对象依赖的对象作出改变时，对象本身不需要更改任何代码。

这样的架构可以实现一种松耦合的状态的系统，因为系统中所有的组件，彼此之间都了解很少或者不需要了解系统中其余组件的具体定义和实现细节。它同时实现了一种可测试和可替换的系统架构，因为在松耦合的系统中，任何组件都可以被提供相同服务的组件所替换。

但是相反的，依赖倒置也有一些缺点，就是你需要一个用于处理依赖倒置逻辑的容器，同时，你还需要配置它。容器通常需要具备能够在系统中注入服务，这些服务需要具备正确的作用域和参数，还应当被注入正确的执行上下文中。

## 以提供Websocket连接服务为例子

举个例子，我们可以在这个例子中学到更多关于依赖倒置的知识，我们将使用Inversify.js作为依赖倒置的容器，通过这个依赖倒置容器，我们可以看看如何针对提供Websocket连接服务的业务场景，提供服务。

比如，我们有一个web服务器提供WebSockets连接服务，同时客户端想要连接服务器，同时接受更新的通知。当前我们有若干种解决方案来提供一个WebSocket服务，比如说Socket.io、Socks或者使用浏览器提供的关于原生的WebSocket接口。每一套解决方案，都提供不同的接口和方法供我们调用，那么问题来了，我们是否可以在一个接口中，将所有的解决方案都抽象成一个提供WebSocket连接服务的提供者？这样，我们就可以根据我们的实际需求，使用不同的WebSocket服务提供者。

首先，我们来定义我们的接口：

    export interface WebSocketConfiguration {
      uri: string;
      options?: Object;
    }
    export interface SocketFactory {
      createSocket(configuration: WebSocketConfiguration): any;
    }

注意在接口中，我们没有提供任何的实现细节，因此它既是我们所拥有的**抽象**。

接下来，如果我们想要一个提供Socket.io服务工厂：

    import {Manager} from 'socket.io-client';
    
    class SocketIOFactory implements SocketFactory {
      createSocket(configuration: WebSocketConfiguration): any {
        return new Manager(configuration.uri, configuration.opts);
      }
    }

这里已经包含了一些具体的实现细节，因此它不再是抽象，因为它声明了一个从Socket.io库中导入的Manager对象，它是我们的具体实现细节。

我们可以通过实现SocketFactory接口，来增加若干工厂类，只要我们实现这个接口即可。

我们在提供一个关于客户端连接实例的抽象：

    export interface SocketClient {
      connect(configuration: WebSocketConfiguration): Promise<any>;
      close(): Promise<any>;
      emit(event: string, ...args: any[]): Promise<any>;
      on(event: string, fn: Function): Promise<any>;
    }

然后再提供一些实现细节：

    class WebSocketClient implements SocketClient {
      private socketFactory: SocketFactory;
      private socket: any;
      public constructor(webSocketFactory: SocketFactory) {
        this.socketFactory = webSocketFactory;
      }
      public connect(config: WebSocketConfiguration): Promise<any> {
        if (!this.socket) {
          this.socket = this.socketFactory.createSocket(config);
        }
        return new Promise<any>((resolve, reject) => {
          this.socket.on('connect', () => resolve());
          this.socket.on('connect_error', (error: Error) => reject(error));
        });
      }
      public emit(event: string, ...args: any[]): Promise<any> {
        return new Promise<string | Object>((resolve, reject) => {
          if (!this.socket) {
            return reject('No socket connection.');
          }
          return this.socket.emit(event, args, (response: any) => {
            if (response.error) {
              return reject(response.error);
            }
            return resolve();
          });
        });
      }
      public on(event: string, fn: Function): Promise<any> {
        return new Promise<any>((resolve, reject) => {
          if (!this.socket) {
            return reject('No socket connection.');
          }
          this.socket.on(event, fn);
          resolve();
        });
      }
      public close(): Promise<any> {
        return new Promise<any>((resolve) => {
          this.socket.close(() => {
            this.socket = ;
            resolve();
          });
        });
      }
    }

值得注意的是，这里我们在构造函数中，传入了一个类型是SocketFactory的参数，这是为了满足关于依赖倒置原则的第一条规则。对于第二条规则，我们需要一种方式来提供这个不需要了解内部实现细节的、可替换的、易于配置的参数。

这也是为什么我们要使用Inversify这个库的原因，我们来加入一些额外的代码和注解（装饰器）：

    import {injectable} from 'inversify';
    const webSocketFactoryType: symbol = Symbol('WebSocketFactory');
    const webSocketClientType: symbol = Symbol('WebSocketClient');
    let TYPES: any = {
        WebSocketFactory: webSocketFactoryType,
        WebSocketClient: webSocketClientType
    };
    
    @injectable()
    class SocketIOFactory implements SocketFactory {...}
    ...
    @injectable()
    class WebSocketClient implements SocketClient {
    public constructor(@inject(TYPES.WebSocketFactory) webSocketFactory: SocketFactory) {
      this.socketFactory = webSocketFactory;
    }

这些注释（装饰器）仅仅会在代码运行时，在如何提供这些组件实例时，提供一些元数据，接下来我们仅仅需要创建一个依赖倒置容器，并将所有的对象按正确的类型绑定起来，如下：

    import {Container} from 'inversify';
    import 'reflect-metadata';
    import {TYPES, SocketClient, SocketFactory, SocketIOFactory, WebSocketClient} from '@web/app';
    const provider = new Container({defaultScope: 'Singleton'});
    // Bindings
    provider.bind<SocketClient>(TYPES.WebSocketClient).to(WebSocketClient);
    provider.bind<SocketFactory>(TYPES.WebSocketFactory).to(SocketIOFactory);
    export default provider;

让我们来看看我们如何使用我们提供连接服务的客户端实例：

    var socketClient = provider.get<SocketClient>(TYPES.WebSocketClient);

当然，使用Inversify可以提供一些更简单易用的绑定，可以通过浏览它的网站来了解。

## 译者注

一般说到依赖倒置原则，往往第一个想到的术语即是依赖注入，这种在各个技术栈都有应用，之后又会马上想到spring、ng等前后端框架。

我们确实是通过使用这些框架熟知这个概念的，但是如果你仔细想想的话，是否还有其他的一些场景也使用了类似的概念呢？

比如：

* 一些使用插件和中间件的框架，如express、redux
* js中this的动态绑定
* js中的回调函数

也许有的人会不同意我的观点，会说依赖注入一般都是面向类和接口来讲的，这确实有一定的道理，但是我认为没有必要局限在一种固定的模式中去理解依赖倒置，毕竟它是一种思想，一种模式，在js中，所有的东西都是动态的，函数是一等公民，是对象，那么把这些与依赖倒置原则联系起来，完全也讲的通。我们真正关心的是核心问题是如何**解耦**，把更多的注意力投入的真正的业务逻辑中去。

[0]: https://en.wikipedia.org/wiki/Robert_Cecil_Martin
## 读懂 SOLID 的「单一职责」原则

来源：[https://segmentfault.com/a/1190000013100807](https://segmentfault.com/a/1190000013100807)

这是理解`SOLID`原则中，关于 **`单一职责原则`** 如何帮助我们编写低耦合和高内聚的第二篇文章。
## 单一职责原则是什么

之前的第一篇[文章][0]阐述了 **`依赖倒置原则（DIP）`** 能够使我们编写的代码变得低耦合，同时具有很好的可测试性，接下来我们来简单了解下单一职责原则的基本概念：

Every module or class should have responsibility over a single part of the functionality provided by the software, and that responsibility should be entirely encapsulated by the class.每一个模块或者类所对应的职责，应对应系统若干功能中的某个单一部分，同时关于该职责的封装都应当通过这个类来完成。


往简单来讲：

A class or module should have one, and only one, reason to be changed.一个类或者模块应当用于单一的，并且唯一的 **`缘由`** 被更改。


如果仅仅通过这两句话去理解, 一个类或者模块如果如果越简单（具有单一职责），那么这个类或者模块就越容易被更改是有一些困难的。为了便于我们理解整个概念，我们将分别从三个不同的角度来分析这句话，这三个角度是：


* Single: 单一
* Responsibility: 职责
* Change: 改变


## 什么是`单一`

Only one; not one of several.唯一的，而不是多个中的某个。

Synonyms: one, one only, sole, lone, solitary, isolated, by itself.

同义词：一，仅有的一个，唯一，独个，独自存在的，孤立的，仅自己。


**`单一`** 意味着某些工作是独立的。比如，在类中，类方法仅完成某家独立的事情，而不是两件，如下：

```js
class UserComponent { 
  // 这是第一件事情，获取用户详情数据
  getUserInfo(id) {
    this.api.getUserInfo(id).then(saveToState)
  }

  // 这是第二件事情，渲染视图的逻辑
  render() {
    const { userInfo } = this.state;
    return 
    

  * Name: { userInfo.name }
  * Surname: { userInfo.surname }
  * Email: { userInfo.email }
      

    

  }
}
```

看了上面的代码，你可能很快就会联想到，这些代码基本存在于所有的`React`组件中。

确实，对于一些小型的项目或者演示型项目，这样编写代码不会产生太大的问题。但是如果在大型或者复杂度很高的项目中，仍然按照这样的风格，则是一件比较糟糕的事情，因为一个组件往往做了它本不应当做的事情（承担了过多的职责）。

这样会带来什么坏处呢？比如对于以上的`api`服务，在将来的某天你做出了一些修改，增加了一些额外的逻辑，那么为了使代码能够正常工作，你至少需要修改项目中的两个地方以适应这个修改，一处修改是在`API`服务中，而另一处则在你的组件中。如果进一步思考的，我们会发现，修改次数与在项目直接使用`API`服务的次数成正比，如果项目足够复杂，足够大，一处简单的逻辑修改，就需要做出一次贯穿整个系统的适配工作。

那么我们如果避免这种情况的发生呢？很简单，我们仅仅需要将 **`关于用户详情数据`** 的逻辑提升到调用层，在上面的例子中，我们应当使用`React.component.prop`来接受用户详情数据。这样，`UserComponent`组件的工作不再与如何获取用户详情数据的逻辑耦合，从而变得 **`单一`** 。

对于鉴别什么是单一，什么不是单一，有很多不同的方式。一般来说，只需要牢记，让你的代码尽可能的少的去了解它已经做的工作。（译者注：我理解意思应当是，应当尽可能的让已有的类或者方法变得简单、轻量，不需要所有事情都亲自为之）

总之，不要让你的对象成为 **`上帝对象`** 。

A God Object aka an Object that knows everything and does everything.上帝对象，一个知道一切事情，完成一切事情的对象。

In object-oriented programming, a God object is an object that knows too much or does too much. The God object is an example of an anti-pattern.

在面向对象编程中，上帝对象指一个了解太情或者做太多事情的对象。上帝对象是反模式的一个典型。


## 什么是`职责`

职责指软件系统中，每一个指派给特定方法、类、包和模块所完成的工作或者动作。

Too much responsibility leads to coupling.太多的职责导致耦合。


**`耦合性`** 代表一个系统中某个部分对系统中另一个部分的了解程度。举个例子，如果一段客户端代码在调用`class A`的过程中，必须要先了解有关`class B`的细节，那么我们说`A`和`B`耦合在了一起。通常来说，这是一件糟糕的事情。因为它会使针对系统本身的变更复杂化，同时会在长期越来越糟。

为了使一个系统到达适当的耦合度，我们需要在以下三个方面做出调整


* 组件的内聚性
* 如何测量每个组件的预期任务
* 组件如何专注于任务本身


低内聚性的组件在完成任务时，和它们本身的职责关联并不紧密。比如，我们现在有一个`User`类，这个类中我们保存了一些基本信息：

```js
class User {
  public age;  
  public name;
  public slug;
  public email;
}
```

对于属性本身，如果对于每个属性声明一些`getter`或者`setter`方法是没什么问题的。但是如果我们加一些别的方法，比如：

```js
class User {
  public age;  
  public name;
  public slug;
  public email;
  // 我们为什么要有以下这些方法？
  checkAge();
  validateEmail();
  slugifyName();
}
```

对于`checkAge`、`validateEmail`、`slugifyName`的职责，与`User`class本身关系并不紧密，因此就会这些方法就会使`User`的内聚性变低。

仔细思考的话，这些方法的职责和校验和格式化用户信息的关系更紧密，因此，它们应当从`User`中被抽离出来，放入到另一个独立的`UserFieldValidation`类中，比如：

```js
class User {
  public age;  
  public name;
  public slug;
  public email;
}

class UserFieldValidation {
  checkAge();
  validateEmail();
  slugifyName();
}
```
## 什么是`变更`

变更指对于已存在代码的修改或者改变。

那么问题来了，什么原因迫使我们需要对源码进行变更？从众多过期的软件系统的历史数据的研究来看，大体有三方面原因促使我们需要作出变更：


* 增加新功能
* 修复缺陷或者bug
* 重构代码以适配将来作出的变更


做为一个程序员，我们天天不都在做这三件事情吗？让我们来用一个例子完整的看一下什么是变更，比方说我们完成了一个组件，现在这个组件性能非常好，而且可读性也非常好，也许是你整个职业生涯中写的最好的一个组件了，所以我们给它一个炫酷的名字叫作`SuperDuper`（译者注：这个名字的意思是 **`超级大骗子`** ）

```js
class SuperDuper {
  makeThingsFastAndEasy() {
    // Super readable and efficient code
  }
}
```

之后过了一段时间，在某一天，你的经理要求你增加一个新功能，比如说去调用别的`class`中的每个函数，从而可以使当前这个组件完成更多的工作。你决定将这个类以参数的形式传入构造方法，并在你的方法调用它。

这个需求很简单，只需要增加一行调用的代码即可，然后你做了以下 **`变更(增加新功能)`** ：

```js
class SuperDuper {
  constructor(notDuper: NotSoDuper) {
    this.notDuper = notDuper
  }
  makeThingsFastAndEasy() {
     // Super readable and efficient code
    this.notDuper.invokeSomeMethod()
  }
}
```

好了，之后你针对你做的变更代码运行了单元测试，然后你突然发现这条简单的代码使`100`多条的测试用例失败了。具体原因是因为在调用`notDuper`方法之前，你需要针对一些额外的业务逻辑增加条件判断来决定是否调用它。

于是你针对这个问题又进行了一次 **`变更(修复缺陷或者bug)`** ，或许还会针对一些别的边界条件进行一些额外的修复和改动：

```js
class SuperDuper {
  constructor(notDuper: NotSoDuper) {
    this.notDuper = notDuper
  }
  makeThingsFastAndEasy() {
     // Super readable and efficient code
    
    if (someCondition) {
      this.notDuper.invokeSomeMethod()
    } else {
      this.callInternalMethod()
    }
  }
}
```

又过了一段时间，因为这个`SuperDuper`毕竟是你职业生涯完成的最棒的类，但是当前调用`noDuper`的方法实在是有点不够逼格，于是你决定引入事件驱动的理念来达到不在`SuperDuper`内部直接调用`noDuper`方法的目的。

这次实际是对已经代码的一次重构工作，你引入了事件驱动模型，并对已有的代码做出了 **`变更(重构代码以适配将来作出的变更)`** :

```js
class SuperDuper {
 
  makeThingsFastAndEasy() {
     // Super readable and efficient code
     ...
     dispatcher.send(actionForTheNotDuper(payload)) // Send a signal
  }
}
```

现在再来看我们的`SuperDuper`类，已经和最原始的样子完全不一样了，因为你必须针对新的需求、存在的缺陷和bug或者适配新的软件架构而做出变更。

因此为了便于我们做出变更，在代码的组织方式上，我们需要用心，这样才会使我们在做出变更时更加容易。
## 如何才能使代码贴近这些原则

很简单，只需要牢记，使代码保持足够简单。

Gather together the things that change for the same reasons. Separate those things that change for different reasons.将由于相同原因而做出改变的东西聚集在一起，将由于不同原因而做出改变的东西彼此分离。


### 孤立变化

对于所编写的做出变更的代码，你需要仔细的检查它们，无论是从整体检查，还是有逻辑的分而治之，都可以达到孤立变化的目的。你需要更多的了解你所编写的代码，比如，为什么这样写，代码到底做了什么等等，并且，对于一些特别长的方法和类要格外关注。

Big is bad, small is good…大即是坏，小即是好。


### 追踪依赖

对于一个类，检查它的构造方法是否包含了太多的参数，因为每一个参数都作为这个类的依赖存在，同时这些参数也拥有自身的依赖。如果可能的话，使用`DI`机制来动态的注入它们。

Use Dependency Injection使用依赖注入


### 追踪方法参数

对于一个方法，检查它是否包含了太多参数，一般来讲，一个方法的参数个数往往代表了其内部所实现的职能。

同时，在方法命名上也投入一精力，尽可能地使方法名保持简单，它将帮助你在重构代码时，更好的达到单一职责。长的函数名称往往意味着其内部有糟糕的味道。

Name things descriptively描述性命名。


### 尽早重构

尽可能早的重构代码，当你看到一些代码可以以更简明的方式进行时，重构它。这将帮助你在项目进行的整个周期不断的整理代码以便于更好的重构。

Refactor to Design Patterns按设计模式重构代码


### 善于做出改变

最后，在需要做出改变时，果断地去做。当然这些改变会使系统的耦合性更低，内聚性更高，而不是往相反的方向，这样你的代码会一直建立在这些原则之上。

Introduce change where it matters. Keep things simple but not simpler.在重要的地方介绍改变。保持事情的简单性，但不是一味追求简单。


## 译者注

单一职责原则其实在我们日常工作中经常会接触到，比方说


* 我们经常会听到`DIY（dont repeat yourself）`原则，其本身就是单一职责的一个缩影，为了达到`DIY`，对于代码中的一些通用方法，我们经常会抽离到独立的`utils`目录甚至编写为独立的工具函数库, 比如`lodash`和`ramda`等等
* `OAOO`, 指`Once And Only Once`, 原则本身的含义可以自行搜索，实际工作中我们对于相同只能模块的代码应当尽可能去在抽象层合并它们，提供抽象类，之后通过继承的方式来满足不同的需求
* 我们都会很熟悉`单例模式`这个模式，但在使用时一定要小心，因为本质上单例模式与单一职责原则相悖，在实践中一定要具体情况具体分析。同时也不要过度优化，就如同文章中最后一部分提及的，我们要保证一件事情的简单性，但不是一味地为了简单而简单。
* 前端的技术栈中，redux对于数据流层的架构思想，便充分体现了单一职责原则的重要性，`action`作为对具体行为的抽象,`store`用来描述应用的状态，`reducer`作为针对不同行为如何对store作出修改的抽象。
* react中经常提及的`木偶组件(dump component)`其实和文章中第一部分的例子如出一辙
* `工厂模式`和`命令模式`也一定程度体现了单一职责原则，前者对于作为生产者存在并不需要关心消费者如何消费对象实例，后者以命令的方式封装功能本身就是单一职责原则的体现。


我能够想到的就这么多，写的比较乱，抛砖引玉，如有错误，还望指正。

[0]: https://segmentfault.com/a/1190000012929864
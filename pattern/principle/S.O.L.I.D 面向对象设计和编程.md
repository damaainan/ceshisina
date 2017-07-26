# S.O.L.I.D 面向对象设计和编程（OOD&OOP）笔记

4个月前 ⋅ 1978 ⋅ 48 ⋅ 10 

SOLID是 **面向对象编程** 和 **面试对象设计** 的五个基本原则，应用这五个原则能创建一个易于维护和扩展的软件系统。SOLID可以指导代码重构和在迭代的过程中进行代码清扫，以使得软件源代码清晰可读和具有良好的扩展性。在测试驱动开发中是典型应用场景，并且也是敏捷开发和自适应软件开发基本原则的重要组成部分。

首字母-简写（全称）|  指代 | 概念
-|-|-
S-SRP(Single Responsibility Principle) | 单一功能原则 | 对象应该仅具有一种单一功能
O-OCP(Opened Closed Principle) | 开闭原则 |   软件应该是对于扩展开放的，但对于修改封闭的
L-LSP(Liscov Substitution Principle) |   里氏替换原则 | 程序中的对象应该是可以在不改变程序正确性的前提下被他的子类所替换
I-ISP(Interface Segregation Principle) | 接口隔离原则 | 多个特定客户端接口要好于一个宽泛用途的接口
D-DIP(Dependency Inversion Principle)  | 依赖反转原则 | 一个方法应该遵从「依赖于抽象而不是一个实例」

### 单一功能原则（S）

![file][1]

  
翻译：你可以这样干，并不是说你应该这样干

**引起类变化的因素永远不要多于一个，也就是说一个类有且只有一个职责。**

如果一个类包含多个职责，代码会变得耦合，难以维护和集中管理。比如我们利用 PHP 的 composer 去定义一个自动加载文件：

    "autoload": {
            "files": [
                "app/Helpers.php"
            ]
        },

大家喜欢把一些全局函数定义在这个文件中，如果项目较小或维护者只有几个的时候，这个文件维护还是较为方便的，但项目一旦变大，有大量的全局函数写到这个文件中就会变得臃肿难以维护。

又比如，在 Laravel 框架的模型中，我们既定义了数表关联关系，又定义了服务器，修改器，还将部分数据访问逻辑写在模型中，模型到最后就会很臃肿而且难以维护。

通过分拆，把相同功能的函数及功能放在一起，使得同一类进行高内聚，可以更好的进行这些代码的集中管理和维护。全局函数分拆可以通过将不同功能的函数放入不同的文件中，然后在 Helpers.php 引入解决，而模型分拆，可以将修改器，访问器定义在 trait 中然后在模型中 use 分拆（PHP 5.4以上版本），数据访问逻辑可以通过 Repository 设计模式进行与模型分离。

如果你不想做出下面这把锤子，那就重视这个问题吧。

![file][2]

### 开闭原则（O）

![file][4]

  
翻译：开胸手术时不需要穿上一件外套

**软件实体（类，模块，函数等等）应当对扩展开放，对修改闭合。**

这个是面向对象编程原则中最为抽象、最难理解的。「对扩展开放」指的是设计类时要考虑到新需求提出是类可以增加新功能，「对修改关闭」指的是一旦一个类开发完成，除了修正 BUG 就不要再去修改它。

这个原则前后两部似乎是冲突的，但是如果正确地设计类和它们的依赖关系，就可以增加功能而不修改已有的源代码。

通常可以通过依赖关系抽象实现开闭原则，比如 interface（接口） 或 abstract（抽象类）而不是具体类，通过创建新的类实现它们来增加功能。

这个原则能减少改出来的 BUG 出现，而且增加软件本身的灵活性。

比如支付功能，不遵守这个原则的话你可能会写出这样的代码:

```php
    public function payInit($payType){
        $payment = null;
        if(true == $payType){
            // 微信支付
            $payment = acceptWechat($total);
        }else{
            // 支付宝支付
            $payment = acceptAlipay($total);
        }
        return $payment;
    }
```

以上的代码中初始化了两种支付方式，当业务增长时要增加信用卡支付只能去修改这个方法增加 elseif 或者改为 switch 而且因为忽视了业务的增长情况，传入参数是一个 Bool 值，不能适用这种变化，因此需要更改调用的传值，这种修改只要一不小心就会改出 BUG 来。

更好的解决方案是：

```php
    interface PaymentMethod{ public function accept($total) }
    
    public function checkOut(PaymentMethod $pm, $total){
        return $pm->accept($total);
    }
```

这样在实现一个新的支付渠道时，只要实现 PaymentMethod 接口就可以创建一个新的支付方式，在调用时将实现接口具体类的实例传入到 checkOut 中就可以得到不同支付渠道付款的实例，而不用每新增一个支付渠道就去修改原来的代码。

### 里氏替换原则（L）

![file][6]

  
翻译：如果它看上去像一只鸭子，并且像鸭子一样嘎嘎叫，但是需要电池-你可能错误的抽象了

**当一个子类的实例应该能够替换任何其父类的实例时，它们之间才具有IS-A关系**

里氏替换原则适用于继承层次结构，指设计类时客户端依赖的父类可以被子类替换，而客户端无须了解这个变化。

一个违反LSP的典型例子是 Square（正方形） 类派生于 Rectangle（长方形） 类。Square 类总是假定宽度与高度相等。如果一个正方形对象用于期望一个长方形的上下文中，可能会出现意外行为，因为一个正方形的宽高不能(或者说不应该)被独立修改。

* 如果没有 LSP，类继承就会混乱；
* 如果子类作为一个参数传递给方法，将会出现未知行为；
* 如果没有 LSP，适用与基类的单元测试将不能成功用于测试子类；

若违反 LSP 进行设计，将导致不明确的行为产生，不明确也意味着它在开发过程中运行良好，但生产环境下会出现偶发 BUG，我们不得不去查阅上百兆的日志找出错误发生在什么地方。

### 接口隔离原则（I）

![file][8]

  
翻译：我需要食物，我想吃（食物，食物)，不要去点亮枝状大烛台或者布置餐桌。

**不要强迫客户端（泛指调用者）去依赖那些他们不使用的接口**

当我们使用非内聚的接口时，ISP 原则指导我们创建多个较小的高内聚接口。

比如我们创建一个鸟类接口 Bird，这个接口中包括了鸟类的很多行为，其中有一个行为是飞行方法 Fly()，但是此时我们要创建一个 Ostrich（鸵鸟）类，那么还是需要实现不必要的 Fly() 方法，此时这个臃肿的接口就应该拆成 Bird 接口和 FlyingBird 接口，Ostrich 类只需去实现 Bird 接口就可以了，在这个接口里没有 Fly() 这个方法，而要创建一个 KingFisher（翠鸟） 类就去实现 FlyingBird，那么当你要创建一个企鹅类，你觉得你应该去实现那个接口呢？

上面这个例子是单一接口实现，也许你觉得太简单了，而且实际业务中无法用的，我们为什么要没事干去创建上面鸟类接口并且去实现它呢，那么我们来接下来看一个更贴近实际业务的例子吧。

想象一个 ATM 取款机，通过屏幕显示我们想要的不同信息，我们现在要为取款机添加一个仅在取款功能界面才出现的信息，比如「ATM机将在您取款时收取一些费用，你同意吗？」。这时你有一个 Messenger 接口，你也许会直接给 Messenger 接口添加一个方法，然后去实现它。这时你就违反了 OCP 原则，代码开始腐败！因为所有实现 Messenger 接口的实现类都需要进行修改实现这个新添加的方法。但我们仅仅需要在取款界面才具有这个方法。

    interface Messenger {
        askForCard();
        tellInvalidCard();
        askForPin();
        tellInvalidPin();
        tellCardWasSiezed();
        askForAccount();
        tellNotEnoughMoneyInAccount();
        tellAmountDeposited();
        tellBalance();
    }

根据 ISP 原则，我们需要将 Messenger 接口进行分切，不同的 ATM 功能依赖于分离后的 Messenger

```php
    interface LoginMessenger {
      askForCard();
      tellInvalidCard();
      askForPin();
      tellInvalidPin(); 
    }
    
    interface WithdrawalMessenger {
      tellNotEnoughMoneyInAccount();
      askForFeeConfirmation();
    }
    
    publc class EnglishMessenger implements LoginMessenger, WithdrawalMessenger {
      ...   
    }
```

### 依赖反转原则（D）

![file][10]

翻译：你会将一个灯直接焊接到墙上的电路吗？

**1.高层模块不应该依赖底层模块，两者都应该依赖其抽象**  
**2.抽象不应该依赖于细节，细节应该依赖于抽象**

```php
    interface Reader { getchar(); }
    interface Writer { putchar($c);}
    
    class CharCopier {
    
      public function copy(Reader reader, Writer writer) {
        $c;
        while ((c = reader.getchar()) != EOF) {
          writer.putchar();
        }
      }
    }
    
    public Keyboard implements Reader {...}
    public Printer implements Writer {...}
```
以上代码片段是一个例子，一个程序依赖于 Reader 和 Writer 接口，Keyboard 和 Printer 作为依赖于这些抽象的细节实现了这些接口，CharCopier 是依赖于 Reader 和 Writer 实现类的底层细节，可以传入任何 Reader 和 Writer 的实现进行正常工作。 

### 总结

S.O.L.I.D 原则应该是你工具箱里很有价值的工具，在设计下一个功能或者应用时它们就会出现在你的脑海中，下面引用 Bob 大叔的总结：

- |  - |  -
-|-|-
SRP | 单一职责原则 | 一个类有且只有一个更改的原因
OCP | 开闭原则 |   能够不更改类而扩展类的行为
LSP | 里氏替换原则 | 派生类可以替换基类使用
ISP | 接口隔离原则 | 使用客户端特定的细粒度接口
DIP | 依赖反转原则 | 依赖抽象而不是具体实现

将这些原则应用在你的项目中，创建一个优秀的应用，不要让你的代码腐败。

来自我的博客：[麦索的麦田][12]

[1]: ../img/Z1x136dcuM.png
[2]: ../img/3kpkIUryn3.png
[4]: ../img/8GYu2goafv.png
[6]: ../img/DkHYuVgABn.png
[8]: ../img/ZxmRhT4l3I.png
[10]: ../img/RTszlFM9Ei.png
[12]: https://www.m2ez.com
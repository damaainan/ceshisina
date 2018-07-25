## PHP面向对象设计五大原则（SOLID）梳理总结

来源：[https://segmentfault.com/a/1190000015615106](https://segmentfault.com/a/1190000015615106)

PHP设计原则梳理，参考《PHP核心技术与最佳实践》、《敏捷开发原则、模式与实践》，文章[PHP面向对象设计的五大原则][0]、[设计模式原则SOLID][1]
#### 单一职责原则（Single Responsibility Principle, SRP）
##### 定义/特性


* 仅有一个引起类变化的原因
* 一个类只承担一项职责（职责：变化的原因）
* 避免相同的职责分散到不同的类，功能重复


##### 问题

* 一个类承担的职责过多，多个职责间相互依赖，一个职责的变换会影响这个类完成其他职责的能力，当引起类变化的原因发生时，会遭受到意想不到的破坏

##### 遵守SPR原则优势


* **`减少类之间的耦合`** ：当需求变化时，只修改一个类，从而隔离了变化带来类对其他职责的影响
* **`提高类的复用性`** ：按需引用，一个类负责一个职责，需求的变动只需要修改对应的类或增加某一职责
* **`降低类的复杂度`** ：职责单一，功能分散开降低一个类多个职责类的复杂度


##### 代码示例

```php
class ParseText
{
    private $content;
    
    public function decodeText(String $content)
    {
        // TODO: decode content
    }
    
    public function saveText()
    {
        // TODO:: save $this->content;
    }
}
/*
问题思考：
解析的文本类型会有多种-html、xml、json
保存的文本也会有多种途径-redis、mysql、file
客户端只需要解析文本时必须会引入saveText不需要的方法
两个职责之间没有强烈的依赖关系存在
任意职责需求变化都需要更改这个类
*/

/*
符合SRP的设计
职责拆分
*/

class Decoder
{
    private $content;
    
    public function decodeText(String $content)
    {
    // TODO: decode content
    }
    
    public function getText()
    {
        return $this->content;
    }
}

class Store
{
    public function save($content)
    {
        // TODE: save
    }
}
```
##### 总结

软件设计所做的许多内容就是发现职责并合理的分离职责间的关系。如果应用程序的变化总是同时影响多个职责，就没必要分离职责。
#### 接口隔离原则（Interface Segregation Principle ISP）
##### 问题

设计应用程序时，类的接口不是内聚的。不同的客户端只包含集中的部分功能，但系统会强制客户端实现模块中所有方法，并且还要编写一些哑方法。这样的接口成为胖接口或者是接口污染，这样的接口会给系统引入一些不当的行为，资源浪费，影响其他客户端程序增强了耦合性等
#### ISP定义/特性


* 不应该强迫客户端依赖与他们不需要的方法/功能
* 一个类对一个类的依赖应该建立在最小的接口上
* 接口的实现类应该只呈现为单一职责原则


##### 遵循ISP原则优势


* 将胖接口分离，每一组接口提供特定功能服务于特定一组的客户端程序
* 对一组接口的更改不会/较小的影响到其他的接口/客户端程序，保证接口的纯洁性


#### 解决方式


* 胖接口分解成多个特定客户端的接口/多重接口分离继承
* 使用委托分离接口，两个对象参与处理同一个请求，接受请求的对象将请求委托给另一个对象处理


##### 代码示例

```php
/*
* 公告接口
*/
interface Employee
{
    public function startWork();
    public function endWork();
}

/*
* 定义特定客户端接口
*/
interface Coder
{
    public function writeCode();
}

interface Ui
{
    public function designPage();
}

class CoderClient implements Employee, Coder
{
    public function startWork()
    {
        //TODO:: start work time
    }
    public function endWork()
    {
        //TODO:: end work time
    }
    
    public function writeCode()
    {
        //TODO:: start write code
        return 'hellow world';
    }
}
$c = new CoderClient();
echo $c->writeCode();

```
##### 总结

胖类会导致他们的客户端程序之间产生不正常的并且有害的耦合关系。通过把胖客户度分解成多个特定于客户端的接口，客户端紧紧依赖于他们实际调用的方法，从而解除了客户端与他们没有调用的方法之间的依赖关系。接口隔离应做的小而少。
##### SRP与ISP比较


* 都是解决软件设计中依赖关系原则
* SRP 注重职责的划分，主要约束类，其实是接口和方法，是程序中的细节和实现。ISP 注重接口的隔离，约束的是接口，从更宏观的角度对接口的抽象设计


#### 开放-封闭原则（Open-Close Principle OCP）
##### 问题

随着软件系统规模的不断扩大，系统的维护和修改的复杂性不断提高。系统一处的更改往往会影响到其他模块。正确的运用OCP原则可以解决此类问题。
##### 定义/特性

* 一个模块在扩展行为方面应该是开放的而在更改性方面应该是封闭的

##### 遵循OCP优势


* 模块的行为是可扩展的，可以方便的对现有模块的行为/功能进行扩展
* 对于模块行为的扩展不会/较小的影响现有系统/模块


##### 代码示例

```php
/*
* 定义有固定行为的抽象接口
*/
interface Process
{
    public function action(String $content);
}

/*
* 继承抽象接口，扩展不同的行为
*/
class WriteToCache implements Process
{
    public function action(String $content)
    {
        return 'write content to cache: '.$content;
    }
}

class ParseText
{
    private $content;
    public function decodeText($content)
    {
        $this->content = $content;
    }
    
    public function addAction(Process $process)
    {
        if ($process instanceof Process) {
            return $process->action($this->content);    
        }
    }
}

$p = new ParseText();
$p->decodeText('content');
echo $p->addAction(new WriteToCache());
```
##### 总结

OCP核心思想就是抽象接口编程，抽象相对稳定。让类依赖与固定的抽象，通过面向对象的继承和多态让类继承抽象，复写其方法或固有行为，是想新的扩展方法/功能，实现扩展。
#### 里氏替换原则（Liskov Substitution Principle LSP）
##### 问题

面向对象中大量的继承关系十分普遍和简单，这种继承规则是什么，最佳的继承层次的规则又是什么，怎样优雅的设计继承关系，子类能正确的对基类中的某些方法进行重新，这是LSP原则所要处理的问题。
##### 定义/特性


* **`子类必须能够替换掉他们的基类型`** ：任何出现基类的地方都可以替换成子类并且客户端程序不会改变基类行为或者出现异常和错误，反之不行。
* 客户端程序只应该使用子类的抽象父类，这样可以实现动态绑定（php多态）


##### 违反LSP原则

假设一个函数a，他的参数引用一个基类b，c是b的派生类，如果将c的对象作为b类型传递给a，会导致a出现错误的行为，那没c就违法了LSP原则。

```php
/*
* 基类
*/
class Computer
{
    public function action($a, $b)
    {
        return $a+$b;
    }
}
/*
* 子类复习了父类方法，改变了action 的行为
* 违反了LSP原则
*/
class Client extends Computer
{
    public function action($a, $b)
    {
        return $a-$b;
    }  
}

function run(Computer $computer, $a, $b) {
    return $computer->action($a, $b);
}

echo run((new Client()), 3, 5);
```
##### 总结

LSP是OCP得以应用的最主要的原则之一，正是因为子类性的可替换行是的基类类型在无需修改的情况下扩展功能。
#### 依赖倒置原则（Depend Inversion Principle DIP）
##### 问题

软件开发设计中，总是倾向于创建一些高层模块依赖底层模块，底层模块更改时直接影响到高层模块，从而迫使他们改变。DIP原则描述了高层次模块怎样调用低层次模块。
##### 定义/特性


* 高层模块不应该依赖与底层模块，二者都应该依赖于抽象
* 抽象不应该依赖与细节，细节应该依赖于抽象


##### 代码示例

```php
interface Arithmetic
{
    //public function sub($a, $b);
}

class Client
{
    
    public function computer(Arithmetic $arithmetic, $a, $b)
    {
        return $arithmetic->add($a, $b);
    }
}

class Addition implements Arithmetic
{
    public function add($a, $b)
    {
        return $a + $b;
    }
}

$c = new Client();
echo $c->computer(new Addition(), 2, 3);

/*
client 高层类 依赖于Arithmetic，Addition底层实现细节类实现Arithmetic接口，达到二者依赖于抽象接口的DIP设计原则
*/
```
##### 总结

DIP原则就是每个高层次模块定义一个它所需服务的接口声明，低层次模块实现这个接口。每个高层次类通过该抽象接口使用服务。
#### 思考

面向对象软件开发中合理的遵循设计原则可以更好的设计代码，减少不必要的错误，提高程序的可维护性，可扩展性和稳定性。


* **`单一职责（SRP）`** 如何正确的划分职责，类的职责单一提高代码复用性，降低耦合性
* **`接口隔离（OCP）`** 合理划分接口功能，保证接口的专一性，纯洁性，减少依赖关系
* **`里氏替换（LSP）`** 合理利用类的继承体系，保证真确的继承关系不被破坏
* **`依赖倒置（DIP）`** 抽象接口编程由于抽象具体实现
* **`开放封闭（OCP）`** 面向对象编程终极目标所达到的结果，类/模块/系统的功能行为可扩展，内部更改性是封闭的


[0]: https://segmentfault.com/a/1190000013812312
[1]: https://www.jianshu.com/p/21573a0b2ad9
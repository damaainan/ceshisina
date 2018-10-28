## 如何提高PHP代码的质量？第二部分 单元测试

来源：[https://blog.csdn.net/ZVAyIVqt0UFji/article/details/80681513](https://blog.csdn.net/ZVAyIVqt0UFji/article/details/80681513)

时间：

说实话，在代码质量方面，PHP的压力非常大。通过阅读本系列文章，您将了解如何提高PHP代码的质量。
 **`PS：丰富的一线技术、多元化的表现形式，尽在“HULK一线技术杂谈 ”，点关注哦！`** 


在“如何提高PHP代码的质量？”的前一部分中，我们设置了一些自动化工具来自动检查我们的代码。这很有帮助，但关于我们的代码如何满足业务需求并没有给我们留下任何印象。我们现在需要创建特定代码域的测试。

1 **`单元测试`** 

最常见的测试软件的方法可能是编写单元测试。它们的目的是测试代码的特定单元，基于这样的假设：一切都按预期运行。为了能够编写适当的单元测试，我们的代码应该遵循一些基本的设计规则。我们应该特别关注SOLID原则。


* 通过实现单一责任原则（我们的代码应该只关注功能的单个部分），我们将确保在测试期间，我们只会同时关注项目的一小部分

* 通过使用Liskov替换原则和依赖倒置原则，我们的代码不会关心我们是否注入模拟依赖关系，只要它们实现了适当的接口


在单元测试中，我们确实希望用模拟对象替换所有依赖的服务，因此我们一次只测试一个类。但模拟是什么？它们是实现与其他对象相同的接口的对象，但它们的行为是受控的。例如，假设我们在创建一个价格比较服务，我们利用另一个服务来获取当前的汇率。在测试我们的比较器时，我们可以使用一个模拟对象来为特定的货币返回特定的汇率，因此我们的测试既不依赖也不调用真正的服务。

2 **`应该使用哪个框架？`** 

有几个好的框架可以达到这个目的。最常见的可能是PHPUnit。在我的工作中，我发现使用行为方法来编写测试会带来更好的结果，并使我更急切地编写测试。对于我们的项目，我们选择phpspec。

安装过程相当简单 - 只需使用：

```
$ php composer.phar require --dev phpspec/phpspec
```

然后，如果你在本文的第一部分中配置了PHing，那么你可以在build.xml中添加构建目标：

```xml
<target name="phpspec">
    <exec executable="bin/phpspec" passthru="true" checkreturn="true">
        <arg line="run --format=pretty"/>
    </exec>
</target>
...
<target name="run" depends="phpcs,phpcpd,phan,phpspec"/>

```

然后，你必须为你想要测试的每个服务类创建一个测试类。让PHPSpec非常容易使用的是模型创建。你只需使用严格的输入，就可以将模拟对象声明为测试函数的参数。PHPSpec会自动为你创建模拟。让我们看一下代码示例：

```php
// spec/Domain/PriceComparatorSpec.php
<?php
namespace spec\Domain;
use Domain\Price;
use Domain\PriceConverter;
use PhpSpec\ObjectBehavior;
class PriceComparatorSpec extends ObjectBehavior{
    public function let(PriceConverter $converter)
    {
        $this->beConstructedWith($converter);
    }

    public function it_should_return_equal()
    {
        $price1 = new Price(100, 'EUR');
        $price2 = new Price(100, 'EUR');
        $this->compare($price1, $price2)->shouldReturn(0);
    }

    public function it_should_convert_first(PriceConverter $converter)
    {
        $price1 = new Price(100, 'EUR');
        $price2 = new Price(100, 'PLN');
        $priceConverted = new Price(25, 'EUR');
        $converter->convert($price2, 'EUR')->willReturn($priceConverted);
        $this->compare($price1, $price2)->shouldReturn(1);
    }
}

```

这里有三个函数：


* let( ) - 它允许使用依赖来初始化服务

* 两个 it_* 函数实现测试。其中一种方法是使用模拟$priceConverter的方法实现priceConverter接口，该接口被注入到测试对象的创建中。


你可以看到创建模拟非常容易。你所需要做的就是将它定义为测试函数的参数，并通过指定在执行代码时应该运行哪些函数来配置mock。如果需要，你还可以设置返回值。

所有测试的方法都是从 $this 上下文中运行的，你可以使用与模拟相同的语法来轻松地检查它们的结果。

3 **`如何设置测试？`** 

Phpspec有一个很好的文档，但是我将尝试向你展示一些在日常实践中有用的基本用例。

 **`构建测试对象`** 

一般来说，设置测试对象的最简单方法是调用$this->beConstructedWith(...)方法，该方法将所有应该传递给对象构造函数的params作为参数。

如果你的对象应该使用工厂方法来创建，那么你可以使用$this->beConstructedThrough($methodName，$argumentsArray）方法。
 **`在模拟中匹配运行时参数`** 

你会发现phpspec使用一种非常类似于人类的语法来配置模拟。例如，如果你想要检查在运行时是否有一个模拟方法someMethod与参数“desired value”被调用，你可以在测试中定义它，如下面的例子：

```
$mockObject->someMethod("desired value")->shouldBeCalled();
```

如果你想要测试代码的行为，当一些mock的函数返回“some value”时，你可以通过调用来轻松地设置它：

```
$mockObject->someFunction("some input")->willReturn("some value");
```

有时我们并不真正关心传递给mock的确切参数。然后可以写这段代码：

```
use Prophecy\Argument\Token\AnyValueToken;
...
$mockObject->someFunction(new AnyValueToken())->willReturn(true);
```

有时你会关心一些参数，最好是写一个检查函数，它会告诉你是否正确地调用了一些方法，例如：

```php
use Prophecy\Argument\Token\CallbackToken;
...
$checker = function (Message $message) use ($to, $text) {
   return $message->to === $to && $message->text === $text;
};
$msgSender->send(new CallbackToken($messageChecker))->shouldBeCalled()

```
 **`匹配运行时异常`** 

。在某些情况下，异常是代码接口的一部分。你希望它们在特定的场景被抛出。你可以通过编写以下代码来完成这项工作：

```
$this->shouldThrow(\DomainException::class)->during('execute', [$command, $responder]);
```

传给during()的第一个参数是将要调用的方法的名称，第二个参数是将传递给我们的方法的参数数组。

5 **`在哪里可以找到更多的例子？`** 

在本文中，我们只介绍了一些基本的用例。请参考phpspec的文档，以找到更多的示例，这些示例将使你的测试代码变得漂亮！
 **`代码覆盖率`** PHPSpec附带了扩展子系统，它允许例如创建代码覆盖率报告。如果您想要检查在测试中执行了多少代码，它们是很有帮助的。

你可以通过以下来安装这个扩展：

```
$ php composer.phar require --dev leanphp/phpspec-code-coverage
```

然后通过创建phpspec来启用它。yml文件内容：

```
extensions:
  LeanPHP\PhpSpec\CodeCoverage\CodeCoverageExtension: ~
```

默认情况下，这个扩展会使用PHP的Xdebug扩展生成代码覆盖率信息，但是PHP的本机调试器 - phpdbg会更快速一些：

```
$ phpdbg -qrr phpspec run
```

现在，你可以在build中更改phpspec的构建目标。xml：

```xml
<target name="phpspec">
    <exec executable="phpdbg" passthru="true" checkreturn="true">
        <arg line="-qrr bin/phpspec run --format=pretty"/>
    </exec>
</target>
...
<target name="run" depends="phpcs,phpcpd,phan,phpspec"/>

```

报告在覆盖率/目录中生成，作为漂亮的HTML页面，可以浏览以检查测试覆盖率。

![][0]

6 **`应该什么时候写单元测试？`** 

答案是……尽可能多的！它们运行得非常快，它们可能是目前最简单的检查代码的方法。这是一种最好的方法，它可以让你花几个小时不花在手工测试上，相反，它是在几秒钟内完成的。单元测试在非编译代码中特别有用，比如PHP。它们有助于捕获在运行时只会出现的问题。

编写测试还可以帮助您编写更有组织的代码。考虑测试？创建一个可测试的代码结构。

 **`总结`** 

现在是时候拿起你的键盘，为你的项目写一些测试，对你的代码充满信心了！

HULK一线技术杂谈


[0]: ../img/phpspecfugailv.png

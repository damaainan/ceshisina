## 如何提高PHP代码的质量？第三部分 端到端/集成测试

来源：[https://blog.csdn.net/ZVAyIVqt0UFji/article/details/80837445](https://blog.csdn.net/ZVAyIVqt0UFji/article/details/80837445)

时间：

说实话，在代码质量方面，PHP的压力非常大。通过阅读本系列文章，您将了解如何提高PHP代码的质量。
 **`PS：丰富的一线技术、多元化的表现形式，尽在“HULK一线技术杂谈 ”，点关注哦！`** 

在本系列的最后一部分，是时候设置端到端/集成测试环境，并确保我们已经准备好检查我们工作的质量。

在本系列的前几部分中，我们建立了一个构建工具，一些静态代码分析器，并开始编写单元测试。如果你还没有读过这些，那就去看看吧！


[如何提高PHP代码的质量？第一部分 自动化工具][1]

[如何提高PHP代码的质量？第二部分 单元测试][2]

为了使我们的测试堆栈更完整，有一些测试可以检查你的代码是否在真实环境中运行，以及它是否能在更复杂的业务场景中运行良好。

在这里，我们可以使用为行为驱动开发构建的工具——官方PHP的 Cucumber 实现——Behat。我们可以通过运行以下代码来安装它：

```
$ php composer.phar require --dev behat/behat
```

增加一个目标到 build.xml（在本文的第一部分中描述了Phing设置）

```xml
<target name="behat">
   <exec executable="bin/behat" passthru="true" checkreturn="true"/></target>
…
<target name="run" depends="phpcs,phpcpd,phan,phpspec,behat"/>

```

然后，你应该为文件 features/price.feature 的测试创建一个规范。

```
Feature: Price Comparison
  In order to compare prices
  As a customer
  I need to break the currency barrier
 
  Scenario: Compare EUR and PLN
    Given I use nbp.pl comparator
    When I compare “100EUR” and “100PLN”
    Then It should return some result

```

这个测试场景非常容易阅读，并且应该给你一个关于该特性应该如何工作的良好印象。不幸的是，计算机通常并不真正理解人类语言，所以现在是为每一步编写代码的时候了。

你可以通过运行 ./bin/behat-init 来生成它的代码模板。它应该会创建一个这样的类：

```php
//features/bootstrap/FeatureContext.php

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
class FeatureContext implements SnippetAcceptingContext{
    /**
     * Initializes context.
     */
    public function __construct()
    {
    }
}

```

然后你可以执行：

```
$ bin/behat --dry-run --append-snippets
```

Behat将自动为场景中定义的每个步骤创建函数。 
现在你可以通过填充函数的主体来开始实现真正的检查： 
```php
// features/bootstrap/FeatureContext.php

<?php
use Behat\Behat\Context\Context;
use Domain\Price;
use Domain\PriceComparator;
use Infrastructure\NBPPriceConverter;

/**
* Defines application features from the specific context.
*/
class FeatureContext implements Context{
   /** @var PriceComparator */
   private $priceComparator;

   /** @var int */
   private $result;

   /**
    * Initializes context.
    *
    * Every scenario gets its own context instance.
    * You can also pass arbitrary arguments to the
    * context constructor through behat.yml.
    */
   public function __construct()
   {
   }

   /**
    * @Given I use nbp.pl comparator
    */
   public function iUseNbpPlComparator()
   {
       $this->priceComparator = new PriceComparator(new NBPPriceConverter());
   }

   /**
    * @When I compare :price1 and :price2
    */
   public function iCompareAnd($price1, $price2)
   {
       preg_match('/(\d+)([A-Z]+)/', $price1, $match1);
       preg_match('/(\d+)([A-Z]+)/', $price2, $match2);
       $price1 = new Price($match1[1], $match1[2]);
       $price2 = new Price($match2[1], $match2[2]);
       $this->result = $this->priceComparator->compare($price1, $price2);
   }

   /**
    * @Then It should return some result
    */
   public function itShouldReturnSomeResult()
   {
       if (!is_int($this->result)) {
           throw new \DomainException('Returned value is not integer');
       }
   }
}

```

最后，使用 ./bin/phing 运行所有的测试。你应该得到以下结果：

```
Buildfile: /home/maciej/workspace/php-testing/build.xmlMyProject > phpcs:

MyProject > phpcpd:

phpcpd 4.0.0 by Sebastian Bergmann.0.00% duplicated lines out of 103 total lines of code.
 
Time: 17 ms, Memory: 4.00MB
 
MyProject > phan:

MyProject > phpspec:

/  skipped: 0%  /  pending: 0%  / passed: 100%  /  failed: 0%   /  broken: 0%   /  3 examples2 specs3 examples (3 passed)15ms
 
MyProject > behat:

Feature: Price Comparison
 In order to compare prices
 As a customer
 I need to break the currency barrier
 
 Scenario: Compare EUR and PLN          # features/price.feature:6
   Given I use nbp.pl comparator        # FeatureContext::iUseNbpPlComparator()
   When I compare "100EUR" and "100PLN" # FeatureContext::iCompareAnd()
   Then It should return some result    # FeatureContext::itShouldReturnSomeResult()1 scenario (1 passed)3 steps (3 passed)0m0.01s (9.13Mb)
 
MyProject > run:
 
BUILD FINISHED
 
Total time: 1.1000 second

```

正如你所看到的，Behat准备了一份很好的报告，说明我们的应用程序做了什么，结果是什么。下一次，当项目经理询问你在测试中涉及到哪些场景时，你可以给他一个Behat输出！

1 **`测试的结构`** 

每个测试都包括：


* 对该场景的一些准备，用“Given”部分表示

* “When”部分所涵盖的一些动作

* 一些检查被标记为“Then”部分


每个部分都可以包含多个与“And”关键字连接的步骤：

```
Scenario: Compare EUR and PLN
    Given nbp.pl comparator is available
    And I use nbp.pl comparator    When I compare "100EUR" and "100PLN"
    And I save the result
    Then It should return some result
    And the first amount should be greater

```

2 **`上下文`** 

Behat允许你为你的测试定义多个上下文。这意味着你可以将步骤代码分割成多个类，并从不同的角度去测试你的场景。

你可以例如：为web上下文编写代码，它将使用你的应用程序HTTP控制器运行你的测试步骤。你还可以创建“domain”上下文，它将只使用PHP API调用来运行你的业务逻辑。通过这种方式，你可以单独地测试业务逻辑集成，从端到端应用程序测试。

关于如何在Behat建立许多上下文的更多信息，请参考http://behat.org/en/latest/userguide/context.html的文档。

3 **`我是如何使用Behat的？`** 

正如一开始所提到的，你可以使用Behat进行集成测试。通常情况下，你的代码依赖于一些外部的第三方系统。当我们在第2部分中编写单元测试时，我们总是假设外部依赖关系像预期的那样工作。使用Behat，你可以编写测试场景，它将自动运行你的代码，并检查它是否正确地使用真实场景的服务。

最重要的是，Behat对于测试系统使用的复杂的端到端场景非常有用。它允许你隐藏在一个可读性的名字后面运行测试步骤所需的复杂代码，并编写一个人人都能理解的场景。

**`总结`** 

从以上的文章中，你已经学习了如何在你的项目中设置六个有用的工具：


* PHing 用于运行你的构建

* PHPCS 用于自动检查代码格式

* PHPCPD 用于检测重复代码的

* Phan 用于高级静态代码分析

* PHPSpec 用于单元测试

* Behat 用于端到端和集成测试


现在，你可以向git提交钩子添加 ./bin/phing，并设置持续集成来运行每个提交的测试。

是不是突然之间，没有什么能阻止你写出高质量的PHP代码！

Well done!

HULK一线技术杂谈

[1]: http://mp.weixin.qq.com/s?__biz=MzIyNzUwMjM2MA==&mid=2247485615&idx=1&sn=788045ee6ea80eecf3c3757dc9a0b68b&chksm=e86178d8df16f1cea928deeaf25417e8294a0bd638b73a397c970a7fe4bec67a5758e41fc264&scene=21#wechat_redirect
[2]: http://mp.weixin.qq.com/s?__biz=MzIyNzUwMjM2MA==&mid=2247485640&idx=1&sn=6c20c41dc703ab7dab2beb2210c94103&chksm=e86178bfdf16f1a9cfae349ebdd870b9d255a913e1527b2153dda7541336913b7f2e4d54b04f&scene=21#wechat_redirect

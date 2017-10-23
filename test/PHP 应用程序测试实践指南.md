# [专题] PHP 应用程序测试实践指南 

[分享][0] ⋅ [JobsLong][1] ⋅ 于 7个月前 ⋅ 最后回复由 [沈益飞][2] 于 5个月前 ⋅ 1906 阅读 

![file][3]

大家在思考如何将测试引入自己/团队的开发流程的整个生命周期的时候，这篇文章可作为知识索引。

## Why TDD ?[#][4]

GitChat 今天有一篇新鲜出炉的文章，可以去看一下 [深度解读测试驱动开发（TDD）][5]，非广告。

个人比较关注的几个方面：

**1）先写测试的好处**

> Give us proper hints about the problems

这是一个很好的时机，跟你的项目或产品经理将业务实现的逻辑细节梳理清楚。确保你在构建之前，就完全了解你要构建的应用究竟是如何运行的，彻底搞清楚你要解决的是什么问题非常重要。

当然也不是说需要你在写代码之前就写完所有的测试，写一小部分测试（在敏捷开发中，即一个 sprint），然后去实现相关的业务代码，并让他们通过测试，持续执行这个循环，直到完成所有规划的功能。

**2）重构保障**

TDD 的好处是覆盖完全的单元测试，对产品代码提供一个保护网，让我们可以轻松的迎接需求变化或改善代码的设计

**3）测试会贯穿整个开发流程**

测试并不是一个一次性的行为，测试需要被持续的修改和改进，就像应用程序本身。

## 测试工具 (或框架)[#][6]

PHP 有一些不同种类的测试工具 (或框架) 可以使用，它们使用不同的方法或理念，但他们都试图避免手动测试和大型 QA 团队的需求，确保最近的变更不会破坏既有功能。

* [PHPUnit: 业界标准][7]
* [Atoum is a simple, modern and intuitive unit testing framework for PHP!][8]
* [Enhance PHP is a lightweight Open Source PHP unit testing framework with support for mocks and stubs, written in PHP, for PHP][9]
* [Simple Test][10]
* [SpechBDD：PHPSpec][11]
* [StoryBDD：Behat][12]
* [Codeception: Elegant and Efficient Testing for PHP][13]
* [Storyplayer][14]

## 视频资源[#][15]

* [PHPUnit Presentions][16]
* [PHP Testing: 138-minute PHP Course][17]
* [Laracasts Testing Series][18]
* [Adam Wathan: Test Driven Laravel][19]
* [TDD - Learn About Test Driven Development | Agile Alliance][20]

## 参考资料[#][21]

* [Laravel Testing 官方文档][22]
* [Test-driven development - Wikipedia][23]
* [10 Best Automated Testing Frameworks For PHP - Hongkiat][24]
* [北京设计模式学习组][25]
* [TDD | 酷 壳 - CoolShell][26]
* [Martin Fowler: Is TDD Dead?][27]
* [Martin Fowler: TestDrivenDevelopment][28]

[0]: https://laravel-china.org/categories/5
[1]: https://laravel-china.org/users/56
[2]: https://laravel-china.org/users/13655
[3]: https://dn-phphub.qbox.me/uploads/images/201703/13/56/B13BEhTe70.png
[4]: #Why-TDD-
[5]: http://www.gitbook.cn/m/mazi/activity/58aea58573bbf56f08a092e7
[6]: #测试工具-或框架
[7]: https://phpunit.de/
[8]: http://atoum.org/
[9]: https://github.com/Enhance-PHP/Enhance-PHP/wiki
[10]: http://simpletest.org/
[11]: http://www.phpspec.net/en/stable/
[12]: http://behat.org/en/latest/
[13]: http://codeception.com/
[14]: http://datasift.github.io/storyplayer/
[15]: #视频资源
[16]: https://phpunit.de/presentations.html
[17]: https://teamtreehouse.com/library/php-testing
[18]: https://laracasts.com/skills/testing
[19]: https://adamwathan.me/test-driven-laravel/
[20]: https://www.agilealliance.org/glossary/tdd/
[21]: #参考资料
[22]: https://laravel.com/docs/5.1/testing
[23]: https://en.wikipedia.org/wiki/Test-driven_development
[24]: http://www.hongkiat.com/blog/automated-php-test/
[25]: http://www.bjdp.org/
[26]: http://coolshell.cn/?s=TDD
[27]: https://martinfowler.com/articles/is-tdd-dead/
[28]: https://martinfowler.com/bliki/TestDrivenDevelopment.html
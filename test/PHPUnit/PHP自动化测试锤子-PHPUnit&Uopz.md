## PHP自动化测试锤子-PHPUnit&amp;Uopz

来源：[http://sadwxqezc.github.io/HuangHuanBlog/php/2017/05/14/PHP的自动化测试.html](http://sadwxqezc.github.io/HuangHuanBlog/php/2017/05/14/PHP的自动化测试.html)

时间 2017-05-14 23:16:41

 
## PHP自动化测试概述
 
PHP是一种偏脚本化的语言，了解过ES6的朋友会发现PHP和ES6其实语法和一些特性上已经很接近了，对于我这种一直学Java的人来讲，觉得PHP和ES6语法上基本没太大区别。由于PHP的这种脚本化的特性，编码的风格一个人一个样，令其难以进行测试，这应该是每个想对PHP搞自动化测试，特别是其中单元化测试的人都会遇到的问题。我最近在公司接了个任务，要对项目中的一些很复杂的回路脚本进行自动化测试，希望组内所有人Merge代码的时候，会自动跑测试Case，这篇文章就是分享我在做这个任务时所找到的锤子。
 
## 锤子一: PHPUnit
 
PHPUnit是大家马上就会找到的一个锤子，关于它官方提供了PHPUnit-Book，里面介绍了其提供的测试工具和一些测试基本理念，大家可以花一天左右的时间系统的看完这个文档，我这里就不做赘述了。我举一个 **`理想情况`**  下的PHPUnit测试的例子：
 
![][0]
 
上图展示的是测试`MyFoo`类中的`doSomething`方法，这个方法实际是调用了传入的`MyBar`类中的`doSomethingElse`方法，在这种情况下，我们只需要利用PHPUnit提供的测试替身工具，构建一个`MyBar`的测试替身，然后Mock掉其中的`doSomethingElse`方法，再将其传入`MyFoo`的`doSomething`方法中。
 
通过上面的步骤，我们实现了单元测试所需要的隔离，`doSomething`方法不再实际依赖于外部。然后我们只需要对这个php文件执行`phpunit`命令，其中`test`前缀的方法就会被运行，一个测试就跑起来了。
 
然而虽然理想是丰满的，但现实很残酷，实际的PHP代码很少有这么写的，且不说每个人各有各的风格，每个公司还各有个的框架，下面举个我们公司常见的写法：
 
![][1]
 
如图上所示，我们的项目中倾向于这种静态调用的面向过程写法，这种写法的问题是PHPUnit基本上就失效了，我们的测试将无从入手，那么该怎么办了?
 
## 锤子二: Uopz
 
PHPUnit无法解决问题，有两个工具可以解决 **`php-test-helper`**  和 **`Uopz`**  ，其中 **`php-test-helper`**  实际已经不再维护了，Github主页上也指向了 **`Uopz`**  ，这里我也就主要说说 **`Uopz`**  工具，这个工具可以做什么了， **`Uopz`**  的全称是 **`User Operations For Zend`**  ，能够在运行时改变PHP的行为，下面是它提供的主要方法：
 
  
```
uopz_function //备份一个方法
uopz_compose //构建一个类
uopz_flags //改变类或者方法的Flag定义
uopz_function //创建一个function
uopz_overload //覆盖一个VM的操作码（这个后来并没有使用，存在问题，实际使用时通过PHPUnit的基境来实现了这个方法的功能）
uopz_redefine //创建或者重定义一个常量
uopz_restore //恢复方法到之前备份的状态
```
 
 
 
这个工具十分强大，基于它真的什么都能搞，就是麻烦点，比如刚才哪个PHPUnit不可测的代码，用上Uopz就不一样了：
 
![][2]
 
PHPUnit不能Mock静态的方法，但Uopz可以，通过`uopz_function`方法，可以改变原始的`MyBar1`和`MyBar2`中`doSomething`方法的行为，达到我们刚才用PHPUnit构造测试替身的效果。甚至你还可以用`uopz_compose`重定义一个自己的`MyBar1`和`MyBar2`，有了这个方法几乎无所不能，但代价也就是麻烦点。
 
我用这个给公司的回路脚本写测试Case，200行的回路代码，写了1000多行的测试代码，因为要Mock太多的类，重定义很多方法，确实很麻烦，对于经常改动的代码，这样搞代价可能不可承受，但如果代码的变动不大，但每次改动的风险却很大，那么这个就值得了。关于Uopz的详细用法，大家可以找我交流，里面坑还是不少。
 
## 锤子三：Jenkins
 
对于自动化来讲，Jenkins应该是最常用的CI工具了，要做的也就是配置对仓库定期的变动检查，或者代码长裤自己挂Hook，通知Jenkins运行自测代码。这样每次代码变动时，Jenkins都会跑一遍Case，如果发现问题则可以邮件通知开发者。
 
## 总结
 
PHPUnit Uopz Jenkins是用的自动化测试三剑客，三者缺一不可，如果想更好的更高效的进行自动化测试，有两个建议：
 
 
* 代码可测性（这其实是最重要的了） 
* 封装Uopz的方法 
 
 
做到这些，PHP的自动化测试将简单而高效。
 


[0]: ../img/yMVFjya.png 
[1]: ../img/2ami2yU.png 
[2]: ../img/ameIv2b.png 
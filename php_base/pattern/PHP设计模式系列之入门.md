# PHP设计模式系列之入门

<font face=黑体>

> 设计模式（Design pattern）是一套被反复使用、多数人知晓的、经过分类编目的、代码设计经验的总结。使用设计模式是为了可重用代码、让代码更容易被他人理解、保证代码可靠性。 毫无疑问，设计模式于己于他人于系统都是多赢的；设计模式使代码编制真正工程化；设计模式是软件工程的基石脉络，如同大厦的结构一样。

## 前言

* 本系列文章不会直接上代码直接进行解释，我一直认为带着问题来学习是效率最高的学习方式。
* 本系列文章不会有演示截图，你为什么不敲一遍加深印象呢，另外说不定我的代码有错。
* 我所写的文章只是我对于编程的理解，如果有错误希望能够得到指正以免误人子弟。

## 怎么样才可以进行设计模式的学习

步子迈大了容易扯到蛋，如果在没有熟悉 OOP 编程思想前就开始学习设计模式，我感觉会有两种可能，不是“扯蛋”，就是“拉跨”。

![][0]

当然上面的话是一句玩笑话，学习设计模式可以有效的提高我们的代码质量与深入的理解 OOP 编程理念，如但是果在没有扎实的功底（至少要要理解了抽象、接口、多态）前就开始学习设计模式会越学越难，脑子越来越浑，那就真变成了从入门到放弃了，因为你的思维还没有真正的走进 OOP(单身狗表示完全无法面向对象^_^)。

对于设计模式的不理解我感觉主要分为两种，一种是不知道怎么实现的，原因就是如上所述，另外一种是不知道为什么要这么用的，其实没有必要纠结于为什么这么用，这么用了有啥作用，设计模式不过是与算法一样只是为了实现某个特定环境下可以使用的一种更好的选择。

更好一点的例子就是当我们对一些数据进行排序的时候，我们首先想到就是那几个排序算法一样，当我们打着打着代码突然灵光一闪，好像这个地方用这个设计模式写起来会轻松一点。

当碰到不懂得地方，思考一下，想不通，就出去走走，把这个东西放下来，反正就算看到第二天凌晨也也是无用的，当真正遇到问题的时候，灵光一闪这个东西可以这么写，然后去实践，这就是我的学习之道。还有就是尽量去学实例，而不是去死扣概念，当你真正用起来了，你也就差不多懂了，算法与数据结构亦是如此。

本系列文章尽量以推导的形式来进行书写，而不是以现成的代码来进行讲解，让读者知道设计模式是怎么来的也就是如何演化出来的，希望各位能够喜欢。**另外本系列的文章并不会提供运行界面的截图，如果想看看结果是否正确，为什么不自己试试呢？**

## 设计模式尝鲜（策略模式）

开头引用的话来自于百度百科，我相信很多刚刚开始接触编程的人都会犯晕，因为所有人都不喜欢被学术化的文字，我们以设计模式中较为常用的策略模式来进行演示，当我们编写一个广告模块的时候，公司给的要求是根据访问者的性别来进行显示广告以提高转化率，那我们应该怎么写呢？


首先我们想到的是在每一个广告位上面都使用 if 判断来判断访客的性别，这样就能够解决这样的需求，那么我们的每一个广告代码的代码块可能是这个样子的：

    判断 男 or 女{
        如果是男的就是男人的广告
    }else{
        显示女人的广告
    }

既然伪代码想好了，那么我们就可以着手进行开发了，然后我们在 if 代码块中添加各自的家在广告代码，于是就变成了下面的样子：

```
    if ($_GET['sex'] == 'man') {
        echo '外星人大减价现在购买立即送电竞瑞文皮肤';
    }else{
        echo '卡西欧美颜相机不要钱免费送！';
    }
```

但是这是属于一种硬编码的编程方式，一旦我们增加了某种需求，要求其年龄大于23岁显示什么样的广告，那么我们就不得不在每一个 if 判断处再加上新的判断条件，这样的设计就是不合理的，为了提高可读性与可维护性，我们会考虑建立两个不同的类来对两个广告类来对其进行管理。于是代码变成了下面的样子。

```php
    //index.php
    include 'GenderAD.php';
    include 'ManAD.php';
    
    if ($_GET['sex'] == 'man') {
        $ad = new ManAD();
    }else{
        $ad = new GenderAD();
    }
    $ad->show();
    
    //GenderAD.php
    class GenderAD
    {
        public algorithm(){
            echo '卡西欧美颜相机不要钱免费送！';
        }
    }
    
    //ManAD.php
    class ManAD{
        public algorithm(){
            echo '外星人大减价现在购买立即送电竞瑞文皮肤';
        }
    }
```

> algorithm 英[ˈælgərɪðəm] 美[ˈælɡəˌrɪðəm] n. 演算法; 运算法则; 计算程序;

其实到了这一步就已经算是一个简单的策略模式了，因为他已经具有策略的特质了，只不过还不够完善，如果说这不算什么的话我也没有办法，因为所有的设计模式其实都是思维模式与表现形式罢了，就像上面的引用中提到的一样，设计模式只不过是为了能够让代码可以重用，更容易他让人理解，因为你的代码并不是你一个人在维护，那么问题来了，只是简单的对其进行封装真的就提高代码的可维护性了么，其实并没有，我们还没有将 OOP 的设计概念发挥到极致。

经过分析我们发现其实 ManAD类和 GenderAD最终都要进行显示，他们的方法的显示方法都是 show，如果是你一个人在开发那么没有什么问题，可是若是两个人开发呢，你们可以直接可以对话的方式进行沟通，协定好都是 show方法来显示，可是为什么不用更工程化的方式来实现呢？

我们可以使用接口来实现这一目的，如果对接口还不了解，可以去查阅一下资料，很快你就能够明白，在本文结束后我会在下方标注出参考范例。

我们可以新建一个接口来对这些策略进行控制。

```php
    interface ADinterface{
        public function algorithm();
    }
    
    class ManAD implements ADInterface
    {
        public function algorithm(){
            echo '外星人大减价现在购买立即送电竞瑞文皮肤';
        }
    }
    class GenderAD implements ADInterface
    {
        public function algorithm(){    
            echo '卡西欧美颜相机不要钱免费送！';
        }
    }
```

这样一来广告策略必须遵循这个接口进行开发，就保证了所有策略类都需要实现 show 方法。

到目前为止，策略模式已经相对的完善了，但是还是不够完美，因为代码依旧并不是很 OOP，我们其实还可以更进一步，让他更 OOP，我们可以对那些策略外面套一个壳子，给外面一个选择器。

```php
    class StrategySelect {
        //具体策略对象
        private $strategyInstance;
        
        //构造函数
        public function __construct($instance)
        {
            $this->strategyInstance = $instance;
        }
        
        public function algorithm($strategy)
        {
            return $this->strategyInstance->algorithm();
        }
    }
```

我们通过构造函数接收到具体的执行策略，然后使用algorithm()执行相对应的策略。

```php
    <?php
    
    interface ADinterface{
        public function algorithm();
    }
    
    class StrategySelect {
        //具体策略对象
        private $strategyInstance;
        
        //构造函数
        public function __construct($instance)
        {
            $this->strategyInstance = $instance;
        }
        
        public function algorithm()
        {
            return $this->strategyInstance->algorithm();
        }
    }
    
    class ManAD implements ADInterface
    {
        public function algorithm(){
            echo '外星人大减价现在购买立即送电竞瑞文皮肤';
        }
    }
    
    class GenderAD implements ADInterface
    {
        public function algorithm(){
            echo '卡西欧相机免费赠送啦';
        }
    }
    
    header("Content-type:text/html;charset=utf-8");
    if ($_GET['sex'] == 'man') {
        $stratey = new StrategySelect(new ManAD());
        $stratey->algorithm();
    }else{
        $stratey = new StrategySelect(new GenderAD());
        $stratey->algorithm();
    }
```

Strategy其实算是一个策略选择器，当满足一定条件的时候，我们通过这个策略选测器来进行选择相对应的策略。这样一来更符合逻辑。是不是很 OOP？

如果有什么不懂得可以在评论区进行留言，有时间我会一一答复，如果发现本文中有什么错误请指出，我也害怕误人子弟，特别是概念上的东西，在最后StrategySelect类的讲解上我依旧感觉写的很模糊，有些差强人意。

我的博客网址：www.aircrayon.xyz，有兴趣的朋友可以去看看，不过上面的东西很久没有更新了，而且有的博文内容不全。

## 参考文档

* 《Leaning PHP Design Patterns》 William Sanders 著 苏金国 王宇飞等译
* 《PHP之道》
* 《PHP大话设计模式》 Rango(韩天峰) 录制者 慕课网视频教程

</font>

[0]: https://segmentfault.com/image?src=http://wanzao2.b0.upaiyun.com/system/pictures/17809564/original/5a08021694a003ca.gif&objectId=1190000006992192&token=88b99080b271f9304723b3132e4e5bc9

# 那些年，我们追过的RPC

[果冻虾仁][0]

1974年冬，互联网大师 Jon Postel发表了RFC674：“_Procedure Call Protocol Documents，Version 2_”，尝试定义一种在包含70个节点的网络中共享资源的通用方法。在大师一生中编辑过的无数个RFC文档中，674属于并不突出的一个，但却拉开了RPC的序幕。也正是接下来我们故事的开始。

- - -

## 1. 从IPC到RPC

**IPC**（_Inter-Process Communication_）即进程间通信。指在不同进程之间的通信过程。Unix家族中IPC种类繁多，其中有一个需要特别关注——**Socket**（**套接字**）。与其他IPC类型只支持同机上进程间通信所不同，**Socket**广泛应用于不同机器之间的远程通信。

把镜头拉到八零年代，自从大神**Bill Joy**漫不经心地对着DARPA官员回答：“我就是边阅读协议文档，边敲代码，就写出来了”这一刻开始，就注定了**Berkeley Socket**_（伯克利套接字）_与众不同的命运。作为TCP协议的实现，在BSD 4.2中Socket正式面世，彼时Bill Joy已作为联合创始人创办了SUN公司。Berkeley Socket不仅仅是Unix操作系统进行 TCP/IP 通信的实现基础，更是如今整个网络世界通信的基石，甚至Windows其套接字也脱胎于此。

**RPC**（_Remote Procedure Call_）即远程过程调用。**RPC是一种技术思想而非一种规范。**其实到底什么是RPC，随着时间的推移与技术的演化，已经越来越难以定义。但站在八九十年代的当口，简单来说，就是我在本地调用了一个函数，或者对象的方法，实际上是调用了远程机器上的函数，或者远程对象的方法，但是这个通信过程对于程序员来说是透明的，即达到了一种位置上的透明性。听起来真的是我等业务逻辑程序员的好帮手，但同样有持反对意见的人士认为：**通信的透明，会对程序员造成通信是无成本的假象**，从而滥用以致于增加了通信成本。

当然RPC概念本身也适用于除TCP以外的其他传输层协议，但我们用的最多的只有TCP/IP啦。

只需要一个**Wire Protocol**，再搭配一个**_Name Service_**(名字服务）就能自定义一个最简单的RPC库了。socket通信，以及序列化反序列化的工作被封装了RPC框架内部，无需程序员手工处理。当然一个成熟的RPC库，并非如此简单，它所包含的功能要负责的多，亟待解决的问题也棘手的多。

_注：所谓Wire Protocol并不是一种网络协议，而是一种数据序列化与反序列化的规则。_

从七十年代中到八十年代末，关于RPC的论文层出不穷。有支持，也有质疑，有狂热，更有批判。就这样时间进入了下一个十年。

- - -

## **2. RPC中间件的三国时代**

上个世纪最后十年的伊始，面向对象的理念已经走入人们视野，互联网还未爆发，但这并不会阻挡企业级网络规模的发展。分布式系统已现端倪，通信是永恒不变的话题。

那些年，不同硬件、不同OS、不同编程语言之间通信与协作简直是一场噩梦。1991年，CORBA横空出世。CORBA全称Common Object Request Broker Architecture，是OMG（Open Manage Group）组织颁布的标准，划时代地提出了**分布式对象（**Distributed object**）**的技术，自此RPC由原先的面向过程语义进化出了面向对象的语义。尽管1.0版本的CORBA只支持C语言的映射，但也着实让人们看到了**异构环境**之间**互操作**的新希望。

    1991年发生了很多事。
    这一年第一个python编译器诞生。
    而彼时，SUN公司的一个小组正在攻坚Oak编程语言。
    五月，Tim Berners-Lee对外公布了第一个HTTP版本HTTP/0.9，World Wide Web 首次露面。
    八月，在大洋的另一端一个芬兰小伙在BBS上发了一个交友贴：
    “Hello everybody out there using minix——I'm doing a (free) operating system”。
    

言归正传，CORBA使用ORB组件来处理通信过程，**ORB**（_Object Request Broker,_ 对象请求代理）即CORBA的中间3个字母。该组件是一个请求代理，客户端的代码只需要向客户端ORB发送请求，ORB去定位到服务端ORB，并且自动处理连接与传送数据。那么在两个ORB之间如何传送“对象”呢？答案是**GIOP**（_General Inter ORB Protocol_），该协议规定了对象的序列化规则与消息传递的规则。GIOP以短小灵活著称，但其本身是一个抽象协议。**IIOP**（_Internet Inter-ORB Protocol_）是GIOP在IP协议上的具体实现， IIOP就是一种Wire Protocol。

另外CORBA利用IDL（**接口描述语言**）来描述接口，屏蔽了不同编程语言之间的差异，使用编译工具可以将IDL文件编译成多种语言的**客户端stub代码**和 **服务端skeleton代码**，且stub和skeleton可以是不同的编程语言。调用远程方法时，stub代码会向ORB发送请求。

除基本的RPC功能外，还有事务管理，并发控制等等功能，由于进一步解放了程序员的双手，使其专注于业务逻辑上，因此以CORBA为代表的技术架构也被称之为——RPC中间件（Middleware）。

1993年，世界上第一款现代意义上的浏览器**Mosaic**发布，新时代的大门终于打开。

1995年Oak升级并更名为Java，如今看来昨日黄花的Applet也曾让人眼前一亮，但Java的未来并不始于此。此后Java推出了RMI，一种基于Java平台的RPC技术（此后推出了RMI-IIOP，使得RMI得以和CORBA系统间进行通信），后来又推出JavaBean。至此其表现仍算中规中矩，直到1998年，SUN公司发布JDK1.2，EJB从此诞生，世人不禁惊呼一声：“**成了**”。Java有了自己的分布式对象解决方案。自此Java得以与COBRA和DCOM鼎足而立，三分天下。

同年，CORBA增加了对于Java语言的映射，第二年CORBA3.0标准面世，提出了不用于DCOM、EJB的第三个组件模型**CCM**（_CORBA Component Model_），另外支持映射的语言更多，不仅支持C/C++、Java，还支持骨灰级语言Smalltalk、Ada、COBOL。但最终还是大势已去，气数已尽。给CORBA制定规范的专家们大部分脱离实际，且CORBA规范艰深晦涩。理论脱离实际，不禁联想起OSI/ISO与TCP/IP的故事，让人唏嘘不已。另外厂商们在实现过程中无法完全理解并遵守该规范，各有各的解读，最后导致各家并不兼容。还有一些其他问题，比如由于不同的语言类型系统，编程范式千差万别。因此要想抽象出适合编译成各种语言接口的IDL语法作为中间语言是十分困难的，但历史也从来不乏后来者一次又一次的尝试这么做，不过这是后话了。

Java语言本身跨平台，Java RMI只专注于一种语言的解决方案，编写简单。无需CORBA那样为了适配各种语言而引入IDL。历史便是如此有趣，为了适应异构的网络环境和不同的编程语言，而提出的CORBA技术，因为其复杂度无法推行。而只支持一种语言的JavaEE解决方案，因为简单而得到人们的支持，进而反向助推了Java语言的流行。孰因孰果，难以预料。只能说不同的时代，人们的选择不同。

## 3. RPC的寓言：十年轮回

RPC的思想自1974年的那篇论文发表至今已经四十余年了。这期间RPC并非一帆风顺，批判之声也不绝于耳。

1987年，Tanenbaum教授（就是发明Minix的谭教授）发表了论文“A Critique of the Remote Procedure Call Paradigm”，认为将本地调用与远程调用当做一样处理是犯了本质错误，并且达到通信的完全透明是不可能的，而一旦一个系统的通信是部分透明，反而会增加程序员工作的复杂度。

1994年，时任SUN公司高级研究员的 [Jim Waldo][1] （等4人）发表了著名论文“A Note on Distributed Computing”，痛陈RPC四大罪状：

1. 通信时延
1. 地址空间隔离
1. 局部故障
1. 并发问题

当然多数罪状是分布式系统本身的固有问题。有趣的是Waldo曾提出过一个十年理论：

> 每隔十年人们便试图将本地计算与远程计算统一，一次又一次。**然而本地计算与远程计算是完全不同的**。

十年必是虚数，但这句话倒也值得玩味，在某种程度上暗示了RPC的周期性。Jim Waldo看似是RPC的批判者，但却对RPC技术的发展功不可没。在任职Sun公司前，Waldo在惠普主导了第一个ORB的设计与开发，从而促进了ORB被纳入第一版的OMG CORBA规范之中。后来在SUN公司工作期间，也主导并参与了多项RPC相关技术的设计研发工作。

回到之前的时间线。

1998年，XML 1.0发布，并成为W3C的推荐标准。此后XML迅速崛起，成为工业界的新宠。“凡是用XML描述的都是好的，凡是不使用XML的都是垃圾”，在两个凡是的方针指导下，XML-RPC应运而生，但是很快继任者SOAP就已出现。_不要叫他肥皂协议哦。_

Web Service（简称WS）使用SOAP协议作为RPC的序列化标准。SOAP是XML描述的，或者说其本身就是XML的子集，SOAP是一种Wire Protocol，其传送仍然依赖“**介质**”，这个介质可以 HTTP、TCP 甚至 JMS。另外WS中也存在类似CORBA中IDL的WSDL，用于描述WS接口，并且通过工具可以将其编译成stub代码。而WSDL同样也是XML语言描述的。通过使用WS，可以方便地完成基于SOA架构思想的工程实践。

世纪之初，微软看到了DCOM的暗淡前途，转而强推WS，彼时，微软与IBM是WS的强力站台者。好景不长，WS虽然也可以适用于企业内部系统间通信与服务化的解决方案，但是更多的被人们应用在接入层（HTTP），后来随着以REST风格为代表的API技术的崛起，WS逐渐偃旗息鼓，这也标志着RPC技术在接入层的完败。

- - -

## 4. 庙堂与江湖：现代RPC

两千年以后，在互联网的泡沫之下，许多中小企业使用单一的MVC架构风格即足以满足要求。许多人都已淡忘了RPC的存在，是否真应了十年轮回之说？答案是否定的。其实RPC从来都未消失，是在大企业内部，RPC技术一直飞速发展，尤其是在移动互联网爆发之后，后台瓶颈日益凸显，RPC再度从幕后走到台前。RPC中间件成井喷之势，彼时现代的RPC框架已经吸收了SOA的架构思想，以及其他技术。虽然RPC仍然是中间件的基础，但是后来的附加技术更加喧宾夺主，且更能代表一个中间件的特色。相形执行，RPC则显得不那么突出了（不过依然重要）。

2008年，Google开源了**_Protocol Buffer_**（简称PB），PB不仅兼容大大小小各类编程语言，而且由于是二进制协议，其效率之高，远超XML、JSON。开源之后，迅速风靡全球。同年，Facebook向Apache贡献的**_Thrift_**正式开源，Thrift提供了多种序列化的方案可供选择，当然也包含二进制的。另外PB虽然优秀，但终归只是一个序列化库，只是一种高效的Wire Protocol，而Thirft则是一套相对完整解决方案，除RPC的基本功能外，还提供了几种现成的Server。同样Thrift包含一个IDL，兼容常见的编程语言。

2015年，Google将**gRPC**框架开源，一经发布，迅速获得广泛关注，毫无疑问，gRPC使用PB作为序列化的解决方案，而在传输的介质上富有创见性的使用了HTTP/2。另外gRPC支持双向流式通信（bidrectional streaming communication），RPC框架终于不再拘泥于万年不变的C/S模型，因此gRPC得以更为方便快速地构建服务（SOA或Microservice）。而这正是Thrift的短板。

当然企业的开源项目是需要经过修改调整才释出的，并非直接拿企业内部的代码就开源了。因此尽管gRPC在谷歌内部运行多年，但开源版本的gRPC目前还相对不够成熟，而Thrift自2007开源以来已经历经10年锤炼。但在可以预见的未来，我相信gRPC与Thrift必将是开源RPC框架中最瞩目的两极。

今天我们可以发现，现在越来越多的前沿技术由工业界巨头和开源社区所把持。庙堂之高与江湖之远，在IT领域其实并不是零和博弈。

我们常说“经济基础决定上层建筑”，在后台技术领域，决定我们上层建筑的不是经济，正是这些RPC框架。通过使用这些成熟的RPC框架，我们得以站在一个更高的维度去思考问题。感谢这些企业以及所有开源社区的贡献者。

- - -

**你以为故事到这里就结束了吗？**

CORBA没落之后，一批OMG的实干派出走，于2002年成立了ZeroC公司，致力于研发ICE框架，并非为CORBA续命，ICE被称之为反叛之冰。Hadoop之父Doug Cutting由于不满于Thrift设计哲学中的中庸之道，开始了继续造轮子之路，因此诞生了Avro。放眼国内，2011年，开源大户阿里也曾开源自研RPC框架——Dubbo。但近两年又风闻其已转向新一代框架——HSF（High Speed Framework）。2014年，Facebook再度开源轻量级Thrift框架——fbthrift……

RPC、中间件、服务，这个江湖很多故事没有提及，并且新的故事还在继续。**唱衰RPC也好，唱红RPC也罢。说“炒冷饭”也好，说“取其精华，去其糟粕”也罢。**我们所处的环境在不停变化，所面临的问题也一变再变。RPC当然不是银弹，也或许真的深陷十年轮回，但我们必然不是西西弗斯。

**技术终将过时，后浪终将拍打前浪。前人在技术道路上的探索，或许我们早已遗忘。但我相信那些熠熠生辉的名字都化作了光。而今日技术上所有的辉煌，都是站在巨人的肩膀上。**

- - -

## **参考资料&推荐阅读**

* [RPC][2]
* [RPC is Not Dead: Rise, Fall and the Rise of Remote Procedure Calls][3]
* [CORBA的兴衰][4]
* [CORBA简介][5]
* [CORBA与RPC的比较（转）][6]
* [CORBA GIOP消息格式学习][7]
* [gRPC官网][8]
* [Apache Thrift官网][9]
* [fbthrift github][10]
* [finagle官网][11]
* [Dubbo官网][12]
* A Critique of the Remote Procedure Call Paradigm
* A Note on Distributed Computing

[0]: https://www.zhihu.com/people/JellyWong
[1]: http://link.zhihu.com/?target=https%3A//www.seas.harvard.edu/directory/waldo
[2]: http://link.zhihu.com/?target=http%3A//christophermeiklejohn.com/pl/2016/04/12/rpc.html
[3]: http://link.zhihu.com/?target=http%3A//dist-prog-book.com/chapter/1/rpc.html
[4]: http://link.zhihu.com/?target=https%3A//wenku.baidu.com/view/9c61422bddccda38376baf8b.html
[5]: http://link.zhihu.com/?target=http%3A//blog.csdn.net/chjttony/article/details/6543116
[6]: http://link.zhihu.com/?target=http%3A//blog.163.com/woshihezhonghua%40126/blog/static/127143636201311461423417/
[7]: http://link.zhihu.com/?target=http%3A//www.cnblogs.com/mosmith/p/5196100.html
[8]: http://link.zhihu.com/?target=https%3A//grpc.io/
[9]: http://link.zhihu.com/?target=http%3A//thrift.apache.org/docs/
[10]: http://link.zhihu.com/?target=https%3A//github.com/facebook/fbthrift
[11]: http://link.zhihu.com/?target=https%3A//twitter.github.io/finagle/
[12]: http://link.zhihu.com/?target=http%3A//dubbo.io/
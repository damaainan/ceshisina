# [PHP怎么做服务化微服务](https://www.iamle.com/archives/2422.html)

[2018-04-17][0] by [wwek][1] · [0评论][2]

# PHP在服务化微服务中遇到的问题

项目大了后服务化是必然的！  
想象下几十个PHP项目，里面有大多数项目有同样的功能需求，我们可以用复制代码解决  
但是对代码维护来说简直是噩梦，一次调整，调整多个项目，简直爽歪歪  
先谈服务化拆分，再谈用微服务落地。 做好服务化是目的，用微服务落地是实现方式  
那么微服务落地需要一大堆的服务治理能力来支撑，服务注册发现，断路器，网关，RPC等等  
微服务中服务注册发现，总不可能手动管理各种服务，那么得有服务注册发现这个东西  
由于apache php ，PHP-FPM php不是常驻内存的方式运行，导致了在服务注册发现等方便不能做，服务注册发现又是基础  
正因为这个特性让PHP在虚拟主机年代大放异彩，也是因为本身特性导致PHP在服务化，微服务领域落地困难，不过就没解决办法了？ 办法是有，但是小公司玩不起来。下面看看市面上的解决方案。

# 目前用PHP做微服务的解决方案

要用PHP做微服务必须要搞定微服务的服务注册发现问题

## Agent模式

微博采用了这样的方式，在跑PHP-FPM的机器上跑了一个Agent（这个和后面会讲到的Service Mesh的 Sidecar模式很像）  
通过Agent去完成服务注册发现，调用方通过Agent去调用。Agent作为一个包裹层。  
Agent其实是后面在Service Mesh节提到的Sidecar的雏形。

## 以Swoole为基础的常驻内存damon模式

Swoole是PHP的一个扩展  
使 PHP 开发人员可以编写高性能的异步并发 TCP、UDP、Unix Socket、HTTP，WebSocket 服务。Swoole 可以广泛应用于互联网、移动通信、企业软件、云计算、网络游戏、物联网（IOT）、车联网、智能家居等领域。  
基于Swoole开发的php程序是直接常驻内存damon的方式运行的  
所以这样就可以方便的做服务注册和发现了  
基于Swoole体系的开源PHP微服务框架有  
[Swoft][3]  
[PHP-MSF][4]  
[GroupCo][5]  
[SwooleDistributed][6]  
[Tencent/Tars][7]

# 综述PHP微服务落地

综上述  
#### 方式1 以微博为代表的Agent代理模式
（php-fpm模式运行的php程序因为运行机制问题，导致只有Agent的模式才能做服务注册发现，极少公司有这个技术支撑）  

#### 方式2 以Swoole为基石的常驻内存damon模式  
生产可用问题，没有广泛的使用经验，目前有部分有强力技术支持的公司在运用，如果要用于自己的环境需要有技术团队去完成这个落地，需要开发一些配套的管控基础设施  
毕竟没有服务治理能力贸然上微服务就是自己搞死自己的事

# 未来PHP做微服务的解决方案

Java体系以 Spring Cloud、Dubbo为代表的微服务框架得到广泛应用的时候无疑是对java编程语言的助推加持  
现有的微服务框架不是就完美了，任何事物都是具备两面性的，现有的框架面临SDK更新，业务代码侵入，编程语言绑定（要做非常多的SDK）等诸多问题

Linkerd横空出世，提出了一个叫做`Service Mesh`的东西中文翻译叫做`服务网格`  
随后以Google Ibm联合起来紧跟着发起了`Istio`项目  
`Service Mesh`是一个非常新的概念，在2017年才提出，被誉为下一代微服务框架  
试图解决现在的微服务框架的诸多问题，最终实现包含现有框架的所有功能，而且还能实现业务代码零侵入

Service Mesh使用了Sidecar模式接管了网络，所以才能实现业务代码零侵入  
正因为Service Mesh的这样的架构设计，所以可以真正的实现编程语言无关性，不同服务可以使用不同的编程语言，不需要为不同的语言每个都去维护SDK

那么用PHP做的服务，不论是apache php ，PHP-FPM php， 还是Swoole damon都可以，可以做到真正的语言无关，应用程序本身是不关心微服务那一堆事情的

目前Service Mesh还存在的问题  
体系还是非常早期的阶段，以Istio为例，目前大部分特性都是处在Alpha阶段（https://istio.io/about/feature-stages.html）  
性能损失问题，据网上看到的测试Istio目前的版本有40%的性能损失（我觉得这个不是重点，只看字面40%，想象下实际业务场景呢？ 解决的问题的收益远大于问题，况且后期肯定有优化的空间）  
可能需要K8s才能更好的落地，以Istio为例虽然不强制依赖k8s，但是在k8s运行Istio才是最佳选择，使用k8s也是需要学习成本的（我觉得用k8s不是问题，中小公司可以用云平台直接提供的k8s能力，大公司也不用担心没人运维k8s的问题）  
Service Mesh到规模落地时间可能还有2年左右的时间

业务代码开发为什么要关注服务治理的东西，微服务的东西？ 这就是抽象成一层框架干的事情

# 作为phper未来还有机会么

诚然PHP在微服务时代被沦为了“前端”语言，写PHP的在大公司沦为“套模板”的。  
在Java Spring Cloud 全家桶面前，phper是看着人家的工具库牛逼。  
phper是否没机会了呢，php是否没机会了呢  
仅仅是从语言角度讲，首先phper当然有机会，php也当然有机会，phper不要局限在php语言本身，js框架vue，Golang都是可以学习的目标  
再次市面上还有大量的web站点只需要php就可以快速简单的达成，根本不需要服务化，什么微服务，老夫拿起php连上redis、mysql就是干，单体应用分分钟出活  
phper不用担心没出路，php在web领域的优势、市场需求都会验证这一点

需要2年时间，`Service Mesh`在中大型公司一定会落地，小公司也会在云平台上找到落地的可能，php一样可以干微服务“后端”干的事！

[0]: https://www.iamle.com/archives/2422.html
[1]: https://www.iamle.com/archives/author/wwek
[2]: https://www.iamle.com/archives/2422.html#respond
[3]: https://www.swoft.org/
[4]: https://github.com/pinguo/php-msf
[5]: https://github.com/fucongcong/GroupCo
[6]: https://github.com/SwooleDistributed/SwooleDistributed
[7]: https://github.com/Tencent/Tars
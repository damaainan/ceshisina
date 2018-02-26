# 控制反转（IOC）和依赖注入（DI）的区别

阅读 101，3 天前 发布，来源：[blog.csdn.net][0]

IOC inversion of control 控制反转

DI Dependency Injection 依赖注入

要理解这两个概念，首先要搞清楚以下几个问题：

* 参与者都有谁？
* 依赖：谁依赖于谁？为什么需要依赖？
* 注入：谁注入于谁？到底注入什么？
* 控制反转：谁控制谁？控制什么？为何叫反转（有反转就应该有正转了）？
* 依赖注入和控制反转是同一概念吗？

 下面就来简要的回答一下上述问题，把这些问题搞明白了，IoC/DI也就明白了。  
（1）参与者都有谁：

 一般有三方参与者，一个是某个对象；一个是IoC/DI的容器；另一个是某个对象的外部资源。  
又要名词解释一下，某个对象指的就是任意的、普通的Java对象; IoC/DI的容器简单点说就是指用来实现IoC/DI功能的一个框架程序；对象的外部资源指的就是对象需要的，但是是从对象外部获取的，都统称资源，比如：对象需要的其它对象、或者是对象需要的文件资源等等。

 （2）谁依赖于谁：

 当然是某个对象依赖于IoC/DI的容器

 （3）为什么需要依赖：

 对象需要IoC/DI的容器来提供对象需要的外部资源

 （4）谁注入于谁：

 很明显是IoC/DI的容器 注入 某个对象

 （5）到底注入什么：

 就是注入某个对象所需要的外部资源

 （6）谁控制谁：

 当然是IoC/DI的容器来控制对象了

 （7）控制什么：

 主要是控制对象实例的创建

 （8）为何叫反转：

 反转是相对于正向而言的，那么什么算是正向的呢？考虑一下常规情况下的应用程序，如果要在A里面使用C，你会怎么做呢？当然是直接去创建C的对象，也就是说，是在A类中主动去获取所需要的外部资源C，这种情况被称为正向的。那么什么是反向呢？就是A类不再主动去获取C，而是被动等待，等待IoC/DI的容器获取一个C的实例，然后反向的注入到A类中。

用图例来说明一下，先看没有IoC/DI的时候，常规的A类使用C类的示意图，如图7所示：

![][1]

  
图7 常规A使用C示意图

当有了IoC/DI的容器后，A类不再主动去创建C了，如图8所示：

![][2]

  
图8 A类不再主动创建C

  
而是被动等待，等待IoC/DI的容器获取一个C的实例，然后反向的注入到A类中，如图9所示：

![][3]

  
图9 有IoC/DI容器后程序结构示意图

（9）依赖注入和控制反转是同一概念吗？

根据上面的讲述，应该能看出来，依赖注入和控制反转是对同一件事情的不同描述，从某个方面讲，就是它们描述的角度不同。 依赖注入是从应用程序的角度在描述，可以把依赖注入描述完整点：应用程序依赖容器创建并注入它所需要的外部资源 ； 而控制反转是从容器的角度在描述，描述完整点：容器控制应用程序，由容器反向的向应用程序注入应用程序所需要的外部资源。

   
（10）小结一下：

其实IoC/DI对编程带来的最大改变不是从代码上，而是从思想上，发生了“主从换位”的变化。应用程序原本是老大，要获取什么资源都是主动出击，但是在IoC/DI思想中，应用程序就变成被动的了，被动的等待IoC/DI容器来创建并注入它所需要的资源了。  
这么小小的一个改变其实是编程思想的一个大进步，这样就有效的分离了对象和它所需要的外部资源，使得它们松散耦合，有利于功能复用，更重要的是使得程序的整个体系结构变得非常灵活



（11）接下演示一下依赖注入机制的过程

 代码 2

 待注入的业务对象 Content.java
```java
package com.zj.ioc.di.ctor;

import com.zj.ioc.di.Content;

public  class MyBusiness {

  private Content myContent ;

  public MyBusiness(Content content) {

  myContent = content;

  }

  public  void doBusiness(){

  myContent .BusniessContent();

  }

  public  void doAnotherBusiness(){

  myContent .AnotherBusniessContent();

  }

}
```
 MyBusniess 类展示了一个业务组件，它的实现需要对象 Content 的注入。代码 3 ，代码 4 ，代码 5 ， 6 分别演示构造子注入（ Constructor Injection ），设值注入（ Setter Injection ）和接口注入（ Interface Injection ）三种方式。

 代码 3

构造子注入（Constructor Injection） MyBusiness.java
```java
package com.zj.ioc.di.ctor;

import com.zj.ioc.di.Content;

public  class MyBusiness {

  private Content myContent ;

  public MyBusiness(Content content) {

  myContent = content;

  }

  public  void doBusiness(){

  myContent .BusniessContent();

  }

  public  void doAnotherBusiness(){

  myContent .AnotherBusniessContent();

  }

}
```
 代码 4

设值注入（Setter Injection） MyBusiness.java
```java
package com.zj.ioc.di.iface;

import com.zj.ioc.di.Content;

public  interface InContent {

  void createContent(Content content);

}
```
 代码 5

 设置注入接口 InContent.java
```java
package com.zj.ioc.di.iface;

import com.zj.ioc.di.Content;

public  interface InContent {

  void createContent(Content content);

}
```
 代码 6 接口注入（ Interface Injection ） MyBusiness.java
```java
package com.zj.ioc.di.iface;

import com.zj.ioc.di.Content;

public  class MyBusiness implements InContent{

  private Content myContent ;

  public  void createContent(Content content) {

  myContent = content;

  }

  public  void doBusniess(){

  myContent .BusniessContent();

  }

  public  void doAnotherBusniess(){

  myContent .AnotherBusniessContent();

  }

}
```
[0]: /r/1250000011573149?shareId=1210000011573150
[1]: ../img/1460000011573151.png
[2]: ../img/1460000011573152.png
[3]: ../img/1460000011573153.png
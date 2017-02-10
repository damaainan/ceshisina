迭代器模式的定义
迭代器模式（Iterator Pattern）目前已经是一个没落的模式，基本上没人会单独写一个迭代器，除非是产品性质的开发， 其定义如下：Provide a way to access the elements of an aggregate object sequentially without exposing its underlying representation.（ 它提供一种方法访问一个容器对象中各个元素， 而又不需暴露该
对象的内部细节。 ）
迭代器是为容器服务的， 那什么是容器呢？ 能容纳对象的所有类型都可以称之为容
器，例如Collection集合类型、Set类型等，迭代器模式就是为解决遍历这些容器中的元素而诞生的。 


角色：● Iterator抽象迭代器
抽象迭代器负责定义访问和遍历元素的接口， 而且基本上是有固定的3个方法： first()获得第一个元素， next()访问下一个元素，isDone()是否已经访问到底部（Java叫做hasNext()方法） 。
● ConcreteIterator具体迭代器
具体迭代器角色要实现迭代器接口， 完成容器元素的遍历。
● Aggregate抽象容器
容器角色负责提供创建具体迭代器角色的接口， 必然提供一个类似createIterator()这样的方法， 在Java中一般是iterator()方法。
● Concrete Aggregate具体容器
具体容器实现容器接口定义的方法， 创建出容纳迭代器的对象。



迭代器模式的应用

迭代器现在应用得越来越广泛了， 甚至已经成为一个最基础的工具。一些大师级人物甚至建议把迭代器模式从23个模式中删除， 为什么呢？就是因为现在它太普通了，已经融入到
各个语言和工具中了，比如PHP中你能找到它的身影，Perl也有它的存在，甚至是前台的页
面技术AJAX也可以有它的出现（如在Struts2中就可以直接使用iterator）。基本上，只要你不是在使用那些古董级（ 指版本号） 的编程语言的话， 都不用自己动手写迭代器


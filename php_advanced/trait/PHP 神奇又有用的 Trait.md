## PHP 神奇又有用的 Trait

来源：[https://mp.weixin.qq.com/s/Tn6mRxaBr42c1fsYmBBSeA](https://mp.weixin.qq.com/s/Tn6mRxaBr42c1fsYmBBSeA)

时间 2018-12-12 09:47:29

 
php和java,c++一样都是单继承模式。但是像python，是支持多继承（即Mixin模式）。那么如何在php中实现多继承模式？这就需要使用trait。
 
### Trait使用方式:  
 
![][0]
 
### Trait使用场景  

 
* 有些功能不需要类的方法属性，但是在不同的类都有使用需求。例如上面的对象转数组方法。
这种情况可以使用一个基类定义toArray方法，则需要将这类基础方法定义在尽可能顶层的基类当中，保证所有的类都能够调用这个方法。
  
* 类因为某些需求，已经继承了第三方类对象。例如第三方orm模型类。这种情况如果要给类附加一些公共的功能，除了创建一个继承于orm模型的基类，复制一套公共功能的代码之外，就可以使用trait。

 
### trait使用注意  
 
#### 方法优先级
 
上面输出内容分别为`model:model`,`trait:model2`,`model:model`,`trait:model2`.可以看出，trait方法优先级为 **`当前对象>trait>父类`**  ,以上规则同样使用于静态调用。
 
![][1]
 
属性定义要特别小心！！trait中可以定义属性。但是不能和`use`trait当前类定义的属性相同,否则会报错:`define the same property`。但是，如果父类使用了trait，子类定义trait中存在的属性，则没有问题。
 
![][2]
 
私有属性私有方法。triat中可以方位use类的私有属性私有方法！！
 
从以上可以看出,trait本身是对类的一个扩展，在trait中使用`$this`,`self`,`static`,`parent`都与当前类一样,zend底层将trait代码嵌入到类当中，相当于底层帮我们实现了代码复制功能。
 
![][3]
 
#### 多个trait相同方法。
 
多trait相同的方法，需要使用instanceof 指定使用哪个trait的方法。instanceof后面的使用的trait。可以使用as设置添加方法别名（添加，原有方法还是能调用！！）。as还可以改变方法的访问控制
 `Arrayabletrait2::logname as private`改为私有方法。


[0]: https://img0.tuicool.com/NRvYbiI.png 
[1]: https://img2.tuicool.com/BRZfu2B.jpg 
[2]: https://img1.tuicool.com/Nj6nEny.jpg 
[3]: https://img1.tuicool.com/qqueuuY.jpg 
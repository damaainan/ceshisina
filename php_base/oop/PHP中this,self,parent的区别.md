[PHP中this,self,parent的区别][0]

<font face=黑体>

 **{一}PHP中this,self,parent的区别之一this篇**

面向对象编程(OOP,Object OrientedProgramming)现已经成为编程人员的一项基本技能。利用OOP的思想进行PHP的高级编程，对于提高PHP编程能力和规划web开发构架都是很有意义的。

PHP5经过重写后，对OOP的支持额有了很大的飞跃，成为了具备了大部分面向对象语言的特性的语言，比PHP4有了很多的面向对象的特性。这里我主要谈的是 **this,self,parent** 三个关键字之间的区别。从字面上来理解，分别是指 **这、自己、父亲**。先初步解释一下，**this是指向当前对象的指针**（可以看成C里面的指针），**self是指向当前类的指针**，**parent是指向父类的指针**。我们这里频繁使用 **指针**来描述，是因为没有更好的语言来表达。关于指针的概念，大家可以去参考百科。

下面我们就根据实际的例子结合来讲讲。

```php
<?php

  classname          //建立了一个名为name的类
 {
    private$name;         //定义属性，私有

    //定义构造函数，用于初始化赋值
    function __construct( $name )
    {
         $this->name =$name;         //这里已经使用了this指针语句①
    }

    //析构函数
    function __destruct(){}

    //打印用户名成员函数
    function printname()
    {
         print( $this->name);             //再次使用了this指针语句②，也可以使用echo输出
    }
 }
 $obj1 = new name("PBPHome");   //实例化对象 语句③

 //执行打印
 $obj1->printname(); //输出:PBPHome
 echo"<br>";                                    //输出：回车

 //第二次实例化对象
 $obj2 = new name( "PHP" );

 //执行打印
 $obj2->printname();                         //输出：PHP
 ?>
```

说明：上面的类分别在 语句① 和 语句② 使用了this指针，那么当时this是指向谁呢？其实 **this是在实例化的时候来确定指向谁**，比如第一次实例化对象的时候( 语句③ )，那么当时`this`就是指向`$obj1`对象，那么执行 语句② 的打印时就把`print( $this-><name )` 变成了 `print($obj1t->name )`，那么当然就输出了"PBPHome"。第二个实例的时候，`print($this->name )`变成了`print( $obj2->name)`，于是就输出了"PHP"。所以说，`this`就是指向当前对象实例的指针，不指向任何其他对象或类。

## {二}。PHP中this,self,parent的区别之二`self`篇

此篇我们就`self`的用法进行讲解

首先我们要明确一点，`self`是指向 **类本身**，也就是`self`是 **不指向任何已经实例化的对象**，一般`self`使用来指向类中的静态变量。假如我们使用类里面 **静态**（一般用关键字`static`）的成员，我们也必须使用`self`来调用。还要注意使用`self`来调用静态变量必须使用 **`::`** (域运算符号)，见实例。

```php
<?php

    classcounter     //定义一个counter的类
    {
        //定义属性，包括一个静态变量$firstCount，并赋初值0 语句①  
        private static $firstCount = 0;
        private $lastCount;

        //构造函数
        function __construct()
        {
             $this->lastCount =++self::$firstCount;      //使用self来调用静态变量 语句②
        }

        //打印lastCount数值
        function printLastCount()
        {
             print( $this->lastCount );
        }
    }

  //实例化对象
  $obj = new Counter();

 $obj->printLastCount();                             //执行到这里的时候，程序输出1

 ?>
```

这里要注意两个地方 语句① 和 语句② 。我们在 语句① 定义了一个静态变量`$firstCount`，那么在 语句② 的时候使用了`self`调用这个值，那么这时候我们调用的就是 **类自己定义的静态变量**`$frestCount`。我们的静态变量与下面对象的实例无关，它只是跟类有关，那么我调用类本身的的，那么我们就无法使用`this`来引用，因为`self`是指向类本身，与任何对象实例无关。然后前面使用的 **`this`调用的是实例化的对象  `$obj`**，大家不要混淆了。

关于`self`就说到这里，结合例子还是比较方便理解的。第二篇结束。

**{三}PHP中this,self,parent的区别之三parent篇**

此篇我们就`parent`的用法进行讲解。

首先，我们明确，**parent是指向父类的指针**，一般我们 **使用`parent`来调用父类的** **构造函数** 。实例如下：

```php
<?php
 //建立基类Animal
 class Animal
 {
    public $name; //基类的属性，名字$name

    //基类的构造函数，初始化赋值
    public function __construct( $name )
    {
         $this->name = $name;
    }
 }

 //定义派生类Person 继承自Animal类
 class Person extends Animal
 {
    public$personSex;       //对于派生类，新定义了属性$personSex性别、$personAge年龄
    public $personAge;

    //派生类的构造函数
    function __construct( $personSex, $personAge )
    {
         parent::__construct( "PBPHome");    //使用parent调用了父类的构造函数 语句①
         $this->personSex = $personSex;
         $this->personAge = $personAge;
    }

    //派生类的成员函数，用于打印，格式：名字 is name,age is 年龄
    function printPerson()
    {
         print( $this->name. " is ".$this->personSex. ",age is ".$this->personAge );
     }
 }

 //实例化Person对象
 $personObject = new Person( "male", "21");

 //执行打印
 $personObject->printPerson();//输出结果：PBPHome is male,age is 21

 ?>

```

里面同样含有`this`的用法，大家自己分析。我们注意这么个细节：成员属性都是`public`（公有属性和方法，类内部和外部的代码均可访问）的，特别是父类的，**这是为了供继承类通过this来访问**。关键点在 语句① ：`parent::__construct( "heiyeluren")`，这时候我们就使用`parent`来调用父类的构造函数进行对父类的初始化，这样，继承类的对象就都给赋值了`name`为`PBPHome`。我们可以测试下，再实例化一个对象`$personObject1`，执行打印后`name`仍然是`PBPHome`。

**总结**：  
`this`是指向对象实例的一个指针，在实例化的时候来确定指向；  
`self`是对类本身的一个引用，一般用来指向类中的静态变量；  
`parent`是对父类的引用，一般使用`parent`来调用父类的构造函数。

</font>

[0]: http://www.cnblogs.com/myjavawork/articles/1793664.html
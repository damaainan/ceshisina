# [PHP中“简单工厂模式”实例讲解][0]


原创文章,转载请注明出处：[http://www.cnblogs.com/hongfei/archive/2012/07/07/2580776.html][1]

简单工厂模式：  
①抽象基类：类中定义抽象一些方法，用以在子类中实现  
②继承自抽象基类的子类：实现基类中的抽象方法  
③工厂类：用以实例化对象

看完文章再回头来看下这张图，效果会比较好

![][2]

 
```php
//采用封装方式

<?php
    class Calc{
        /**
         * 计算结果
         *
         * @param int|float $num1
         * @param int|float $num2
         * @param string $operator
         * @return int|float
         */
        public function calculate($num1,$num2,$operator){
            try {
                $result=0;
                switch ($operator){
                    case '+':
                        $result= $num1+$num2;
                        break;
                    case '-':
                        $result= $num1-$num2;
                        break;
                    case '*':
                        $result= $num1*$num2;
                        break;
                    case '/':
                        if ($num2==0) {
                            throw new Exception("除数不能为0");
                        }
                        $result= $num1/$num2;
                        break;
                }  
            return $result;
            }catch (Exception $e){
                echo "您输入有误:".$e->getMessage();
            }
        }
    }
    $test=new Calc();
//    echo $test->calculate(2,3,'+');//打印:5
    echo $test->calculate(5,0,'/');//打印:您输入有误:除数不能为0
?>
```
优点：以上代码使用了面向对象的封装特性，只要有了include这个类，其他页面就可以随便使用了

缺点：无法灵活的扩展和维护  
比如：想要增加一个“求余”运算，需要在switch语句块中添加一个分支语句，代码需要做如下改动

 
```php
//添加分支语句

<?php
    class Calc{
        public function calculate($num1,$num2,$operator){
            try {
                $result=0;
                switch ($operator){
                    //......省略......
                    case '%':
                        $result= $num1%$num2;
                        break;
                    //......省略......
                }
            }catch (Exception $e){
                echo "您输入有误:".$e->getMessage();
            }
        }
    }
?>
```
代码分析：用以上方法实现给计算器添加新的功能运算有以下几个缺点

①需要改动原有的代码块，可能会在为了“添加新功能”而改动原有代码的时候，不小心将原有的代码改错了  
②如果要添加的功能很多，比如：‘乘方’，‘开方’，‘对数’，‘三角函数’，‘统计’，或者添加一些程序员专用的计算功能，比如：And, Or, Not, Xor，这样就需要在switch语句中添加N个分支语句。想象下，一个计算功能的函数如果有二三十个case分支语句，代码将超过一屏，不仅令代码的可读性大大降低，关键是，为了添加小功能，还得让其余不相关都参与解释，这令程序的执行效率大大降低  
解决途径：采用OOP的继承和多态思想

 
```php
//简单工厂模式的初步实现
 <?php
     /**
      * 操作类
      * 因为包含有抽象方法，所以类必须声明为抽象类
      */
     abstract class Operation{
         //抽象方法不能包含函数体
         abstract public function getValue($num1,$num2);//强烈要求子类必须实现该功能函数
     }
     /**
      * 加法类
      */
     class OperationAdd extends Operation {
         public function getValue($num1,$num2){
             return $num1+$num2;
         }
     }
     /**
      * 减法类
      */
     class OperationSub extends Operation {
         public function getValue($num1,$num2){
             return $num1-$num2;
         }
     }
     /**
      * 乘法类
      */
     class OperationMul extends Operation {
         public function getValue($num1,$num2){
             return $num1*$num2;
         }
     }
     /**
      * 除法类
      */
     class OperationDiv extends Operation {
         public function getValue($num1,$num2){
             try {
                 if ($num2==0){
                     throw new Exception("除数不能为0");
                 }else {
                     return $num1/$num2;
                 }
             }catch (Exception $e){
                 echo "错误信息：".$e->getMessage();
             }
         }
     }
 ?>
```
这里采用了面向对象的继承特性，首先声明一个虚拟基类，在基类中指定子类务必实现的方法（getValue()）

分析：通过采用面向对象的继承特性，我们可以很容易就能对原有程序进行扩展，比如:‘乘方’，‘开方’，‘对数’，‘三角函数’，‘统计’等等。

 
```php
    <?php
        /**
         * 求余类（remainder）
         *
         */
        class OperationRem extends Operation {
            public function getValue($num1,$num2){
                return $num1%$num12;
            }
        }
    ?>
```
我们只需要另外写一个类（该类继承虚拟基类）,在类中完成相应的功能（比如：求乘方的运算）,而且大大的降低了耦合度，方便日后的维护及扩展

现在还有一个问题未解决,就是如何让程序根据用户输入的操作符实例化相应的对象呢？  
解决办法：使用一个单独的类来实现实例化的过程，这个类就是工厂  
代码如下:

 
```php
<?php
    /**
     * 工程类，主要用来创建对象
     * 功能：根据输入的运算符号，工厂就能实例化出合适的对象
     *
     */
    class Factory{
        public static function createObj($operate){
            switch ($operate){
                case '+':
                    return new OperationAdd();
                    break;
                case '-':
                    return new OperationSub();
                    break;
                case '*':
                    return new OperationSub();
                    break;
                case '/':
                    return new OperationDiv();
                    break;
            }
        }
    }
    $test=Factory::createObj('/');
    $result=$test->getValue(23,0);
    echo $result;
?>
```

[0]: http://www.cnblogs.com/hongfei/archive/2012/07/09/2580776.html
[1]: http://www.cnblogs.com/hongfei/archive/2012/07/07/2580776.html
[2]: ../img/2012070718075239.png
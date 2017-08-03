# 完整的PHP依赖倒置原则例程

 时间 2017-07-23 09:26:23 

原文[https://segmentfault.com/a/1190000010307619][1]



设计模式中 **依赖倒置原则** ( Dependence Inversion Principle )的定义是“ 高层模块不应该依赖低层模块，二者都应该依赖其抽象；抽象不应该依赖细节；细节应该依赖抽象。 ”理解起来并不难，但在具体实现上，网上给出的很多 PHP 示例都有缺陷。 

就拿 [这篇文章][3] 来说，概念讲的没有问题，但在具体实现上，特别是代码中有很多错误，不能体现 PHP 特色，比如 PHP 中应该用 -> 而不是用 . 来调用方法，变量名应该带 $ 等很多错误，这就不说了，最关键的是即使把这些语法错误都改正，例子也不能说明原则，不够有说服力。因为即使不加接口或抽象类，妈妈也一样能给孩子讲故事、读报纸、读杂志。 

以下可运行代码，没有用到任何接口和抽象类，一样可以实现功能，并且可扩展，不需要修改 Mother 类里的任何代码，一样可以轻松自如地让妈妈读各种读物，无非就是在上面追加各种 class ，只要这个 class 里有 getContent 方法，妈妈全部可以识别： 

```php
    <?php
    class Book {
        public function getContent(){
            return "很久很久以前有一个阿拉伯的故事……\n";
        }
    }
    
    class Newspaper {
        public function getContent(){
            return "林书豪17+9助尼克斯击败老鹰……\n";
        }
    }
    
    class Mother{
        public function narrate($book){
            echo "妈妈开始讲故事\n";
            echo $book->getContent();
        }
    }
    
    class Client{
        public static function main(){
            $mother = new Mother();
            $mother->narrate(new Book());
            $mother->narrate(new Newspaper());
        }
    }
    
    $client = new Client();
    $client->main();
```

既然如此随意，还如何体现依赖倒置呢？这是因为 PHP 是 **弱类型语言** ，特点就是不需要为变量指定类型，导致的结果就是只要你的 class 里有我需要调用的方法（在这里是 getContent 方法），那就无论如何也不会出错，至于你是不是实现了什么 interface 接口，都无所谓的。像这样，是无法真正体现依赖倒置原则的。那到底如何才能真正体现依赖倒置呢？秘诀就是我们通过使用PHP的 **类型约束** 来规定 narrate 函数的 $book 参数必须是一个接口： 

    class Mother{
        public function narrate(IReader $book){
            echo "妈妈开始讲故事\n";
            echo $book->getContent();
        }
    }

在这里，我们规定了 $book 参数必须是一个 IReader 接口，那么凡是需要让妈妈讲的读物都必须是对于 IReader 这个接口的一个实现，否则就会报错。完整代码如下： 

```php
    <?php
    interface IReader{
        public function getContent();
    }
    
    class Book implements IReader {
        public function getContent(){
            return "很久很久以前有一个阿拉伯的故事……\n";
        }
    }
    
    class Newspaper implements IReader {
        public function getContent(){
            return "林书豪17+9助尼克斯击败老鹰……\n";
        }
    }
    
    class Mother{
        public function narrate(IReader $book){
            echo "妈妈开始讲故事\n";
            echo $book->getContent();
        }
    }
    
    class Client{
        public static function main(){
            $mother = new Mother();
            $mother->narrate(new Book());
            $mother->narrate(new Newspaper());
        }
    }
    
    $client = new Client();
    $client->main();
```

你可以试着把 class Newspaper 后面的 implements IReader 去掉然后运行一下，马上就会报错： 

    PHP Fatal error:  Uncaught TypeError: Argument 1 passed to Mother::narrate() must implement interface IReader, instance of Newspaper given, called in /Users/zhangjing/Projects/phpdesignpattern/client.php on line 29 and defined in /Users/zhangjing/Projects/phpdesignpattern/client.php:19

所以结论是： 对于PHP这种弱类型语言来讲，要想真正实现依赖倒置原则，必须加上类型约束，否则实现的只是表象，并不能真正体现原则的作用。


[1]: https://segmentfault.com/a/1190000010307619
[3]: http://www.yii-china.com/post/detail/331.html
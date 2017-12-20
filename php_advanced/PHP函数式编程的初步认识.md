## [PHP函数式编程的初步认识](https://segmentfault.com/a/1190000012402859)


 

![][0]

> 最近朋友推荐这本书：[> Functional PHP][1]>  ，很多对于程序设计方面的思路值得多思考和借鉴。函数式编程不是一个框架或工具，而是一种编写代码的方式。FP 是一种软件开发风格，主要强调功能的使用，个人觉得对于重构代码很有帮助。书中也谈到了例如 PHP5.3 中引入的闭包函数和高阶函数，在实际开发过程中善于活学活用也是函数式的灵魂所在。

### PHP 新版本的解读

> 增加了严格的键入和标量类型声明类型声明允许你用合适的类或标量类型（ boolean，integer，string，MyClass 等）限定任何函数参数。这些在PHP 5中被部分支持为“类型提示”，但没有标量支持。在PHP 7中，你也可以声明函数返回值的类型。  
> 作为一种动态语言，PHP 将总是试图将错误类型的值强制转换为期望的标量类型。  
> 例如，当给定一个字符串时，需要一个整数参数的函数将强制该值为一个整数，文件顶部引用强制类型检测模式

![][2]

    declare(strict_types=1);

> 参数异常会抛出如下错误e

    PHP Warning:  Uncaught TypeError: Argument 1 passed to increment() must be of the type integer, string given...

### 声明性编码

> 感觉翻译后的理解很模糊，看例子可能会更加清晰透彻一点。“函数式编程首先是一个声明式编程范例。这意味着它们表达了操作的逻辑连接，而不会泄露它们是如何实现的，或者数据如何实际流经它们，它着重于使用表达式来描述程序的逻辑是什么”

> 在 PHP 中，声明性代码是使用高阶函数来实现的，个人觉得作者的意思还是灵活运用系统内置函数处理逻辑，放弃复杂而不简洁的逻辑控制，代码越复杂，重构越麻烦，bug率更高。一个简单的例子走一个。

    // method 1
    
    $array = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9];
    for($i = 0; $i < count($array); $i++) {
        $array[$i] = pow($array[$i], 2);
    }
    
    print_r($array); //-> [0, 1, 4, 9, 16, 25, 36, 49, 64, 81]
    
    // method 2
    $square = function (int $num): int {
        return pow($num, 2);
    };
    
    print_r(array_map($square, $array))
    
    // q: 结果累加
    function add(float $a, float $b): float {
         return $a + $b;
    }
    
    print_r(array_reduce(array_map($square, $array), 'add')); //-> 285
    

### 设计为不变性和无状态

> 例如是上面的例子中使用的 array_map ，好处在于它不可变，也就是说不会改变原始数组的内容，使用不可变变量进行编码好处如下：

* 程序出现异常的主要原因之一是对象的状态无意中改变，或者其引用变为空。不可变对象可以传递给任何函数，它们的状态将始终保持不变。
* 不可变的数据结构在共享内存多线程应用程序中非常重要。在本书中，我们不会多谈关于并发处理的问题，因为 PHP 进程大部分是孤立运行的。现在，无论是否设计并行性，无状态对象都是在许多常见PHP部署中广泛使用的模式。例如，作为最佳实践，Symfony 服务（或服务对象）应该始终是无状态的。一个服务不应该持续任何状态，并提供一组临时函数，它们将处理它所在的域，执行某种业务逻辑的计算，并返回结果。


> PHP 对于不可变变量的支持很差，实际开发过程中使用常量定义 define const 关键字。对于 define 和 const 的比较。const 在编译时定义，这意味着编译器可以聪明地存储它们，但是你不能有条件地声明。用 define 声明的常量是多功能和动态的。因为编译器不会尝试为它们分配空间，直到它真正看到它们。defined($name) 在使用它的值之前，你应该经常检查是否定义了一个常量 constant($name)。举个例子

    // error : throw exception
    if (<some condition>) {
        const C1 = 'FOO';
    } else {
        const C2 = 'BAR';
    }
    
    // ok normal
    if (<some condition>) {
        define('C1', 'FOO')
    } else {
        define('C2', 'BAR')
    }
    

### 纯函数

> 函数式编程基于的前提是您将基于纯函数构建不可变的程序作为业务逻辑的构建块。

### 高阶 PHP

> 关于高阶函数和闭包本书都会提到，高阶函数被定义为可以接受其他函数作为参数或返回其他函数的函数。当然函数可以分配给变量。

> PHP 中的函数可以像对象一样进行操作。事实上，如果你要检查一个函数的类型，你会发现它们是[> Closure][3]  
> 的实例。将一个函数赋予给一个变量这个在实际应用中很常见。例如下面的例子

    $str = function (string $str1, string $str2) {
        return $str1 . ' ' . $str2;
    }
    
    $str('hello', 'word'); // output hello word;
    
    is_callable($str) // 1
    

> 这个代码使用匿名函数（RHS）并将其分配给变量 $str（LHS）。或者，您可以使用 is_callable() 来检查是否存在函数变量

> 函数也可以从其他函数返回。这是创建函数族的非常有用的技巧。

    function concatWith(string $a): callable {
        return function (string $b) use ($a): string {
            return $a . $b;
    };
    }
    
    $helloWith = concatWith('Hello');
    $helloWith('World'); // output -> 'Hello World'

> 提供函数作为参数, 创建了一个简单的函数，它接受一个可调用的函数并将其应用于其他参数

    function apply(callable $operator, $a, $b) {
        return $operator($a, $b);
    }
    
    $add = function (float $a, float $b): float {
        return $a + $b;
    };
    
    apply($add, 1, 2); // output -> 3
    
    // or power
    
    function apply(callable $operator): callable {
        return function($a, $b) use ($operator) {
            return $operator($a, $b);
        };
    }
    
    apply($add)(5, 5); //output -> 10
    
    $adder = apply($add);
    
    $adder(5, 5) // output -> 10
    

> 遇到另外一种情况，也就是两个数相除分母不能为0，这个时候构建一个空检查函数会比较好，时刻检查变量的值是个好习惯。

    function safeDivide(float $a, float $b): float {   
        return empty($b) ? NAN : $a / $b;
    }
    
    apply($safeDivide)(5, 0); //-> NAN
    
    $result = apply($safeDivide)(5, 0);
    if(!is_nan($result)) {
        return $result;
    } else {
        Log::warning('Math error occurred! Division by zero!');
    }
    

> “这种方法避免了抛出一个异常。回想一下抛异常的情况，它会导致程序堆栈展开和记录写入，但也不尊重代码的局部性原则。尤其是它不服从空间地域性，它指出应该依次执行的相关陈述应该相互靠近。这在 CPU 架构上有更多的应用，但也可以应用于代码设计。”这种翻译型的语句我还是日后在理解吧，说不定有一天就豁然开朗了，毕竟这是一条很遥远的路。

> PHP 还通过可调用的对象将其提升到了一个新的水平。现在，这不是一个真正的功能概念，但正确使用它可能是一个非常强大的技术。事实上，引擎盖下的 PHP 匿名函数语法被编译成一个类，并且有一个[> invoke()][4]>  方法。查资料的释义就是调用函数的方式调用一个对象时的回应方法

    class Demo
    {
        private $collect;
    
        public function __construct($num)
        {
            $this->collect = $num;
        }
    
        public function increment() : int {
    
            return ++$this->collect;
        }
    
        public function __invoke() {
            return $this->increment();
        }
    }
    
    $demo = new Demo(1);
    
    echo $demo(); // output -> 2

### 使用容器改善api

> 使用包装来控制对特定变量的访问并提供额外的行为。先看下例子中的这个 class ，下面的例子扩展性较强

    class Container {
        private $_value;
    
        private function __construct($value) {        
            $this->_value = $value;                
        }
    
        // Unit function
        public static function of($val) {            
            return new static($val);
        }
    
        // Map function
        public function map(callable $f) {
            return static::of(call_user_func($f, $this->_value));
        }
    
        // Print out the container
        public function __toString(): string {
            return "Container[ {$this->_value} ]";  
        }
    
        // Deference container
        public function __invoke() {
            return $this->_value;
        }
    }

    function container_map(callable $f, Container $c): Container {
        return $c->map($f);
    }

    $c = Container::of('</ Hello FP >')->map('htmlspecialchars')->map('strtolower');
    
    $c; //output-> Container[ </ hello fp > ]

### 关闭

> 在 PHP 5.4+之后，PHP中的所有函数都是从 Closure 类创建的对象。使用[> RFC][5]> 可以使代码更加简洁明了

    function addTo($a) {
        return function ($b) use ($a) {
               return $a + $b;
        };
    }
    
    $filter = function (callable $f): Container {
        return Container::of(call_user_func($f, $this->_value) ? $this->_value : 0);
    };
    
    $wrappedInput = Container::of(2);
    
    $validatableContainer = $filter->bindTo($wrappedInput, Container);
    
    $validatableContainer('is_numeric')->map(addTo(40)); // output-> 42
    
    $wrappedInput = Container::of('abc); $validatableContainer('is_numeric')->map(addTo(40)); // output-> 40
    

### 说明

> 关于这本书的详细内容和例子戳链接 [Functional PHP][6]，关于 函数式编程的 composer 包 [Functional PHP: Functional primitives for PHP][7]

    {
        "require": {
            "lstrojny/functional-php": "~1.2"
        }
    }

> 本质上这本书我还没有看完，翻译起来很多地方确实词不达意，我还是根据实际的举例逐个去理解的，此文章后续还会继续补充和追加学习心得。 Go PHP!

[0]: https://segmentfault.com/img/remote/1460000012402865
[1]: https://www.reallyli.xin/pdf/Functional-PHP.pdf
[2]: https://segmentfault.com/img/remote/1460000012402866
[3]: http://php.net/manual/en/class.closure.php
[4]: http://www.golaravel.com/post/magic-methods-and-magic-constants-in-php/
[5]: https://wiki.php.net/rfc/arrow_functions
[6]: https://leanpub.com/functional-php/read_full
[7]: https://github.com/lstrojny/functional-php
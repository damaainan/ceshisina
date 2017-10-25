# PHP代码简洁之道——SOLID原则

 时间 2017-10-24 15:28:27 

原文[http://developer.51cto.com/art/201710/555228.htm][1]

![][4]

SOLID 是Michael Feathers推荐的便于记忆的首字母简写，它代表了Robert Martin命名的最重要的五个面对对象编码设计原则：

* S: 单一职责原则 (SRP)
* O: 开闭原则 (OCP)
* L: 里氏替换原则 (LSP)
* I: 接口隔离原则 (ISP)
* D: 依赖反转原则 (DIP)

#### 单一职责原则 Single Responsibility Principle (SRP)

"修改一个类应该只为一个理由"。人们总是易于用一堆方法塞满一个类，如同我们在飞机上只能携带一个行李箱(把所有的东西都塞到箱子里)。这样做的问题是：从概念上这样的类不是高内聚的，并且留下了很多理由去修改它。将你需要修改类的次数降低到最小很重要。这是因为，当有很多方法在类中时，修改其中一处，你很难知晓在代码库中哪些依赖的模块会被影响到。

#### Bad:

```php
    <?php
    class UserSettings{     
        private $user;     
        public function __construct($user) 
        {         
            $this->user = $user; 
        }     
        public function changeSettings($settings) 
        {         
            if ($this->verifyCredentials()) {            
             // ... 
            } 
        }     
        private function verifyCredentials() 
        {         
        // ... 
        } 
    }  
```

#### Good:

```php
    <?php
    class UserAuth {     
    private $user;     
    public function __construct($user){         
        $this->user = $user; 
    }     
    public function verifyCredentials(){         
        // ... 
     
    } 
    } 
    class UserSettings {     
    private $user;     
    private $auth;     
    public function __construct($user) {         
      $this->user = $user;         
      $this->auth = new UserAuth($user); 
    }     
    public function changeSettings($settings){         
        if ($this->auth->verifyCredentials()) {             
        // ... 
            } 
        } 
    }  
```

#### 开闭原则 Open/Closed Principle (OCP)

正如Bertrand Meyer所述，"软件的实体(类, 模块, 函数,等)应该对扩展开放，对修改关闭。"这个原则是在说明应该允许用户在不改变已有代码的情况下增加新的功能。

#### Bad:

```php
    <?php
    abstract class Adapter{     
    protected $name;     
    public function getName(){         
        return $this->name; 
    } 
    } 
    class AjaxAdapter extends Adapter{     
    public function __construct(){      
          parent::__construct();         
          $this->name = 'ajaxAdapter'; 
     } 
    } 
    class NodeAdapter extends Adapter{     
        public function __construct(){    
            parent::__construct();         
            $this->name = 'nodeAdapter'; 
        } 
    } 
        class HttpRequester{     
        private $adapter;     
        public function __construct($adapter) 
        {         
            $this->adapter = $adapter; 
        }     
        public function fetch($url) 
        { 
            $adapterName = $this->adapter->getName();         
        if ($adapterName === 'ajaxAdapter') {             
            return $this->makeAjaxCall($url); 
            }  
        elseif ($adapterName === 'httpNodeAdapter') {             
            return $this->makeHttpCall($url); 
            } 
        }     
        private function makeAjaxCall($url) 
        {        // request and return promise 
        }     
        private function makeHttpCall($url) 
        {        // request and return promise 
        } 
    }  
```

在上面的代码中，对于HttpRequester类中的fetch方法，如果我新增了一个新的xxxAdapter类并且要在fetch方法中用到的话，就需要在HttpRequester类中去修改类(如加上一个elseif 判断)，而通过下面的代码，就可很好的解决这个问题。下面代码很好的说明了如何在不改变原有代码的情况下增加新功能。

Good:

```php
    <?php
    interface Adapter{     
        public function request($url); 
    } 
        class AjaxAdapter implements Adapter{     
        public function request($url) 
        {        // request and return promise 
        } 
    } 
    class NodeAdapter implements Adapter{     
        public function request($url) 
        {        // request and return promise 
        } 
    } 
        class HttpRequester{     
        private $adapter;     
        public function __construct(Adapter $adapter) 
        {
                $this->adapter = $adapter; 
        }     
        public function fetch($url) 
        {
                return $this->adapter->request($url); 
        } 
    }  
```

#### 里氏替换原则 Liskov Substitution Principle (LSP)

对这个概念最好的解释是：如果你有一个父类和一个子类，在不改变原有结果正确性的前提下父类和子类可以互换。这个听起来让人有些迷惑，所以让我们来看一个经典的正方形-长方形的例子。从数学上讲，正方形是一种长方形，但是当你的模型通过继承使用了"is-a"的关系时，就不对了。

#### Bad:

```php
    <?php
    class Rectangle{     
        protected $width = 0;     
        protected $height = 0;     
        public function render($area) 
        {        // ... 
        }     
        public function setWidth($width) 
        {        $this->width = $width; 
        }     
        public function setHeight($height) 
        {        $this->height = $height; 
        }     
        public function getArea() 
        {        return $this->width * $this->height; 
        } 
    } 
    class Square extends Rectangle{     
        public function setWidth($width) 
        {         
            $this->width = $this->height = $width; 
        }     
        public function setHeight(height) 
        {        $this->width = $this->height = $height; 
        } 
    } 
    function renderLargeRectangles($rectangles){     
        foreach ($rectangles as $rectangle) { 
            $rectangle->setWidth(4); 
            $rectangle->setHeight(5); 
            $area = $rectangle->getArea(); // BAD: Will return 25 for Square. Should be 20. 
            $rectangle->render($area); 
        } 
    } 
     
    $rectangles =  
    [new Rectangle(), new Rectangle(), new Square()]; 
    renderLargeRectangles($rectangles);  
```

#### Good:

```php
    <?php
    abstract class Shape{     
        protected $width = 0;     
        protected $height = 0;     
        abstract public function getArea();     
        public function render($area)    {        // ... 
        } 
    } 
    class Rectangle extends Shape{     
        public function setWidth($width) 
        {        $this->width = $width; 
        }     
        public function setHeight($height) 
        {        $this->height = $height; 
        }     
        public function getArea() 
        {        return $this->width * $this->height; 
        } 
    } 
    class Square extends Shape{     
        private $length = 0;     
        public function setLength($length) 
        {        $this->length = $length; 
        }     
        public function getArea() 
        {        return pow($this->length, 2); 
        } 
    } 
    function renderLargeRectangles($rectangles){     
    foreach ($rectangles as $rectangle) {         
    if ($rectangle instanceof Square) { 
                $rectangle->setLength(5); 
            } elseif ($rectangle instanceof Rectangle) { 
                $rectangle->setWidth(4); 
                $rectangle->setHeight(5); 
            } 
     
            $area = $rectangle->getArea();  
            $rectangle->render($area); 
        } 
    } 
     
    $shapes = [new Rectangle(), new Rectangle(), new Square()]; 
    renderLargeRectangles($shapes);  
```

#### 接口隔离原则

接口隔离原则："客户端不应该被强制去实现于它不需要的接口"。

有一个清晰的例子来说明示范这条原则。当一个类需要一个大量的设置项，为了方便不会要求客户端去设置大量的选项，因为在通常他们不需要所有的设置项。使设置项可选有助于我们避免产生"胖接口"

#### Bad:

```php
    <?php
    interface Employee{     
        public function work();     
        public function eat(); 
    } 
    class Human implements Employee{     
        public function work() 
        {        // ....working 
        }     
        public function eat() 
        {        // ...... eating in lunch break 
        } 
    }
    class Robot implements Employee{     
        public function work() 
        {        //.... working much more 
        }     
        public function eat() 
        {        //.... robot can't eat, but it must implement this method 
        } 
    }  
```

上面的代码中，Robot类并不需要eat()这个方法，但是实现了Emplyee接口，于是只能实现所有的方法了，这使得Robot实现了它并不需要的方法。所以在这里应该对Emplyee接口进行拆分，正确的代码如下：

#### Good:

```php
    <?php
    interface Workable{     
        public function work(); 
    } 
    interface Feedable{     
        public function eat(); 
    } 
    interface Employee extends Feedable, Workable{ 
    } 
    class Human implements Employee{     
        public function work() 
        {        // ....working 
        }     
        public function eat() 
        {        //.... eating in lunch break 
        } 
    }// robot can only work 
     
    class Robot implements Workable{     
        public function work() 
        {        // ....working 
        } 
    }  
```

#### 依赖反转原则 Dependency Inversion Principle (DIP)

这条原则说明两个基本的要点：

* 高阶的模块不应该依赖低阶的模块，它们都应该依赖于抽象
* 抽象不应该依赖于实现，实现应该依赖于抽象

这条起初看起来有点晦涩难懂，但是如果你使用过php框架(例如 Symfony)，你应该见过依赖注入(DI)对这个概念的实现。虽然它们不是完全相通的概念，依赖倒置原则使高阶模块与低阶模块的实现细节和创建分离。可以使用依赖注入(DI)这种方式来实现它。更多的好处是它使模块之间解耦。耦合会导致你难于重构，它是一种非常糟糕的的开发模式。

#### Bad:

```php
    <?php
    class Employee{     
        public function work() 
        {        // ....working 
        } 
    } 
    class Robot extends Employee{     
        public function work()    {        //.... working much more 
        } 
    } 
    class Manager{     
        private $employee;    
        public function __construct(Employee $employee) 
        {
                $this->employee = $employee; 
        } 
       public function manage() 
        {
                $this->employee->work(); 
        } 
    }  
```

Good:

```php
    <?php
    interface Employee{    
     public function work(); 
    } 
     class Human implements Employee{    
    public function work() 
        {        // ....working 
        } 
    } 
    class Robot implements Employee{     
    public function work() 
        {        //.... working much more 
        } 
    } 
    class Manager{     
    private $employee;     
    public function __construct(Employee $employee) 
        {
                $this->employee = $employee; 
        }
        public function manage() 
        {
                $this->employee->work(); 
        } 
    }  
```

#### 别写重复代码 (DRY)

这条原则大家应该都是比较熟悉了。

尽你最大的努力去避免复制代码，它是一种非常糟糕的行为，复制代码通常意味着当你需要变更一些逻辑时，你需要修改不止一处。

#### Bad:

```php
    <?php
    function showDeveloperList($developers){     
    foreach ($developers as $developer) { 
            $expectedSalary =  $developer->calculateExpectedSalary(); 
            $experience = $developer->getExperience(); 
            $githubLink = $developer->getGithubLink(); 
            $data = [ 
                $expectedSalary, 
                $experience, 
                $githubLink 
            ]; 
     
            render($data); 
        } 
    } 
    function showManagerList($managers){     
    foreach ($managers as $manager) { 
            $expectedSalary =  $manager->calculateExpectedSalary(); 
            $experience = $manager->getExperience(); 
            $githubLink = $manager->getGithubLink(); 
            $data = [ 
                $expectedSalary, 
                $experience, 
                $githubLink 
            ]; 
     
            render($data); 
        } 
    }  
```

#### Good:

```php
    <?php
    function showList($employees){     
    foreach ($employees as $employee) { 
            $expectedSalary =  $employee->calculateExpectedSalary(); 
            $experience = $employee->getExperience(); 
            $githubLink = $employee->getGithubLink(); 
            $data = [ 
                $expectedSalary, 
                $experience, 
                $githubLink 
            ]; 
     
            render($data); 
        } 
    }  
```

#### Very good:

```php
    <?php
    function showList($employees){
            foreach ($employees as $employee) { 
            render([ 
                $employee->calculateExpectedSalary(), 
                $employee->getExperience(), 
                $employee->getGithubLink() 
            ]); 
        } 
    }  
```

后记：虽然OOP设计需要遵守如上原则，不过实际的代码设计一定要简单、简单、简单。在实际编码中要根据情况进行取舍，一味遵守原则，而不注重实际情况的话，可能会让你的代码变的难以理解!


[1]: http://developer.51cto.com/art/201710/555228.htm

[4]: https://img0.tuicool.com/maIzEbm.jpg
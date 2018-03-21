## PHP静态方法和属性、延迟静态绑定

来源：[https://segmentfault.com/a/1190000013741642](https://segmentfault.com/a/1190000013741642)

 **`静态方法和属性`** 

静态方法是`以类作为作用域`的函数。静态方法不能访问这个类中的`普通属性`，因为那些属性`属于一个对象`，但可以访问静态属性。如果修改了一个静态属性，那么这个类的`所有实例`都能访问到这个新值。
因为是通过类而不是实例来访问静态元素，所以访问静态元素时不再需要引用对象的变量，而是使用`::`来连接类名和属性或类名和方法。

```php
class StaicExample {
    static public $aNum = 0;
    static public function sayHello() {
        print "hello";
    }
}

print StaicExample::$aNum;
StaicExample::sayHello();

```

一个子类可以使用`parent`关键字来访问父类，而不使用其类名。要从当前类（不是子类）中访问静态方法或属性，可以使用`self`关键字。`self`指向当前类，就像伪变量$this指向当前对象一样。因此，在StaticExample类的外部可以使用其类名访问属性$aNum:

```php
StaicExample::$aNum;
```

而在StaicExample类内部，可以使用`self`关键字：

```php
class StaicExample {
    static public $aNum = 0;
    static public function sayHello() {
        self::$aNum++;
        print "hello (".self::$aNum.")\n";
    }
}

```

只有在使用parent关键字调用方法的时候，才能对一个非静态方法进行静态形式的调用(使用`::`）。除非是访问一个被覆写的方法，否则永远只能使用::访问被明确声明为static的方法或属性。有时看到使用static语法来引用方法或属性，可能并不意味着其中的方法或属性必须是静态的，只不过说明它属于特定的类。

根据定义，不能在对象中调用静态方法。因此静态方法和属性又被称为`类变量和属性`，也就不能在静态方法中使用伪变量$this。

为什么要使用静态方法或属性呢？



* 在代码中的任何地方都可用（假设你可以访问该类）。也就是说，你不需要在对象间传递类的实例，也不需要将实例存放在全局变量中，就可以访问类中方法。

* 类的每个实例都可以访问类中定义的静态属性，所以可以利用静态属性来设置值，该值可以被类的所有对象使用。

* 不需要实例对象就能访问静态属性或方法，这样就不用为了获取一个简单的功能而实例化对象。

 **`延迟静态绑定：static关键字`** 
静态方法可以用作工厂方法，工厂方法是生成包含类的实例的一种方法。
先看下面的重复代码：

```php
abstract class DomainObject {
}

class User extends DomainObject {
    public static function create() {
        return new User();
    }
}

class Document extends DomainObject {
    public static function create() {
        return new Document();
    }
}

```

想必大家都不想为每个DomainObject子类都创建与上面代码类似的标准代码。如果把create()放在超类呢？

```php
abstract class DomainObject {
    public static function create() {
        return new self();
    }
}
    
class User extends DomainObject {
        
}
    
class Document extends DomainObject {
        
}
Document::create();
```

这回看起来简洁多了。现在把常见的代码放在一个位置，并使用`self`作为对该类的引用。实际上，self对该类所起的作用与$this对对象所起的作用并不完全相同。`self指的不是调用上下文，而是解析上下文`。因此，运行刚才上面的代码会得到：

```
PHP Fatal error: Cannot instantiate abstract class DomainObject in ...

```

因此，`self被解析为定义create()的DomainObject，而不是解析为调用self的Document类`。PHP5.3之前，在这方面有严格的限制，产生很多笨拙的解决方案。PHP5.3引入了`延迟静态绑定`的概念。该特性最明显的标志就是新关键字static。static类似于self，但它指的是`被调用的类而不是包含类`。

在本例中，它的意思是调用Document::create()将生成一个新的Document对象，而不是试图实例化一个DomainObject对象。
因此，现在在静态上下文使用继承关系。

```php
abstract class DomainObject {
    public static function create() {
        return new static();
    }
}
    
class User extends DomainObject {
        
}
    
class Document extends DomainObject {
        
}
print_r(Document::create());//Document Object {}
```

static关键字不仅仅可以用于实例化。和self和parent一样，static还可以作为静态方法调用的标识符，甚至是从非静态上下文中调用。

如果想为DomainObject引入组（group）的概念。默认情况下，所有类都属于default类别，但想可以为继承层次结构的某些分支重写类别。

```php
abstract class DomainObject {
    private $group;
    
    public function __construct() {
        $this->group = static::getGroup();
    }
    
    public static function create() {
        return new static();
    }
    
    static function getGroup() {
        return "default";
    }
}
    
class User extends DomainObject {
        
}
    
class Document extends DomainObject {
    static function getGroup() {
        return "document";
    }
} 

class SpreadSheet extends Document {

}

print_r(User::create());
print_r(SpreadSheet::create());
```

在DomainObject类中定义了构造函数。该构造函数使用static关键字调用静态方法getGroup()。DomainObject提供了默认实现，但Document将其覆盖了。创建的SpreadSheet新类扩展了Document类。下面是打印结果：

```
User Object
(
    [group:DomainObject:private] =>  default
)
SpreadSheet Object
(
    [group:DomainObject:private] => document
)

```

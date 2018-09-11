# PHP 设计模式系列 —— 备忘录模式（Memento）

 Posted on [2016年1月5日2016年1月5日][0] by [学院君][1]

### **1、模式定义**

[备忘录模式][2]又叫做快照模式（Snapshot）或 Token 模式，备忘录模式的用意是在不破坏封装性的前提下，捕获一个对象的内部状态，并在该对象之外保存这个状态，这样就可以在合适的时候将该对象恢复到原先保存的状态。

我们在编程的时候，经常需要保存对象的中间状态，当需要的时候，可以恢复到这个状态。比如，我们使用Eclipse进行编程时，假如编写失误（例如不小心误删除了几行代码），我们希望返回删除前的状态，便可以使用Ctrl+Z来进行返回。这时我们便可以使用备忘录模式来实现。

### **2、UML类图**

![Memento-Design-Pattern-Uml][3]

备忘录模式所涉及的角色有三个：备忘录(Memento)角色、发起人(Originator)角色、负责人(Caretaker)角色。

这三个角色的职责分别是：

* 发起人：记录当前时刻的内部状态，负责定义哪些属于备份范围的状态，负责创建和恢复备忘录数据。
* 备忘录：负责存储发起人对象的内部状态，在需要的时候提供发起人需要的内部状态。
* 管理角色：对备忘录进行管理，保存和提供备忘录。

### **3、示例代码**

#### **Memento.php**

```php
<?php

namespace DesignPatterns\Behavioral\Memento;

class Memento
{
    /* @var mixed */
    private $state;

    /**
     * @param mixed $stateToSave
     */
    public function __construct($stateToSave)
    {
        $this->state = $stateToSave;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }
}
```
#### **Originator.php**

```php
<?php

namespace DesignPatterns\Behavioral\Memento;

class Originator
{
    /* @var mixed */
    private $state;

    // 这个类还可以包含不属于备忘录状态的额外数据

    /**
     * @param mixed $state
     */
    public function setState($state)
    {
        // 必须检查该类子类内部的状态类型或者使用依赖注入
        $this->state = $state;
    }

    /**
     * @return Memento
     */
    public function getStateAsMemento()
    {
        // 在Memento中必须保存一份隔离的备份
        $state = is_object($this->state) ? clone $this->state : $this->state;

        return new Memento($state);
    }

    public function restoreFromMemento(Memento $memento)
    {
        $this->state = $memento->getState();
    }
}
```
#### **Caretaker.php**

```php
<?php

namespace DesignPatterns\Behavioral\Memento;

class Caretaker
{
    protected $history = array();

    /**
     * @return Memento
     */
    public function getFromHistory($id)
    {
        return $this->history[$id];
    }

    /**
     * @param Memento $state
     */
    public function saveToHistory(Memento $state)
    {
        $this->history[] = $state;
    }

    public function runCustomLogic()
    {
        $originator = new Originator();

        //设置状态为State1
        $originator->setState("State1");
        //设置状态为State2
        $originator->setState("State2");
        //将State2保存到Memento
        $this->saveToHistory($originator->getStateAsMemento());
        //设置状态为State3
        $originator->setState("State3");

        //我们可以请求多个备忘录, 然后选择其中一个进行[回滚][4]
        
        //保存State3到Memento
        $this->saveToHistory($originator->getStateAsMemento());
        //设置状态为State4
        $originator->setState("State4");

        $originator->restoreFromMemento($this->getFromHistory(1));
        //从备忘录恢复后的状态: State3

        return $originator->getStateAsMemento()->getState();
    }
}
```
### **4、测试代码**

#### **Tests/MementoTest.php**

```php
<?php

namespace DesignPatterns\Behavioral\Memento\Tests;

use DesignPatterns\Behavioral\Memento\Caretaker;
use DesignPatterns\Behavioral\Memento\Memento;
use DesignPatterns\Behavioral\Memento\Originator;

/**
 * MementoTest用于测试备忘录模式
 */
class MementoTest extends \PHPUnit\Framework\TestCase
{

    public function testUsageExample()
    {
        $originator = new Originator();
        $caretaker = new Caretaker();

        $character = new \stdClass();
        // new object
        $character->name = "Gandalf";
        // connect Originator to character object
        $originator->setState($character);

        // work on the object
        $character->name = "Gandalf the Grey";
        // still change something
        $character->race = "Maia";
        // time to save state
        $snapshot = $originator->getStateAsMemento();
        // put state to log
        $caretaker->saveToHistory($snapshot);

        // change something
        $character->name = "Sauron";
        // and again
        $character->race = "Ainur";
        // state inside the Originator was equally changed
        $this->assertAttributeEquals($character, "state", $originator);

        // time to save another state
        $snapshot = $originator->getStateAsMemento();
        // put state to log
        $caretaker->saveToHistory($snapshot);

        $rollback = $caretaker->getFromHistory(0);
        // return to first state
        $originator->restoreFromMemento($rollback);
        // use character from old state
        $character = $rollback->getState();

        // yes, that what we need
        $this->assertEquals("Gandalf the Grey", $character->name);
        // make new changes
        $character->name = "Gandalf the White";

        // and Originator linked to actual object again
        $this->assertAttributeEquals($character, "state", $originator);
    }

    public function testStringState()
    {
        $originator = new Originator();
        $originator->setState("State1");

        $this->assertAttributeEquals("State1", "state", $originator);

        $originator->setState("State2");
        $this->assertAttributeEquals("State2", "state", $originator);

        $snapshot = $originator->getStateAsMemento();
        $this->assertAttributeEquals("State2", "state", $snapshot);

        $originator->setState("State3");
        $this->assertAttributeEquals("State3", "state", $originator);

        $originator->restoreFromMemento($snapshot);
        $this->assertAttributeEquals("State2", "state", $originator);
    }

    public function testSnapshotIsClone()
    {
        $originator = new Originator();
        $object = new \stdClass();

        $originator->setState($object);
        $snapshot = $originator->getStateAsMemento();
        $object->new_property = 1;

        $this->assertAttributeEquals($object, "state", $originator);
        $this->assertAttributeNotEquals($object, "state", $snapshot);

        $originator->restoreFromMemento($snapshot);
        $this->assertAttributeNotEquals($object, "state", $originator);
    }

    public function testCanChangeActualState()
    {
        $originator = new Originator();
        $first_state = new \stdClass();

        $originator->setState($first_state);
        $snapshot = $originator->getStateAsMemento();
        $second_state = $snapshot->getState();

        // still actual
        $first_state->first_property = 1;
        // just history
        $second_state->second_property = 2;
        $this->assertAttributeEquals($first_state, "state", $originator);
        $this->assertAttributeNotEquals($second_state, "state", $originator);

        $originator->restoreFromMemento($snapshot);
        // now it lost state
        $first_state->first_property = 11;
        // must be actual
        $second_state->second_property = 22;
        $this->assertAttributeEquals($second_state, "state", $originator);
        $this->assertAttributeNotEquals($first_state, "state", $originator);
    }

    public function testStateWithDifferentObjects()
    {
        $originator = new Originator();

        $first = new \stdClass();
        $first->data = "foo";

        $originator->setState($first);
        $this->assertAttributeEquals($first, "state", $originator);

        $first_snapshot = $originator->getStateAsMemento();
        $this->assertAttributeEquals($first, "state", $first_snapshot);

        $second       = new \stdClass();
        $second->data = "bar";
        $originator->setState($second);
        $this->assertAttributeEquals($second, "state", $originator);

        $originator->restoreFromMemento($first_snapshot);
        $this->assertAttributeEquals($first, "state", $originator);
    }

    public function testCaretaker()
    {
        $caretaker = new Caretaker();
        $memento1 = new Memento("foo");
        $memento2 = new Memento("bar");
        $caretaker->saveToHistory($memento1);
        $caretaker->saveToHistory($memento2);
        $this->assertAttributeEquals(array($memento1, $memento2), "history", $caretaker);
        $this->assertEquals($memento1, $caretaker->getFromHistory(0));
        $this->assertEquals($memento2, $caretaker->getFromHistory(1));

    }

    public function testCaretakerCustomLogic()
    {
        $caretaker = new Caretaker();
        $result = $caretaker->runCustomLogic();
        $this->assertEquals("State3", $result);
    }
}
```
### **5、总结**

如果有需要提供回滚操作的需求，使用备忘录模式非常适合，比如数据库的事务操作，文本编辑器的 Ctrl+Z 恢复等。

[0]: http://laravelacademy.org/post/2903.html
[1]: http://laravelacademy.org/post/author/nonfu
[2]: http://laravelacademy.org/tags/%e5%a4%87%e5%bf%98%e5%bd%95%e6%a8%a1%e5%bc%8f
[3]: ../img/Memento-Design-Pattern-Uml.png
[4]: http://laravelacademy.org/tags/%e5%9b%9e%e6%bb%9a
[5]: http://laravelacademy.org/tags/php
# [Flyweight][0][¶][1]

## Purpose[¶][2]

To minimise memory usage, a Flyweight shares as much as possible memory with similar objects. It is needed when a large amount of objects is used that don’t differ much in state. A common practice is to hold state in external data structures and pass them to the flyweight object when needed.

##  UML Diagram[¶][3]

![Alt Flyweight UML Diagram][4]

##  Code[¶][5]

You can also find this code on [GitHub][6]

FlyweightInterface.php

```php
<?php

namespace DesignPatterns\Structural\Flyweight;

interface FlyweightInterface
{
    public function render(string $extrinsicState): string;
}
```
CharacterFlyweight.php

```php
<?php

namespace DesignPatterns\Structural\Flyweight;

/**
 * Implements the flyweight interface and adds storage for intrinsic state, if any.
 * Instances of concrete flyweights are shared by means of a factory.
 */
class CharacterFlyweight implements FlyweightInterface
{
    /**
     * Any state stored by the concrete flyweight must be independent of its context.
     * For flyweights representing characters, this is usually the corresponding character code.
     *
     * @var string
     */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function render(string $font): string
    {
         // Clients supply the context-dependent information that the flyweight needs to draw itself
         // For flyweights representing characters, extrinsic state usually contains e.g. the font.

        return sprintf('Character %s with font %s', $this->name, $font);
    }
}
```

FlyweightFactory.php


```php
<?php

namespace DesignPatterns\Structural\Flyweight;

/**
 * A factory manages shared flyweights. Clients should not instantiate them directly,
 * but let the factory take care of returning existing objects or creating new ones.
 */
class FlyweightFactory implements \Countable
{
    /**
     * @var CharacterFlyweight[]
     */
    private $pool = [];

    public function get(string $name): CharacterFlyweight
    {
        if (!isset($this->pool[$name])) {
            $this->pool[$name] = new CharacterFlyweight($name);
        }

        return $this->pool[$name];
    }

    public function count(): int
    {
        return count($this->pool);
    }
}
```

## 2.9.4. Test[¶][7]

Tests/FlyweightTest.php


```php
<?php

namespace DesignPatterns\Structural\Flyweight\Tests;

use DesignPatterns\Structural\Flyweight\FlyweightFactory;
use PHPUnit\Framework\TestCase;

class FlyweightTest extends TestCase
{
    private $characters = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k',
        'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'];
    private $fonts = ['Arial', 'Times New Roman', 'Verdana', 'Helvetica'];

    public function testFlyweight()
    {
        $factory = new FlyweightFactory();

        foreach ($this->characters as $char) {
            foreach ($this->fonts as $font) {
                $flyweight = $factory->get($char);
                $rendered = $flyweight->render($font);

                $this->assertEquals(sprintf('Character %s with font %s', $char, $font), $rendered);
            }
        }

        // Flyweight pattern ensures that instances are shared
        // instead of having hundreds of thousands of individual objects
        // there must be one instance for every char that has been reused for displaying in different fonts
        $this->assertCount(count($this->characters), $factory);
    }
}
```

[0]: https://en.wikipedia.org/wiki/Flyweight_pattern
[1]: #flyweight
[2]: #purpose
[3]: #uml-diagram
[4]: ../img/uml33.png
[5]: #code
[6]: https://github.com/domnikl/DesignPatternsPHP/tree/master/Structural/Flyweight
[7]: #test
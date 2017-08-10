<?php

namespace Prototype\Tests;

$autoLoadFilePath = '../../vendor/autoload.php';
require_once $autoLoadFilePath;

use Prototype\BookPrototype;
use Prototype\FooBookPrototype;
use Prototype\BarBookPrototype;

/**
 * PrototypeTest tests the prototype pattern
 */
class PrototypeTest extends \PHPUnit\Framework\TestCase
{

     public function getPrototype(){
         return array(
             array(new FooBookPrototype()),
             array(new BarBookPrototype())
         );
     }

     /**
      * @dataProvider getPrototype
      */
     public function testCreation(BookPrototype $prototype)
     {
         $book = clone $prototype;
         $book->setTitle("45555".' Book');
         $this->assertInstanceOf('Prototype\BookPrototype', $book);
     }
}
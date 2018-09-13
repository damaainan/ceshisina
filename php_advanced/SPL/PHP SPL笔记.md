# PHP SPL笔记

作者： [阮一峰][0]

日期： [2008年7月 8日][1]

这几天，我在学习PHP语言中的[SPL][2]。

这个东西应该属于PHP中的高级内容，看上去很复杂，但是非常有用，所以我做了长篇笔记。不然记不住，以后要用的时候，还是要从头学起。

由于这是供自己参考的笔记，不是教程，所以写得比较简单，没有多解释。但是我想，如果你是一个熟练的PHP5程序员，应该足以看懂下面的材料，而且会发现它很有用。现在除此之外，网上根本没有任何深入的SPL中文介绍。

================

**PHP SPL笔记**

目录

第一部分 简介

1. 什么是SPL？

2. 什么是Iterator？

第二部分 SPL Interfaces

3. Iterator界面

4. ArrayAccess界面

5. IteratorAggregate界面

6. RecursiveIterator界面

7. SeekableIterator界面

8. Countable界面

第三部分 SPL Classes

9. SPL的内置类

10. DirectoryIterator类

11. ArrayObject类

12. ArrayIterator类

13. RecursiveArrayIterator类和RecursiveIteratorIterator类

14. FilterIterator类

15. SimpleXMLIterator类

16. CachingIterator类

17. LimitIterator类

18. SplFileObject类

**第一部 简介**

**1. 什么是SPL？**SPL是Standard PHP Library（PHP标准库）的缩写。

根据官方定义，它是"a collection of interfaces and classes that are meant to solve standard problems"。但是，目前在使用中，SPL更多地被看作是一种使object（物体）模仿array（数组）行为的interfaces和classes。

**2. 什么是Iterator？**

SPL的核心概念就是Iterator。这指的是一种Design Pattern，根据《Design Patterns》一书的定义，Iterator的作用是"provide an object which traverses some aggregate structure, abstracting away assumptions about the implementation of that structure."

wikipedia中说，"an iterator is an object which allows a programmer to traverse through all the elements of a collection, regardless of its specific implementation"......."the iterator pattern is a design pattern in which iterators are used to access the elements of an aggregate object sequentially without exposing its underlying representation".

通俗地说，Iterator能够使许多不同的数据结构，都能有统一的操作界面，比如一个数据库的结果集、同一个目录中的文件集、或者一个文本中每一行构成的集合。

如果按照普通情况，遍历一个MySQL的结果集，程序需要这样写：
```php
// Fetch the "aggregate structure"
$result = mysql_query("SELECT * FROM users");

// Iterate over the structure
while ( $row = mysql_fetch_array($result) ) {
   // do stuff with the row here
}
```

读出一个目录中的内容，需要这样写：
```php
// Fetch the "aggregate structure"
$dh = opendir('/home/harryf/files');

// Iterate over the structure
while ( $file = readdir($dh) ) {
   // do stuff with the file here
}
```
读出一个文本文件的内容，需要这样写：
```php
// Fetch the "aggregate structure"
$fh = fopen("/home/hfuecks/files/results.txt", "r");

// Iterate over the structure
while (!feof($fh)) {

   $line = fgets($fh);
   // do stuff with the line here

}
```

上面三段代码，虽然处理的是不同的resource（资源），但是功能都是遍历结果集（loop over contents），因此Iterator的基本思想，就是将这三种不同的操作统一起来，用同样的命令界面，处理不同的资源。

**第二部分 SPL Interfaces**

**3. Iterator界面**

SPL规定，所有部署了Iterator界面的class，都可以用在foreach Loop中。Iterator界面中包含5个必须部署的方法：

        * current()
    
          This method returns the current index's value. You are solely
          responsible for tracking what the current index is as the 
         interface does not do this for you.
    
        * key()
    
          This method returns the value of the current index's key. For 
          foreach loops this is extremely important so that the key 
          value can be populated.
    
        * next()
    
          This method moves the internal index forward one entry.
    
        * rewind()
    
          This method should reset the internal index to the first element.
    
        * valid()
    
          This method should return true or false if there is a current 
          element. It is called after rewind() or next().
    
    

下面就是一个部署了Iterator界面的class示例：
```php
/**
* An iterator for native PHP arrays, re-inventing the wheel
*
* Notice the "implements Iterator" - important!
*/
class ArrayReloaded implements Iterator {

   /**
   * A native PHP array to iterate over
   */
 private $array = array();

   /**
   * A switch to keep track of the end of the array
   */
 private $valid = FALSE;

   /**
   * Constructor
   * @param array native PHP array to iterate over
   */
 function __construct($array) {
   $this->array = $array;
 }

   /**
   * Return the array "pointer" to the first element
   * PHP's reset() returns false if the array has no elements
   */
 function rewind(){
   $this->valid = (FALSE !== reset($this->array));
 }

   /**
   * Return the current array element
   */
 function current(){
   return current($this->array);
 }

   /**
   * Return the key of the current array element
   */
 function key(){
   return key($this->array);
 }

   /**
   * Move forward by one
   * PHP's next() returns false if there are no more elements
   */
 function next(){
   $this->valid = (FALSE !== next($this->array));
 }

   /**
   * Is the current element valid?
   */
 function valid(){
   return $this->valid;
 }
}
```

使用方法如下：
```php
    // Create iterator object
    $colors = new ArrayReloaded(array ('red','green','blue',));
    
    // Iterate away!
    foreach ( $colors as $color ) {
     echo $color."<br>";
    }
```

你也可以在foreach循环中使用key()方法：
```php
    // Display the keys as well
    foreach ( $colors as $key => $color ) {
     echo "$key: $color<br>";
    }
```

除了foreach循环外，也可以使用while循环，
```php
    // Reset the iterator - foreach does this automatically
    $colors->rewind();
    
    // Loop while valid
    while ( $colors->valid() ) {
    
       echo $colors->key().": ".$colors->current()." > ";
       $colors->next();
    
    }
```

根据测试，while循环要稍快于foreach循环，因为运行时少了一层中间调用。

**4. ArrayAccess界面**部署ArrayAccess界面，可以使得object像array那样操作。ArrayAccess界面包含四个必须部署的方法：

        * offsetExists($offset)
    
          This method is used to tell php if there is a value
          for the key specified by offset. It should return 
          true or false.
    
        * offsetGet($offset)
    
          This method is used to return the value specified 
          by the key offset.
    
        * offsetSet($offset, $value)
    
          This method is used to set a value within the object, 
          you can throw an exception from this function for a 
          read-only collection.
    
        * offsetUnset($offset)
    
          This method is used when a value is removed from 
          an array either through unset() or assigning the key 
          a value of null. In the case of numerical arrays, this 
          offset should not be deleted and the array should 
          not be reindexed unless that is specifically the 


下面就是一个部署ArrayAccess界面的实例：
```php
/**
* A class that can be used like an array
*/
class Article implements ArrayAccess {

 public $title;

 public $author;

 public $category;  

 function __construct($title,$author,$category) {
   $this->title = $title;
   $this->author = $author;
   $this->category = $category;
 }

 /**
 * Defined by ArrayAccess interface
 * Set a value given it's key e.g. $A['title'] = 'foo';
 * @param mixed key (string or integer)
 * @param mixed value
 * @return void
 */
 function offsetSet($key, $value) {
   if ( array_key_exists($key,get_object_vars($this)) ) {
     $this->{$key} = $value;
   }
 }

 /**
 * Defined by ArrayAccess interface
 * Return a value given it's key e.g. echo $A['title'];
 * @param mixed key (string or integer)
 * @return mixed value
 */
 function offsetGet($key) {
   if ( array_key_exists($key,get_object_vars($this)) ) {
     return $this->{$key};
   }
 }

 /**
 * Defined by ArrayAccess interface
 * Unset a value by it's key e.g. unset($A['title']);
 * @param mixed key (string or integer)
 * @return void
 */
 function offsetUnset($key) {
   if ( array_key_exists($key,get_object_vars($this)) ) {
     unset($this->{$key});
   }
 }

 /**
 * Defined by ArrayAccess interface
 * Check value exists, given it's key e.g. isset($A['title'])
 * @param mixed key (string or integer)
 * @return boolean
 */
 function offsetExists($offset) {
   return array_key_exists($offset,get_object_vars($this));
 }

}
```

使用方法如下：
```php
    // Create the object
    $A = new Article('SPL Rocks','Joe Bloggs', 'PHP');
    
    // Check what it looks like
    echo 'Initial State:<div>';
    print_r($A);
    echo '</div>';
    
    // Change the title using array syntax
    $A['title'] = 'SPL _really_ rocks';
    
    // Try setting a non existent property (ignored)
    $A['not found'] = 1;
    
    // Unset the author field
    unset($A['author']);
    
    // Check what it looks like again
    echo 'Final State:<div>';
    print_r($A);
    echo '</div>';
```

运行结果如下：

    Initial State:
    
    Article Object
    (
       [title] => SPL Rocks
       [author] => Joe Bloggs
       [category] => PHP
    )
    
    Final State:
    
    Article Object
    (
       [title] => SPL _really_ rocks
       [category] => PHP
    )

可以看到，$A虽然是一个object，但是完全可以像array那样操作。

你还可以在读取数据时，增加程序内部的逻辑：
```php
    function offsetGet($key) {
       if ( array_key_exists($key,get_object_vars($this)) ) {
         return strtolower($this->{$key});
       }
     }
```
**5. IteratorAggregate界面**

但是，虽然$A可以像数组那样操作，却无法使用foreach遍历，除非部署了前面提到的Iterator界面。

另一个解决方法是，有时会需要将数据和遍历部分分开，这时就可以部署IteratorAggregate界面。它规定了一个getIterator()方法，返回一个使用Iterator界面的object。

还是以上一节的Article类为例：
```php
    class Article implements ArrayAccess, IteratorAggregate {
    
    /**
     * Defined by IteratorAggregate interface
     * Returns an iterator for for this object, for use with foreach
     * @return ArrayIterator
     */
     function getIterator() {
       return new ArrayIterator($this);
     }
```

使用方法如下：
```php
    $A = new Article('SPL Rocks','Joe Bloggs', 'PHP');
    
    // Loop (getIterator will be called automatically)
    echo 'Looping with foreach:<div>';
    foreach ( $A as $field => $value ) {
     echo "$field : $value<br>";
    }
    echo '</div>';
    
    // Get the size of the iterator (see how many properties are left)
    echo "Object has ".sizeof($A->getIterator())." elements";
```

显示结果如下：

    Looping with foreach:
    
    title : SPL Rocks
    author : Joe Bloggs
    category : PHP
    
    Object has 3 elements


**6. RecursiveIterator界面**

这个界面用于遍历多层数据，它继承了Iterator界面，因而也具有标准的current()、key()、next()、 rewind()和valid()方法。同时，它自己还规定了getChildren()和hasChildren()方法。The getChildren() method must return an object that implements RecursiveIterator.

**7. SeekableIterator界面**

SeekableIterator界面也是Iterator界面的延伸，除了Iterator的5个方法以外，还规定了seek()方法，参数是元素的位置，返回该元素。如果该位置不存在，则抛出OutOfBoundsException。

下面是一个是实例：

```php
<?php

class PartyMemberIterator implements SeekableIterator
{
    public function __construct(PartyMember $member)
    {
        // Store $member locally for iteration
    }

    public function seek($index)
    {
        $this->rewind();
        $position = 0;

        while ($position < $index && $this->valid()) {
            $this->next();
            $position++;
        }

        if (!$this->valid()) {
            throw new OutOfBoundsException('Invalid position');
        }
    }

    // Implement current(), key(), next(), rewind()
    // and valid() to iterate over data in $member
}
```

**8. Countable界面**

这个界面规定了一个count()方法，返回结果集的数量。

**第三部分 SPL Classes**

**9. SPL的内置类**

SPL除了定义一系列Interfaces以外，还提供一系列的内置类，它们对应不同的任务，大大简化了编程。

查看所有的内置类，可以使用下面的代码：

```php
<?php
// a simple foreach() to traverse the SPL class names
foreach(spl_classes() as $key=>$value)
{
    echo $key.' -> '.$value.'<br />';
}
```

**10. DirectoryIterator类**

这个类用来查看一个目录中的所有文件和子目录：

```php
<?php

try{
  /*** class create new DirectoryIterator Object ***/
    foreach ( new DirectoryIterator('./') as $Item )
    {
        echo $Item.'<br />';
    }
}
/*** if an exception is thrown, catch it here ***/
catch(Exception $e){
    echo 'No files Found!<br />';
}
```
查看文件的详细信息：

```php
<table>
<?php

foreach(new DirectoryIterator('./' ) as $file ){
    if( $file->getFilename()  == 'foo.txt' ){
        echo '<tr><td>getFilename()</td><td> '; var_dump($file->getFilename()); echo '</td></tr>';
        echo '<tr><td>getBasename()</td><td> '; var_dump($file->getBasename()); echo '</td></tr>';
        echo '<tr><td>isDot()</td><td> '; var_dump($file->isDot()); echo '</td></tr>';
        echo '<tr><td>__toString()</td><td> '; var_dump($file->__toString()); echo '</td></tr>';
        echo '<tr><td>getPath()</td><td> '; var_dump($file->getPath()); echo '</td></tr>';
        echo '<tr><td>getPathname()</td><td> '; var_dump($file->getPathname()); echo '</td></tr>';
        echo '<tr><td>getPerms()</td><td> '; var_dump($file->getPerms()); echo '</td></tr>';
        echo '<tr><td>getInode()</td><td> '; var_dump($file->getInode()); echo '</td></tr>';
        echo '<tr><td>getSize()</td><td> '; var_dump($file->getSize()); echo '</td></tr>';
        echo '<tr><td>getOwner()</td><td> '; var_dump($file->getOwner()); echo '</td></tr>';
        echo '<tr><td>$file->getGroup()</td><td> '; var_dump($file->getGroup()); echo '</td></tr>';
        echo '<tr><td>getATime()</td><td> '; var_dump($file->getATime()); echo '</td></tr>';
        echo '<tr><td>getMTime()</td><td> '; var_dump($file->getMTime()); echo '</td></tr>';
        echo '<tr><td>getCTime()</td><td> '; var_dump($file->getCTime()); echo '</td></tr>';
        echo '<tr><td>getType()</td><td> '; var_dump($file->getType()); echo '</td></tr>';
        echo '<tr><td>isWritable()</td><td> '; var_dump($file->isWritable()); echo '</td></tr>';
        echo '<tr><td>isReadable()</td><td> '; var_dump($file->isReadable()); echo '</td></tr>';
        echo '<tr><td>isExecutable(</td><td> '; var_dump($file->isExecutable()); echo '</td></tr>';
        echo '<tr><td>isFile()</td><td> '; var_dump($file->isFile()); echo '</td></tr>';
        echo '<tr><td>isDir()</td><td> '; var_dump($file->isDir()); echo '</td></tr>';
        echo '<tr><td>isLink()</td><td> '; var_dump($file->isLink()); echo '</td></tr>';
        echo '<tr><td>getFileInfo()</td><td> '; var_dump($file->getFileInfo()); echo '</td></tr>';
        echo '<tr><td>getPathInfo()</td><td> '; var_dump($file->getPathInfo()); echo '</td></tr>';
        echo '<tr><td>openFile()</td><td> '; var_dump($file->openFile()); echo '</td></tr>';
        echo '<tr><td>setFileClass()</td><td> '; var_dump($file->setFileClass()); echo '</td></tr>';
        echo '<tr><td>setInfoClass()</td><td> '; var_dump($file->setInfoClass()); echo '</td></tr>';
    }
}
?>
</table>
```

除了foreach循环外，还可以使用while循环：

```php
<?php
/*** create a new iterator object ***/
$it = new DirectoryIterator('./');

/*** loop directly over the object ***/
while($it->valid())
{
    echo $it->key().' -- '.$it->current().'<br />';
    /*** move to the next iteration ***/
    $it->next();
}
```

如果要过滤所有子目录，可以在valid()方法中过滤：

```php
<?php
/*** create a new iterator object ***/
$it = new DirectoryIterator('./');

/*** loop directly over the object ***/
while($it->valid())
{
    /*** check if value is a directory ***/
    if($it->isDir())
    {
        /*** echo the key and current value ***/
        echo $it->key().' -- '.$it->current().'<br />';
    }
    /*** move to the next iteration ***/
    $it->next();
}
```

**11. ArrayObject类**

这个类可以将Array转化为object。

```php
<?php

/*** a simple array ***/
$array = array('koala', 'kangaroo', 'wombat', 'wallaby', 'emu', 'kiwi', 'kookaburra', 'platypus');

/*** create the array object ***/
$arrayObj = new ArrayObject($array);

/*** iterate over the array ***/
for($iterator = $arrayObj->getIterator();
   /*** check if valid ***/
   $iterator->valid();
   /*** move to the next array member ***/
   $iterator->next())
    {
        /*** output the key and current array value ***/
        echo $iterator->key() . ' => ' . $iterator->current() . '<br />';
    }
```

增加一个元素：

    $arrayObj->append('dingo');

对元素排序：

    $arrayObj->natcasesort();

显示元素的数量：

    echo $arrayObj->count();

删除一个元素：

    $arrayObj->offsetUnset(5);

某一个元素是否存在：

    if ($arrayObj->offsetExists(3))
    {
        echo 'Offset Exists<br />';
    }

更改某个位置的元素值：

     $arrayObj->offsetSet(5, "galah");

显示某个位置的元素值：

    echo $arrayObj->offsetGet(4);

**12. ArrayIterator类**这个类实际上是对ArrayObject类的补充，为后者提供遍历功能。

示例如下：


```php
<?php
/*** a simple array ***/
$array = array('koala', 'kangaroo', 'wombat', 'wallaby', 'emu', 'kiwi', 'kookaburra', 'platypus');

try {
    $object = new ArrayIterator($array);
    foreach($object as $key=>$value)
    {
        echo $key.' => '.$value.'<br />';
    }
}catch (Exception $e){
    echo $e->getMessage();
}
```

ArrayIterator类也支持offset类方法和count()方法：

```php
<ul>
<?php
/*** a simple array ***/
$array = array('koala', 'kangaroo', 'wombat', 'wallaby', 'emu', 'kiwi', 'kookaburra', 'platypus');

try {
    $object = new ArrayIterator($array);
    /*** check for the existence of the offset 2 ***/
    if($object->offSetExists(2))
    {
        /*** set the offset of 2 to a new value ***/
        $object->offSetSet(2, 'Goanna');
    }
   /*** unset the kiwi ***/
   foreach($object as $key=>$value){
        /*** check the value of the key ***/
        if($object->offSetGet($key) === 'kiwi'){
            /*** unset the current key ***/
            $object->offSetUnset($key);
        }
        echo '<li>'.$key.' - '.$value.'</li>'."\n";
    }
}catch (Exception $e){
    echo $e->getMessage();
}
?>
</ul>
```

**13. RecursiveArrayIterator类和RecursiveIteratorIterator类**

ArrayIterator类和ArrayObject类，只支持遍历一维数组。如果要遍历多维数组，必须先用RecursiveIteratorIterator生成一个Iterator，然后再对这个Iterator使用RecursiveIteratorIterator。

```php
<?php
$array = array(
    array('name'=>'butch', 'sex'=>'m', 'breed'=>'boxer'),
    array('name'=>'fido', 'sex'=>'m', 'breed'=>'doberman'),
    array('name'=>'girly','sex'=>'f', 'breed'=>'poodle')
);

foreach(new RecursiveIteratorIterator(new RecursiveArrayIterator($array)) as $key=>$value)
{
    echo $key.' -- '.$value.'<br />';
}
```

**14. FilterIterator类**

FilterIterator类可以对元素进行过滤，只要在accept()方法中设置过滤条件就可以了。

示例如下：

```php
<?php
/*** a simple array ***/
$animals = array('koala', 'kangaroo', 'wombat', 'wallaby', 'emu', 'NZ'=>'kiwi', 'kookaburra', 'platypus');

class CullingIterator extends FilterIterator{

    /*** The filteriterator takes  a iterator as param: ***/
    public function __construct( Iterator $it ){
        parent::__construct( $it );
    }

    /*** check if key is numeric ***/
    function accept(){
        return is_numeric($this->key());
    }

}/*** end of class ***/
$cull = new CullingIterator(new ArrayIterator($animals));

foreach($cull as $key=>$value)
{
    echo $key.' == '.$value.'<br />';
}
```
    
下面是另一个返回质数的例子：

```php
<?php

class PrimeFilter extends FilterIterator
{

/*** The filteriterator takes  a iterator as param: ***/
    public function __construct(Iterator $it)
    {
        parent::__construct($it);
    }

/*** check if current value is prime ***/
    public function accept()
    {
        if ($this->current() % 2 != 1) {
            return false;
        }
        $d = 3;
        $x = sqrt($this->current());
        while ($this->current() % $d != 0 && $d < $x) {
            $d += 2;
        }
        return (($this->current() % $d == 0 && $this->current() != $d) * 1) == 0 ? true : false;
    }

} /*** end of class ***/

/*** an array of numbers ***/
$numbers = range(212345, 212456);

/*** create a new FilterIterator object ***/
$primes = new primeFilter(new ArrayIterator($numbers));

foreach ($primes as $value) {
    echo $value . ' is prime.<br />';
}

```

**15. SimpleXMLIterator类**

这个类用来遍历xml文件。

示例如下：

```php
<?php

/*** a simple xml tree ***/
$xmlstring = <<<XML
    <?xml version = "1.0" encoding="UTF-8" standalone="yes"?>
    <document>
      <animal>
        <category id="26">
          <species>Phascolarctidae</species>
          <type>koala</type>
          <name>Bruce</name>
        </category>
      </animal>
      <animal>
        <category id="27">
          <species>macropod</species>
          <type>kangaroo</type>
          <name>Bruce</name>
        </category>
      </animal>
      <animal>
        <category id="28">
          <species>diprotodon</species>
          <type>wombat</type>
          <name>Bruce</name>
        </category>
      </animal>
      <animal>
        <category id="31">
          <species>macropod</species>
          <type>wallaby</type>
          <name>Bruce</name>
        </category>
      </animal>
      <animal>
        <category id="21">
          <species>dromaius</species>
          <type>emu</type>
          <name>Bruce</name>
        </category>
      </animal>
      <animal>
        <category id="22">
          <species>Apteryx</species>
          <type>kiwi</type>
          <name>Troy</name>
        </category>
      </animal>
      <animal>
        <category id="23">
          <species>kingfisher</species>
          <type>kookaburra</type>
          <name>Bruce</name>
        </category>
      </animal>
      <animal>
        <category id="48">
          <species>monotremes</species>
          <type>platypus</type>
          <name>Bruce</name>
        </category>
      </animal>
      <animal>
        <category id="4">
          <species>arachnid</species>
          <type>funnel web</type>
          <name>Bruce</name>
          <legs>8</legs>
        </category>
      </animal>
    </document>
XML;

// a new simpleXML iterator object
try {
    /*** a new simple xml iterator ***/
    $it = new SimpleXMLIterator($xmlstring);
    /*** a new limitIterator object ***/
    foreach (new RecursiveIteratorIterator($it, 1) as $name => $data) {
        echo $name . ' -- ' . $data . '<br />';
    }
} catch (Exception $e) {
    echo $e->getMessage();
}

```
new RecursiveIteratorIterator($it,1)表示显示所有包括父元素在内的子元素。

显示某一个特定的元素值，可以这样写：

```php
<?php
try {
    /*** a new simpleXML iterator object ***/
    $sxi = new SimpleXMLIterator($xmlstring);

    foreach ($sxi as $node) {
        foreach ($node as $k => $v) {
            echo $v->species . '<br />';
        }
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
```

相对应的while循环写法为：

```php
<?php
    
try {
    $sxe = simplexml_load_string($xmlstring, 'SimpleXMLIterator');

    for ($sxe->rewind(); $sxe->valid(); $sxe->next()) {
        if ($sxe->hasChildren()) {
            foreach ($sxe->getChildren() as $element => $value) {
                echo $value->species . '<br />';
            }
        }
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
```

最方便的写法，还是使用xpath：

```php
<?php
try {
    /*** a new simpleXML iterator object ***/
    $sxi = new SimpleXMLIterator($xmlstring);

    /*** set the xpath ***/
    $foo = $sxi->xpath('animal/category/species');

    /*** iterate over the xpath ***/
    foreach ($foo as $k => $v) {
        echo $v . '<br />';
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
```

下面的例子，显示有namespace的情况：


```php
<?php
    
    /*** a simple xml tree ***/
     $xmlstring = <<<XML
    <?xml version = "1.0" encoding="UTF-8" standalone="yes"?>
    <document xmlns:spec="http://example.org/animal-species">
      <animal>
        <category id="26">
          <species>Phascolarctidae</species>
          <spec:name>Speed Hump</spec:name>
          <type>koala</type>
          <name>Bruce</name>
        </category>
      </animal>
      <animal>
        <category id="27">
          <species>macropod</species>
          <spec:name>Boonga</spec:name>
          <type>kangaroo</type>
          <name>Bruce</name>
        </category>
      </animal>
      <animal>
        <category id="28">
          <species>diprotodon</species>
          <spec:name>pot holer</spec:name>
          <type>wombat</type>
          <name>Bruce</name>
        </category>
      </animal>
      <animal>
        <category id="31">
          <species>macropod</species>
          <spec:name>Target</spec:name>
          <type>wallaby</type>
          <name>Bruce</name>
        </category>
      </animal>
      <animal>
        <category id="21">
          <species>dromaius</species>
          <spec:name>Road Runner</spec:name>
          <type>emu</type>
          <name>Bruce</name>
        </category>
      </animal>
      <animal>
        <category id="22">
          <species>Apteryx</species>
          <spec:name>Football</spec:name>
          <type>kiwi</type>
          <name>Troy</name>
        </category>
      </animal>
      <animal>
        <category id="23">
          <species>kingfisher</species>
          <spec:name>snaker</spec:name>
          <type>kookaburra</type>
          <name>Bruce</name>
        </category>
      </animal>
      <animal>
        <category id="48">
          <species>monotremes</species>
          <spec:name>Swamp Rat</spec:name>
          <type>platypus</type>
          <name>Bruce</name>
        </category>
      </animal>
      <animal>
        <category id="4">
          <species>arachnid</species>
          <spec:name>Killer</spec:name>
          <type>funnel web</type>
          <name>Bruce</name>
          <legs>8</legs>
        </category>
      </animal>
    </document>
    XML;
    
    /*** a new simpleXML iterator object ***/
    try {
        /*** a new simpleXML iterator object ***/
        $sxi =  new SimpleXMLIterator($xmlstring);
    
        $sxi-> registerXPathNamespace('spec', 'http://www.exampe.org/species-title');
    
        /*** set the xpath ***/
        $result = $sxi->xpath('//spec:name');
    
        /*** get all declared namespaces ***/
       foreach($sxi->getDocNamespaces('animal') as $ns)
            {
            echo $ns.'<br />';
            }
    
        /*** iterate over the xpath ***/
        foreach ($result as $k=>$v)
            {
            echo $v.'<br />';
            }
        }
    catch(Exception $e)
        {
        echo $e->getMessage();
        }
```

增加一个节点：

```php
    <?php 
     $xmlstring = <<<XML
    <?xml version = "1.0" encoding="UTF-8" standalone="yes"?>
    <document>
      <animal>koala</animal>
      <animal>kangaroo</animal>
      <animal>wombat</animal>
      <animal>wallaby</animal>
      <animal>emu</animal>
      <animal>kiwi</animal>
      <animal>kookaburra</animal>
      <animal>platypus</animal>
      <animal>funnel web</animal>
    </document>
    XML;
    
    try {
        /*** a new simpleXML iterator object ***/
        $sxi =  new SimpleXMLIterator($xmlstring);
    
        /*** add a child ***/
        $sxi->addChild('animal', 'Tiger');
    
        /*** a new simpleXML iterator object ***/
        $new = new SimpleXmlIterator($sxi->saveXML());
    
        /*** iterate over the new tree ***/
        foreach($new as $val)
            {
            echo $val.'<br />';
            }
        }
    catch(Exception $e)
        {
        echo $e->getMessage();
        }
    ?>
```

增加属性：

```php
    <?php 
    $xmlstring =<<<XML
    <?xml version = "1.0" encoding="UTF-8" standalone="yes"?>
    <document>
      <animal>koala</animal>
      <animal>kangaroo</animal>
      <animal>wombat</animal>
      <animal>wallaby</animal>
      <animal>emu</animal>
      <animal>kiwi</animal>
      <animal>kookaburra</animal>
      <animal>platypus</animal>
      <animal>funnel web</animal>
    </document>
    XML;
    
    try {
        /*** a new simpleXML iterator object ***/
        $sxi =  new SimpleXMLIterator($xmlstring);
    
        /*** add an attribute with a namespace ***/
        $sxi->addAttribute('id:att1', 'good things', 'urn::test-foo');
    
        /*** add an attribute without a  namespace ***/
        $sxi->addAttribute('att2', 'no-ns');
    
        echo htmlentities($sxi->saveXML());
        }
    catch(Exception $e)
        {
        echo $e->getMessage();
        }
    ?>
```

**16. CachingIterator类**

这个类有一个hasNext()方法，用来判断是否还有下一个元素。

示例如下：

```php
<?php
/*** a simple array ***/
$array = array('koala', 'kangaroo', 'wombat', 'wallaby', 'emu', 'kiwi', 'kookaburra', 'platypus');

try {
    /*** create a new object ***/
    $object = new CachingIterator(new ArrayIterator($array));
    foreach ($object as $value) {
        echo $value;
        if ($object->hasNext()) {
            echo ',';
        }
    }
} catch (Exception $e) {
    echo $e->getMessage();
}

```

**17. LimitIterator类**

这个类用来限定返回结果集的数量和位置，必须提供offset和limit两个参数，与SQL命令中limit语句类似。

示例如下：

```php
<?php
/*** the offset value ***/
$offset = 3;

/*** the limit of records to show ***/
$limit = 2;

$array = array('koala', 'kangaroo', 'wombat', 'wallaby', 'emu', 'kiwi', 'kookaburra', 'platypus');

$it = new LimitIterator(new ArrayIterator($array), $offset, $limit);

foreach ($it as $k => $v) {
    echo $it->getPosition() . '<br />';
}

```

另一个例子是：

```php
<?php

/*** a simple array ***/
$array = array('koala', 'kangaroo', 'wombat', 'wallaby', 'emu', 'kiwi', 'kookaburra', 'platypus');

$it = new LimitIterator(new ArrayIterator($array));

try
{
    $it->seek(5);
    echo $it->current();
} catch (OutOfBoundsException $e) {
    echo $e->getMessage() . "<br />";
}

```

**18. SplFileObject类**

这个类用来对文本文件进行遍历。

示例如下：

```php
<?php

try {
    // iterate directly over the object
    foreach (new SplFileObject("/usr/local/apache/logs/access_log") as $line)
    // and echo each line of the file
    {
        echo $line . '<br />';
    }

} catch (Exception $e) {
    echo $e->getMessage();
}

```

返回文本文件的第三行，可以这样写：

```php
<?php

try {
    $file = new SplFileObject("/usr/local/apache/logs/access_log");

    $file->seek(3);

    echo $file->current();
} catch (Exception $e) {
    echo $e->getMessage();
}

```

[参考文献]

1. [Introduction to Standard PHP Library (SPL), By Kevin Waterson][3]

2. [Introducing PHP 5's Standard Library, By Harry Fuecks][4]

3. [The Standard PHP Library (SPL), By Ben Ramsey][5]

4. [SPL - Standard PHP Library Documentation][6]

（完）

[0]: http://www.ruanyifeng.com
[1]: http://www.ruanyifeng.com/blog/2008/07/
[2]: http://www.php.net/spl
[3]: http://www.phpro.org/tutorials/Introduction-to-SPL.html
[4]: http://www.sitepoint.com/article/php5-standard-library
[5]: http://devzone.zend.com/article/2565-The-Standard-PHP-Library-SPL
[6]: http://www.php.net/~helly/php/ext/spl/
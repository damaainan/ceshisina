# PHP 设计模式系列 —— 迭代器模式（Iterator）

 Posted on [2016年1月3日2016年1月3日][0] by [学院君][1]

### **1、模式定义**

[迭代器模式][2]（[Iterator][3]），又叫做游标（Cursor）模式。提供一种方法访问一个容器（Container）对象中各个元素，而又不需暴露该对象的内部细节。

当你需要访问一个聚合对象，而且不管这些对象是什么都需要[遍历][4]的时候，就应该考虑使用迭代器模式。另外，当需要对聚集有多种方式遍历时，可以考虑去使用迭代器模式。迭代器模式为遍历不同的聚集结构提供如开始、下一个、是否结束、当前哪一项等统一的接口。

[PHP][5]标准库（[SPL][6]）中提供了迭代器接口 Iterator，要实现迭代器模式，实现该接口即可。

### **2、UML类图**

![Iterator-Design-Pattern-UML][7]

### **3、示例代码**

#### **Book.php**

```php
    <?php
    
    namespace DesignPatterns\Behavioral\Iterator;
    
    class Book
    {
    
        private $author;
    
        private $title;
    
        public function __construct($title, $author)
        {
            $this->author = $author;
            $this->title = $title;
        }
    
        public function getAuthor()
        {
            return $this->author;
        }
    
        public function getTitle()
        {
            return $this->title;
        }
    
        public function getAuthorAndTitle()
        {
            return $this->getTitle() . ' by ' . $this->getAuthor();
        }
    }
```
#### **BookList.php**

```php
    <?php
    
    namespace DesignPatterns\Behavioral\Iterator;
    
    class BookList implements \Countable
    {
    
        private $books;
    
        public function getBook($bookNumberToGet)
        {
            if (isset($this->books[$bookNumberToGet])) {
                return $this->books[$bookNumberToGet];
            }
    
            return null;
        }
    
        public function addBook(Book $book)
        {
            $this->books[] = $book;
        }
    
        public function removeBook(Book $bookToRemove)
        {
            foreach ($this->books as $key => $book) {
                /** @var Book $book */
                if ($book->getAuthorAndTitle() === $bookToRemove->getAuthorAndTitle()) {
                    unset($this->books[$key]);
                }
            }
        }
    
        public function count()
        {
            return count($this->books);
        }
    }
```
#### **BookListIterator.php**

```php
    <?php
    
    namespace DesignPatterns\Behavioral\Iterator;
    
    class BookListIterator implements \Iterator
    {
    
        /**
         * @var BookList
         */
        private $bookList;
    
        /**
         * @var int
         */
        protected $currentBook = 0;
    
        public function __construct(BookList $bookList)
        {
            $this->bookList = $bookList;
        }
    
        /**
         * Return the current book
         * @link http://php.net/manual/en/iterator.current.php
         * @return Book Can return any type.
         */
        public function current()
        {
            return $this->bookList->getBook($this->currentBook);
        }
    
        /**
         * (PHP 5 >= 5.0.0)
         *
         * Move forward to next element
         * @link http://php.net/manual/en/iterator.next.php
         * @return void Any returned value is ignored.
         */
        public function next()
        {
            $this->currentBook++;
        }
    
        /**
         * (PHP 5 >= 5.0.0)
         *
         * Return the key of the current element
         * @link http://php.net/manual/en/iterator.key.php
         * @return mixed scalar on success, or null on failure.
         */
        public function key()
        {
            return $this->currentBook;
        }
    
        /**
         * (PHP 5 >= 5.0.0)
         *
         * Checks if current position is valid
         * @link http://php.net/manual/en/iterator.valid.php
         * @return boolean The return value will be casted to boolean and then evaluated.
         *       Returns true on success or false on failure.
         */
        public function valid()
        {
            return null !== $this->bookList->getBook($this->currentBook);
        }
    
        /**
         * (PHP 5 >= 5.0.0)
         *
         * Rewind the Iterator to the first element
         * @link http://php.net/manual/en/iterator.rewind.php
         * @return void Any returned value is ignored.
         */
        public function rewind()
        {
            $this->currentBook = 0;
        }
    }
```
#### **BookListReverseIterator.php**

```php
    <?php
    
    namespace DesignPatterns\Behavioral\Iterator;
    
    class BookListReverseIterator implements \Iterator
    {
    
        /**
         * @var BookList
         */
        private $bookList;
    
        /**
         * @var int
         */
        protected $currentBook = 0;
    
        public function __construct(BookList $bookList)
        {
            $this->bookList = $bookList;
            $this->currentBook = $this->bookList->count() - 1;
        }
    
        /**
         * Return the current book
         * @link http://php.net/manual/en/iterator.current.php
         * @return Book Can return any type.
         */
        public function current()
        {
            return $this->bookList->getBook($this->currentBook);
        }
    
        /**
         * (PHP 5 >= 5.0.0)
         *
         * Move forward to next element
         * @link http://php.net/manual/en/iterator.next.php
         * @return void Any returned value is ignored.
         */
        public function next()
        {
            $this->currentBook--;
        }
    
        /**
         * (PHP 5 >= 5.0.0)
         *
         * Return the key of the current element
         * @link http://php.net/manual/en/iterator.key.php
         * @return mixed scalar on success, or null on failure.
         */
        public function key()
        {
            return $this->currentBook;
        }
    
        /**
         * (PHP 5 >= 5.0.0)
         *
         * Checks if current position is valid
         * @link http://php.net/manual/en/iterator.valid.php
         * @return boolean The return value will be casted to boolean and then evaluated.
         *       Returns true on success or false on failure.
         */
        public function valid()
        {
            return null !== $this->bookList->getBook($this->currentBook);
        }
    
        /**
         * (PHP 5 >= 5.0.0)
         *
         * Rewind the Iterator to the first element
         * @link http://php.net/manual/en/iterator.rewind.php
         * @return void Any returned value is ignored.
         */
        public function rewind()
        {
            $this->currentBook = $this->bookList->count() - 1;
        }
    }
```
### **4、测试代码**

#### **Tests/IteratorTest.php**

```php
    <?php
    
    namespace DesignPatterns\Behavioral\Iterator\Tests;
    
    use DesignPatterns\Behavioral\Iterator\Book;
    use DesignPatterns\Behavioral\Iterator\BookList;
    use DesignPatterns\Behavioral\Iterator\BookListIterator;
    use DesignPatterns\Behavioral\Iterator\BookListReverseIterator;
    
    class IteratorTest extends \PHPUnit\Framework\TestCase
    {
    
        /**
         * @var BookList
         */
        protected $bookList;
    
        protected function setUp()
        {
            $this->bookList = new BookList();
            $this->bookList->addBook(new Book('Learning PHP Design Patterns', 'William Sanders'));
            $this->bookList->addBook(new Book('Professional Php Design Patterns', 'Aaron Saray'));
            $this->bookList->addBook(new Book('Clean Code', 'Robert C. Martin'));
        }
    
        public function expectedAuthors()
        {
            return array(
                array(
                    array(
                        'Learning PHP Design Patterns by William Sanders',
                        'Professional Php Design Patterns by Aaron Saray',
                        'Clean Code by Robert C. Martin'
                    )
                ),
            );
        }
    
        /**
         * @dataProvider expectedAuthors
         */
        public function testUseAIteratorAndValidateAuthors($expected)
        {
            $iterator = new BookListIterator($this->bookList);
    
            while ($iterator->valid()) {
                $expectedBook = array_shift($expected);
                $this->assertEquals($expectedBook, $iterator->current()->getAuthorAndTitle());
                $iterator->next();
            }
        }
    
        /**
         * @dataProvider expectedAuthors
         */
        public function testUseAReverseIteratorAndValidateAuthors($expected)
        {
            $iterator = new BookListReverseIterator($this->bookList);
    
            while ($iterator->valid()) {
                $expectedBook = array_pop($expected);
                $this->assertEquals($expectedBook, $iterator->current()->getAuthorAndTitle());
                $iterator->next();
            }
        }
    
        /**
         * Test BookList Remove
         */
        public function testBookRemove()
        {
            $this->bookList->removeBook($this->bookList->getBook(0));
            $this->assertEquals($this->bookList->count(), 2);
        }
    }
```

[0]: http://laravelacademy.org/post/2882.html
[1]: http://laravelacademy.org/post/author/nonfu
[2]: http://laravelacademy.org/tags/%e8%bf%ad%e4%bb%a3%e5%99%a8%e6%a8%a1%e5%bc%8f
[3]: http://laravelacademy.org/tags/iterator
[4]: http://laravelacademy.org/tags/%e9%81%8d%e5%8e%86
[5]: http://laravelacademy.org/tags/php
[6]: http://laravelacademy.org/tags/spl
[7]: ../img/Iterator-Design-Pattern-UML.png
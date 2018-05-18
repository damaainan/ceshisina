<?php
namespace app;

use Iterator;
use ArrayAccess;
use Countable;
use Serializable;
use SplQueue;
use SplDoublyLinkedList;

/**
 * Class CubeMsgSubscribeController
 * @package app
 * @author dongchao
 * @email dongchao@bigo.sg
 * @link https://www.cubetv.sg
 */
abstract class CourseExample implements
    //如果需要继承的类，需要实现的接口太多，120个字符内写不完，建议换行。每行一个类名、接口名
    Iterator,
    ArrayAccess,
    Countable,
    Serializable
{

    /**
     * @var array 课程列表
     */
    private $course = [
        "Math",
        "English",
        "Biology",
        "Physics",
        "Chemistry"
    ];

    /**
     * @var array 教师列表
     */
    private $teacher = [
        "Jackson",
        "Lucy",
        "Lily",
        "Bruce",
        "John"
    ];

    /**
     * @var array 教师 - 课程列表
     */
    private static $teacherCoursemap = [];

    /**
     * @var int 数组当前下标
     */
    private $position;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->position = 0;
    }

    /**
     * 注意 abstract protected static修饰符的顺序
     */
    abstract protected function zim();

    /**
     * 注意 final public static修饰符的顺序
     */
    final public static function bar()
    {
        $queue = new SplQueue();
        $queue->push('Music');
        $queue->pop();
    }

    /**
     * 测试方法
     */
    public function insertData()
    {
        $msg = 'Hello World';
        //如果调用方法或者函数时，参数太多，建议写成多行，每行一个参数
        $this->matchTeacher(
            1,
            2,
            function () use ($msg) {
                //注意闭包函数括号、参数、花括号之间的空格与缩进
                echo $msg;
            }
        );
    }

    /**
     * 注意return注释，类型是SplDoublyLinkedList
     * @return SplDoublyLinkedList
     */
    public function getLinkedList()
    {
        return new SplDoublyLinkedList();
    }

    /**
     * 计算课程数目
     * 注意方法名称使用驼峰式命名
     * @return int 课程数目
     */
    public function courseNum()
    {
        return sizeof($this->course);
    }

    /**
     * 课程与老师配对
     * 如果参数个数太多，超过120个字符，建议换行。每行一个
     * @param int $courseNo 课程编号
     * @param int $teacherNo 教师编号
     * @param Callable $callbackFunc 回调函数
     */
    public function matchTeacher(
        $courseNo,
        $teacherNo,
        $callbackFunc
        //如果参数个数太多，超过120个字符，建议换行。每行一个
    ) {
        self::$teacherCoursemap[$this->course[$courseNo]] = $this->teacher[$teacherNo];
        call_user_func($callbackFunc);
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        return $this->course[$this->position];
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        return isset($this->course[$this->position]);
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return true;
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return null;
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
    }

    /**
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        return '';
    }

    /**
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
    }

    /**
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        return 1;
    }
}
<?php
namespace iterator;

/**
 * 老师迭代实体
 */
class TeacherIterator implements Iterator
{

  /**
   * 索引值
   * @var integer
   */
    private $index = 0;

  /**
   * 要迭代的对象
   * @var object
   */
    private $teachers;

  /**
   * 构造函数
   *
   * @param School $school
   */
    public function __construct(School $school)
    {
        $this->teachers = $school->teachers;
    }

  /**
   * 是否还有下一个
   *
   * @return boolean
   */
    public function hasNext()
    {
        if ($this->index < count($this->teachers)) {
            return true;
        }
        return false;
    }

  /**
   * 下一个
   *
   * @return object
   */
    public function next()
    {
        if (!$this->hasNext()) {
            echo null;
            return;
        }
        $index = $this->index + 1;
        echo $this->teachers[$index];
    }

  /**
   * 当前
   *
   * @return mixed
   */
    public function current()
    {
        if (!isset($this->teachers[$this->index])) {
            echo  null;
            return;
        }
        $current = $this->teachers[$this->index];
        $this->index += 1;
        echo $current . "\n";
    }

  /**
   * 当前索引
   *
   * @return integer
   */
    public function index()
    {
        echo $this->index;
    }
}

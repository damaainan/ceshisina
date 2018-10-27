<?php
namespace mediator;

/**
 * 抽象类人
 */
abstract class Person
{
  /**
   * 名字
   * @var string
   */
    private $name = '';

  /**
   * 构造函数
   */
    public function __construct($name)
    {
        $this->name = $name;
    }

  /**
   * 魔术方法
   * 读取私有属性
   *
   * @param  string $name 属性名称
   * @return mixed
   */
    public function __get($name = '')
    {
        // $name = '_' . $name;
        return $this->$name;
    }

  /**
   * 抽象方法
   *
   * @return mixed
   */
    abstract public function doSomthing(Person $person);
}

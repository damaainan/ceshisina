<?php
namespace facade;

/**
 * 外观类
 */
class AnimalMaker
{
  /**
   * 鸡实工厂例
   * @var object
   */
    private $chicken;

  /**
   * 猪实工厂例
   * @var object
   */
    private $pig;

  /**
   * 构造函数
   *
   * @return void
   */
    public function __construct()
    {
        $this->chicken = new Chicken();
        $this->pig     = new Pig();
    }

  /**
   * 生产方法
   *
   * 生产鸡
   * @return string
   */
    public function produceChicken()
    {
        $this->chicken->produce();
    }

  /**
   * 生产方法
   *
   * 生产猪
   * @return string
   */
    public function producePig()
    {
        $this->pig->produce();
    }
}

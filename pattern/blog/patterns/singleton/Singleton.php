<?php
namespace singleton;

/**
 * 单例
 */
class Singleton
{
  /**
   * 自身实例
   *
   * @var object
   */
    private static $instance;

  /**
   * 构造函数
   *
   * @return void
   */
    private function __construct()
    {
    }

  /**
   * 魔法方法
   * 禁止clone对象
   *
   * @return string
   */
    public function __clone()
    {
        echo 'clone is forbidden';
    }

  /**
   * 获取实例
   *
   * @return object
   */
    public static function getInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self;
        }
        return self::$instance;
    }

  /**
   * 测试方法
   *
   * @return string
   */
    public function test()
    {
        echo "这是个测试 \n";
    }
}

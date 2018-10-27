<?php
namespace state;

/**
 * 农耕接口
 */
class FarmSpring implements Farm
{
  /**
   * 作物名称
   * @var string
   */
    private $name = '玉米';

  /**
   * 种植
   *
   * @return string
   */
    public function grow()
    {
        echo "种植了一片 {$this->name} \n";
    }

  /**
   * 收割
   *
   * @return string
   */
    public function harvest()
    {
        echo "收获了一片 {$this->name} \n";
    }
}

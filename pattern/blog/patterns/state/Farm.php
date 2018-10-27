<?php
namespace state;

/**
 * 农耕接口
 */
interface Farm
{
  /**
   * 种植
   *
   * @return string
   */
    public function grow();

  /**
   * 收割
   *
   * @return string
   */
    public function harvest();
}

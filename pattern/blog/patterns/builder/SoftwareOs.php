<?php
namespace builder;

/**
 * 操作系统实体
 */
class SoftwareOs implements Software
{
  public function produce($os='android')
  {
    echo "操作系统：" . $os . "\n";
  }
}

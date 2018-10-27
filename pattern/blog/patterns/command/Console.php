<?php
namespace command;

/**
 * 控制台
 * 负责命令执行
 */
class Console
{
  /**
   * 命令队列
   * @var array
   */
    private $orderList = [];

  /**
   * 添加命令到队列
   * @param Order $order
   */
    public function add(Order $order)
    {
        array_push($this->orderList, $order);
    }

  /**
   * 执行命令
   * @return mixed
   */
    public function run()
    {
        foreach ($this->orderList as $k => $v) {
            $v->execute();
        }
    }
}

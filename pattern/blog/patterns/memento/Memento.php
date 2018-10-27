<?php
namespace memento;

class Memento
{
  /**
   * 备忘录列表
   *
   * @var array
   */
    private $mementoList = [];

  /**
   * 添加编辑器实例状态
   *
   * @param Editor $editor 编辑器实例
   */
    public function add(Editor $editor)
    {
        array_push($this->mementoList, $editor);
    }

  /**
   * 返回编辑器实例上个状态
   *
   * @param Editor $editor 编辑器实例
   */
    public function undo()
    {
        return array_pop($this->mementoList);
    }

  /**
   * 返回编辑器实例开始状态
   *
   * @param Editor $editor 编辑器实例
   */
    public function redo()
    {
        return array_shift($this->mementoList);
    }
}
